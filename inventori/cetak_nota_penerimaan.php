<?php
// File: inventori/cetak_nota_penerimaan.php
// Bukti Penerimaan Barang & Retur Fisik
// Standar Operasional Gudang Farmasi & Apotek ZenCare Medical

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';

requireLogin();

$idPenerimaan = intval($_GET['id'] ?? 0);

if ($idPenerimaan <= 0) {
    die("ID Dokumen Penerimaan tidak valid.");
}

// 1. Ambil Data Header Penerimaan
$stmtHead = $pdo->prepare("
    SELECT ps.*, 
           u.nama_lengkap AS nama_petugas, u.role AS role_petugas,
           s.nama AS nama_supplier, s.kontak AS kontak_supplier, s.email AS email_supplier, s.alamat AS alamat_supplier
    FROM penerimaan_stok ps
    LEFT JOIN users u ON u.id = ps.dibuat_oleh
    LEFT JOIN supplier s ON s.id = ps.id_supplier
    WHERE ps.id = ?
");
$stmtHead->execute([$idPenerimaan]);
$doc = $stmtHead->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    die("Dokumen penerimaan dengan ID #$idPenerimaan tidak ditemukan.");
}

// 2. Ambil Rincian Item Barang
$stmtDet = $pdo->prepare("
    SELECT pd.*, 
           pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
           pi.nama_produk, pi.kategori, pi.sku_induk
    FROM penerimaan_detail pd
    JOIN produk_variasi pv ON pv.id = pd.id_variasi
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    WHERE pd.id_penerimaan = ?
    ORDER BY pd.id ASC
");
$stmtDet->execute([$idPenerimaan]);
$items = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

// 3. Perhitungan Statistik Finansial & Klaim
$totalQtyPesan          = 0;
$totalQtyTerimaAwal     = 0;
$totalQtyRusak          = 0;
$totalQtyPengganti      = 0;
$totalQtyFisikAkhir     = 0;

$totalNilaiFaktur       = 0; // Total Nilai Pesanan Faktur Awal (Pesan x Harga)
$totalNilaiTerimaAwal   = 0; // Total Nilai Fisik Awal Masuk Stok (Terima Awal x Harga)
$totalNilaiRusak        = 0; // Total Nilai Barang Retur Cacat (Rusak x Harga)
$totalNilaiPengganti    = 0; // Total Nilai Barang Pengganti yang Telah Diterima (Pengganti x Harga)
$totalNilaiPotongFaktur = 0; // Total Nilai Potong Faktur jika klaim via credit note
$totalNilaiFisikAkhir   = 0; // Total Nilai Fisik yang Akhirnya Berada di Gudang

$hasPendingRetur   = false;
$hasResolvedTukar  = false;
$hasResolvedPotong = false;
$returItemsList    = [];

foreach ($items as $it) {
    $qPesan     = (int)($it['qty_pesan'] ?: $it['qty_terima']);
    $qTerima    = (int)$it['qty_terima'];
    $qRusak     = (int)($it['qty_rusak'] ?? 0);
    $qPengganti = (int)($it['qty_pengganti_diterima'] ?? 0);
    $hBeli      = (float)($it['harga_beli'] ?? 0);
    $stRet      = $it['status_retur'] ?? 'tidak_ada';

    $totalQtyPesan      += $qPesan;
    $totalQtyTerimaAwal += $qTerima;
    $totalQtyRusak      += $qRusak;

    $totalNilaiFaktur     += ($qPesan * $hBeli);
    $totalNilaiTerimaAwal += ($qTerima * $hBeli);
    $totalNilaiRusak      += ($qRusak * $hBeli);

    if ($stRet === 'selesai_tukar_barang') {
        $hasResolvedTukar       = true;
        $totalQtyPengganti      += $qPengganti;
        $totalNilaiPengganti    += ($qPengganti * $hBeli);
    } elseif ($stRet === 'selesai_potong_faktur') {
        $hasResolvedPotong      = true;
        $totalNilaiPotongFaktur += ($qRusak * $hBeli);
    } elseif ($stRet === 'menunggu_pengganti') {
        $hasPendingRetur        = true;
    }

    if ($qRusak > 0) {
        $returItemsList[] = $it;
    }
}

// Total Kuantiti dan Nilai Fisik Akhir di Gudang
$totalQtyFisikAkhir   = $totalQtyTerimaAwal + $totalQtyPengganti;
$totalNilaiFisikAkhir = $totalNilaiTerimaAwal + $totalNilaiPengganti;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penerimaan Barang #<?= htmlspecialchars($doc['no_referensi']) ?> - ZenCare Medical</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 24px;
            font-size: 11px;
            line-height: 1.45;
        }
        .container {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 24px 28px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        /* Toolbar Layar (Tidak ikut tercetak) */
        .screen-toolbar {
            max-width: 860px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .toolbar-title {
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            border: none;
        }
        .btn-print {
            background: #0f2d5a;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #1e3a8a;
        }
        .btn-back {
            background: #334155;
            color: #f1f5f9;
        }
        .btn-back:hover {
            background: #475569;
        }

        /* Kop Surat Resmi ZenCare Medical */
        .header-kop {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-kop-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-kop-brand img {
            height: 48px;
            width: auto;
            object-fit: contain;
        }
        .header-kop-title {
            font-size: 15pt;
            font-weight: 800;
            color: #0f2d5a;
            margin: 0;
            line-height: 1.1;
        }
        .header-kop-sub {
            font-size: 8.5pt;
            color: #475569;
            margin: 2px 0 0 0;
            font-weight: 600;
        }
        .header-kop-legal {
            font-size: 8pt;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .header-kop-meta {
            text-align: right;
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.4;
        }
        .header-kop-meta strong {
            color: #0f2d5a;
            font-size: 10pt;
            display: block;
        }

        /* Judul Dokumen */
        .doc-title-block {
            text-align: center;
            margin: 10px 0 14px 0;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Metadata Info */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
        }
        .meta-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .meta-label {
            color: #64748b;
            width: 18%;
        }
        .meta-val {
            color: #0f172a;
            font-weight: 600;
            width: 32%;
        }

        /* Tabel Rincian Barang */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .items-table th, .items-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            vertical-align: middle;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
        }
        .col-center { text-align: center !important; }
        .col-right  { text-align: right !important; }
        .col-mono   { font-family: ui-monospace, SFMono-Regular, monospace; }

        /* Kolom Tanda Tangan */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 28px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 16px;
        }

        /* Footer Dokumen */
        .doc-footer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            display: flex;
            justify-content: space-between;
            font-size: 7.5pt;
            color: #94a3b8;
        }

        /* Media Print Rules */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .container {
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<!-- TOOLBAR SCREEN ONLY (Tidak ikut tercetak) -->
<div class="screen-toolbar no-print">
    <div class="toolbar-title">
        <svg style="width:16px;height:16px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <span>Pratinjau Bukti Penerimaan Barang</span>
    </div>
    <div style="display:flex;gap:8px;">
        <button type="button" onclick="window.print()" class="btn-action btn-print">
            <svg style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            <span>Cetak / PDF (A4)</span>
        </button>
        <button type="button" onclick="window.close()" class="btn-action btn-back">
            <span>Tutup</span>
        </button>
    </div>
</div>

<div class="container">
    <!-- KOP RESMI ZENCARE MEDICAL -->
    <div class="header-kop">
        <div class="header-kop-brand">
            <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" onerror="this.src='../gambar/Logo.png'">
            <div>
                <h1 class="header-kop-title">ZENCARE MEDICAL</h1>
                <p class="header-kop-sub">Distributor Alat Kesehatan dan Obat Omnichannel</p>
                <p class="header-kop-legal">Izin Apotek: 445/SIA/2026 • SIPA: 19890425/SIPA/2026/01 • Jl. Veteran No. 12, Kota Malang</p>
            </div>
        </div>
        <div class="header-kop-meta">
            <strong>BUKTI PENERIMAAN</strong>
            <div>No: <?= htmlspecialchars($doc['no_referensi']) ?></div>
            <div>Tgl: <?= date('d/m/Y', strtotime($doc['tanggal_terima'])) ?></div>
        </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title-block">
        <h2 class="doc-title">BUKTI PENERIMAAN BARANG</h2>
    </div>

    <!-- METADATA DOKUMEN (BERSIH & RAPI) -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">No. Referensi:</td>
            <td class="meta-val col-mono"><?= htmlspecialchars($doc['no_referensi']) ?></td>
            <td class="meta-label">Supplier / Pengirim:</td>
            <td class="meta-val"><?= htmlspecialchars($doc['nama_supplier'] ?: 'Umum / Pembelian Langsung') ?></td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Terima:</td>
            <td class="meta-val"><?= date('d/m/Y', strtotime($doc['tanggal_terima'])) ?> (<?= date('H:i', strtotime($doc['created_at'])) ?> WIB)</td>
            <td class="meta-label">Kontak Supplier:</td>
            <td class="meta-val"><?= htmlspecialchars($doc['kontak_supplier'] ? ($doc['kontak_supplier'] . ($doc['email_supplier'] ? ' (' . $doc['email_supplier'] . ')' : '')) : ($doc['email_supplier'] ?: '-')) ?></td>
        </tr>
        <tr>
            <td class="meta-label">Sumber Pengadaan:</td>
            <td class="meta-val"><?= htmlspecialchars($doc['sumber']) ?></td>
            <td class="meta-label">Diinput Sistem:</td>
            <td class="meta-val"><?= htmlspecialchars($doc['nama_petugas'] ?: 'Admin Operasional') ?></td>
        </tr>
    </table>

    <!-- TABEL RINCIAN BARANG -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-center" style="width: 4%;">No</th>
                <th style="width: 30%;">Nama Produk</th>
                <th class="col-center" style="width: 14%;">Batch / Exp / SN</th>
                <th class="col-center" style="width: 8%;">Pesan</th>
                <th class="col-center" style="width: 8%;">Diterima</th>
                <th class="col-center" style="width: 8%;">Rusak</th>
                <th class="col-right" style="width: 13%;">Harga Satuan</th>
                <th class="col-right" style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $idx => $it): 
                $qP = (int)($it['qty_pesan'] ?: $it['qty_terima']);
                $qT = (int)$it['qty_terima'];
                $qR = (int)($it['qty_rusak'] ?? 0);
                $qGanti = (int)($it['qty_pengganti_diterima'] ?? 0);
                $stRet = $it['status_retur'] ?? 'tidak_ada';
                $hB = (float)($it['harga_beli'] ?? 0);
                $uom = $it['satuan_besar'] ?? 'Dus';

                // Subtotal baris
                if ($stRet === 'selesai_tukar_barang') {
                    $subBaris = ($qT + $qGanti) * $hB;
                } else {
                    $subBaris = $qT * $hB;
                }

                // Identitas Batch/Exp/SN
                $identitasArr = [];
                if ($it['kategori'] === 'Obat') {
                    if (!empty($it['no_batch'])) $identitasArr[] = "B: " . htmlspecialchars($it['no_batch']);
                    if (!empty($it['tgl_exp']))  $identitasArr[] = "Exp: " . date('d/m/y', strtotime($it['tgl_exp']));
                    if ($stRet === 'selesai_tukar_barang' && !empty($it['no_batch_pengganti'])) {
                        $identitasArr[] = "<span style='color:#0f2d5a;font-weight:700;'>Ganti B: " . htmlspecialchars($it['no_batch_pengganti']) . "</span>";
                    }
                } else {
                    $identitasArr[] = "Alkes (SN)";
                }
            ?>
            <tr>
                <td class="col-center col-mono"><?= $idx + 1 ?></td>
                <td>
                    <strong style="color:#0f172a;"><?= htmlspecialchars($it['nama_produk']) ?></strong>
                    <div style="font-size:9px;color:#64748b;"><?= htmlspecialchars($it['nama_variasi']) ?></div>
                    <?php if ($stRet === 'selesai_tukar_barang' && !empty($it['catatan_retur'])): ?>
                        <div style="font-size:8pt;color:#0f2d5a;margin-top:2px;font-weight:600;">
                            <?= htmlspecialchars($it['catatan_retur']) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="col-center" style="font-size:8.5pt;line-height:1.25;">
                    <?= implode('<br>', $identitasArr) ?>
                </td>
                <td class="col-center"><?= $qP ?> <span style="font-size:8.5pt;color:#64748b;"><?= htmlspecialchars($uom) ?></span></td>
                <td class="col-center" style="font-weight:700;">
                    <?php if ($stRet === 'selesai_tukar_barang' && $qGanti > 0): ?>
                        <div><?= ($qT + $qGanti) ?> <span style="font-size:8.5pt;color:#64748b;"><?= htmlspecialchars($uom) ?></span></div>
                        <div style="font-size:7.5pt;color:#0f2d5a;font-weight:600;">(<?= $qT ?> awal + <?= $qGanti ?> ganti)</div>
                    <?php else: ?>
                        <?= $qT ?> <span style="font-size:8.5pt;color:#64748b;"><?= htmlspecialchars($uom) ?></span>
                    <?php endif; ?>
                </td>
                <td class="col-center">
                    <?php if ($qR > 0): ?>
                        <strong style="color:#0f172a;"><?= $qR ?></strong> <span style="font-size:8.5pt;"><?= htmlspecialchars($uom) ?></span>
                        <?php if ($stRet === 'selesai_tukar_barang'): ?>
                            <div style="font-size:7.5pt;color:#16a34a;font-weight:700;">Diganti</div>
                        <?php elseif ($stRet === 'selesai_potong_faktur'): ?>
                            <div style="font-size:7.5pt;color:#0284c7;font-weight:700;">Potong Faktur</div>
                        <?php else: ?>
                            <div style="font-size:7.5pt;color:#d97706;font-weight:700;">Menunggu</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#94a3b8;">0</span>
                    <?php endif; ?>
                </td>
                <td class="col-right col-mono">Rp <?= number_format($hB, 0, ',', '.') ?></td>
                <td class="col-right col-mono" style="font-weight:700;">Rp <?= number_format($subBaris, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="background:#f8fafc;font-weight:700;">
            <tr>
                <td colspan="3" class="col-right">Total Kuantiti:</td>
                <td class="col-center"><?= $totalQtyPesan ?></td>
                <td class="col-center">
                    <?php if ($totalQtyPengganti > 0): ?>
                        <?= $totalQtyFisikAkhir ?>
                        <span style="font-size:7.5pt;display:block;color:#0f2d5a;">(Lengkap)</span>
                    <?php else: ?>
                        <?= $totalQtyTerimaAwal ?>
                    <?php endif; ?>
                </td>
                <td class="col-center"><?= $totalQtyRusak ?></td>
                <td class="col-right">Total Fisik:</td>
                <td class="col-right col-mono" style="font-size:11px;font-weight:800;">Rp <?= number_format($totalNilaiFisikAkhir, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- CATATAN & RINGKASAN PEMBAYARAN (SEPERTI FAKTUR ASLI) -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 10px; margin-bottom: 20px;">
        <div style="width: 56%; font-size: 9pt; color: #334155;">
            <div style="font-weight: 700; color: #0f172a; margin-bottom: 2px;">Catatan:</div>
            <div><?= htmlspecialchars($doc['catatan'] ?: 'Barang telah diperiksa fisik dan diterima sesuai surat jalan.') ?></div>
            
            <?php if ($totalQtyRusak > 0): ?>
                <div style="margin-top: 6px; padding: 6px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 8.5pt;">
                    <strong style="color: #0f172a;">Keterangan Retur:</strong>
                    <div style="margin-top: 2px;">
                        Terdapat <?= $totalQtyRusak ?> unit barang rusak/cacat yang dikembalikan ke pihak supplier.
                        <?php if ($totalQtyPengganti > 0 && !$hasPendingRetur): ?>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">
                                Selesai Ditukar: <?= $totalQtyPengganti ?> unit barang pengganti telah diterima fisik dan masuk stok gudang. Total persediaan fisik diterima telah lengkap 100%.
                            </div>
                        <?php elseif ($hasResolvedPotong && !$hasPendingRetur): ?>
                            <div style="font-weight: 600; color: #0f172a; margin-top: 2px;">
                                Selesai Potong Faktur: Nilai tagihan invoice dikurangi Rp <?= number_format($totalNilaiPotongFaktur, 0, ',', '.') ?> (Credit Note).
                            </div>
                        <?php else: ?>
                            <div style="color: #64748b; margin-top: 2px;">
                                Menunggu Penggantian: Sedang menunggu pengiriman unit pengganti dari supplier (Saldo klaim: Rp <?= number_format($totalNilaiRusak, 0, ',', '.') ?>).
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div style="width: 40%;">
            <table style="width: 100%; border-collapse: collapse; font-size: 9.5pt;">
                <tr>
                    <td style="padding: 3px 0; color: #64748b;">Total Faktur Pesanan:</td>
                    <td style="padding: 3px 0; text-align: right; font-family: monospace; font-weight: 600;">Rp <?= number_format($totalNilaiFaktur, 0, ',', '.') ?></td>
                </tr>
                <?php if ($totalQtyRusak > 0): ?>
                <tr>
                    <td style="padding: 3px 0; color: #64748b;">Barang Retur Rusak (<?= $totalQtyRusak ?> unit):</td>
                    <td style="padding: 3px 0; text-align: right; font-family: monospace; color: #64748b;">- Rp <?= number_format($totalNilaiRusak, 0, ',', '.') ?></td>
                </tr>
                <?php if ($totalQtyPengganti > 0): ?>
                <tr>
                    <td style="padding: 3px 0; color: #0f2d5a; font-weight: 600;">Barang Pengganti Masuk (<?= $totalQtyPengganti ?> unit):</td>
                    <td style="padding: 3px 0; text-align: right; font-family: monospace; color: #0f2d5a; font-weight: 600;">+ Rp <?= number_format($totalNilaiPengganti, 0, ',', '.') ?></td>
                </tr>
                <tr style="border-top: 1.5px solid #0f172a; font-weight: 800; font-size: 10pt;">
                    <td style="padding: 6px 0; color: #0f172a;">Total Diterima Fisik (Lengkap):</td>
                    <td style="padding: 6px 0; text-align: right; font-family: monospace; color: #0f172a;">Rp <?= number_format($totalNilaiFisikAkhir, 0, ',', '.') ?></td>
                </tr>
                <?php elseif ($hasResolvedPotong && !$hasPendingRetur): ?>
                <tr style="border-top: 1.5px solid #0f172a; font-weight: 800; font-size: 10pt;">
                    <td style="padding: 6px 0; color: #0f172a;">Total Tagihan Akhir Faktur:</td>
                    <td style="padding: 6px 0; text-align: right; font-family: monospace; color: #0f172a;">Rp <?= number_format($totalNilaiFisikAkhir, 0, ',', '.') ?></td>
                </tr>
                <?php else: ?>
                <tr style="border-top: 1.5px solid #0f172a; font-weight: 800; font-size: 10pt;">
                    <td style="padding: 6px 0; color: #0f172a;">Total Diterima Fisik (Saat Ini):</td>
                    <td style="padding: 6px 0; text-align: right; font-family: monospace; color: #0f172a;">Rp <?= number_format($totalNilaiFisikAkhir, 0, ',', '.') ?></td>
                </tr>
                <?php endif; ?>
                <?php else: ?>
                <tr style="border-top: 1.5px solid #0f172a; font-weight: 800; font-size: 10pt;">
                    <td style="padding: 6px 0; color: #0f172a;">Total Diterima Fisik:</td>
                    <td style="padding: 6px 0; text-align: right; font-family: monospace; color: #0f172a;">Rp <?= number_format($totalNilaiFisikAkhir, 0, ',', '.') ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- TANDA TANGAN DUA PIHAK -->
    <table class="signature-table">
        <tr>
            <td>
                <div style="font-size: 9pt; color: #64748b; margin-bottom: 2px;">Yang Menyerahkan,</div>
                <div style="font-size: 9.5pt; font-weight: 700; color: #0f172a; margin-bottom: 50px;">( Pihak Supplier / Pengirim )</div>
                <div style="border-bottom: 1px solid #0f172a; width: 170px; margin: 0 auto 4px auto;"></div>
                <div style="font-size: 8.5pt; color: #64748b;">Tanda Tangan &amp; Nama Terang</div>
            </td>
            <td>
                <div style="font-size: 9pt; color: #64748b; margin-bottom: 2px;">Yang Menerima,</div>
                <div style="font-size: 9.5pt; font-weight: 700; color: #0f172a; margin-bottom: 50px;">ZenCare Medical</div>
                <div style="border-bottom: 1px solid #0f172a; width: 170px; margin: 0 auto 4px auto;"></div>
                <div style="font-size: 8.5pt; color: #64748b;">Petugas Gudang / Pemeriksa Fisik</div>
            </td>
        </tr>
    </table>

    <!-- FOOTER INFORMASI DOKUMEN -->
    <div class="doc-footer">
        <div>
            Dokumen Bukti Penerimaan Barang • ZenCare Medical • Diinput Sistem: <?= htmlspecialchars($doc['nama_petugas'] ?: 'Admin Operasional') ?>
        </div>
        <div>
            Dicetak: <?= date('d/m/Y H:i') ?> WIB
        </div>
    </div>
</div>

</body>
</html>
