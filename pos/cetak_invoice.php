<?php
// File: pos/cetak_invoice.php
// Generator Invoice PDF / Print A4 Bebas Clipping
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$noInvoice = $_GET['no_invoice'] ?? '';

if (empty($noInvoice)) {
    die("No Invoice tidak valid.");
}

$stmt = $pdo->prepare("
    SELECT p.*, c.nama AS nama_cabang, c.alamat AS alamat_cabang
    FROM penjualan p
    JOIN cabang c ON p.id_cabang = c.id
    WHERE p.no_invoice = ?
");
$stmt->execute([$noInvoice]);
$trx = $stmt->fetch();

if (!$trx) {
    die("Transaksi dengan No Invoice $noInvoice tidak ditemukan.");
}

$stmtDetail = $pdo->prepare("
    SELECT d.*, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_item, v.sku_variasi
    FROM detail_penjualan d
    JOIN produk_variasi v ON d.id_variasi = v.id
    JOIN produk_induk i ON v.id_produk_induk = i.id
    WHERE d.id_penjualan = ?
");
$stmtDetail->execute([$trx['id']]);
$items = $stmtDetail->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= htmlspecialchars($trx['no_invoice']) ?> - ZenCare Medical</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
            font-size: 12px;
            line-height: 1.4;
        }
        .invoice-box {
            width: 100%;
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }
        .header-table, .details-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            box-sizing: border-box;
            table-layout: fixed;
            word-wrap: break-word;
        }
        .header-table td { vertical-align: top; }
        .title { font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .subtitle { font-size: 11px; color: #555; }
        .divider { border-bottom: 2px solid #000; margin: 15px 0; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 11px; }
        .items-table th { background-color: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        @media print {
            .invoice-box { border: none; padding: 0; }
            .no-print { display: none; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin: 20px 0;">
    <button onclick="window.print()" style="background: #000; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
        🖨️ Cetak / Download Invoice PDF (A4)
    </button>
</div>

<div class="invoice-box">
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <div class="title">ZenCare Medical</div>
                <div class="subtitle"><?= htmlspecialchars($trx['nama_cabang']) ?></div>
                <div class="subtitle"><?= htmlspecialchars($trx['alamat_cabang']) ?></div>
            </td>
            <td style="width: 40%;" class="text-right">
                <h2 style="margin: 0; font-size: 16px;">INVOICE</h2>
                <div style="font-weight: bold; margin-top: 5px;">#<?= htmlspecialchars($trx['no_invoice']) ?></div>
                <div class="subtitle">Tanggal: <?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?></div>
                <div class="subtitle">Tipe: <?= strtoupper(htmlspecialchars($trx['tipe_transaksi'])) ?></div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="details-table" style="margin-bottom: 20px;">
        <tr>
            <td style="width: 50%;">
                <strong>Penerima / Pelanggan:</strong><br>
                <?= htmlspecialchars($trx['nama_penerima'] ?? 'Pembeli POS Kasir Direct') ?><br>
                <?= htmlspecialchars($trx['telepon'] ?? '-') ?><br>
                <?= htmlspecialchars($trx['alamat_lengkap'] ?? '-') ?>
            </td>
            <td style="width: 50%;" class="text-right">
                <strong>Pengiriman &amp; Status:</strong><br>
                Kurir: <?= strtoupper(htmlspecialchars($trx['kurir'] ?? 'POS Kasir')) ?><br>
                Layanan: <?= htmlspecialchars($trx['layanan'] ?? 'Direct Takeaway') ?><br>
                Status Pesanan: <strong><?= htmlspecialchars($trx['status_pesanan']) ?></strong>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">No</th>
                <th style="width: 20%;">SKU</th>
                <th style="width: 40%;">Nama Barang / Variasi</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 20%;" class="text-right">Harga Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($items as $item): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= htmlspecialchars($item['sku_variasi']) ?></td>
                    <td><?= htmlspecialchars($item['nama_item']) ?></td>
                    <td class="text-center"><?= intval($item['qty']) ?></td>
                    <td class="text-right">Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="header-table" style="margin-top: 15px;">
        <tr>
            <td style="width: 60%;">
                <div class="subtitle" style="margin-top: 20px;">
                    * Struk / Invoice ini sah secara digital dari ZenCare Medical Omnichannel System.<br>
                    * Klaim garansi barang rusak wajib melampirkan nomor invoice ini.
                </div>
            </td>
            <td style="width: 40%;">
                <table style="width: 100%; font-size: 11px;">
                    <tr>
                        <td style="padding: 4px 0;">Subtotal:</td>
                        <td class="text-right">Rp <?= number_format($trx['total_harga'] - $trx['ongkir'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0;">Ongkos Kirim:</td>
                        <td class="text-right">Rp <?= number_format($trx['ongkir'], 0, ',', '.') ?></td>
                    </tr>
                    <tr style="font-weight: bold; border-top: 1px solid #000;">
                        <td style="padding: 6px 0; font-size: 13px;">GRAND TOTAL:</td>
                        <td class="text-right" style="font-size: 13px;">Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top: 40px; text-align: center; font-size: 10px; color: #777; border-top: 1px dashed #ccc; padding-top: 10px;">
        Terima Kasih Telah Berbelanja di ZenCare Medical | Single Source of Truth Inventory
    </div>
</div>

</body>
</html>
