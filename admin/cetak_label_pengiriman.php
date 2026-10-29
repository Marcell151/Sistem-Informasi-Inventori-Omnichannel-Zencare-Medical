<?php
// File: admin/cetak_label_pengiriman.php
// Cetak Label Pengiriman Stiker Thermal (Ukuran Standar 100mm x 150mm / A6)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';

requireRole(['superadmin', 'admin', 'kasir']);

$noInvoice = trim($_GET['no_invoice'] ?? '');

if (empty($noInvoice)) {
    die("No Invoice tidak valid.");
}

$stmt = $pdo->prepare("
    SELECT p.*, u.nama_lengkap AS nama_user, u.email AS email_user, u.telepon AS telp_user
    FROM penjualan p
    LEFT JOIN users u ON p.id_user = u.id
    WHERE p.no_invoice = ?
");
$stmt->execute([$noInvoice]);
$order = $stmt->fetch();

if (!$order) {
    die("Data pesanan dengan Invoice $noInvoice tidak ditemukan.");
}

$stmtDetail = $pdo->prepare("
    SELECT d.*, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS nama_item, pv.sku_variasi, pi.kategori
    FROM detail_penjualan d
    JOIN produk_variasi pv ON d.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE d.id_penjualan = ?
");
$stmtDetail->execute([$order['id']]);
$items = $stmtDetail->fetchAll();

// Klasifikasi Transaksi & Pengiriman
$isPos = ($order['tipe_transaksi'] === 'pos');
$isPickup = ($order['metode_pengambilan'] === 'Pick-up');
$isEcommerce = ($order['tipe_transaksi'] === 'ecommerce');

if ($isPos) {
    $namaPenerima = !empty($order['nama_penerima']) ? $order['nama_penerima'] : 'Pelanggan Langsung (Kasir)';
    $telpPenerima = !empty($order['telepon_penerima']) ? $order['telepon_penerima'] : '-';
    $alamatLengkap = !empty($order['alamat_lengkap']) ? $order['alamat_lengkap'] : 'ZenCare Medical (Front Office - Pembelian Langsung di Toko)';
    $kotaTujuan = 'KOTA MALANG';
    $kurirDisplay = 'TRANSAKSI KASIR';
    $layananDisplay = 'OFFLINE';
    $statusBayarDisplay = 'SUDAH DIBAYAR (KASIR / ' . strtoupper($order['metode_bayar_pos'] ?? 'TUNAI') . ')';
    $bannerTujuan = 'TRANSAKSI KASIR - MALANG';
} elseif ($isPickup) {
    $namaPenerima = !empty($order['nama_penerima']) ? $order['nama_penerima'] : ($order['nama_user'] ?? 'Pelanggan ZenCare');
    $telpPenerima = !empty($order['telepon_penerima']) ? $order['telepon_penerima'] : ($order['telp_user'] ?? '-');
    $alamatLengkap = 'Pengambilan Mandiri di Toko ZenCare Medical (Jl. Muharto No. 1, Kota Malang)';
    $kotaTujuan = 'KOTA MALANG';
    $kurirDisplay = 'AMBIL DI TOKO';
    $layananDisplay = 'PICK-UP';
    $statusBayarDisplay = 'SUDAH DIBAYAR (ONLINE PAYMENT)';
    $bannerTujuan = 'PICK-UP POINT (TOKO)';
} else {
    // Kurir Ekspedisi (JNE / POS Indonesia / SICEPAT / TIKI / Kurir Toko)
    $namaPenerima = !empty($order['nama_penerima']) ? $order['nama_penerima'] : ($order['nama_user'] ?? 'Pelanggan ZenCare');
    $telpPenerima = !empty($order['telepon_penerima']) ? $order['telepon_penerima'] : ($order['telp_user'] ?? '-');
    $alamatLengkap = !empty($order['alamat_lengkap']) ? $order['alamat_lengkap'] : 'Alamat Pengiriman Sesuai Pesanan';
    $kotaTujuan = !empty($order['kota_tujuan']) ? strtoupper($order['kota_tujuan']) : 'KOTA MALANG';
    
    $k = strtolower($order['kurir'] ?? '');
    if ($k === 'jne') {
        $kurirDisplay = 'JNE EXPRESS';
    } elseif ($k === 'pos') {
        $kurirDisplay = 'POS INDONESIA';
    } elseif ($k === 'jnt') {
        $kurirDisplay = 'J&T EXPRESS';
    } elseif ($k === 'sicepat') {
        $kurirDisplay = 'SICEPAT';
    } elseif ($k === 'tiki') {
        $kurirDisplay = 'TIKI';
    } elseif (!empty($k)) {
        $kurirDisplay = strtoupper($k);
    } else {
        $kurirDisplay = 'KURIR TOKO';
    }
    
    $layananDisplay = !empty($order['layanan']) ? strtoupper($order['layanan']) : 'STANDAR';
    $statusBayarDisplay = 'SUDAH DIBAYAR (ONLINE PAYMENT)';
    $bannerTujuan = $kotaTujuan;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman #<?= htmlspecialchars($order['no_invoice']) ?> - ZenCare Medical</title>
    <!-- JsBarcode untuk rendering barcode thermal yang presisi -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 3mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 15px;
            color: #000;
        }
        .label-container {
            width: 100mm;
            min-height: 140mm;
            background: #fff;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }
        .header-section {
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .brand-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .brand-logo-svg {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
        }
        .brand-title {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .brand-sub {
            font-size: 8.5px;
            font-weight: bold;
            color: #333;
            margin-top: 1px;
        }
        .courier-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            font-weight: 900;
            font-size: 13px;
            padding: 3px 7px;
            border-radius: 3px;
            letter-spacing: 0.5px;
        }
        .service-badge {
            display: inline-block;
            border: 1.5px solid #000;
            font-weight: 800;
            font-size: 11px;
            padding: 2.5px 6px;
            border-radius: 3px;
            margin-left: 3px;
        }
        .barcode-section {
            text-align: center;
            padding: 5px 0;
            border-bottom: 2px dashed #000;
        }
        .barcode-svg {
            max-width: 88mm;
            height: 38px;
        }
        .invoice-text {
            font-size: 11px;
            font-weight: 800;
            font-family: monospace;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .pickup-code-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            font-size: 10px;
            font-weight: 900;
            padding: 2px 6px;
            border-radius: 3px;
            margin-top: 3px;
            letter-spacing: 0.5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 2px solid #000;
            font-size: 9.5px;
        }
        .info-box {
            padding: 5px 4px;
        }
        .info-box:first-child {
            border-right: 1.5px solid #000;
        }
        .info-title {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 2px;
            background: #e2e8f0;
            padding: 2px 4px;
            display: inline-block;
            border-radius: 2px;
        }
        .info-name {
            font-size: 10.5px;
            font-weight: bold;
            line-height: 1.2;
        }
        .info-phone {
            font-size: 9.5px;
            font-weight: bold;
            margin: 2px 0;
        }
        .info-address {
            font-size: 9px;
            line-height: 1.25;
            color: #111;
        }
        .dest-city-banner {
            background: #000;
            color: #fff;
            padding: 3.5px 5px;
            font-size: 11px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
            border-radius: 2px;
        }
        .items-section {
            padding: 5px 0;
            border-bottom: 2px solid #000;
            flex-grow: 1;
        }
        .items-title {
            font-size: 8.5px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .items-table th {
            border-bottom: 1px solid #000;
            padding: 2.5px 2px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            padding: 2.5px 2px;
            border-bottom: 1px dotted #ccc;
            vertical-align: top;
        }
        .footer-section {
            padding-top: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
        }
        .footer-left {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .payment-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 3px 6px;
            font-weight: 900;
            font-size: 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-radius: 2px;
            white-space: nowrap;
        }
        .total-qty-text {
            font-size: 9px;
            font-weight: bold;
            color: #111;
            white-space: nowrap;
        }
        .footer-right {
            text-align: right;
            font-size: 8px;
            color: #333;
            line-height: 1.25;
        }
        .system-tag {
            font-weight: 800;
            text-transform: uppercase;
        }
        .system-sub {
            font-size: 7.5px;
            color: #555;
        }
        .print-btn-bar {
            text-align: center;
            margin-bottom: 15px;
        }
        .btn-print {
            background: #1a75d2;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }
        .btn-print:hover {
            background: #1562b3;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .print-btn-bar {
                display: none;
            }
            .label-container {
                width: 100%;
                min-height: 100%;
                border: 2px solid #000;
                margin: 0;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <button onclick="window.print()" class="btn-print">
            🏷️ Cetak Stiker Label Pengiriman Thermal (100x150mm / A6)
        </button>
        <a href="pesanan.php" style="margin-left: 10px; font-size: 12px; color: #475569; text-decoration: underline;">Kembali ke Daftar Pesanan</a>
    </div>

    <div class="label-container">
        <!-- Header dengan Logo Resmi ZenCare Medical -->
        <div class="header-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="brand-wrapper">
                    <!-- Logo Vektor Medis & Cross (Monochrome Crisp untuk Thermal Printer) -->
                    <svg class="brand-logo-svg" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="M12 8v8"/>
                        <path d="M8 12h8"/>
                    </svg>
                    <div>
                        <div class="brand-title">ZenCare Medical</div>
                        <div class="brand-sub">Toko Alat Kesehatan dan Obat Resmi</div>
                    </div>
                </div>
                <div style="text-align: right; white-space: nowrap;">
                    <span class="courier-badge"><?= htmlspecialchars($kurirDisplay) ?></span>
                    <span class="service-badge"><?= htmlspecialchars($layananDisplay) ?></span>
                </div>
            </div>
        </div>

        <!-- Barcode & No Invoice -->
        <div class="barcode-section">
            <svg id="barcode" class="barcode-svg"></svg>
            <div class="invoice-text">
                <?= htmlspecialchars($order['no_invoice']) ?>
            </div>
            <?php if (!empty($order['kode_pickup'])): ?>
                <div class="pickup-code-badge">
                    KODE VERIFIKASI PICK-UP: <?= htmlspecialchars($order['kode_pickup']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Penerima & Pengirim -->
        <div class="info-grid">
            <div class="info-box">
                <div class="info-title">Penerima (Kepada):</div>
                <div class="info-name"><?= htmlspecialchars($namaPenerima) ?></div>
                <div class="info-phone">Telp: <?= htmlspecialchars($telpPenerima) ?></div>
                <div class="info-address"><?= nl2br(htmlspecialchars($alamatLengkap)) ?></div>
                <div class="dest-city-banner"><?= htmlspecialchars($bannerTujuan) ?></div>
            </div>
            <div class="info-box">
                <div class="info-title">Pengirim (Dari):</div>
                <div class="info-name">ZenCare Medical Store</div>
                <div class="info-phone">Telp: 0812-3456-7890</div>
                <div class="info-address">
                    Jl. Muharto No. 1, Kota Malang<br>
                    Jawa Timur - Indonesia 65136<br>
                    <em>Pusat Distribusi Alkes &amp; Obat Terdaftar</em>
                </div>
                <div style="margin-top: 6px; border: 1px solid #000; padding: 3px; border-radius: 2px; text-align: center;">
                    <span style="font-size: 7.5px; font-weight: bold; text-transform: uppercase;">Waktu Order:</span>
                    <span style="font-size: 8.5px; font-weight: bold;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?> WIB</span>
                </div>
            </div>
        </div>

        <!-- Isi Paket / Checklist Logistik & Validasi FEFO -->
        <div class="items-section">
            <div class="items-title">Rincian Paket &amp; Validasi Fisik Logistik:</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Produk &amp; Variasi</th>
                        <th style="width: 15%; text-align: center;">Qty</th>
                        <th style="width: 35%;">Alokasi Batch FEFO / SN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalQty = 0;
                    foreach ($items as $it): 
                        $totalQty += intval($it['qty']);
                    ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($it['nama_item']) ?></strong>
                                <div style="font-size: 7.5px; color: #555;"><?= htmlspecialchars($it['sku_variasi']) ?></div>
                            </td>
                            <td style="text-align: center; font-weight: bold;">
                                <?= intval($it['qty']) ?>
                            </td>
                            <td style="font-family: monospace; font-size: 7.5px;">
                                <?= !empty($it['catatan_logistik']) ? htmlspecialchars($it['catatan_logistik']) : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer Bersih (Bebas COD & Rapi Tanpa Teks Terpotong) -->
        <div class="footer-section">
            <div class="footer-left">
                <div class="payment-badge">
                    ✓ <?= htmlspecialchars($statusBayarDisplay) ?>
                </div>
                <div class="total-qty-text">
                    Total Muatan: <strong><?= $totalQty ?> Unit / Item</strong>
                </div>
            </div>
            <div class="footer-right">
                <div class="system-tag">ZenCare Fulfillment System</div>
                <div class="system-sub">Resmi • Terverifikasi FEFO • Bukti Serah Terima</div>
            </div>
        </div>
    </div>

    <script>
        // Inisialisasi barcode Code 128 dengan JsBarcode
        try {
            JsBarcode("#barcode", "<?= addslashes($order['no_invoice']) ?>", {
                format: "CODE128",
                width: 1.7,
                height: 38,
                displayValue: false,
                margin: 0
            });
        } catch(e) {
            console.error("Gagal render barcode:", e);
        }
    </script>
</body>
</html>
