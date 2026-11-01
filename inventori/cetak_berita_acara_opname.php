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
    SELECT dob.*, pv.nama_variasi, pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp,
           u_hit.nama_lengkap AS nama_penghitung, u_mut.nama_lengkap AS nama_pemutasi
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    LEFT JOIN users u_hit ON dob.dihitung_oleh = u_hit.id
    LEFT JOIN users u_mut ON dob.dimutasi_oleh = u_mut.id
    WHERE dob.id_sesi = ?
    ORDER BY dob.jenis_komoditas ASC, pi.nama_produk ASC, pv.nama_variasi ASC, sb.tgl_exp ASC
");
$stmtItems->execute([$idSesi]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$totalItem = count($items);
$totalSesuai = 0;
$totalSelisih = 0;
$totalMutasi = 0;

foreach ($items as $it) {
    $sel = intval($it['selisih'] ?? 0);
    if ($it['hitung_fisik'] !== null && $sel === 0) {
        $totalSesuai++;
    } elseif ($sel !== 0) {
        $totalSelisih++;
        if ($it['status_mutasi'] === 'Sudah Dimutasi') {
            $totalMutasi++;
        }
    }
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
            size: A4 portrait;
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
            margin-bottom: 12px;
            font-size: 8pt;
        }
        .meta-summary-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            background: #f8fafc;
        }
        .meta-summary-table .label {
            color: #64748b;
            font-size: 7pt;
            display: block;
            text-transform: uppercase;
        }
        .meta-summary-table .val {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 16px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
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
                <div class="address">Jl. Muharto No. 1, Kota Malang, Jawa Timur &bull; Telp: 0812-3456-7890</div>
            </div>
        </div>
        <div class="header-meta-doc">
            <strong>DOKUMEN RESMI INVENTORI</strong>
            <div>No. Sesi: #<?= htmlspecialchars($sesi['kode_sesi']) ?></div>
            <div>Dicetak: <?= date('d/m/Y H:i') ?> WIB</div>
            <div>Oleh: <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Superadmin') ?></div>
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
                <span class="label">Tingkat Akurasi</span>
                <span class="val" style="color: #0f2d5a;"><?= $totalItem > 0 ? round(($totalSesuai / $totalItem) * 100, 1) : 0 ?>% Sesuai</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Hasil Pemeriksaan Fisik Lapangan -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th style="width: 60px;">Jenis</th>
                <th>Nama Produk &amp; Variasi</th>
                <th style="width: 120px;">Batch / Identitas SN</th>
                <th style="width: 50px;">Satuan</th>
                <th style="width: 55px;">Sistem</th>
                <th style="width: 55px;">Fisik</th>
                <th style="width: 55px;">Selisih</th>
                <th style="width: 110px;">Tindakan &amp; Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($items as $it): 
                $sel = intval($it['selisih'] ?? 0);
            ?>
                <tr>
                    <td style="text-align: center;"><?= $no++ ?></td>
                    <td style="text-align: center; font-weight: bold; font-size: 7.5pt; color: <?= $it['jenis_komoditas'] === 'Obat' ? '#1e40af' : '#065f46' ?>;">
                        <?= htmlspecialchars($it['jenis_komoditas']) ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($it['nama_produk']) ?></strong>
                        <div style="font-size: 7.5pt; color: #475569;"><?= htmlspecialchars($it['nama_variasi']) ?></div>
                    </td>
                    <td style="font-family: monospace; font-size: 7.5pt;">
                        <?php if ($it['jenis_komoditas'] === 'Obat'): ?>
                            <strong><?= htmlspecialchars($it['no_batch'] ?: '-') ?></strong>
                            <div style="font-size: 7pt; color: #64748b;">Exp: <?= $it['tgl_exp'] ? date('d/m/Y', strtotime($it['tgl_exp'])) : '-' ?></div>
                        <?php else: ?>
                            <span style="color: #065f46; font-weight: bold;">Serial Number (1:1)</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;"><?= htmlspecialchars($it['nama_satuan']) ?></td>
                    <td style="text-align: right; font-family: monospace;"><?= number_format($it['stok_sistem_fisik']) ?></td>
                    <td style="text-align: right; font-family: monospace; font-weight: bold; background: #fffbeb;">
                        <?= $it['hitung_fisik'] !== null ? number_format($it['hitung_fisik']) : '-' ?>
                    </td>
                    <td style="text-align: right; font-family: monospace; font-weight: bold; color: <?= $sel < 0 ? '#b91c1c' : ($sel > 0 ? '#1d4ed8' : '#047857') ?>;">
                        <?= $sel > 0 ? "+$sel" : "$sel" ?>
                    </td>
                    <td style="font-size: 7.5pt; text-align: center;">
                        <?php if ($sel === 0): ?>
                            <span style="color: #047857; font-weight: bold;">Sesuai (0)</span>
                        <?php else: ?>
                            <div style="font-weight: bold; color: <?= $it['status_mutasi'] === 'Sudah Dimutasi' ? '#047857' : '#b91c1c' ?>;">
                                <?= $it['status_mutasi'] === 'Sudah Dimutasi' ? 'Telah Dimutasi' : 'Belum Dimutasi' ?>
                            </div>
                            <div style="font-size: 7pt; color: #475569;"><?= htmlspecialchars($it['alasan_mutasi'] ?: '-') ?></div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Bagian Tanda Tangan (TTD) Resmi Berita Acara -->
    <div class="sign-container">
        <table class="sign-table">
            <tr>
                <td>
                    Kota Malang, <?= date('d F Y', strtotime($sesi['dibuka_pada'] ?: $sesi['dibekukan_pada'])) ?><br>
                    <strong>Petugas Pemeriksa Lapangan (Admin / Gudang)</strong>
                    <div class="sign-space"></div>
                    <div class="sign-name">
                        <?= htmlspecialchars($sesi['nama_pembuka'] ?? 'Petugas Logistik Medis') ?>
                    </div>
                    <div class="sign-role">Admin Pengelola Stok &amp; Logistik</div>
                </td>
                <td>
                    Mengetahui &amp; Menyetujui,<br>
                    <strong>Penanggung Jawab Toko (Superadmin / Pemilik)</strong>
                    <div class="sign-space"></div>
                    <div class="sign-name">
                        <?= htmlspecialchars($sesi['nama_penutup'] ?: $sesi['nama_pembuka'] ?: 'Superadmin') ?>
                    </div>
                    <div class="sign-role">Superadmin / Pemilik Toko ZenCare Medical</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Catatan Penutup Sesi -->
    <?php if (!empty($sesi['catatan'])): ?>
        <div style="margin-top: 14px; padding: 6px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-size: 7.5pt; color: #475569;">
            <strong>Catatan Audit:</strong> <?= htmlspecialchars($sesi['catatan']) ?>
        </div>
    <?php endif; ?>

</body>
</html>
