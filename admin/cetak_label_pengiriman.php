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
$itemsRaw = $stmtDetail->fetchAll();

// Agregasi kuantitas & gabung catatan batch/SN jika produk/variasi sama
$aggregatedItems = [];
foreach ($itemsRaw as $row) {
    $key = $row['id_variasi'];
    if (isset($aggregatedItems[$key])) {
        $aggregatedItems[$key]['qty'] += (int)$row['qty'];
        if (!empty($row['catatan_logistik'])) {
            if (!empty($aggregatedItems[$key]['catatan_logistik'])) {
                $parts = array_map('trim', explode(',', $aggregatedItems[$key]['catatan_logistik'] . ',' . $row['catatan_logistik']));
                $aggregatedItems[$key]['catatan_logistik'] = implode(', ', array_unique(array_filter($parts)));
            } else {
                $parts = array_map('trim', explode(',', $row['catatan_logistik']));
                $aggregatedItems[$key]['catatan_logistik'] = implode(', ', array_unique(array_filter($parts)));
            }
        }
    } else {
        $row['qty'] = (int)$row['qty'];
        $aggregatedItems[$key] = $row;
    }
}
$items = array_values($aggregatedItems);

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
    $alamatLengkap = 'Pengambilan Mandiri di Toko ZenCare Medical (Jl. Muharto No. 55D, Jodipan, Kota Malang)';
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
    } elseif ($k === 'internal') {
        $kurirDisplay = 'KURIR TOKO';
    } elseif (!empty($k)) {
        $kurirDisplay = strtoupper($k);
    } else {
        $kurirDisplay = 'KURIR TOKO';
    }
    
    // Format nama layanan agar ringkas, informatif & tidak meluap keluar stiker
    $layananRaw = trim($order['layanan'] ?? '');
    if ($k === 'internal' || stripos($layananRaw, 'ZenCare Direct') !== false) {
        if (preg_match('/\(([^,)]+)/', $layananRaw, $mDist)) {
            $layananDisplay = 'DIRECT DELIVERY (' . trim($mDist[1]) . ')';
        } else {
            $layananDisplay = 'DIRECT DELIVERY';
        }
    } else {
        $layananDisplay = !empty($layananRaw) ? strtoupper($layananRaw) : 'STANDAR';
    }
    $statusBayarDisplay = 'SUDAH DIBAYAR (ONLINE PAYMENT)';
    $bannerTujuan = $kotaTujuan;
}

$printFormat = (isset($_GET['format']) && $_GET['format'] === 'a4') ? 'a4' : 'thermal';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman #<?= htmlspecialchars($order['no_invoice']) ?> - ZenCare Medical</title>
    <!-- JsBarcode Offline Asset (dengan Fallback CDN jika file lokal tidak ditemukan) -->
    <script src="../assets/js/JsBarcode.all.min.js"></script>
    <script>
        if (typeof JsBarcode === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>');
        }
    </script>
    <style>
        <?php if ($printFormat === 'a4'): ?>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        <?php else: ?>
        @page {
            size: 100mm 150mm;
            margin: 3mm;
        }
        <?php endif; ?>
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
            flex: 1;
            min-width: 0;
        }
        .brand-logo-img {
            height: 38px;
            width: auto;
            max-width: 38px;
            object-fit: contain;
            flex-shrink: 0;
            filter: grayscale(100%) contrast(150%);
        }
        .brand-text {
            flex: 1;
            min-width: 0;
        }
        .brand-title {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.1;
            color: #000;
        }
        .brand-sub {
            font-size: 7.5px;
            font-weight: bold;
            color: #222;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .brand-address {
            font-size: 7px;
            color: #444;
            line-height: 1.2;
            margin-top: 1px;
        }
        .courier-box {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2.5px;
            max-width: 44%;
            flex-shrink: 0;
            text-align: right;
        }
        .courier-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            font-weight: 900;
            font-size: 11px;
            padding: 2.5px 6px;
            border-radius: 3px;
            letter-spacing: 0.5px;
            text-align: center;
            line-height: 1.15;
            max-width: 100%;
            box-sizing: border-box;
        }
        .service-badge {
            display: inline-block;
            border: 1.5px solid #000;
            font-weight: 800;
            font-size: 8.5px;
            padding: 2px 5px;
            border-radius: 3px;
            text-align: center;
            line-height: 1.15;
            word-break: break-word;
            max-width: 100%;
            box-sizing: border-box;
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
            width: 100mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 10px 14px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
            box-sizing: border-box;
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .toolbar-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f2d5a;
        }
        .toolbar-badge {
            display: inline-block;
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 6px;
            text-transform: uppercase;
            background: #e0f2fe;
            color: #0369a1;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-print {
            background: #1a75d2;
            color: #fff;
            border: 1px solid #1562b3;
        }
        .btn-print:hover {
            background: #1562b3;
        }
        .btn-close {
            background: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-close:hover {
            background: #f8fafc;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .print-btn-bar {
                display: none;
            }
            <?php if ($printFormat === 'a4'): ?>
            .label-container {
                width: 105mm !important;
                min-height: 148mm !important;
                border: 2px dashed #000 !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
            <?php else: ?>
            .label-container {
                width: 100% !important;
                min-height: 100% !important;
                border: 2px solid #000 !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
            <?php endif; ?>
        }
    </style>
</head>
<body>

    <!-- Top Action Toolbar (Layar Saja / Tidak Dicetak) -->
    <div class="print-btn-bar">
        <div class="toolbar-info">
            <span class="toolbar-title">Pratinjau Label Pengiriman</span>
            <span class="toolbar-badge">#<?= htmlspecialchars($order['no_invoice']) ?></span>
            <span style="font-size: 10px; color: #64748b;">(<?= $printFormat === 'a4' ? 'Format Kertas A4' : 'Thermal 100 &times; 150 mm' ?>)</span>
        </div>
        <div class="toolbar-actions">
            <?php if ($printFormat === 'a4'): ?>
                <a href="?no_invoice=<?= urlencode($noInvoice) ?>&format=thermal" class="btn-action btn-close" style="font-size: 10.5px;">
                    Mode Thermal 100x150
                </a>
            <?php else: ?>
                <a href="?no_invoice=<?= urlencode($noInvoice) ?>&format=a4" class="btn-action btn-close" style="font-size: 10.5px;">
                    Mode Kertas A4
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn-action btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak Label (<?= strtoupper($printFormat) ?>)</span>
            </button>
            <button onclick="window.close()" class="btn-action btn-close">
                Tutup
            </button>
        </div>
    </div>

    <div class="label-container">
        <!-- Header dengan Logo Resmi ZenCare Medical -->
        <div class="header-section">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 6px;">
                <div class="brand-wrapper">
                    <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" class="brand-logo-img" onerror="this.src='../gambar/Logo.png'">
                    <div class="brand-text">
                        <div class="brand-title">ZENCARE MEDICAL</div>
                        <div class="brand-sub">Toko Alat Kesehatan dan Obat Omnichannel</div>
                        <div class="brand-address">Jl. Muharto No. 55D, Jodipan, Kota Malang &bull; Telp: 0895-3434-36025</div>
                    </div>
                </div>
                <div class="courier-box">
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
                <div class="info-phone">Telp: 0895-3434-36025</div>
                <div class="info-address">
                    Jl. Muharto No. 55D, Jodipan, Kec. Blimbing<br>
                    Kota Malang, Jawa Timur 65127<br>
                    <em>Toko Alat Kesehatan dan Obat Resmi</em>
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
                                <?php if (!empty($it['catatan_logistik'])): ?>
                                    <?= htmlspecialchars($it['catatan_logistik']) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
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
                <div class="system-sub">Resmi • Bukti Serah Terima</div>
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
