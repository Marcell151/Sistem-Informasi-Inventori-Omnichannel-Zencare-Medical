<?php
require_once __DIR__ . '/../config/koneksi.php';

function auditProduct($pdo, $idVar, $name) {
    echo "========================================================\n";
    echo "AUDIT STOK: $name (ID Variasi: $idVar)\n";
    echo "========================================================\n";

    // 1. Info Produk
    $stmtVar = $pdo->prepare("
        SELECT pv.id, pi.nama_produk, pv.nama_variasi, pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE pv.id = ?
    ");
    $stmtVar->execute([$idVar]);
    $p = $stmtVar->fetch(PDO::FETCH_ASSOC);
    $rasio = max(1, intval($p['rasio_konversi']));
    $isAlkes = ($p['kategori'] === 'Alat Kesehatan');

    // 2. Stok Toko (Tabel stok_toko)
    $stokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVar")->fetchColumn();
    $stokToko = intval($stokToko);

    // 3. Batch / SN & Reserved
    if ($isAlkes) {
        $snTersedia = intval($pdo->query("SELECT COUNT(*) FROM unit_serial WHERE id_variasi = $idVar AND status = 'Tersedia'")->fetchColumn());
        $snReserved = intval($pdo->query("SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = $idVar AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')")->fetchColumn());
        $snRetur = intval($pdo->query("SELECT COUNT(*) FROM unit_serial WHERE id_variasi = $idVar AND status = 'Retur/Rusak'")->fetchColumn());
        $snTerjual = intval($pdo->query("SELECT COUNT(*) FROM unit_serial WHERE id_variasi = $idVar AND status = 'Terjual'")->fetchColumn());
        $totalSnFisikToko = $snTersedia + $snReserved;
        
        $stokSisaKecil = $snTersedia;
        $reservedKecil = $snReserved;
    } else {
        $batchData = $pdo->query("
            SELECT sb.id, sb.no_batch, sb.stok_sisa,
                   COALESCE((
                       SELECT SUM(pba.qty_satuan_kecil)
                       FROM pesanan_batch_alokasi pba
                       JOIN penjualan p ON pba.id_penjualan = p.id
                       WHERE pba.id_batch = sb.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
                   ), 0) as reserved
            FROM stok_batch sb
            WHERE sb.id_variasi = $idVar
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $stokSisaKecil = 0;
        $reservedKecil = 0;
        foreach ($batchData as $bd) {
            $stokSisaKecil += intval($bd['stok_sisa']);
            $reservedKecil += intval($bd['reserved']);
        }
        $totalSnFisikToko = $stokSisaKecil + $reservedKecil;
    }

    // 4. Kartu Stok (Saldo Akhir Terakhir)
    $lastKs = $pdo->query("
        SELECT id, tanggal, sisa_stok, satuan_tipe, jenis_mutasi, qty, alasan_mutasi, no_ref_dokumen
        FROM kartu_stok
        WHERE id_variasi = $idVar
        ORDER BY id DESC LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    // 5. Dashboard Angka
    // Logika Dashboard Superadmin/Admin
    if ($isAlkes) {
        $dashTersedia = $snTersedia;
        $dashReserved = $snReserved;
        $dashFisik    = $snTersedia + $snReserved;
    } else {
        // Dashboard menghitung stok fisik dus / satuan kecil
        $dashTersedia = $stokSisaKecil;
        $dashReserved = $reservedKecil;
        $dashFisik    = $stokSisaKecil + $reservedKecil;
    }

    // 6. Laporan Ketersediaan Stok
    // Ketersediaan stok menampilkan stok tersedia, reserved, dan fisik
    $lapTersedia = $stokSisaKecil;
    $lapReserved = $reservedKecil;
    $lapFisik    = $stokSisaKecil + $reservedKecil;

    // 7. POS Eceran & Grosir yang ditawarkan
    // Di POS, stok yang bisa dijual adalah stok_toko (tersedia)
    $posStokJual = $stokToko;

    // 8. E-Commerce (Kemasan Besar Box)
    // Di E-commerce, stok yang bisa di-checkout adalah floor(stok_toko / rasio)
    $ecomStokBox = floor($stokToko / $rasio);

    echo "Kategori           : {$p['kategori']}\n";
    echo "Satuan             : {$p['satuan_besar']} (Besar) / {$p['satuan_kecil']} (Kecil) | Rasio: $rasio\n";
    echo "Tabel stok_toko    : $stokToko (satuan kecil)\n";
    echo "Stok Sisa Batch/SN : $stokSisaKecil\n";
    echo "Stok Reserved      : $reservedKecil\n";
    echo "Stok Fisik di Toko : " . ($stokSisaKecil + $reservedKecil) . " (stok_sisa + reserved)\n";
    echo "Saldo Kartu Stok   : " . ($lastKs ? "{$lastKs['sisa_stok']} ({$lastKs['satuan_tipe']}) [ID KS: {$lastKs['id']}, Mutasi: {$lastKs['alasan_mutasi']}]" : "Belum ada record") . "\n";
    echo "Dashboard          : Tersedia = $dashTersedia | Reserved = $dashReserved | Total Fisik = $dashFisik\n";
    echo "Lap Ketersediaan   : Tersedia = $lapTersedia | Reserved = $lapReserved | Total Fisik = $lapFisik\n";
    echo "POS Siap Jual      : $posStokJual {$p['satuan_kecil']}\n";
    echo "E-Commerce Siap Jual: $ecomStokBox {$p['satuan_besar']}\n";
    if ($isAlkes) {
        echo "Logistik Medis SN  : Tersedia = $snTersedia | Reserved = $snReserved | Retur/Rusak = $snRetur | Terjual = $snTerjual\n";
    }
    echo "\n";
}

// Uji 1 Obat: Amoxicillin (id 1) dan Paracetamol (id 10)
auditProduct($pdo, 1, "Amoxicillin 500mg");
auditProduct($pdo, 10, "Paracetamol 500mg");

// Uji 1 Alkes: Termometer (id 5) dan Tensimeter (id 4)
auditProduct($pdo, 5, "Termometer Digital Omron");
auditProduct($pdo, 4, "Tensimeter Digital Omron");

