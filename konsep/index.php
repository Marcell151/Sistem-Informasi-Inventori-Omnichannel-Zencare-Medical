<?php
// File: index.php
// Dashboard & POS Sederhana (Grayscale Theme)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

try {
    $pdo = DB::connect();
    
    // Ambil Data Master Cabang
    $cabang = $pdo->query("SELECT * FROM cabang WHERE is_active = 1")->fetchAll();
    
    // Default Cabang aktif (Sesi bisa diatur lewat login, ini contoh sederhana POS)
    $activeCabang = $_GET['cabang'] ?? 1;

    // Ambil Stok Cabang Aktif
    $stmtStok = $pdo->prepare("
        SELECT v.id, i.nama_produk, v.nama_variasi, i.kategori, v.harga, sc.stok
        FROM produk_variasi v
        JOIN produk_induk i ON v.id_produk_induk = i.id
        LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = ?
        WHERE v.is_active = 1 AND i.is_active = 1
        ORDER BY i.nama_produk ASC
    ");
    $stmtStok->execute([$activeCabang]);
    $stokProduk = $stmtStok->fetchAll();

} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zencare POS & Inventory Dashboard</title>
    <style>
        /* Grayscale / B&W Theme */
        :root {
            --bg-color: #f5f5f5;
            --text-color: #333333;
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
            font-size: 24px;
            font-weight: 600;
        }
        .nav-link {
            color: #ffffff;
            text-decoration: none;
            border: 1px solid #ffffff;
            padding: 8px 16px;
            border-radius: 4px;
            transition: 0.2s;
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
        }
        .btn-dark:hover {
            background-color: #444444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px;
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
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .badge-ok { background-color: #555555; }
        .badge-low { background-color: #000000; }
    </style>
</head>
<body>

<div class="header">
    <h1>Zencare Medical - Omnichannel Inventory & POS</h1>
    <div style="display: flex; gap: 10px;">
        <a href="pos/pos.php" class="nav-link">🖥️ Terminal POS Kasir</a>
        <a href="zencare_store.php" class="nav-link">🛒 Buka E-Commerce</a>
    </div>
</div>

<div class="container">
    <div class="controls">
        <form method="GET" action="">
            <label for="cabang"><strong>Pilih Cabang: </strong></label>
            <select name="cabang" id="cabang" onchange="this.form.submit()">
                <?php foreach($cabang as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $activeCabang == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="inventori/proses_mutasi.php" class="btn-dark" style="text-decoration: none; padding: 8px 14px; font-size: 13px; display: inline-block;">⇄ Mutasi Stok</a>
            <a href="inventori/karantina.php" class="btn-dark" style="text-decoration: none; padding: 8px 14px; font-size: 13px; display: inline-block;">⚠️ Gudang Karantina</a>
            <a href="inventori/import_pengadaan.php" class="btn-dark" style="text-decoration: none; padding: 8px 14px; font-size: 13px; display: inline-block;">📥 Import Excel</a>
        </div>
    </div>

    <h2>Ketersediaan Stok Cabang</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Kategori</th>
                <th>Nama Produk & Variasi</th>
                <th>Harga Jual (Rp)</th>
                <th>Stok Tersedia</th>
                <th>Aksi POS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($stokProduk as $p): ?>
                <?php $stok = intval($p['stok'] ?? 0); ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['kategori']) ?></td>
                    <td><strong><?= htmlspecialchars($p['nama_produk']) ?></strong><br><small><?= htmlspecialchars($p['nama_variasi']) ?></small></td>
                    <td><?= number_format($p['harga'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge <?= $stok > 5 ? 'badge-ok' : 'badge-low' ?>">
                            <?= $stok ?> Unit
                        </span>
                    </td>
                    <td>
                        <button class="btn-dark" <?= $stok <= 0 ? 'disabled' : '' ?> onclick="alert('Fitur Kasir POS segera hadir.')">Jual (POS)</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>