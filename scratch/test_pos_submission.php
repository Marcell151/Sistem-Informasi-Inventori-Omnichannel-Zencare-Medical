<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nama_lengkap'] = 'Kasir Utama';
$_SESSION['role'] = 'superadmin';

require_once __DIR__ . '/../config/koneksi.php';

// Siapkan cart JSON persis seperti yang dikirim frontend POS
$cartPayload = [
    [
        'id' => 51,
        'cartId' => '51_kecil',
        'nama' => 'Alat Cek Asam Urat EasyTouch - Tube Isi 25',
        'harga' => 95000,
        'qty' => 1,
        'satuan_tipe' => 'kecil',
        'satuan_label' => 'Tube',
        'maxPcs' => 15,
        'product_rasio' => 1,
        'rasio' => 1,
        'multiplier' => 1,
        'kategori' => 'Alat Kesehatan',
        'sn_list' => '',
        'batch_list' => 'bmhp-esyua-28|15',
        'needs_verification' => true,
        'allocations' => [
            [
                'batch' => 'bmhp-esyua-28',
                'qty' => 1,
                'verified' => true,
                'scanned_batch' => 'BMHP-ESYUA-28',
                'is_anomaly' => false,
                'batch_seharusnya' => null
            ]
        ],
        'alkes_allocations' => []
    ],
    [
        'id' => 1,
        'cartId' => '1_kecil',
        'nama' => 'Amoxicillin 500mg - Tablet 500mg',
        'harga' => 5000,
        'qty' => 1,
        'satuan_tipe' => 'kecil',
        'satuan_label' => 'Strip',
        'maxPcs' => 8,
        'product_rasio' => 10,
        'rasio' => 1,
        'multiplier' => 1,
        'kategori' => 'Obat',
        'sn_list' => '',
        'batch_list' => 'amx-2026-01|40,amx-2026-01.a|8',
        'needs_verification' => true,
        'allocations' => [
            [
                'batch' => 'amx-2026-01.a',
                'qty' => 1,
                'verified' => true,
                'scanned_batch' => 'AMX-2026-01.A',
                'is_anomaly' => false,
                'batch_seharusnya' => null
            ]
        ],
        'alkes_allocations' => []
    ]
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['aksi'] = 'bayar_pos';
$_POST['cart_data'] = json_encode($cartPayload);
$_POST['metode_pembayaran'] = 'Tunai';

$pdo->beginTransaction();

try {
    ob_start();
    // Jalankan blok pemrosesan transaksi di pos.php
    $cartItems = json_decode($_POST['cart_data'], true);
    $invoiceNo = 'TEST-POS-' . date('YmdHis');
    $metodePembayaran = $_POST['metode_pembayaran'];
    $kasirUserId = $_SESSION['user_id'];

    // 1. Insert header penjualan
    $stmt = $pdo->prepare("INSERT INTO penjualan (no_invoice, id_user, tipe_transaksi, total_harga, status_pesanan, metode_bayar_pos, created_at) VALUES (?, ?, 'pos', 0, 'Selesai', ?, NOW())");
    $stmt->execute([$invoiceNo, $kasirUserId, $metodePembayaran]);
    $idPenjualan = $pdo->lastInsertId();

    $totalHarga = 0;
    foreach ($cartItems as $c) {
        $idVar       = intval($c['id']);
        $qtyInput    = intval($c['qty']);
        $satuanTipe  = $c['satuan_tipe'];
        $allocations = $c['allocations'] ?? [];

        $chk = $pdo->prepare("SELECT v.satuan_kecil, v.satuan_besar, v.rasio_konversi, v.harga_jual_kecil, v.harga_jual_besar, sc.stok, pi.kategori FROM produk_variasi v JOIN produk_induk pi ON v.id_produk_induk = pi.id LEFT JOIN stok_toko sc ON sc.id_variasi = v.id WHERE v.id=? FOR UPDATE");
        $chk->execute([$idVar]);
        $varData = $chk->fetch();

        $rasio = max(1, intval($varData['rasio_konversi']));
        $qtyPotong = ($satuanTipe === 'besar') ? ($qtyInput * $rasio) : $qtyInput;
        $effPrice  = ($satuanTipe === 'besar') ? floatval($varData['harga_jual_besar']) : floatval($varData['harga_jual_kecil']);

        $stmtDet = $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan) VALUES (?, ?, ?, ?)");
        $stmtDet->execute([$idPenjualan, $idVar, $qtyInput, $effPrice]);
        $idDetail = $pdo->lastInsertId();

        // Potong batch
        foreach ($allocations as $al) {
            $alBatch = $al['batch'];
            $alQtySatuan = intval($al['qty']);
            $alQtyPotong = ($satuanTipe === 'besar') ? ($alQtySatuan * $rasio) : $alQtySatuan;

            $b = $pdo->prepare("SELECT id, no_batch, tgl_exp, stok_sisa FROM stok_batch WHERE id_variasi = ? AND no_batch = ? FOR UPDATE");
            $b->execute([$idVar, $alBatch]);
            $bRow = $b->fetch();
            if (!$bRow || $bRow['stok_sisa'] < $alQtyPotong) throw new Exception("Stok batch $alBatch tidak cukup!");

            $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$alQtyPotong, $bRow['id']]);
            $pdo->prepare("INSERT INTO pesanan_batch_alokasi (id_penjualan, id_detail_penjualan, id_batch, qty_satuan_kecil) VALUES (?, ?, ?, ?)")->execute([$idPenjualan, $idDetail, $bRow['id'], $alQtyPotong]);
        }

        $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$qtyPotong, $idVar]);
        $totalHarga += ($effPrice * $qtyInput);
    }

    $pdo->prepare("UPDATE penjualan SET total_harga = ? WHERE id = ?")->execute([$totalHarga, $idPenjualan]);
    
    echo "SUCCESS: POS Transaction processed successfully!\n";
    echo "Invoice: $invoiceNo | Total: Rp " . number_format($totalHarga, 0, ',', '.') . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $pdo->rollBack();
    echo "Rollback test transaction: DB restored cleanly.\n";
}
