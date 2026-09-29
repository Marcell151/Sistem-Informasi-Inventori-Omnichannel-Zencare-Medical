<?php
require_once '../config/config.php';
require_once '../config/koneksi.php';
require_once '../config/auth.php'; // Pastikan hanya user yang login yang bisa cetak

$tipe = $_GET['tipe'] ?? '';
$id = $_GET['id'] ?? '';

$nama_produk = '';
$kode = '';
$tgl_exp = '';

if ($tipe === 'batch') {
    $stmt = $pdo->prepare("
        SELECT sb.no_batch, sb.tgl_exp, pi.nama_produk, pv.nama_variasi 
        FROM stok_batch sb
        JOIN produk_variasi pv ON sb.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE sb.id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if ($data) {
        $nama_produk = $data['nama_produk'] . ' - ' . $data['nama_variasi'];
        $kode = $data['no_batch'];
        $tgl_exp = 'EXP: ' . date('d/m/Y', strtotime($data['tgl_exp']));
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
    $data = $stmt->fetch();
    if ($data) {
        $nama_produk = $data['nama_produk'] . ' - ' . $data['nama_variasi'];
        $kode = $data['serial_number'];
        $tgl_exp = 'Garansi Aktif';
    }
}

if (!$kode) {
    die("Data tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Stiker - <?= htmlspecialchars($kode) ?></title>
    <!-- Include JsBarcode for reliable Code 128 rendering -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: 50mm 30mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            width: 50mm;
            height: 30mm;
            background-color: #fff;
            color: #000;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-sizing: border-box;
        }
        /* Wrapper untuk padding dalam stiker */
        .sticker-wrapper {
            width: 48mm;
            height: 28mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 1mm;
            box-sizing: border-box;
        }
        .title {
            font-size: 8px;
            font-weight: bold;
            line-height: 1.1;
            max-height: 18px;
            overflow: hidden;
            width: 100%;
        }
        .barcode-container {
            margin: 2px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        /* Override SVG size to fit perfectly in thermal label */
        .barcode-container svg {
            max-width: 100%;
            height: 15mm;
        }
        .footer {
            font-size: 7px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Simulated 50x30 thermal sticker -->
    <div class="sticker-wrapper">
        <div class="title"><?= htmlspecialchars($nama_produk) ?></div>
        
        <!-- Barcode Code 128 Container -->
        <div class="barcode-container">
            <svg id="barcode"></svg>
        </div>
        
        <div class="footer">
            <span>Zencare Medical</span>
            <span><?= htmlspecialchars($tgl_exp) ?></span>
        </div>
    </div>

    <script>
        // Generate real Code 128 barcode
        JsBarcode("#barcode", "<?= addslashes($kode) ?>", {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 40,
            displayValue: true,
            fontSize: 16,
            fontOptions: "bold",
            textMargin: 2
        });

        // Tunggu barcode selesai digambar sebelum trigger print
        setTimeout(function(){ window.print(); }, 800);
    </script>
</body>
</html>
