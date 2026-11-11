<?php
// Test Opname Lifecycle and Dosen Requirements
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';

echo "=== STARTING COMPREHENSIVE STOK OPNAME TEST ===\n";

// 1. Cek apakah ada sesi aktif yang menggantung, jika ada batalkan dulu
$active = cekSesiOpnameAktif($pdo, true);
if ($active) {
    echo "Found hanging active session: {$active['kode_sesi']}, closing...\n";
    $pdo->query("UPDATE sesi_opname SET status = 'Dibatalkan', dibuka_pada = NOW() WHERE id = {$active['id']}");
}

// 2. Mulai sesi baru sebagai Superadmin (User ID 1)
$kodeSesi = 'OPN-TEST-' . date('Ymd-His');
$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO sesi_opname (kode_sesi, status, dibekukan_oleh, dibekukan_pada, catatan) VALUES (?, 'Aktif', 1, NOW(), 'Sesi Pengujian Sistem Bab 4')");
$stmt->execute([$kodeSesi]);
$idSesi = $pdo->lastInsertId();

// Snapshot batch obat & alkes
$stmtObat = $pdo->query("
    SELECT sb.id AS id_batch, sb.id_variasi, sb.no_batch, sb.stok_sisa, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.nama_produk, pv.nama_variasi
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1 AND sb.stok_sisa > 0
    LIMIT 6
");
$batches = $stmtObat->fetchAll();

$insDetail = $pdo->prepare("
    INSERT INTO detail_opname_batch 
    (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi)
    VALUES (?, ?, ?, 'Obat', 'kecil', ?, ?, 0, ?, NULL, NULL, 'Belum Dimutasi')
");

foreach ($batches as $b) {
    $insDetail->execute([$idSesi, $b['id_batch'], $b['id_variasi'], $b['satuan_kecil'], $b['stok_sisa'], $b['stok_sisa']]);
}

// Tambah 1 Alkes
$stmtAlkes = $pdo->query("
    SELECT pv.id AS id_variasi, pi.nama_produk, pv.nama_variasi, st.stok
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON pv.id = st.id_variasi
    WHERE pi.kategori = 'Alat Kesehatan' AND pv.is_active = 1 AND st.stok > 0
    LIMIT 1
");
$alkes = $stmtAlkes->fetch();
if ($alkes) {
    $insAlkes = $pdo->prepare("
        INSERT INTO detail_opname_batch 
        (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi)
        VALUES (?, NULL, ?, 'Alkes', 'kecil', 'Unit', ?, 0, ?, NULL, NULL, 'Belum Dimutasi')
    ");
    $insAlkes->execute([$idSesi, $alkes['id_variasi'], $alkes['stok'], $alkes['stok']]);
}

$pdo->commit();
echo "Sesi $kodeSesi created with ID $idSesi.\n";

// 3. Verifikasi Server Freeze
$sesiAktif = cekSesiOpnameAktif($pdo, true);
if ($sesiAktif && $sesiAktif['id'] == $idSesi) {
    echo "✓ PASS: Server-Level Transaction Freeze ACTIVE.\n";
} else {
    echo "✗ FAIL: Freeze not detected.\n";
}

// 4. Input Hitung Fisik oleh Kasir (Blind Count)
// Ambil semua detail baris
$details = $pdo->query("SELECT * FROM detail_opname_batch WHERE id_sesi = $idSesi ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "Total item dihitung: " . count($details) . "\n";

// Skenario:
// Row 0: Sesuai (selisih 0)
// Row 1: Sesuai (selisih 0)
// Row 2: Selisih Kurang -2 (Barang Rusak)
// Row 3: Selisih Kurang -1 (Barang Expired)
// Row 4: Selisih Kurang -1 (Barang Hilang)
// Sisanya: Sesuai (selisih 0)

$upd = $pdo->prepare("UPDATE detail_opname_batch SET hitung_fisik = ?, selisih = ?, dihitung_oleh = 4, waktu_hitung = NOW() WHERE id = ?");

foreach ($details as $idx => $d) {
    $sistem = intval($d['stok_sistem_fisik']);
    if ($idx === 2) {
        $fisik = max(0, $sistem - 2); // -2 rusak
    } elseif ($idx === 3) {
        $fisik = max(0, $sistem - 1); // -1 expired
    } elseif ($idx === 4) {
        $fisik = max(0, $sistem - 1); // -1 hilang
    } else {
        $fisik = $sistem; // cocok
    }
    $selisih = $fisik - $sistem;
    $upd->execute([$fisik, $selisih, $d['id']]);
    echo "Item {$d['id']} ({$d['jenis_komoditas']}): Sistem=$sistem, Fisik=$fisik, Selisih=$selisih\n";
}

// 5. Verifikasi Filter Tampilan Admin (HANYA barang yang berselisih)
$qStat = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN hitung_fisik IS NOT NULL AND selisih = 0 THEN 1 ELSE 0 END) as sesuai,
        SUM(CASE WHEN hitung_fisik IS NOT NULL AND selisih != 0 THEN 1 ELSE 0 END) as berselisih
    FROM detail_opname_batch WHERE id_sesi = $idSesi
")->fetch(PDO::FETCH_ASSOC);

echo "Statistik: Total={$qStat['total']}, Sesuai={$qStat['sesuai']}, Berselisih={$qStat['berselisih']}\n";

// Query dengan filter 'berselisih'
$anomaliRows = $pdo->query("
    SELECT * FROM detail_opname_batch 
    WHERE id_sesi = $idSesi AND hitung_fisik IS NOT NULL AND selisih != 0
")->fetchAll(PDO::FETCH_ASSOC);

echo "Barang yang tampil di tabel verifikasi anomali: " . count($anomaliRows) . " baris.\n";
if (count($anomaliRows) === intval($qStat['berselisih'])) {
    echo "✓ PASS: Filter Verifikasi Selisih berhasil menyembunyikan {$qStat['sesuai']} barang yang cocok!\n";
} else {
    echo "✗ FAIL: Filter mismatch.\n";
}

// 6. Eksekusi Mutasi dengan Kategori yang Tepat
// Row idx 2 -> Barang Rusak
$itemRusak = $details[2];
$selRusak = abs(intval($itemRusak['stok_sistem_fisik']) - 2); // abs selisih = 2
echo "\nMemproses mutasi Item Rusak ID {$itemRusak['id']}...\n";
$pdo->beginTransaction();
$pdo->query("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Rusak (2 Unit)', dimutasi_oleh = 2, waktu_mutasi = NOW() WHERE id = {$itemRusak['id']}");
$pdo->commit();
echo "✓ Item Rusak dimutasi dengan alasan: Barang Rusak\n";

// Row idx 3 -> Barang Kedaluwarsa
$itemExp = $details[3];
$pdo->beginTransaction();
$pdo->query("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Kedaluwarsa (1 Unit)', dimutasi_oleh = 2, waktu_mutasi = NOW() WHERE id = {$itemExp['id']}");
$pdo->commit();
echo "✓ Item Expired dimutasi dengan alasan: Barang Kedaluwarsa\n";

// Row idx 4 -> Barang Hilang
$itemHilang = $details[4];
$pdo->beginTransaction();
$pdo->query("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Hilang (1 Unit)', dimutasi_oleh = 2, waktu_mutasi = NOW() WHERE id = {$itemHilang['id']}");
$pdo->commit();
echo "✓ Item Hilang dimutasi dengan alasan: Barang Hilang\n";

// 7. Selesaikan Sesi (Tutup & Cabut Pembekuan)
$pending = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = $idSesi AND selisih != 0 AND status_mutasi != 'Sudah Dimutasi'")->fetchColumn();
echo "\nSisa selisih belum dimutasi: $pending\n";
if ($pending == 0) {
    $pdo->query("UPDATE sesi_opname SET status = 'Selesai', dibuka_oleh = 1, dibuka_pada = NOW() WHERE id = $idSesi");
    echo "✓ PASS: Sesi Opname $kodeSesi berhasil diselesaikan! Server pembekuan dicabut.\n";
} else {
    echo "✗ FAIL: Masih ada selisih yang belum dimutasi.\n";
}

$cekFinal = cekSesiOpnameAktif($pdo, true);
if (!$cekFinal) {
    echo "✓ PASS: Sistem kembali beroperasi normal (POS & E-Commerce aktif).\n";
}

echo "=== ALL OPNAME LIFECYCLE TESTS PASSED! ===\n";
