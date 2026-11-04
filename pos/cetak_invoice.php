<?php
// File: pos/cetak_invoice.php
// Dual-Mode Document Generator: Faktur Resmi (A4) & Struk Kasir (Thermal 80mm/58mm)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$noInvoice = $_GET['no_invoice'] ?? '';

if (empty($noInvoice)) {
    die("Nomor Invoice tidak valid.");
}

$stmt = $pdo->prepare("
    SELECT p.*, 
           u.nama_lengkap AS nama_user_trx, 
           u.role AS role_user_trx,
           'ZenCare Medical - Pusat' AS nama_cabang, 
           'Jl. Muharto No. 55D, Jodipan, Kec. Blimbing, Kota Malang 65127' AS alamat_cabang,
           '0895-3434-36025' AS telp_cabang
    FROM penjualan p
    LEFT JOIN users u ON p.id_user = u.id
    WHERE p.no_invoice = ?
");
$stmt->execute([$noInvoice]);
$trx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trx) {
    die("Transaksi dengan Nomor Invoice " . htmlspecialchars($noInvoice) . " tidak ditemukan.");
}

$stmtDetail = $pdo->prepare("
    SELECT d.*, 
           CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_item, 
           i.nama_produk,
           v.nama_variasi,
           v.sku_variasi,
           v.satuan_besar,
           v.satuan_kecil,
           v.rasio_konversi
    FROM detail_penjualan d
    JOIN produk_variasi v ON d.id_variasi = v.id
    JOIN produk_induk i ON v.id_produk_induk = i.id
    WHERE d.id_penjualan = ?
    ORDER BY d.id ASC
");
$stmtDetail->execute([$trx['id']]);
$items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

// Agregasi Kuantitas & Gabung Catatan Batch / Serial Number jika variasi & harga sama
$aggregatedItems = [];
foreach ($items as $row) {
    $key = $row['id_variasi'] . '_' . (float)$row['harga_satuan'];
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

// Format Data Pembeli & Petugas Toko (Pisahkan logika POS vs E-Commerce)
$isPos = ($trx['tipe_transaksi'] === 'pos');

if ($isPos) {
    // Pada transaksi POS: id_user adalah kasir/user toko yang bertugas
    if (!empty($trx['role_user_trx']) && in_array($trx['role_user_trx'], ['kasir', 'admin', 'superadmin'])) {
        $namaPetugas = $trx['nama_user_trx'];
    } else {
        $namaPetugas = $_SESSION['nama_lengkap'] ?? 'Kasir (Front Office)';
    }
    $labelPetugas = 'Petugas Kasir';
    $labelTtd = 'Petugas Kasir / Penanggung Jawab';

    $namaPenerima = !empty($trx['nama_penerima']) 
        ? $trx['nama_penerima'] 
        : 'Pelanggan Direct (Walk-in POS)';
    $telepon = !empty($trx['telepon_penerima']) ? $trx['telepon_penerima'] : '-';
    $alamat = !empty($trx['alamat_lengkap']) 
        ? $trx['alamat_lengkap'] 
        : 'Transaksi Langsung di Toko (Kasir POS)';
    $kurirLayanan = 'Direct Takeaway (Kasir Fisik)';
    $metodeBayar = !empty($trx['metode_bayar_pos']) ? $trx['metode_bayar_pos'] : 'Tunai';
} else {
    // Pada transaksi E-Commerce: id_user adalah akun PELANGGAN (misal Budi Santoso), BUKAN penanggung jawab toko!
    if (!empty($_SESSION['nama_lengkap']) && in_array($_SESSION['role'] ?? '', ['admin', 'superadmin', 'kasir'])) {
        $namaPetugas = $_SESSION['nama_lengkap'];
    } else {
        $namaPetugas = 'Admin Toko & Logistik';
    }
    $labelPetugas = 'Admin / Petugas Logistik';
    $labelTtd = 'Petugas Logistik & Admin Toko';

    $namaPenerima = !empty($trx['nama_penerima']) 
        ? $trx['nama_penerima'] 
        : (!empty($trx['nama_user_trx']) ? $trx['nama_user_trx'] : 'Pelanggan Online');
    $telepon = !empty($trx['telepon_penerima']) ? $trx['telepon_penerima'] : '-';
    $alamat = !empty($trx['alamat_lengkap']) ? $trx['alamat_lengkap'] : '-';
    $metodeBayar = !empty($trx['payment_method_ecommerce']) 
        ? strtoupper(str_replace('_', ' ', $trx['payment_method_ecommerce'])) 
        : 'Payment Gateway';
    $kurirLayanan = ($trx['metode_pengambilan'] === 'Pick-up') 
        ? 'Ambil Sendiri di Toko (Self Pick-up)' 
        : strtoupper($trx['kurir'] ?? 'Kurir') . ' - ' . ($trx['layanan'] ?? 'Standard');
}

$subtotal = 0;
foreach ($aggregatedItems as $it) {
    $subtotal += ($it['qty'] * $it['harga_satuan']);
}
$ongkir = floatval($trx['ongkir'] ?? 0);
$grandTotal = floatval($trx['total_harga'] ?? ($subtotal + $ongkir));

// Tentukan Format Default
$reqFormat = strtolower($_GET['format'] ?? '');
if (in_array($reqFormat, ['thermal', 'struk', 'pos'])) {
    $initialFormat = 'thermal';
} elseif ($reqFormat === 'a4' || $reqFormat === 'invoice') {
    $initialFormat = 'a4';
} else {
    $initialFormat = $isPos ? 'thermal' : 'a4';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($trx['no_invoice']) ?> - ZenCare Medical</title>
    
    <!-- Dynamic Print Page Setup -->
    <style id="print-page-style">
        <?php if ($initialFormat === 'thermal'): ?>
        @page { size: 80mm auto; margin: 3mm 4mm; }
        <?php else: ?>
        @page { size: A4 portrait; margin: 12mm 15mm; }
        <?php endif; ?>
    </style>

    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            background: #e2e8f0;
            margin: 0;
            padding: 24px 12px;
            font-size: 11px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ─── Top Control Toolbar (Screen Only) ────────────────────────── */
        .print-toolbar {
            max-width: 860px;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 18px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06);
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toolbar-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f2d5a;
        }
        .toolbar-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            background: #e0f2fe;
            color: #0369a1;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .format-switch-group {
            display: inline-flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .btn-switch {
            border: none;
            background: transparent;
            color: #475569;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-switch.active {
            background: #0f2d5a;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
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

        /* ─── Mode Switching Rules ────────────────────────────────────── */
        body.mode-a4 .invoice-thermal {
            display: none !important;
        }
        body.mode-a4 .invoice-a4 {
            display: block !important;
        }

        body.mode-thermal .invoice-a4 {
            display: none !important;
        }
        body.mode-thermal .invoice-thermal {
            display: block !important;
        }

        /* ─── FORMAT 1: A4 INVOICE STYLING ────────────────────────────── */
        .invoice-a4 {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 32px 36px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
        }
        .a4-kop-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f2d5a;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .a4-logo-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .a4-logo-area img {
            height: 52px;
            width: auto;
            object-fit: contain;
        }
        .a4-brand-text h1 {
            font-size: 17pt;
            font-weight: 800;
            color: #0f2d5a;
            margin: 0;
            line-height: 1.1;
            letter-spacing: 0.5px;
        }
        .a4-brand-text .tagline {
            font-size: 8.5pt;
            font-weight: 700;
            color: #334155;
            margin: 2px 0 0 0;
        }
        .a4-brand-text .address {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 1px;
        }
        .a4-doc-meta {
            text-align: right;
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.45;
        }
        .a4-doc-meta .doc-type {
            font-size: 13pt;
            font-weight: 800;
            color: #0f2d5a;
            letter-spacing: 0.5px;
        }
        .a4-doc-meta .doc-inv {
            font-size: 10pt;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .a4-meta-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }
        .a4-info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 8.5pt;
            line-height: 1.5;
        }
        .a4-info-card .card-heading {
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.4px;
            margin-bottom: 5px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
        }
        .a4-info-card strong {
            color: #0f172a;
        }
        table.a4-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 18px;
        }
        table.a4-table th, table.a4-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            line-height: 1.35;
        }
        table.a4-table th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #1e293b;
            text-align: left;
            font-size: 8pt;
            letter-spacing: 0.3px;
        }
        table.a4-table td.text-center, table.a4-table th.text-center { text-align: center; }
        table.a4-table td.text-right, table.a4-table th.text-right { text-align: right; }
        
        .a4-batch-tag {
            display: block;
            margin-top: 3px;
            font-size: 7.5pt;
            color: #0369a1;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 4px;
            padding: 2px 6px;
            width: fit-content;
        }

        .a4-summary-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-top: 10px;
        }
        .a4-legal-note {
            flex: 1;
            font-size: 7.5pt;
            color: #64748b;
            line-height: 1.45;
        }
        .a4-sign-box {
            margin-top: 18px;
            text-align: left;
            font-size: 8pt;
        }
        .a4-sign-space {
            height: 48px;
        }
        .a4-total-card {
            width: 280px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            padding: 10px 14px;
            font-size: 8.5pt;
        }
        .a4-total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            color: #334155;
        }
        .a4-total-grand {
            border-top: 1px solid #0f2d5a;
            margin-top: 4px;
            padding-top: 6px;
            font-size: 11pt;
            font-weight: 800;
            color: #0f2d5a;
        }
        .a4-footer-bottom {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
        }

        /* ─── FORMAT 2: THERMAL RECEIPT STYLING (80mm) ─────────────────── */
        .invoice-thermal {
            width: 76mm;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px 14px;
            font-family: 'Courier New', Courier, monospace, Arial;
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            box-sizing: border-box;
        }
        .thermal-header {
            text-align: center;
            margin-bottom: 8px;
        }
        .thermal-logo {
            height: 38px;
            width: auto;
            object-fit: contain;
            margin-bottom: 4px;
            filter: grayscale(100%) contrast(150%);
        }
        .thermal-title {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .thermal-sub {
            font-size: 10px;
            color: #222;
        }
        .thermal-divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .thermal-divider-double {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 8px 0;
        }
        .thermal-row {
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            margin: 2px 0;
        }
        .thermal-row-bold {
            font-weight: bold;
            font-size: 11.5px;
        }
        .thermal-item-block {
            margin-bottom: 6px;
        }
        .thermal-item-name {
            font-weight: bold;
            word-break: break-word;
            font-size: 10.5px;
        }
        .thermal-item-calc {
            padding-left: 10px;
            color: #111;
        }
        .thermal-item-sn {
            padding-left: 10px;
            font-size: 9.5px;
            color: #333;
            font-style: italic;
        }
        .thermal-footer {
            text-align: center;
            font-size: 9.5px;
            margin-top: 10px;
            line-height: 1.3;
        }

        /* ─── PRINT MEDIA SPECIFIC RULES ──────────────────────────────── */
        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-toolbar {
                display: none !important;
            }

            body.mode-a4 {
                width: 100% !important;
            }
            body.mode-a4 .invoice-a4 {
                display: block !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
            }
            body.mode-a4 .invoice-thermal {
                display: none !important;
            }

            body.mode-thermal {
                width: 76mm !important;
                margin: 0 auto !important;
            }
            body.mode-thermal .invoice-thermal {
                display: block !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                width: 76mm !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
            }
            body.mode-thermal .invoice-a4 {
                display: none !important;
            }
        }
    </style>
</head>
<body class="mode-<?= $initialFormat ?>">

    <!-- Top Action Toolbar (Tidak Dicetak) -->
    <div class="print-toolbar">
        <div class="toolbar-info">
            <span class="toolbar-title">Pratinjau Dokumen Transaksi</span>
            <span class="toolbar-badge">#<?= htmlspecialchars($trx['no_invoice']) ?></span>
            <span style="font-size: 11px; color: #64748b;">(<?= strtoupper(htmlspecialchars($trx['tipe_transaksi'])) ?>)</span>
        </div>

        <div class="toolbar-actions">
            <!-- Format Switcher -->
            <div class="format-switch-group">
                <button type="button" id="btn-mode-a4" class="btn-switch <?= $initialFormat === 'a4' ? 'active' : '' ?>" onclick="switchPrintFormat('a4')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>Faktur Resmi (A4)</span>
                </button>
                <button type="button" id="btn-mode-thermal" class="btn-switch <?= $initialFormat === 'thermal' ? 'active' : '' ?>" onclick="switchPrintFormat('thermal')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="8" y1="10" x2="16" y2="10"></line><line x1="8" y1="14" x2="12" y2="14"></line><line x1="8" y1="18" x2="16" y2="18"></line></svg>
                    <span>Struk Kasir (Thermal 80mm)</span>
                </button>
            </div>

            <!-- Print Button -->
            <button type="button" onclick="window.print()" class="btn-action btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak Dokumen</span>
            </button>

            <!-- Close Button -->
            <button type="button" onclick="window.close()" class="btn-action btn-close">
                Tutup
            </button>
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- DOKUMEN 1: FORMAT FAKTUR RESMI INVOICE (A4)                     -->
    <!-- ============================================================== -->
    <div class="invoice-a4">
        <!-- Kop Surat Resmi Bersama Logo ZenCare (Identik dengan Berita Acara & Laporan) -->
        <div class="a4-kop-header">
            <div class="a4-logo-area">
                <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" onerror="this.src='../gambar/Logo.png'">
                <div class="a4-brand-text">
                    <h1>ZENCARE MEDICAL</h1>
                    <p class="tagline">Toko Alat Kesehatan dan Obat Omnichannel</p>
                    <div class="address"><?= htmlspecialchars($trx['alamat_cabang']) ?> &bull; Telp: <?= htmlspecialchars($trx['telp_cabang']) ?></div>
                </div>
            </div>
            <div class="a4-doc-meta">
                <div class="doc-type">FAKTUR PENJUALAN</div>
                <div class="doc-inv">#<?= htmlspecialchars($trx['no_invoice']) ?></div>
                <div><strong>Tanggal:</strong> <?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?> WIB</div>
                <div><strong>Status:</strong> <span style="color: #0369a1; font-weight: bold;"><?= strtoupper(htmlspecialchars($trx['status_pesanan'])) ?></span></div>
            </div>
        </div>

        <!-- Detail Pembeli & Pengiriman -->
        <div class="a4-meta-box">
            <div class="a4-info-card">
                <div class="card-heading">Informasi Penerima / Pelanggan</div>
                <div>Nama: <strong><?= htmlspecialchars($namaPenerima) ?></strong></div>
                <div>Telepon: <strong><?= htmlspecialchars($telepon) ?></strong></div>
                <div>Alamat: <?= htmlspecialchars($alamat) ?></div>
            </div>
            <div class="a4-info-card">
                <div class="card-heading">Metode &amp; Info Pengiriman</div>
                <div>Kanal: <strong><?= $isPos ? 'Kasir Toko Fisik (POS Direct)' : 'Toko Online E-Commerce' ?></strong></div>
                <div>Metode Bayar: <strong><?= htmlspecialchars($metodeBayar) ?></strong></div>
                <div>Pengiriman / Ambil: <strong><?= htmlspecialchars($kurirLayanan) ?></strong></div>
                <div><?= htmlspecialchars($labelPetugas) ?>: <strong><?= htmlspecialchars($namaPetugas) ?></strong></div>
            </div>
        </div>

        <!-- Tabel Barang Terjual -->
        <table class="a4-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No</th>
                    <th style="width: 15%;">SKU</th>
                    <th style="width: 45%;">Nama Barang &amp; Detail Alokasi</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 12%;" class="text-right">Harga Satuan</th>
                    <th style="width: 13%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($aggregatedItems as $it): 
                    $lineTotal = $it['qty'] * $it['harga_satuan'];
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($it['sku_variasi']) ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($it['nama_item']) ?></strong>
                            <?php if (!empty($it['catatan_logistik'])): ?>
                                <span class="a4-batch-tag">▷ Batch / SN: <?= htmlspecialchars($it['catatan_logistik']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><strong><?= intval($it['qty']) ?></strong></td>
                        <td class="text-right">Rp <?= number_format($it['harga_satuan'], 0, ',', '.') ?></td>
                        <td class="text-right"><strong>Rp <?= number_format($lineTotal, 0, ',', '.') ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Ringkasan & Tanda Tangan -->
        <div class="a4-summary-section">
            <div class="a4-legal-note">
                <strong>Catatan Legalitas &amp; Garansi:</strong>
                <div>1. Faktur penjualan ini sah secara digital dari ZenCare Medical.</div>
                <div>2. Segala bentuk klaim garansi produk alkes atau ketidaksesuaian obat wajib menyertakan faktur ini.</div>
                
                <div class="a4-sign-box">
                    <div>Malang, <?= date('d/m/Y', strtotime($trx['created_at'])) ?></div>
                    <div><?= htmlspecialchars($labelTtd) ?>,</div>
                    <div class="a4-sign-space"></div>
                    <div style="font-weight: bold; text-decoration: underline; color: #0f172a;">
                        ( <?= htmlspecialchars($namaPetugas) ?> )
                    </div>
                </div>
            </div>

            <div class="a4-total-card">
                <div class="a4-total-row">
                    <span>Subtotal Barang:</span>
                    <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                <?php if ($ongkir > 0): ?>
                    <div class="a4-total-row">
                        <span>Ongkos Kirim:</span>
                        <span>Rp <?= number_format($ongkir, 0, ',', '.') ?></span>
                    </div>
                <?php endif; ?>
                <div class="a4-total-row a4-total-grand">
                    <span>GRAND TOTAL:</span>
                    <span>Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
                </div>
                <div class="a4-total-row" style="margin-top: 6px; font-size: 8pt; color: #64748b;">
                    <span>Metode:</span>
                    <span><strong><?= htmlspecialchars($metodeBayar) ?></strong></span>
                </div>
                <div class="a4-total-row" style="font-size: 8pt; color: #059669;">
                    <span>Status Bayar:</span>
                    <span><strong>LUNAS</strong></span>
                </div>
            </div>
        </div>

        <div class="a4-footer-bottom">
            Terima Kasih Telah Berbelanja di ZenCare Medical | Single Source of Truth Inventory
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- DOKUMEN 2: FORMAT STRUK KASIR THERMAL (80mm / 58mm)            -->
    <!-- ============================================================== -->
    <div class="invoice-thermal">
        <!-- Thermal Header -->
        <div class="thermal-header">
            <img src="/inventory_zencare/gambar/Logo.png" alt="Logo" class="thermal-logo" onerror="this.src='../gambar/Logo.png'">
            <div class="thermal-title">ZENCARE MEDICAL</div>
            <div class="thermal-sub">Toko Alat Kesehatan dan Obat</div>
            <div class="thermal-sub"><?= htmlspecialchars($trx['alamat_cabang']) ?></div>
            <div class="thermal-sub">Telp: <?= htmlspecialchars($trx['telp_cabang']) ?></div>
        </div>

        <div class="thermal-divider-double"></div>

        <!-- Thermal Metadata -->
        <div class="thermal-row">
            <span>No. Inv</span>
            <span>#<?= htmlspecialchars($trx['no_invoice']) ?></span>
        </div>
        <div class="thermal-row">
            <span>Tanggal</span>
            <span><?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></span>
        </div>
        <div class="thermal-row">
            <span><?= $isPos ? 'Kasir' : 'Admin/Kasir' ?></span>
            <span><?= htmlspecialchars($namaPetugas) ?></span>
        </div>
        <div class="thermal-row">
            <span>Pelanggan</span>
            <span><?= htmlspecialchars($namaPenerima) ?></span>
        </div>

        <div class="thermal-divider"></div>

        <!-- Thermal Items -->
        <?php $no = 1; foreach ($aggregatedItems as $it): 
            $lineTotal = $it['qty'] * $it['harga_satuan'];
        ?>
            <div class="thermal-item-block">
                <div class="thermal-item-name"><?= $no++ ?>. <?= htmlspecialchars($it['nama_item']) ?></div>
                <div class="thermal-row thermal-item-calc">
                    <span><?= intval($it['qty']) ?> x <?= number_format($it['harga_satuan'], 0, ',', '.') ?></span>
                    <span>Rp <?= number_format($lineTotal, 0, ',', '.') ?></span>
                </div>
                <?php if (!empty($it['catatan_logistik'])): ?>
                    <div class="thermal-item-sn">▷ <?= htmlspecialchars($it['catatan_logistik']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="thermal-divider"></div>

        <!-- Thermal Totals -->
        <div class="thermal-row">
            <span>Subtotal</span>
            <span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
        </div>
        <?php if ($ongkir > 0): ?>
            <div class="thermal-row">
                <span>Ongkos Kirim</span>
                <span>Rp <?= number_format($ongkir, 0, ',', '.') ?></span>
            </div>
        <?php endif; ?>
        <div class="thermal-row thermal-row-bold">
            <span>TOTAL</span>
            <span>Rp <?= number_format($grandTotal, 0, ',', '.') ?></span>
        </div>
        <div class="thermal-row">
            <span>Pembayaran</span>
            <span><?= strtoupper(htmlspecialchars($metodeBayar)) ?></span>
        </div>
        <div class="thermal-row">
            <span>Status</span>
            <span>LUNAS</span>
        </div>

        <div class="thermal-divider-double"></div>

        <!-- Thermal Footer -->
        <div class="thermal-footer">
            <div>Barang yang dibeli tidak dapat ditukar/dikembalikan kecuali cacat pabrik.</div>
            <div style="margin-top: 6px; font-weight: bold;">Terima Kasih Atas Kunjungan Anda!</div>
            <div>ZenCare Medical - Sehat &amp; Terpercaya</div>
        </div>
    </div>

    <!-- Script Pengendali Format Switcher -->
    <script>
        function switchPrintFormat(fmt) {
            const body = document.body;
            const pageStyle = document.getElementById('print-page-style');
            const btnA4 = document.getElementById('btn-mode-a4');
            const btnThermal = document.getElementById('btn-mode-thermal');

            if (fmt === 'thermal') {
                body.classList.remove('mode-a4');
                body.classList.add('mode-thermal');
                if (pageStyle) {
                    pageStyle.innerHTML = '@page { size: 80mm auto; margin: 3mm 4mm; }';
                }
                btnThermal.classList.add('active');
                btnA4.classList.remove('active');
            } else {
                body.classList.remove('mode-thermal');
                body.classList.add('mode-a4');
                if (pageStyle) {
                    pageStyle.innerHTML = '@page { size: A4 portrait; margin: 12mm 15mm; }';
                }
                btnA4.classList.add('active');
                btnThermal.classList.remove('active');
            }

            // Simpan status di URL tanpa reload halaman
            const url = new URL(window.location);
            url.searchParams.set('format', fmt);
            window.history.replaceState({}, '', url);
        }
    </script>
</body>
</html>
