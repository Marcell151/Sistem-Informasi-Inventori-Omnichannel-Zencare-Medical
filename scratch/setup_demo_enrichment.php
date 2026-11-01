<?php
require 'config/koneksi.php';

try {
    $pdo->beginTransaction();

    echo "=== 1. MENYESUAIKAN TANGGAL KEDALUWARSA (2 & 3 NOVEMBER 2026) ===\n";
    // Sanmol Sirup: 2026-11-02
    $pdo->query("UPDATE stok_batch SET tgl_exp = '2026-11-02' WHERE id = 3");
    // Bodrex Migra: 2026-11-03
    $pdo->query("UPDATE stok_batch SET tgl_exp = '2026-11-03' WHERE id = 4");
    // CTM: 2026-11-05
    $pdo->query("UPDATE stok_batch SET tgl_exp = '2026-11-05' WHERE id = 5");
    echo "[OK] Tanggal kedaluwarsa berhasil diperbarui.\n";

    echo "=== 2. MENAMBAHKAN VARIASI STATUS RETUR (POTONG FAKTUR) ===\n";
    // Set Decolgen retur di KUL-20261024-CAMPUR-RETUR menjadi selesai_potong_faktur
    $pdo->query("
        UPDATE penerimaan_detail 
        SET status_retur = 'selesai_potong_faktur',
            catatan_retur = 'Kompensasi disepakati potong tagihan faktur tunai karena stok distributor pengganti habis',
            tgl_selesai_retur = '2026-10-25 15:30:00'
        WHERE id = 27
    ");
    echo "[OK] Status retur potong faktur berhasil diterapkan.\n";

    echo "=== 3. MEMBUAT SESI STOK OPNAME HISTORIS (SELESAI & LENGKAP) ===\n";
    // Cek apakah sesi OPN-20261015-001 sudah ada
    $cekSesi = $pdo->query("SELECT id FROM sesi_opname WHERE kode_sesi = 'OPN-20261015-001'")->fetchColumn();
    if ($cekSesi) {
        $idSesiHistori = $cekSesi;
        echo "[INFO] Sesi OPN-20261015-001 sudah ada (ID: $idSesiHistori).\n";
    } else {
        $stmtInsSesi = $pdo->prepare("
            INSERT INTO sesi_opname (kode_sesi, status, dibekukan_oleh, dibekukan_pada, dibuka_oleh, dibuka_pada, catatan)
            VALUES ('OPN-20261015-001', 'Selesai', 1, '2026-10-15 08:00:00', 1, '2026-10-15 11:30:00', 'Stok Opname Triwulan III - Audit Fisik Berkala Seluruh Obat dan Alkes (Selesai 100% Rekonsiliasi)')
        ");
        $stmtInsSesi->execute();
        $idSesiHistori = $pdo->lastInsertId();
        echo "[OK] Sesi historis OPN-20261015-001 berhasil dibuat (ID: $idSesiHistori).\n";

        // Ambil snapshot produk variasi & batch obat
        // Snapshot 1: Batch Obat
        $stmtObat = $pdo->query("
            SELECT sb.id AS id_batch, sb.id_variasi, sb.stok_sisa, sb.no_batch,
                   pv.satuan_kecil, pi.nama_produk, pv.nama_variasi
            FROM stok_batch sb
            JOIN produk_variasi pv ON sb.id_variasi = pv.id
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE pi.kategori != 'Alat Kesehatan'
            ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
        ");
        $batchObat = $stmtObat->fetchAll(PDO::FETCH_ASSOC);

        // Snapshot 2: Alkes
        $stmtAlkes = $pdo->query("
            SELECT pv.id AS id_variasi, st.stok,
                   pv.satuan_kecil, pi.nama_produk, pv.nama_variasi
            FROM produk_variasi pv
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            LEFT JOIN stok_toko st ON pv.id = st.id_variasi
            WHERE pi.kategori = 'Alat Kesehatan'
            ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
        ");
        $alkesList = $stmtAlkes->fetchAll(PDO::FETCH_ASSOC);

        $stmtInsDetail = $pdo->prepare("
            INSERT INTO detail_opname_batch 
            (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi, alasan_mutasi, dihitung_oleh, waktu_hitung, dimutasi_oleh, waktu_mutasi)
            VALUES (?, ?, ?, ?, 'kecil', ?, ?, 0, ?, ?, ?, ?, ?, 1, '2026-10-15 10:30:00', ?, ?)
        ");

        $itemCount = 0;
        // Insert detail obat
        foreach ($batchObat as $idx => $b) {
            $stokFisik = (int)$b['stok_sisa'];
            $hitung = $stokFisik;
            $sel = 0;
            $statusMutasi = 'Belum Dimutasi';
            $alasan = null;
            $dimutasiOleh = null;
            $waktuMutasi = null;

            // Berikan 2 sampel selisih yang sudah dimutasi
            if ($idx === 3) {
                // Sampel 1: Kurang 1 unit karena kemasan rusak
                $hitung = max(0, $stokFisik - 1);
                $sel = -1;
                $statusMutasi = 'Sudah Dimutasi';
                $alasan = 'Barang Rusak';
                $dimutasiOleh = 1;
                $waktuMutasi = '2026-10-15 11:10:00';
            } elseif ($idx === 7) {
                // Sampel 2: Kurang 2 unit karena pemusnahan kedaluwarsa
                $hitung = max(0, $stokFisik - 2);
                $sel = -2;
                $statusMutasi = 'Sudah Dimutasi';
                $alasan = 'Barang Kedaluwarsa';
                $dimutasiOleh = 1;
                $waktuMutasi = '2026-10-15 11:15:00';
            }

            $stmtInsDetail->execute([
                $idSesiHistori,
                $b['id_batch'],
                $b['id_variasi'],
                'Obat',
                $b['satuan_kecil'] ?: 'Pcs',
                $stokFisik,
                $stokFisik,
                $hitung,
                $sel,
                $statusMutasi,
                $alasan,
                $dimutasiOleh,
                $waktuMutasi
            ]);
            $itemCount++;
        }

        // Insert detail alkes
        foreach ($alkesList as $idx => $a) {
            $stokFisik = (int)($a['stok'] ?? 0);
            $hitung = $stokFisik;
            $sel = 0;
            $statusMutasi = 'Belum Dimutasi';
            $alasan = null;
            $dimutasiOleh = null;
            $waktuMutasi = null;

            // Sampel Alkes selisih 1 unit rusak/cacat
            if ($idx === 2 && $stokFisik > 2) {
                $hitung = $stokFisik - 1;
                $sel = -1;
                $statusMutasi = 'Sudah Dimutasi';
                $alasan = 'Barang Rusak';
                $dimutasiOleh = 1;
                $waktuMutasi = '2026-10-15 11:20:00';
            }

            $stmtInsDetail->execute([
                $idSesiHistori,
                null,
                $a['id_variasi'],
                'Alkes',
                $a['satuan_kecil'] ?: 'Unit',
                $stokFisik,
                $stokFisik,
                $hitung,
                $sel,
                $statusMutasi,
                $alasan,
                $dimutasiOleh,
                $waktuMutasi
            ]);
            $itemCount++;
        }

        echo "[OK] Berhasil memasukkan $itemCount baris detail opname untuk sesi OPN-20261015-001.\n";

        // Catat histori kartu stok untuk rekonsiliasi opname 15 Oktober 2026
        $stmtKS = $pdo->prepare("
            INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh, tanggal)
            VALUES (?, ?, 'kecil', 'Keluar', 'Manual', 'Selisih Stok Opname', 'OPN-20261015-001', ?, ?, ?, 1, ?)
        ");
        $stmtKS->execute([$batchObat[3]['id_variasi'], $batchObat[3]['id_batch'], 1, (int)$batchObat[3]['stok_sisa'], "[Stok Opname: OPN-20261015-001] Batch: {$batchObat[3]['no_batch']} | Barang Rusak - Kemasan pecah saat penataan", '2026-10-15 11:10:00']);
        $stmtKS->execute([$batchObat[7]['id_variasi'], $batchObat[7]['id_batch'], 2, (int)$batchObat[7]['stok_sisa'], "[Stok Opname: OPN-20261015-001] Batch: {$batchObat[7]['no_batch']} | Barang Kedaluwarsa - Pemusnahan obat expired", '2026-10-15 11:15:00']);
        $stmtKS->execute([$alkesList[2]['id_variasi'], null, 1, (int)$alkesList[2]['stok'], "[Stok Opname: OPN-20261015-001] SN: OMR-TENS-RET1 | Barang Rusak - Cacat pabrik display bergaris", '2026-10-15 11:20:00']);

        echo "[OK] Kartu stok historis opname berhasil dicatat.\n";
    }

    $pdo->commit();
    echo "=== SEMUA PENYESUAIAN BERHASIL DISIMPAN KE MYSQL ===\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "[ERROR] " . $e->getMessage() . "\n";
}

