<?php
// File: inventori/cetak_berita_acara_opname.php
// Dokumen Resmi Berita Acara Hasil Pemeriksaan Fisik (Stok Opname) - ZenCare Medical
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';

requireRole(['superadmin', 'admin']);

$idSesi = intval($_GET['id_sesi'] ?? 0);

if (!$idSesi) {
    die("ID Sesi tidak valid.");
}

$stmtSesi = $pdo->prepare("
    SELECT so.*, u_buka.nama_lengkap AS nama_pembuka, u_tutup.nama_lengkap AS nama_penutup
    FROM sesi_opname so
    LEFT JOIN users u_buka ON so.dibekukan_oleh = u_buka.id
    LEFT JOIN users u_tutup ON so.dibuka_oleh = u_tutup.id
    WHERE so.id = ?
");
$stmtSesi->execute([$idSesi]);
$sesi = $stmtSesi->fetch(PDO::FETCH_ASSOC);

if (!$sesi) {
    die("Data sesi opname tidak ditemukan.");
}

$stmtItems = $pdo->prepare("
    SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
           pv.harga_jual_kecil, pv.harga_jual_besar,
           (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli,
           pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp,
           u_hit.nama_lengkap AS nama_penghitung, u_mut.nama_lengkap AS nama_pemutasi
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    LEFT JOIN users u_hit ON dob.dihitung_oleh = u_hit.id
    LEFT JOIN users u_mut ON dob.dimutasi_oleh = u_mut.id
    WHERE dob.id_sesi = ?
    ORDER BY dob.jenis_komoditas ASC, pi.nama_produk ASC, pv.nama_variasi ASC, (dob.satuan_tipe = 'besar') DESC, sb.tgl_exp ASC
");
$stmtItems->execute([$idSesi]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$totalItem = count($items);
$totalSesuai = 0;
$totalSelisih = 0;
$totalMutasi = 0;
$grandTotalKerugian = 0;

$groupedItems = [];
foreach ($items as &$it) {
    $sel = intval($it['selisih'] ?? 0);
    if ($it['hitung_fisik'] !== null && $sel === 0) {
        $totalSesuai++;
    } elseif ($sel !== 0) {
        $totalSelisih++;
        if ($it['status_mutasi'] === 'Sudah Dimutasi') {
            $totalMutasi++;
        }
    }

    // Tentukan Harga Beli Acuan Acuan (Harga Beli):
    $rasio = max(1, intval($it['rasio_konversi'] ?? 1));
    $hBeli = floatval($it['last_harga_beli'] ?? 0);
    if ($hBeli <= 0) {
        // Estimasi Harga Beli modal 80% dari harga jual jika belum ada riwayat penerimaan
        $hBeli = floatval($it['harga_jual_besar'] ?? 0) * 0.8;
    }
    if ($hBeli <= 0) {
        $hBeli = floatval($it['harga_jual_kecil'] ?? 0) * $rasio * 0.8;
    }
    if ($hBeli <= 0) $hBeli = 15000;

    $hargaSatuan = ($it['satuan_tipe'] === 'besar') ? $hBeli : round($hBeli / $rasio);
    $it['harga_satuan'] = $hargaSatuan;

    // Hitung kerugian: Hanya selisih kurang (defisit) yang menimbulkan beban kerugian
    if ($sel < 0) {
        $subKerugian = abs($sel) * $hargaSatuan;
    } else {
        $subKerugian = 0;
    }
    $it['subtotal_kerugian'] = $subKerugian;
    $grandTotalKerugian += $subKerugian;

    $vid = $it['id_variasi'];
    if (!isset($groupedItems[$vid])) {
        $groupedItems[$vid] = [
            'id_variasi'     => $vid,
            'nama_produk'    => $it['nama_produk'],
            'nama_variasi'   => $it['nama_variasi'],
            'sku_variasi'    => $it['sku_variasi'] ?? '',
            'kategori'       => $it['kategori'] ?? '',
            'jenis_komoditas'=> $it['jenis_komoditas'],
            'satuan_besar'   => $it['satuan_besar'] ?? '',
            'satuan_kecil'   => $it['satuan_kecil'] ?? '',
            'rasio_konversi' => intval($it['rasio_konversi'] ?? 1),
            'items'          => []
        ];
    }
    $groupedItems[$vid]['items'][] = $it;
}
unset($it);

// Ambil seluruh Serial Number aktif (Tersedia) per variasi alkes untuk dicantumkan di Berita Acara
$alkesSnMap = [];
$snRows = $pdo->query("SELECT id_variasi, serial_number, status FROM unit_serial WHERE status = 'Tersedia' ORDER BY serial_number ASC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($snRows as $sr) {
    $alkesSnMap[$sr['id_variasi']][] = $sr['serial_number'];
}

// Ambil rekaman Serial Number yang disesuaikan/dimutasi pada sesi opname ini
$mutatedSnMap = [];
$dosRows = $pdo->prepare("
    SELECT dos.id_detail_opname, dos.serial_number, dos.jenis_tindakan 
    FROM detail_opname_sn dos
    JOIN detail_opname_batch dob ON dos.id_detail_opname = dob.id
    WHERE dob.id_sesi = ?
    ORDER BY dos.id ASC
");
$dosRows->execute([$idSesi]);
foreach ($dosRows->fetchAll(PDO::FETCH_ASSOC) as $dr) {
    $mutatedSnMap[$dr['id_detail_opname']][] = $dr;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Stok Opname #<?= htmlspecialchars($sesi['kode_sesi']) ?> - ZenCare Medical</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #0f172a;
            margin: 0;
            padding: 24px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @page {
            size: A4 landscape;
            margin: 12mm 10mm 15mm 10mm;
        }
        @media print {
            .print-btn-bar { display: none !important; }
            body { padding: 0 !important; }
        }
        .header-container {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-logo-area img {
            height: 48px;
            width: auto;
            object-fit: contain;
        }
        .header-title-text h1 {
            font-size: 15pt;
            font-weight: 800;
            color: #0f2d5a;
            margin: 0;
            line-height: 1.1;
        }
        .header-title-text p {
            font-size: 8.5pt;
            color: #475569;
            margin: 2px 0 0 0;
            font-weight: 600;
        }
        .header-title-text .address {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 1px;
            font-weight: normal;
        }
        .header-meta-doc {
            text-align: right;
            font-size: 8pt;
            color: #64748b;
            line-height: 1.4;
        }
        .header-meta-doc strong {
            color: #0f2d5a;
            font-size: 9pt;
        }
        .doc-title-box {
            text-align: center;
            margin: 12px 0 14px 0;
        }
        .doc-title-box h2 {
            font-size: 12pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title-box .doc-sub {
            font-size: 8.5pt;
            color: #334155;
            margin-top: 3px;
        }
        .meta-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 8.5pt;
        }
        .meta-summary-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            background: #f8fafc;
        }
        .meta-summary-table .label {
            color: #64748b;
            font-size: 7.5pt;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }
        .meta-summary-table .val {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 20px;
        }
        thead {
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
        }
        tr.row-anomali td {
            border-top: 2.5px solid #dc2626 !important;
            border-bottom: 2.5px solid #dc2626 !important;
            background-color: #fef2f2 !important;
            font-weight: 600;
        }
        tr.row-anomali td:first-child {
            border-left: 2.5px solid #dc2626 !important;
        }
        tr.row-anomali td:last-child {
            border-right: 2.5px solid #dc2626 !important;
        }
        .page-footer-num {
            position: fixed;
            bottom: 0;
            right: 0;
            font-size: 8pt;
            color: #64748b;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            line-height: 1.35;
        }
        table.data-table th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            font-size: 8pt;
            letter-spacing: 0.3px;
            display: table-cell;
            vertical-align: middle;
        }
        .sign-container {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sign-table {
            width: 100%;
            border: none;
            text-align: center;
            font-size: 8.5pt;
        }
        .sign-table td {
            width: 50%;
            border: none;
            padding: 0 40px;
            vertical-align: top;
        }
        .sign-space {
            height: 65px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            font-size: 9pt;
        }
        .sign-role {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }
        .print-btn-bar {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 24px;
            margin: -24px -24px 20px -24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }
        .btn-print {
            background: #1a75d2;
            color: #fff;
            border: none;
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
            background: #f1f5f9;
        }
    </style>
</head>
<body>

    <!-- Bar Navigasi Tombol Cetak (Layar Saja, Otomatis Tersembunyi Saat Dicetak) -->
    <div class="print-btn-bar">
        <div style="font-size: 11px; font-weight: bold; color: #0f2d5a;">
            Pratinjau Dokumen Berita Acara Stok Opname
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-action btn-print">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak Dokumen Resmi (A4)</span>
            </button>
            <button onclick="window.close()" class="btn-action btn-close">
                Tutup
            </button>
        </div>
    </div>

    <!-- Kop Surat Resmi Bersama Logo ZenCare (Identik dengan Laporan Logistik & Penerimaan) -->
    <div class="header-container">
        <div class="header-logo-area">
            <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" onerror="this.src='../gambar/Logo.png'">
            <div class="header-title-text">
                <h1>ZENCARE MEDICAL</h1>
                <p>Distributor Alat Kesehatan dan Obat Omnichannel</p>
                <div class="address">Jl. Muharto No. 55D, Jodipan, Kec. Blimbing, Kota Malang 65127 &bull; Telp: 0895-3434-36025</div>
            </div>
        </div>
        <div class="header-meta-doc">
            <strong>DOKUMEN RESMI INVENTORI</strong>
            <div>No. Sesi: #<?= htmlspecialchars($sesi['kode_sesi']) ?></div>
            <div>Dicetak: <?= date('d/m/Y H:i') ?> WIB</div>
            <div>Oleh: <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Pengelola Stok') ?></div>
        </div>
    </div>

    <!-- Judul Dokumen Berita Acara -->
    <div class="doc-title-box">
        <h2>BERITA ACARA HASIL PEMERIKSAAN FISIK (STOK OPNAME)</h2>
        <div class="doc-sub">
            Status Sesi: <strong><?= strtoupper(htmlspecialchars($sesi['status'])) ?></strong>
            &nbsp;|&nbsp; Waktu Mulai: <?= date('d/m/Y H:i', strtotime($sesi['dibekukan_pada'])) ?> WIB
            &nbsp;|&nbsp; Waktu Selesai: <?= $sesi['dibuka_pada'] ? date('d/m/Y H:i', strtotime($sesi['dibuka_pada'])) . ' WIB' : 'Sedang Berjalan' ?>
        </div>
    </div>

    <!-- Ringkasan Statistik Cetak Standar ZenCare (5 Kotak Statistik Resmi) -->
    <table class="meta-summary-table">
        <tr>
            <td style="width: 20%;">
                <span class="label">Total Baris Barang</span>
                <span class="val"><?= number_format($totalItem) ?> Baris</span>
            </td>
            <td style="width: 20%;">
                <span class="label">Kondisi Sesuai</span>
                <span class="val" style="color: #047857;"><?= number_format($totalSesuai) ?> Baris</span>
            </td>
            <td style="width: 20%;">
                <span class="label">Selisih Terdeteksi</span>
                <span class="val" style="color: <?= $totalSelisih > 0 ? '#b91c1c' : '#047857' ?>;"><?= number_format($totalSelisih) ?> Baris</span>
            </td>
            <td style="width: 20%;">
                <span class="label">Status Mutasi</span>
                <span class="val" style="color: #1d4ed8;"><?= number_format($totalMutasi) ?> / <?= number_format($totalSelisih) ?> Dimutasi</span>
            </td>
            <td style="width: 20%;">
                <span class="label">Total Estimasi Kerugian</span>
                <span class="val" style="color: #b91c1c;">Rp <?= number_format($grandTotalKerugian, 0, ',', '.') ?></span>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Hasil Pemeriksaan Fisik Lapangan (Dikelompokkan Berdasarkan Produk Induk & Variasi) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th style="width: 85px;">Tingkat Satuan</th>
                <th>Batch / Identitas Serial Number</th>
                <th style="width: 45px;">Satuan</th>
                <th style="width: 45px;">Sistem</th>
                <th style="width: 45px;">Fisik</th>
                <th style="width: 48px;">Selisih</th>
                <th style="width: 70px;">Harga Beli Acuan</th>
                <th style="width: 80px;">Kerugian (Rp)</th>
                <th style="width: 105px;">Tindakan Mutasi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($groupedItems)): ?>
                <tr>
                    <td colspan="10" style="text-align: center; padding: 12px; color: #64748b; font-style: italic;">
                        Tidak ada data pemeriksaan fisik pada sesi ini.
                    </td>
                </tr>
            <?php else: 
                $pNo = 1;
                foreach ($groupedItems as $grp): 
                    $firstItemGrp = $grp['items'][0] ?? null;
                    $grpHasBatch = ($firstItemGrp && (!empty($firstItemGrp['id_batch']) || !empty($firstItemGrp['no_batch'])));
            ?>
                <!-- Header Produk Induk & Variasi (Format Bersih & Rapi) -->
                <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #64748b; border-bottom: 1px solid #cbd5e1; page-break-after: avoid;">
                    <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: center; color: #334155; font-weight: bold;"><?= $pNo++ ?></td>
                    <td colspan="4" style="border: 1px solid #cbd5e1; padding: 6px 10px;">
                        <span style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; margin-right: 6px;">
                            <?= $grp['kategori'] === 'Alat Kesehatan' ? ($grpHasBatch ? 'Alkes (BMHP)' : 'Alkes (SN)') : 'Obat' ?>
                        </span>
                        <strong style="font-size: 9pt; color: #0f2d5a;"><?= htmlspecialchars($grp['nama_produk']) ?></strong>
                        <span style="font-weight: normal; color: #475569;">— <?= htmlspecialchars($grp['nama_variasi']) ?></span>
                        <?php if (!empty($grp['sku_variasi'])): ?>
                            <span style="font-family: monospace; font-size: 7.5pt; color: #64748b;">[<?= htmlspecialchars($grp['sku_variasi']) ?>]</span>
                        <?php endif; ?>
                    </td>
                    <td colspan="5" style="border: 1px solid #cbd5e1; padding: 6px 10px; text-align: right; font-size: 8pt; color: #334155;">
                        <?php if ($grpHasBatch && $grp['rasio_konversi'] > 1): ?>
                            <span style="font-weight: 600; color: #334155;">Konversi: 1 <?= htmlspecialchars($grp['satuan_besar']) ?> = <?= $grp['rasio_konversi'] ?> <?= htmlspecialchars($grp['satuan_kecil']) ?></span>
                            <span style="color: #64748b; font-size: 7.5pt;">(Satuan Besar &amp; Satuan Kecil)</span>
                        <?php elseif (!$grpHasBatch): ?>
                            <span style="font-weight: 600; color: #0f2d5a;">Pelacakan Serial Number</span>
                        <?php else: ?>
                            <span style="color: #64748b;">Satuan Tunggal (<?= htmlspecialchars($grp['satuan_kecil']) ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Baris Sub-Item / Batch / SN di Bawah Produk Tersebut -->
                <?php foreach ($grp['items'] as $it): 
                    $sel = intval($it['selisih'] ?? 0);
                    $fRusakRow = intval($it['fisik_rusak'] ?? 0);
                    $fExpRow = intval($it['fisik_expired'] ?? 0);
                    $isAnomali = ($sel !== 0 || $fRusakRow > 0 || $fExpRow > 0);
                    $itHasBatch = (!empty($it['id_batch']) || !empty($it['no_batch']));
                ?>
                    <tr class="<?= $isAnomali ? 'row-anomali' : '' ?>" style="border-bottom: 1px solid #cbd5e1;">
                        <td style="text-align: center; color: #94a3b8; font-size: 8pt; border: 1px solid #cbd5e1;">&gt;</td>
                        <td style="border: 1px solid #cbd5e1; font-size: 8pt; padding: 6px 8px;">
                            <?php if ($itHasBatch): ?>
                                <?php if ($it['satuan_tipe'] === 'besar'): ?>
                                    <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; font-size: 7.5pt;">Satuan Besar</span>
                                <?php else: ?>
                                    <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: 600; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 7.5pt;">Satuan Kecil</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; font-weight: bold; background: #f1f5f9; color: #0f2d5a; border: 1px solid #cbd5e1; font-size: 7.5pt;">Unit Fisik (SN)</span>
                            <?php endif; ?>
                        </td>
                        <td style="border: 1px solid #cbd5e1; padding: 6px 8px;">
                            <?php if ($itHasBatch): ?>
                                <strong style="font-family: monospace; font-size: 8pt;"><?= htmlspecialchars($it['no_batch'] ?: '-') ?></strong>
                                <span style="font-size: 7.5pt; color: #64748b; font-family: sans-serif; margin-left: 4px;">(Exp: <?= $it['tgl_exp'] ? date('d/m/Y', strtotime($it['tgl_exp'])) : '-' ?>)</span>
                            <?php else: 
                                $snList = $alkesSnMap[$it['id_variasi']] ?? [];
                                $mutSnList = $mutatedSnMap[$it['id']] ?? [];
                            ?>
                                <div style="font-size: 7.5pt; line-height: 1.35;">
                                    <?php if (!empty($snList)): ?>
                                        <div style="color: #334155; margin-bottom: 2px;">
                                            <strong style="color: #0f2d5a;">SN Fisik Terverifikasi (<?= count($snList) ?> Unit):</strong>
                                        </div>
                                        <div style="font-family: monospace; font-size: 7pt; color: #1e293b; background: #f8fafc; padding: 3px 6px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                            <?= implode(', ', array_map('htmlspecialchars', $snList)) ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #64748b; font-style: italic;">Tidak ada unit serial aktif</span>
                                    <?php endif; ?>

                                    <?php if (!empty($mutSnList)): ?>
                                        <div style="margin-top: 3px; padding-top: 2px; border-top: 1px dashed #cbd5e1; font-size: 7pt; color: #b91c1c;">
                                            <strong>SN Disesuaikan:</strong>
                                            <?php 
                                             $snMutTxt = [];
                                             foreach ($mutSnList as $msn) {
                                                 $snMutTxt[] = htmlspecialchars($msn['serial_number']) . ' (' . str_replace('_', ' ', $msn['jenis_tindakan']) . ')';
                                             }
                                             echo implode(', ', $snMutTxt);
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; border: 1px solid #cbd5e1; font-weight: 600; font-size: 8pt; color: #334155;">
                            <?= htmlspecialchars($it['nama_satuan']) ?>
                        </td>
                        <td style="text-align: right; border: 1px solid #cbd5e1; font-family: monospace; font-size: 8.5pt; padding: 6px 8px;">
                            <?= number_format($it['stok_sistem_fisik']) ?>
                        </td>
                        <td style="text-align: right; border: 1px solid #cbd5e1; font-family: monospace; font-size: 8.5pt; font-weight: bold; background: #fafafa; padding: 6px 8px;">
                            <?= $it['hitung_fisik'] !== null ? number_format($it['hitung_fisik']) : '-' ?>
                            <?php if ($fRusakRow > 0): ?>
                                <div style="font-size: 7pt; color: #b45309; font-family: sans-serif; font-weight: 600;">Rusak: <?= $fRusakRow ?></div>
                            <?php endif; ?>
                            <?php if ($fExpRow > 0): ?>
                                <div style="font-size: 7pt; color: #b91c1c; font-family: sans-serif; font-weight: 600;">Exp: <?= $fExpRow ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; border: 1px solid #cbd5e1; font-family: monospace; font-size: 8.5pt; font-weight: bold; color: <?= $sel < 0 ? '#b91c1c' : ($sel > 0 ? '#1d4ed8' : '#047857') ?>; padding: 6px 8px;">
                            <?= $sel > 0 ? "+$sel" : "$sel" ?>
                        </td>
                        <td style="text-align: right; border: 1px solid #cbd5e1; font-family: monospace; font-size: 8pt; color: #475569; padding: 6px 8px;">
                            Rp <?= number_format($it['harga_satuan'], 0, ',', '.') ?>
                        </td>
                        <td style="text-align: right; border: 1px solid #cbd5e1; font-family: monospace; font-size: 8.5pt; padding: 6px 8px;">
                            <?php if ($it['subtotal_kerugian'] > 0): ?>
                                <strong style="color: #b91c1c;">Rp <?= number_format($it['subtotal_kerugian'], 0, ',', '.') ?></strong>
                            <?php elseif ($sel > 0): ?>
                                <span style="color: #1d4ed8; font-size: 7.5pt;">Rp 0 (Surplus)</span>
                            <?php else: ?>
                                <span style="color: #64748b; font-size: 7.5pt;">Rp 0</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 8pt; text-align: center; border: 1px solid #cbd5e1; padding: 6px 8px;">
                            <?php if ($sel === 0 && $fRusakRow === 0 && $fExpRow === 0): ?>
                                <span style="color: #047857; font-weight: bold;">Sesuai (0)</span>
                            <?php else: ?>
                                <div style="font-weight: bold; color: <?= $it['status_mutasi'] === 'Sudah Dimutasi' ? '#047857' : '#b91c1c' ?>;">
                                    <?= $it['status_mutasi'] === 'Sudah Dimutasi' ? 'Telah Dimutasi' : 'Belum Dimutasi' ?>
                                </div>
                                <div style="font-size: 7.5pt; color: #1e293b; font-weight: 600;"><?= htmlspecialchars($it['alasan_mutasi'] ?: '-') ?></div>
                                <?php if (!empty($it['keterangan_mutasi'])): ?>
                                    <div style="font-size: 7pt; color: #64748b; margin-top: 1px;"><?= htmlspecialchars($it['keterangan_mutasi']) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                <td colspan="8" style="text-align: right; padding: 8px 10px; font-size: 8.5pt; text-transform: uppercase;">
                    Total Estimasi Nilai Acuan Kerugian Persediaan:
                </td>
                <td style="text-align: right; padding: 8px 10px; font-size: 9.5pt; color: #b91c1c; font-family: monospace;">
                    Rp <?= number_format($grandTotalKerugian, 0, ',', '.') ?>
                </td>
                <td style="text-align: center; padding: 8px 6px; font-size: 7.5pt; color: #64748b;">
                    <?= $totalSelisih > 0 ? ($totalMutasi >= $totalSelisih ? 'Tuntas Rekonsiliasi' : 'Sebagian Dimutasi') : 'Stok Klop' ?>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Bagian Tanda Tangan (TTD) Resmi Berita Acara -->
    <div class="sign-container">
        <table class="sign-table">
            <tr>
                <td>
                    Kota Malang, <?= date('d F Y', strtotime($sesi['dibuka_pada'] ?: $sesi['dibekukan_pada'])) ?><br>
                    <strong>Pengelola Stok Gudang</strong>
                    <div class="sign-space"></div>
                    <div class="sign-name">( ............................................................ )</div>
                    <div class="sign-role">Petugas Pengelola Stok &amp; Logistik</div>
                </td>
                <td>
                    Mengetahui &amp; Menyetujui,<br>
                    <strong>Pemilik Toko</strong>
                    <div class="sign-space"></div>
                    <div class="sign-name">( ............................................................ )</div>
                    <div class="sign-role">Pemilik Toko ZenCare Medical</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Catatan Penutup Sesi -->
    <?php if (!empty($sesi['catatan'])): ?>
        <div style="margin-top: 14px; padding: 6px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 7.5pt; color: #475569;">
            <strong>Catatan Verifikasi Fisik:</strong> <?= htmlspecialchars($sesi['catatan']) ?>
        </div>
    <?php endif; ?>

</body>
</html>
