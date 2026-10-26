<?php
// File: inventori/cetak_batch_stiker.php
// Cetak Label Barcode Kelompok / Massal (Batch Printing)
// Mendukung Kertas Stiker A4 (Grid 4x10 dengan Garis Potong) dan Printer Thermal Roll (50x30mm)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';

requireLogin();

$mode = $_GET['mode'] ?? 'a4'; // 'a4' atau 'thermal'
$idPenerimaan = intval($_GET['id_penerimaan'] ?? 0);
$idVariasi    = intval($_GET['id_variasi'] ?? 0);

$itemsToPrint = [];

// Skenario 1: Cetak Berdasarkan Dokumen Penerimaan Barang (Faktur Masuk / PO)
if ($idPenerimaan > 0) {
    // Ambil header penerimaan
    $stmtHead = $pdo->prepare("SELECT no_referensi, tanggal_terima, sumber FROM penerimaan_stok WHERE id = ?");
    $stmtHead->execute([$idPenerimaan]);
    $docHeader = $stmtHead->fetch(PDO::FETCH_ASSOC);

    // Ambil detail item penerimaan (filter per variasi jika id_variasi disertakan)
    $sqlDet = "
        SELECT pd.*, pi.nama_produk, pv.nama_variasi, pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi
        FROM penerimaan_detail pd
        JOIN produk_variasi pv ON pv.id = pd.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        WHERE pd.id_penerimaan = ?
    ";
    $paramsDet = [$idPenerimaan];
    if ($idVariasi > 0) {
        $sqlDet .= " AND pd.id_variasi = ?";
        $paramsDet[] = $idVariasi;
    }
    $sqlDet .= " ORDER BY pd.id ASC";
    $stmtDet = $pdo->prepare($sqlDet);
    $stmtDet->execute($paramsDet);
    $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

    foreach ($details as $d) {
        $qty = intval($d['qty_terima']);
        if ($qty <= 0) {
            continue;
        }
        $namaLengkap = $d['nama_produk'] . ' - ' . $d['nama_variasi'];

        if ($d['kategori'] === 'Obat' && !empty($d['no_batch'])) {
            $expFormatted = !empty($d['tgl_exp']) ? 'EXP: ' . date('d/m/Y', strtotime($d['tgl_exp'])) : 'EXP: -';
            $itemsToPrint[] = [
                'nama'  => $namaLengkap,
                'kode'  => $d['no_batch'],
                'exp'   => $expFormatted,
                'qty'   => $qty,
                'tipe'  => 'Batch Obat'
            ];
        } elseif ($d['kategori'] === 'Alat Kesehatan') {
            $foundSns = [];

            // Prioritas 1: Ambil SN spesifik yang dicatat pada dokumen penerimaan ini di kartu_stok jika ada
            if (!empty($docHeader['no_referensi'])) {
                $stmtKS = $pdo->prepare("
                    SELECT keterangan FROM kartu_stok 
                    WHERE no_ref_dokumen = ? AND id_variasi = ? AND jenis_mutasi = 'Masuk' 
                    ORDER BY id DESC LIMIT 1
                ");
                $stmtKS->execute([$docHeader['no_referensi'], $d['id_variasi']]);
                $ksKet = $stmtKS->fetchColumn();
                if ($ksKet && preg_match('/\[SN:\s*([^\]]+)\]/', $ksKet, $matches)) {
                    $snListRaw = array_filter(array_map('trim', explode(',', $matches[1])));
                    foreach ($snListRaw as $snItem) {
                        if ($snItem !== '') {
                            $foundSns[] = $snItem;
                        }
                    }
                }
            }

            // Prioritas 2: Jika tidak ada di keterangan riwayat penerimaan, ambil dari unit_serial berstatus Tersedia
            if (empty($foundSns)) {
                $stmtSN = $pdo->prepare("
                    SELECT serial_number FROM unit_serial 
                    WHERE id_variasi = ? AND status = 'Tersedia' 
                    ORDER BY id DESC LIMIT ?
                ");
                $stmtSN->bindValue(1, $d['id_variasi'], PDO::PARAM_INT);
                $stmtSN->bindValue(2, $qty, PDO::PARAM_INT);
                $stmtSN->execute();
                $snRows = $stmtSN->fetchAll(PDO::FETCH_ASSOC);
                foreach ($snRows as $sn) {
                    $foundSns[] = $sn['serial_number'];
                }
            }

            if (!empty($foundSns)) {
                foreach ($foundSns as $snCode) {
                    $itemsToPrint[] = [
                        'nama'  => $namaLengkap,
                        'kode'  => $snCode,
                        'exp'   => 'Serial Number',
                        'qty'   => 1, // SN selalu 1:1
                        'tipe'  => 'Unit Alkes'
                    ];
                }
            } else {
                // Fallback jika SN belum diinput terpisah
                $itemsToPrint[] = [
                    'nama'  => $namaLengkap,
                    'kode'  => 'ALKES-' . str_pad($d['id_variasi'], 4, '0', STR_PAD_LEFT),
                    'exp'   => 'Unit Alkes',
                    'qty'   => $qty,
                    'tipe'  => 'Unit Alkes'
                ];
            }
        } else {
            // Produk umum (Suplemen / Non-Obat)
            $itemsToPrint[] = [
                'nama'  => $namaLengkap,
                'kode'  => !empty($d['no_batch']) ? $d['no_batch'] : ('PRD-' . $d['id_variasi']),
                'exp'   => !empty($d['tgl_exp']) ? 'EXP: ' . date('d/m/Y', strtotime($d['tgl_exp'])) : 'ZenCare Item',
                'qty'   => $qty,
                'tipe'  => 'Produk Fisik'
            ];
        }
    }
}
// Skenario 2: Cetak Massal melalui POST Payload dari Modal Master Produk / Logistik
elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !empty($_POST['selected_items'])) {
    $rawItems = json_decode($_POST['selected_items'], true);
    if (is_array($rawItems)) {
        foreach ($rawItems as $item) {
            $tipe = $item['tipe'] ?? '';
            $id   = $item['id'] ?? '';
            $qty  = max(1, intval($item['qty'] ?? 1));

            if ($tipe === 'batch') {
                $stmt = $pdo->prepare("
                    SELECT sb.no_batch, sb.tgl_exp, pi.nama_produk, pv.nama_variasi 
                    FROM stok_batch sb
                    JOIN produk_variasi pv ON sb.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    WHERE sb.id = ?
                ");
                $stmt->execute([$id]);
                $b = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($b) {
                    $itemsToPrint[] = [
                        'nama' => $b['nama_produk'] . ' - ' . $b['nama_variasi'],
                        'kode' => $b['no_batch'],
                        'exp'  => 'EXP: ' . date('d/m/Y', strtotime($b['tgl_exp'])),
                        'qty'  => $qty,
                        'tipe' => 'Batch Obat'
                    ];
                }
            } elseif ($tipe === 'sn') {
                $stmt = $pdo->prepare("
                    SELECT us.serial_number, pi.nama_produk, pv.nama_variasi 
                    FROM unit_serial us
                    JOIN produk_variasi pv ON us.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    WHERE us.serial_number = ?
                ");
                $stmt->execute([$id]);
                $sn = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($sn) {
                    $itemsToPrint[] = [
                        'nama' => $sn['nama_produk'] . ' - ' . $sn['nama_variasi'],
                        'kode' => $sn['serial_number'],
                        'exp'  => 'Serial Number',
                        'qty'  => 1, // SN selalu 1:1
                        'tipe' => 'Unit Alkes'
                    ];
                }
            }
        }
    }
}
// Skenario 3: Cetak 1 Item melalui GET (backward-compatible dengan cetak_stiker lama)
elseif (!empty($_GET['tipe']) && !empty($_GET['id'])) {
    $tipe = $_GET['tipe'];
    $id   = $_GET['id'];
    $qty  = max(1, intval($_GET['qty'] ?? 1));

    if ($tipe === 'batch') {
        $stmt = $pdo->prepare("
            SELECT sb.no_batch, sb.tgl_exp, pi.nama_produk, pv.nama_variasi 
            FROM stok_batch sb
            JOIN produk_variasi pv ON sb.id_variasi = pv.id
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE sb.id = ?
        ");
        $stmt->execute([$id]);
        $b = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($b) {
            $itemsToPrint[] = [
                'nama' => $b['nama_produk'] . ' - ' . $b['nama_variasi'],
                'kode' => $b['no_batch'],
                'exp'  => 'EXP: ' . date('d/m/Y', strtotime($b['tgl_exp'])),
                'qty'  => $qty,
                'tipe' => 'Batch Obat'
            ];
        }
    } elseif ($tipe === 'sn') {
        $stmt = $pdo->prepare("
            SELECT us.serial_number, pi.nama_produk, pv.nama_variasi 
            FROM unit_serial us
            JOIN produk_variasi pv ON us.id_variasi = pv.id
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE us.serial_number = ?
        ");
        $stmt->execute([$id]);
        $sn = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sn) {
            $itemsToPrint[] = [
                'nama' => $sn['nama_produk'] . ' - ' . $sn['nama_variasi'],
                'kode' => $sn['serial_number'],
                'exp'  => 'Serial Number',
                'qty'  => 1,
                'tipe' => 'Unit Alkes'
            ];
        }
    }
}
// Skenario 4: Cetak Berdasarkan Variasi Alat Kesehatan Langsung (id_variasi)
elseif ($idVariasi > 0) {
    $qty = max(1, intval($_GET['qty'] ?? 1));
    $stmtVar = $pdo->prepare("
        SELECT pv.*, pi.nama_produk, pi.kategori 
        FROM produk_variasi pv 
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
        WHERE pv.id = ?
    ");
    $stmtVar->execute([$idVariasi]);
    $varData = $stmtVar->fetch(PDO::FETCH_ASSOC);
    if ($varData) {
        $namaLengkap = $varData['nama_produk'] . ' - ' . $varData['nama_variasi'];
        if ($varData['kategori'] === 'Alat Kesehatan') {
            $stmtSN = $pdo->prepare("
                SELECT serial_number FROM unit_serial 
                WHERE id_variasi = ? AND status = 'Tersedia' 
                ORDER BY id DESC LIMIT ?
            ");
            $stmtSN->bindValue(1, $idVariasi, PDO::PARAM_INT);
            $stmtSN->bindValue(2, $qty, PDO::PARAM_INT);
            $stmtSN->execute();
            $snRows = $stmtSN->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($snRows)) {
                foreach ($snRows as $sn) {
                    $itemsToPrint[] = [
                        'nama'  => $namaLengkap,
                        'kode'  => $sn['serial_number'],
                        'exp'   => 'Serial Number',
                        'qty'   => 1,
                        'tipe'  => 'Unit Alkes'
                    ];
                }
            } else {
                $itemsToPrint[] = [
                    'nama'  => $namaLengkap,
                    'kode'  => 'ALKES-' . str_pad($idVariasi, 4, '0', STR_PAD_LEFT),
                    'exp'   => 'Unit Alkes',
                    'qty'   => $qty,
                    'tipe'  => 'Unit Alkes'
                ];
            }
        } else {
            $itemsToPrint[] = [
                'nama'  => $namaLengkap,
                'kode'  => 'PRD-' . $idVariasi,
                'exp'   => 'ZenCare Item',
                'qty'   => $qty,
                'tipe'  => 'Produk Fisik'
            ];
        }
    }
}

// Simpan ke sesi agar saat berpindah mode atau merefresh halaman, data tidak hilang
if (!empty($itemsToPrint)) {
    $_SESSION['zencare_last_print_items'] = $itemsToPrint;
    $_SESSION['zencare_last_doc_header']  = $docHeader ?? null;
} elseif (empty($itemsToPrint) && !empty($_SESSION['zencare_last_print_items'])) {
    $itemsToPrint = $_SESSION['zencare_last_print_items'];
    $docHeader    = $_SESSION['zencare_last_doc_header'] ?? null;
}

// Expand items berdasarkan kuantitas (qty)
$flatStickers = [];
foreach ($itemsToPrint as $it) {
    for ($i = 0; $i < $it['qty']; $i++) {
        $flatStickers[] = [
            'nama' => $it['nama'],
            'kode' => $it['kode'],
            'exp'  => $it['exp'],
            'tipe' => $it['tipe'],
            'seq'  => ($it['qty'] > 1) ? ($i + 1) . '/' . $it['qty'] : null
        ];
    }
}

$totalStiker = count($flatStickers);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Stiker Kelompok - ZenCare Medical (<?= $totalStiker ?> Label)</title>
    <!-- JsBarcode untuk barcode Code 128 yang scannable & presisi -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }

        /* TOOLBAR ATAS (HANYA MUNCUL DI LAYAR MONITOR, TIDAK IKUT TERCETAK) */
        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .badge-count {
            background: #10b981;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 9999px;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #334155;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        .btn-active {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
        }

        /* PANDUAN PETUNJUK DI MONITOR */
        .screen-guide {
            max-width: 1000px;
            margin: 16px auto 0 auto;
            padding: 12px 18px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 12px;
            color: #334155;
            line-height: 1.5;
        }

        /* ======================================================== */
        /* MODE A4: GRID LEMBARAN A4 (4 KOLOM x 10 BARIS = 40 LABEL) */
        /* ======================================================== */
        .sheet-container-a4 {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 8mm 6mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            /* Formasi 4 kolom presisi */
            display: grid;
            grid-template-columns: repeat(4, 48mm);
            grid-auto-rows: 27mm;
            gap: 2mm 2mm;
            justify-content: center;
            align-content: start;
        }

        /* 1 STIKER PADA GRID A4 */
        .sticker-card {
            width: 48mm;
            height: 27mm;
            padding: 1.5mm 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            background: #ffffff;
            /* Garis putus-putus panduan potong gunting/cutter */
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .sticker-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 7.5pt;
            line-height: 1.1;
            font-weight: 700;
            color: #0f172a;
            max-height: 18px;
            overflow: hidden;
        }
        .sticker-title {
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 38mm;
        }
        .sticker-seq {
            font-size: 6pt;
            color: #64748b;
            font-family: monospace;
            white-space: nowrap;
        }

        .barcode-area {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0.5mm 0;
        }
        .barcode-area svg {
            width: 100%;
            max-width: 44mm;
            height: 14mm;
        }

        .sticker-footer {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 6.5pt;
            font-weight: 700;
            color: #334155;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 0.5mm;
        }
        .sticker-brand {
            color: #0369a1;
            letter-spacing: -0.2px;
        }
        .sticker-exp {
            color: #047857;
            font-family: monospace;
        }

        /* ======================================================== */
        /* MODE THERMAL ROLL: 50mm x 30mm KONTINU                   */
        /* ======================================================== */
        .thermal-body .sheet-container-a4 {
            width: 50mm;
            min-height: auto;
            margin: 10px auto;
            padding: 0;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            gap: 2mm;
        }
        .thermal-body .sticker-card {
            width: 50mm;
            height: 30mm;
            border: 1px dashed #e2e8f0;
            border-radius: 0;
            padding: 1.5mm;
            page-break-after: always;
            break-after: page;
        }
        .thermal-body .sticker-header .sticker-title {
            max-width: 40mm;
            font-size: 8pt;
        }
        .thermal-body .barcode-area svg {
            max-width: 47mm;
            height: 16mm;
        }

        /* ======================================================== */
        /* ATURAN SAAT DICETAK KE PRINTER (@media print)            */
        /* ======================================================== */
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print, .no-print-toolbar, .screen-guide {
                display: none !important;
            }

            /* Halaman A4 Presisi vs Thermal Presisi */
            .sheet-container-a4 {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            .sticker-card {
                /* Garis putus-putus tipis tetap tercetak sebagai panduan potong gunting */
                border: 0.5px dashed #94a3b8 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .thermal-body .sheet-container-a4 {
                width: 50mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .thermal-body .sticker-card {
                width: 50mm !important;
                height: 30mm !important;
                border: none !important;
                page-break-after: always !important;
                break-after: page !important;
            }
        }
    </style>
    <style id="dynamic-print-page">
        @media print {
            @page {
                size: <?= $mode === 'thermal' ? '50mm 30mm' : 'A4 portrait' ?>;
                margin: <?= $mode === 'thermal' ? '0' : '8mm 6mm' ?>;
            }
        }
    </style>
</head>
<body class="<?= $mode === 'thermal' ? 'thermal-body' : '' ?>">

    <!-- TOOLBAR LAYAR (KONTROL CETAK & MODE) -->
    <div class="no-print-toolbar">
        <div class="toolbar-info">
            <h1 style="font-size: 15px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak Label Stiker Kelompok
            </h1>
            <span class="badge-count"><?= $totalStiker ?> Label Terbentuk</span>
            <?php if (!empty($docHeader)): ?>
                <span style="font-size: 12px; color: #64748b; font-weight: 600;">
                    Ref Dokumen: <strong style="color: #0f172a;"><?= htmlspecialchars($docHeader['no_referensi']) ?></strong>
                </span>
            <?php endif; ?>
        </div>

        <div class="toolbar-actions">
            <!-- Pilihan Format Kertas (Instant Client-side Toggle: Bebas Reset, Data Tetap Utuh) -->
            <button type="button" id="btn_mode_a4" onclick="setPrintMode('a4')" 
               class="btn-action <?= $mode === 'a4' ? 'btn-active' : 'btn-secondary' ?>" 
               title="Format Kertas Stiker HVS A4 Grid 4x10 (Printer Biasa / Laser / Inkjet)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Kertas A4 (Grid 4x10)</span>
            </button>
            <button type="button" id="btn_mode_thermal" onclick="setPrintMode('thermal')" 
               class="btn-action <?= $mode === 'thermal' ? 'btn-active' : 'btn-secondary' ?>" 
               title="Format Roll Thermal 50x30mm (Printer Barcode Khusus)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6v12M7 6v12M11 6v12M14 6v12M17 6v12M20 6v12"/></svg>
                <span>Thermal Roll (50x30mm)</span>
            </button>

            <!-- Tombol Print Dialog -->
            <button type="button" onclick="window.print()" class="btn-action btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak (Print)</span>
            </button>
            <button type="button" onclick="window.close()" class="btn-action btn-secondary">
                Tutup
            </button>
        </div>
    </div>

    <!-- PETUNJUK VISUAL DI LAYAR -->
    <div class="screen-guide no-print">
        <p style="font-weight: 700; color: #0f2d5a; display: flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Panduan Penyesuaian Format Cetak:</span>
        </p>
        <ul style="margin-left: 18px; margin-top: 4px;">
            <li><strong>Kertas Stiker A4:</strong> Stiker disusun berurutan dari <em>kiri ke kanan</em> kemudian baris berikutnya (maksimal 40 stiker per lembar A4). Jika Anda hanya mencetak 1 stiker, stiker akan berada di sudut <strong>kiri atas</strong> sehingga sisa lembaran A4 dapat disimpan dan dipakai kembali.</li>
            <li><strong>Garis Panduan Potong:</strong> Garis putus-putus tipis (<em>dashed lines</em>) disediakan sebagai batas pemotongan gunting/cutter agar hasil tempelan rapi di kardus/botol obat.</li>
            <li><strong>Multiple Copies:</strong> Produk dengan kuantitas lebih dari 1 unit otomatis dicetak sejumlah fisik yang diterima dengan barcode & nomor batch yang sama.</li>
        </ul>
    </div>

    <!-- CONTAINER GRID STIKER -->
    <?php if (empty($flatStickers)): ?>
        <div style="max-width: 600px; margin: 40px auto; text-align: center; background: white; padding: 30px; border-radius: 12px; border: 1px solid #cbd5e1;">
            <p style="font-weight: 700; color: #ef4444; font-size: 15px; margin-bottom: 6px;">Tidak ada item stiker yang dipilih untuk dicetak.</p>
            <p style="font-size: 12px; color: #64748b;">Silakan pilih minimal 1 nomor batch atau serial number untuk dicetak.</p>
        </div>
    <?php else: ?>
        <div class="sheet-container-a4">
            <?php foreach ($flatStickers as $idx => $stk): ?>
                <div class="sticker-card" id="stk_<?= $idx ?>">
                    <div class="sticker-header">
                        <span class="sticker-title" title="<?= htmlspecialchars($stk['nama']) ?>">
                            <?= htmlspecialchars($stk['nama']) ?>
                        </span>
                        <?php if ($stk['seq']): ?>
                            <span class="sticker-seq"><?= $stk['seq'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="barcode-area">
                        <svg id="bc_<?= $idx ?>"></svg>
                    </div>

                    <div class="sticker-footer">
                        <span class="sticker-brand">ZenCare</span>
                        <span class="sticker-exp"><?= htmlspecialchars($stk['exp']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script>
        // Render semua Barcode Code 128 secara presisi
        document.addEventListener('DOMContentLoaded', function() {
            <?php foreach ($flatStickers as $idx => $stk): ?>
            try {
                JsBarcode("#bc_<?= $idx ?>", "<?= addslashes($stk['kode']) ?>", {
                    format: "CODE128",
                    lineColor: "#000000",
                    width: <?= $mode === 'thermal' ? '1.8' : '1.5' ?>,
                    height: <?= $mode === 'thermal' ? '36' : '30' ?>,
                    displayValue: true,
                    fontSize: 13,
                    fontOptions: "bold",
                    textMargin: 1,
                    margin: 0
                });
            } catch (err) {
                console.error("Gagal merender barcode #bc_<?= $idx ?>:", err);
            }
            <?php endforeach; ?>
        });

        function setPrintMode(mode) {
            const dynStyle = document.getElementById('dynamic-print-page');
            const btnA4 = document.getElementById('btn_mode_a4');
            const btnThermal = document.getElementById('btn_mode_thermal');
            
            if (mode === 'thermal') {
                document.body.classList.add('thermal-body');
                if (btnThermal) {
                    btnThermal.classList.add('btn-active');
                    btnThermal.classList.remove('btn-secondary');
                }
                if (btnA4) {
                    btnA4.classList.remove('btn-active');
                    btnA4.classList.add('btn-secondary');
                }
                if (dynStyle) {
                    dynStyle.innerHTML = `@media print { @page { size: 50mm 30mm; margin: 0; } }`;
                }
            } else {
                document.body.classList.remove('thermal-body');
                if (btnA4) {
                    btnA4.classList.add('btn-active');
                    btnA4.classList.remove('btn-secondary');
                }
                if (btnThermal) {
                    btnThermal.classList.remove('btn-active');
                    btnThermal.classList.add('btn-secondary');
                }
                if (dynStyle) {
                    dynStyle.innerHTML = `@media print { @page { size: A4 portrait; margin: 8mm 6mm; } }`;
                }
            }
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('mode', mode);
                window.history.replaceState(null, '', url.toString());
            }
        }
    </script>
</body>
</html>
