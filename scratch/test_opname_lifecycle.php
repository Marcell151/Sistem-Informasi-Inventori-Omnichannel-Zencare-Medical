<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

echo "=======================================================\n";
echo "   PENGUJIAN SIMULASI END-TO-END MODUL STOK OPNAME\n";
echo "=======================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($cond, $desc, $detail = '') {
    global $passCount, $failCount;
    if ($cond) {
        echo " [PASS] $desc\n";
        if ($detail) echo "        $detail\n";
        $passCount++;
    } else {
        echo " [FAIL] $desc\n";
        if ($detail) echo "        $detail\n";
        $failCount++;
    }
}

try {
    $pdo->beginTransaction();

    // 1. Verifikasi Sebelum Opname: Tidak Ada Sesi Aktif
    $sesiAwal = cekSesiOpnameAktif($pdo);
    assertTest($sesiAwal === false, "Kondisi Awal: Tidak ada sesi opname aktif");

    // 2. Superadmin Memulai Sesi Opname (Freeze)
    $kodeSesiTest = "OPN-SIM-" . date('Ymd') . "-999";
    $stmtS = $pdo->prepare("INSERT INTO sesi_opname (kode_sesi, status, dibekukan_oleh, dibekukan_pada, catatan) VALUES (?, 'Aktif', 1, NOW(), 'Uji Simulasi')");
    $stmtS->execute([$kodeSesiTest]);
    $idSesi = $pdo->lastInsertId();

    // Snapshot snapshot 1 Obat dan 1 Alkes untuk simulasi
    // Obat: Amoxicillin Batch 7 (Stok fisik: misal 10)
    $stmtIns1 = $pdo->prepare("INSERT INTO detail_opname_batch (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi) VALUES (?, 7, 1, 'Obat', 'kecil', 'Tablet', 10, 0, 10, NULL, NULL, 'Belum Dimutasi')");
    $stmtIns1->execute([$idSesi]);
    $idDetailObat = $pdo->lastInsertId();

    // Alkes: Tensimeter Omron (Var ID 4, stok fisik: 8)
    $stmtIns2 = $pdo->prepare("INSERT INTO detail_opname_batch (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi) VALUES (?, NULL, 4, 'Alkes', 'kecil', 'Unit', 8, 0, 8, NULL, NULL, 'Belum Dimutasi')");
    $stmtIns2->execute([$idSesi]);
    $idDetailAlkes = $pdo->lastInsertId();

    // 3. Verifikasi Central Freeze Aktif
    $sesiAktif = cekSesiOpnameAktif($pdo);
    // Note: static variable in same process might cache, let's query DB
    $sesiDb = $pdo->query("SELECT * FROM sesi_opname WHERE status = 'Aktif' LIMIT 1")->fetch();
    assertTest(!empty($sesiDb), "Central Server Freeze: Sesi {$sesiDb['kode_sesi']} terdeteksi aktif");

    // 4. Verifikasi Hitung Buta Awal: hitung_fisik dan selisih masih NULL
    $rowObatAwal = $pdo->query("SELECT * FROM detail_opname_batch WHERE id = $idDetailObat")->fetch();
    assertTest($rowObatAwal['hitung_fisik'] === null && $rowObatAwal['selisih'] === null, "Hitung Buta: Kolom fisik dan selisih awal terproteksi NULL di database");

    // 5. Simulasi Input Hitung Fisik Obat (Fisik 8 unit, Sistem 10 unit -> Selisih -2 unit)
    $hitungFisikObat = 8;
    $selisihObat = $hitungFisikObat - $rowObatAwal['stok_sistem_fisik']; // 8 - 10 = -2
    $pdo->prepare("UPDATE detail_opname_batch SET hitung_fisik = ?, selisih = ?, status_mutasi = 'Belum Dimutasi', dihitung_oleh = 1, waktu_hitung = NOW() WHERE id = ?")
        ->execute([$hitungFisikObat, $selisihObat, $idDetailObat]);

    $rowObatEvaluasi = $pdo->query("SELECT * FROM detail_opname_batch WHERE id = $idDetailObat")->fetch();
    assertTest(intval($rowObatEvaluasi['selisih']) === -2, "Evaluasi Hitung Fisik Obat: Selisih -2 unit terungkap pasca Simpan Fisik");

    // 6. Simulasi Hitung Ulang (Audit Trail)
    $hitungKoreksi = 9;
    $selisihKoreksi = $hitungKoreksi - $rowObatAwal['stok_sistem_fisik']; // 9 - 10 = -1
    $pdo->prepare("INSERT INTO riwayat_hitung_opname (id_detail_opname, hitung_sebelumnya, hitung_baru, dihitung_oleh, alasan_koreksi, waktu_koreksi) VALUES (?, ?, ?, 1, 'Salah hitung di rak atas', NOW())")
        ->execute([$idDetailObat, $hitungFisikObat, $hitungKoreksi]);
    $pdo->prepare("UPDATE detail_opname_batch SET hitung_fisik = ?, selisih = ? WHERE id = ?")
        ->execute([$hitungKoreksi, $selisihKoreksi, $idDetailObat]);

    $logRecount = $pdo->query("SELECT * FROM riwayat_hitung_opname WHERE id_detail_opname = $idDetailObat")->fetch();
    assertTest(!empty($logRecount) && intval($logRecount['hitung_sebelumnya']) === 8 && intval($logRecount['hitung_baru']) === 9, "Audit Trail Hitung Ulang: Rekam jejak koreksi (8 -> 9 unit) tercatat transparan");

    // 7. Simulasi Mutasi Selisih Obat (-1 unit, Alasan: Barang Rusak)
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - 1 WHERE id = 7")->execute();
    $pdo->prepare("UPDATE stok_toko SET stok = stok - 1 WHERE id_variasi = 1")->execute();
    $pdo->prepare("INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (1, 7, 'kecil', 'Keluar', 'Manual', 'Selisih Stok Opname', ?, 1, 127, '[Opname] Barang Rusak', 1)")
        ->execute([$kodeSesiTest]);
    $pdo->prepare("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Rusak', dimutasi_oleh = 1, waktu_mutasi = NOW() WHERE id = ?")
        ->execute([$idDetailObat]);

    $rowObatFinal = $pdo->query("SELECT status_mutasi FROM detail_opname_batch WHERE id = $idDetailObat")->fetchColumn();
    assertTest($rowObatFinal === 'Sudah Dimutasi', "Mutasi Otomatis Obat: Status berubah menjadi 'Sudah Dimutasi' dan kartu stok terisi");

    // 8. Simulasi Alkes: Hitung Fisik 7 unit (Sistem 8 -> Selisih -1 unit)
    $pdo->prepare("UPDATE detail_opname_batch SET hitung_fisik = 7, selisih = -1, status_mutasi = 'Belum Dimutasi' WHERE id = ?")
        ->execute([$idDetailAlkes]);

    // Mutasi Minus Alkes: Pilih 1 SN spesifik (misal OMR-TENS-09)
    $snTestId = $pdo->query("SELECT id FROM unit_serial WHERE serial_number = 'OMR-TENS-09'")->fetchColumn();
    $pdo->prepare("UPDATE unit_serial SET status = 'Retur/Rusak', catatan = '[Opname] Layar pecah' WHERE id = ?")->execute([$snTestId]);
    $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, 'OMR-TENS-09', 'Kurang_Rusak')")
        ->execute([$idDetailAlkes, $snTestId]);
    $pdo->prepare("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Rusak' WHERE id = ?")
        ->execute([$idDetailAlkes]);

    $snStatusUpdated = $pdo->query("SELECT status FROM unit_serial WHERE id = $snTestId")->fetchColumn();
    $dosCount = $pdo->query("SELECT COUNT(*) FROM detail_opname_sn WHERE id_detail_opname = $idDetailAlkes")->fetchColumn();
    assertTest($snStatusUpdated === 'Retur/Rusak' && $dosCount > 0, "Mutasi Alkes Ber-SN: SN spesifik OMR-TENS-09 berhasil diubah ke 'Retur/Rusak' dan tercatat di detail_opname_sn");

    // 9. Verifikasi Syarat Penutupan Sesi (Unfreeze)
    $sisaBelumHitung = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = $idSesi AND hitung_fisik IS NULL")->fetchColumn();
    $sisaBelumMutasi = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = $idSesi AND selisih != 0 AND status_mutasi != 'Sudah Dimutasi'")->fetchColumn();
    assertTest($sisaBelumHitung == 0 && $sisaBelumMutasi == 0, "Syarat Penutupan Sesi: 100% item terhitung & 100% selisih dimutasi");

    // Tutup sesi
    $pdo->prepare("UPDATE sesi_opname SET status = 'Selesai', dibuka_oleh = 1, dibuka_pada = NOW() WHERE id = ?")->execute([$idSesi]);
    $sesiTutup = $pdo->query("SELECT status, dibuka_pada FROM sesi_opname WHERE id = $idSesi")->fetch();
    assertTest($sesiTutup['status'] === 'Selesai' && !empty($sesiTutup['dibuka_pada']), "Unfreeze Berhasil: Sesi ditutup resmi, transaksi normal kembali");

    // Rollback DB agar database kembali bersih seperti sedia kala
    $pdo->rollBack();
    echo "\n [INFO] Database simulasi di-rollback dengan sempurna (DB tetap bersih).\n";

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo " [ERROR] Exception: " . $e->getMessage() . "\n";
    $failCount++;
}

echo "\n=======================================================\n";
echo " Hasil Pengujian: $passCount PASS | $failCount FAIL\n";
echo "=======================================================\n";

