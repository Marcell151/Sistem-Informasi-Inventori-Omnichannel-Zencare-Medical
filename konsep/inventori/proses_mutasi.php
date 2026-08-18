<?php
// File: inventori/proses_mutasi.php
// Modul Mutasi Stok Antar-Cabang ZenCare Medical

require_once __DIR__ . '/../config/koneksi.php';

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'transfer_stok') {
    $idVariasi    = intval($_POST['id_variasi'] ?? 0);
    $cabangAsal   = intval($_POST['cabang_asal'] ?? 0);
    $cabangTujuan = intval($_POST['cabang_tujuan'] ?? 0);
    $qty          = intval($_POST['qty'] ?? 0);

    if ($idVariasi <= 0 || $cabangAsal <= 0 || $cabangTujuan <= 0 || $qty <= 0) {
        $message = "Semua bidang formulir wajib diisi dengan benar!";
        $messageType = 'error';
    } elseif ($cabangAsal === $cabangTujuan) {
        $message = "Cabang asal dan cabang tujuan tidak boleh sama!";
        $messageType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $stmtAsal = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ? FOR UPDATE");
            $stmtAsal->execute([$idVariasi, $cabangAsal]);
            $stokAsalCurrent = $stmtAsal->fetchColumn();

            if ($stokAsalCurrent === false || intval($stokAsalCurrent) < $qty) {
                throw new Exception("Stok cabang asal tidak mencukupi! Tersisa: " . ($stokAsalCurrent === false ? 0 : $stokAsalCurrent));
            }

            // 1. Potong Stok Cabang Asal
            $stmtSub = $pdo->prepare("UPDATE stok_cabang SET stok = stok - ? WHERE id_variasi = ? AND id_cabang = ?");
            $stmtSub->execute([$qty, $idVariasi, $cabangAsal]);

            // 2. Tambah Stok Cabang Tujuan
            $stmtAdd = $pdo->prepare("INSERT INTO stok_cabang (id_variasi, id_cabang, stok) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stok = stok + VALUES(stok)");
            $stmtAdd->execute([$idVariasi, $cabangTujuan, $qty]);

            // 3. Catat Tabel Mutasi
            $stmtMutasi = $pdo->prepare("INSERT INTO mutasi_stok (id_variasi, cabang_asal, cabang_tujuan, qty) VALUES (?, ?, ?, ?)");
            $stmtMutasi->execute([$idVariasi, $cabangAsal, $cabangTujuan, $qty]);

            // 4. Catat Kartu Stok (Keluar & Masuk)
            $sisaAsal = intval($stokAsalCurrent) - $qty;
            $stmtLogAsal = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Keluar', ?, ?, ?)");
            $stmtLogAsal->execute([$cabangAsal, $idVariasi, $qty, $sisaAsal, "Mutasi keluar ke Cabang #$cabangTujuan"]);

            $stmtSisaTujuan = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ?");
            $stmtSisaTujuan->execute([$idVariasi, $cabangTujuan]);
            $sisaTujuan = $stmtSisaTujuan->fetchColumn();

            $stmtLogTujuan = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Masuk', ?, ?, ?)");
            $stmtLogTujuan->execute([$cabangTujuan, $idVariasi, $qty, $sisaTujuan, "Mutasi masuk dari Cabang #$cabangAsal"]);

            $pdo->commit();
            $message = "Mutasi stok sebanyak $qty unit berhasil dipindahkan!";
            $messageType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Gagal memproses mutasi stok: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$cabangList = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
$variasiList = $pdo->query("
    SELECT v.id, CONCAT(i.nama_produk, ' (', v.nama_variasi, ')') AS nama_item, v.sku_variasi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    WHERE v.is_active = 1 AND i.is_active = 1
    ORDER BY i.nama_produk ASC
")->fetchAll();

$historiMutasi = $pdo->query("
    SELECT m.*, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_item,
           ca.nama AS nama_asal, ct.nama AS nama_tujuan
    FROM mutasi_stok m
    JOIN produk_variasi v ON m.id_variasi = v.id
    JOIN produk_induk i ON v.id_produk_induk = i.id
    JOIN cabang ca ON m.cabang_asal = ca.id
    JOIN cabang ct ON m.cabang_tujuan = ct.id
    ORDER BY m.tanggal DESC LIMIT 20
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ZenCare - Mutasi Stok Antar Cabang</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .card { background: white; border: 1px solid #ccc; padding: 25px; border-radius: 6px; margin-bottom: 25px; }
        h1, h2 { margin-top: 0; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #e2f0d9; border-left: 4px solid #385723; color: #385723; }
        .alert-error { background: #fce4d6; border-left: 4px solid #c65911; color: #c65911; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { background: #000; color: #fff; border: none; padding: 12px 24px; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .btn:hover { background: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>Modul Inventaris: Mutasi Stok Antar Cabang</h1>
        <p>Gunakan formulir ini untuk memindahkan persediaan barang dari satu cabang ke cabang lainnya.</p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType == 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="aksi" value="transfer_stok">
            
            <div class="form-group">
                <label for="id_variasi">Pilih Produk & Variasi:</label>
                <select name="id_variasi" id="id_variasi" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php foreach ($variasiList as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nama_item']) ?> [<?= htmlspecialchars($v['sku_variasi']) ?>]</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label for="cabang_asal">Cabang Asal (Pengirim):</label>
                    <select name="cabang_asal" id="cabang_asal" required>
                        <option value="">-- Pilih Asal --</option>
                        <?php foreach ($cabangList as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label for="cabang_tujuan">Cabang Tujuan (Penerima):</label>
                    <select name="cabang_tujuan" id="cabang_tujuan" required>
                        <option value="">-- Pilih Tujuan --</option>
                        <?php foreach ($cabangList as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="qty">Jumlah (Qty Mutasi):</label>
                <input type="number" name="qty" id="qty" min="1" required placeholder="Masukkan jumlah unit">
            </div>

            <button type="submit" class="btn">Proses Transfer Stok</button>
        </form>
    </div>

    <div class="card">
        <h2>Histori Mutasi Stok Terakhir</h2>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Nama Barang</th>
                    <th>Dari Cabang</th>
                    <th>Ke Cabang</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historiMutasi)): ?>
                    <tr><td colspan="5" style="text-align: center;">Belum ada riwayat mutasi stok.</td></tr>
                <?php else: ?>
                    <?php foreach ($historiMutasi as $hm): ?>
                        <tr>
                            <td><?= date('d-m-Y H:i', strtotime($hm['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($hm['nama_item']) ?></td>
                            <td><?= htmlspecialchars($hm['nama_asal']) ?></td>
                            <td><?= htmlspecialchars($hm['nama_tujuan']) ?></td>
                            <td><strong><?= intval($hm['qty']) ?> Unit</strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 15px;">
            <a href="../index.php" style="color: #000; text-decoration: underline;">&laquo; Kembali ke Dashboard Utama</a>
        </div>
    </div>
</div>

</body>
</html>
