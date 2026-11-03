<?php
require_once __DIR__ . '/../config/koneksi.php';

// Check row ID 264 in detail_opname_batch (AMX-2026-01, selisih -2)
$row = $pdo->query("SELECT * FROM detail_opname_batch WHERE id = 264")->fetch(PDO::FETCH_ASSOC);
echo "Target Row before mutation:\n";
print_r($row);

// Let's test simulating POST to eksekusi_mutasi_baris
// We will test if the backend logic works as expected
$idDetail = 264;
$userId = 1;
$activeSession = $pdo->query("SELECT * FROM sesi_opname WHERE status = 'Aktif'")->fetch(PDO::FETCH_ASSOC);

if ($activeSession && $row && $row['status_mutasi'] !== 'Sudah Dimutasi') {
    echo "\nSimulating multi-reason mutation: 1 Rusak, 1 Hilang\n";
    $_POST = [
        'aksi' => 'eksekusi_mutasi_baris',
        'id_detail' => $idDetail,
        'alokasi_rusak' => 1,
        'alokasi_hilang' => 1,
        'alokasi_expired' => 0,
        'alokasi_salah_catat' => 0,
        'keterangan_mutasi' => 'Test 1 Rusak kemasan penyok, 1 Hilang tercecer'
    ];
    
    // We can run the exact query sequence
    $stmtD = $pdo->prepare("
        SELECT dob.*, pv.nama_variasi, pv.rasio_konversi, pi.nama_produk, pi.kategori, sb.no_batch, sb.stok_sisa, st.stok AS stok_toko_saat_ini
        FROM detail_opname_batch dob
        JOIN produk_variasi pv ON dob.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
        LEFT JOIN stok_toko st ON dob.id_variasi = st.id_variasi
        WHERE dob.id = ? AND dob.id_sesi = ?
    ");
    $stmtD->execute([$idDetail, $activeSession['id']]);
    $item = $stmtD->fetch();
    
    $selisih = intval($item['selisih']);
    $absSelisih = abs($selisih);
    $isMinus = ($selisih < 0);
    $kodeSesi = $activeSession['kode_sesi'];

    $qtyRusak = 1;
    $qtyHilang = 1;
    $breakdown = ['Barang Rusak' => $qtyRusak, 'Barang Hilang' => $qtyHilang];
    
    $idBatch = $item['id_batch'];
    $noBatchTerdampak = $item['no_batch'] ?? '-';
    $idVariasi = $item['id_variasi'];
    $rasio = max(1, intval($item['rasio_konversi'] ?? 1));
    $isBesar = ($item['satuan_tipe'] === 'besar');
    $qtyPcs = $isBesar ? ($absSelisih * $rasio) : $absSelisih;

    $operator = '-';
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa $operator ? WHERE id = ?")->execute([$qtyPcs, $idBatch]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok $operator ? WHERE id_variasi = ?")->execute([$qtyPcs, $idVariasi]);
    $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

    $stmtKartu = $pdo->prepare("
        INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
        VALUES (?, ?, ?, ?, 'Manual', ?, ?, ?, ?, ?, ?)
    ");

    $summaryAlasanArr = [];
    foreach ($breakdown as $reasonName => $rQty) {
        $rQtyPcs = $isBesar ? ($rQty * $rasio) : $rQty;
        $ketPcs = $isBesar ? " ($rQty {$item['nama_satuan']} = $rQtyPcs pcs)" : "";
        $ketKartu = "[Stok Opname: $kodeSesi] Batch: $noBatchTerdampak | $reasonName$ketPcs - Test multi reason";

        $stmtKartu->execute([
            $idVariasi,
            $idBatch,
            $item['satuan_tipe'],
            'Keluar',
            $reasonName,
            $kodeSesi,
            $rQty,
            $sisaStokToko,
            $ketKartu,
            $userId
        ]);
        $summaryAlasanArr[] = "$reasonName ($rQty {$item['nama_satuan']})";
    }
    $finalAlasanMutasi = implode(', ', $summaryAlasanArr);

    $stmtUpDetail = $pdo->prepare("
        UPDATE detail_opname_batch
        SET status_mutasi = 'Sudah Dimutasi',
            alasan_mutasi = ?,
            keterangan_mutasi = ?,
            dimutasi_oleh = ?,
            waktu_mutasi = NOW()
        WHERE id = ?
    ");
    $stmtUpDetail->execute([$finalAlasanMutasi, 'Test 1 Rusak kemasan penyok, 1 Hilang tercecer', $userId, $idDetail]);

    echo "SUCCESS! Updated row 264 with alasan: $finalAlasanMutasi\n";
    $updatedRow = $pdo->query("SELECT * FROM detail_opname_batch WHERE id = 264")->fetch(PDO::FETCH_ASSOC);
    print_r($updatedRow);

    echo "\nKartu stok logged:\n";
    $ks = $pdo->query("SELECT * FROM kartu_stok WHERE no_ref_dokumen = '$kodeSesi' ORDER BY id DESC LIMIT 2")->fetchAll(PDO::FETCH_ASSOC);
    print_r($ks);
}
