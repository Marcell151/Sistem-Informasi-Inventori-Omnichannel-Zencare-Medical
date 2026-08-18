<?php
// File: index_wireframe.php
// Grayscale Wireframe Mode Dashboard - ZenCare Medical
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

$activeCabang = $_GET['cabang'] ?? ($_SESSION['id_cabang'] ?? 1);
$_SESSION['id_cabang'] = $activeCabang;

try {
    $cabangList = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
    
    $stmtStok = $pdo->prepare("
        SELECT v.id, v.sku_variasi, i.nama_produk, v.nama_variasi, i.kategori, v.harga, COALESCE(sc.stok, 0) AS stok
        FROM produk_variasi v
        JOIN produk_induk i ON v.id_produk_induk = i.id
        LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
        WHERE v.is_active = 1 AND i.is_active = 1
        ORDER BY i.nama_produk ASC
    ");
    $stmtStok->execute([$activeCabang]);
    $stokProduk = $stmtStok->fetchAll();

} catch (Exception $e) {
    die("Error loading wireframe dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZenCare Medical - Wireframe Mode (B&W Concept)</title>
    <style>
        /* Strict Grayscale Theme */
        :root {
            --bg-color: #f5f5f5;
            --text-color: #000000;
            --border-color: #cccccc;
            --accent-color: #000000;
            --hover-bg: #e0e0e0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: var(--accent-color);
            color: #ffffff;
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        .nav-link {
            color: #ffffff;
            text-decoration: none;
            border: 1px solid #ffffff;
            padding: 8px 16px;
            border-radius: 4px;
            transition: 0.2s;
            font-size: 13px;
        }
        .nav-link:hover {
            background-color: #ffffff;
            color: var(--accent-color);
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .controls {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        select, button, input {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: inherit;
        }
        .btn-dark {
            background-color: var(--accent-color);
            color: white;
            cursor: pointer;
            border: none;
            font-size: 13px;
        }
        .btn-dark:hover {
            background-color: #444444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            font-size: 13px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background-color: #eaeaea;
            font-weight: 600;
        }
        tr:hover {
            background-color: var(--hover-bg);
        }
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }
        .badge-ok { background-color: #555555; }
        .badge-low { background-color: #000000; }
        .mode-banner {
            background: #e0e0e0;
            border-bottom: 1px solid #ccc;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="mode-banner">
    📐 <strong>WIREFRAME MODE (Konsep Hitam Putih)</strong> &nbsp;|&nbsp; 
    <a href="index.php" style="color: #000; font-weight: bold; text-decoration: underline;">Switch to Full Medical-Tech Production System &raquo;</a>
</div>

<div class="header">
    <h1>ZenCare Medical - Wireframe Concept Overview</h1>
    <div style="display: flex; gap: 10px;">
        <a href="pos/pos.php" class="nav-link">🖥️ POS Terminal</a>
        <a href="zencare_store.php" class="nav-link">🛒 Store Katalog</a>
    </div>
</div>

<div class="container">
    <div class="controls">
        <form method="GET" action="">
            <label for="cabang"><strong>Pilih Cabang: </strong></label>
            <select name="cabang" id="cabang" onchange="this.form.submit()">
                <?php foreach($cabangList as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $activeCabang == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="inventori/proses_mutasi.php" class="btn-dark" style="text-decoration: none; padding: 8px 14px; inline-block;">⇄ Mutasi Stok</a>
            <a href="inventori/karantina.php" class="btn-dark" style="text-decoration: none; padding: 8px 14px; inline-block;">⚠️ Karantina</a>
            <a href="inventori/import_pengadaan.php" class="btn-dark" style="text-decoration: none; padding: 8px 14px; inline-block;">📥 Import Excel</a>
        </div>
    </div>

    <h2>Ketersediaan Stok Cabang (Wireframe View)</h2>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Kategori</th>
                <th>Nama Produk &amp; Variasi</th>
                <th>Harga Jual (Rp)</th>
                <th>Stok Fisik</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($stokProduk as $p): ?>
                <?php $stok = intval($p['stok']); ?>
                <tr>
                    <td><code><?= htmlspecialchars($p['sku_variasi']) ?></code></td>
                    <td><?= htmlspecialchars($p['kategori']) ?></td>
                    <td><strong><?= htmlspecialchars($p['nama_produk']) ?></strong><br><small><?= htmlspecialchars($p['nama_variasi']) ?></small></td>
                    <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge <?= $stok > 5 ? 'badge-ok' : 'badge-low' ?>">
                            <?= $stok ?> Unit
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
