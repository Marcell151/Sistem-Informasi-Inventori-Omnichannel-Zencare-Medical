<?php
// File: inventori/import_pengadaan.php
// Modul Import Excel Pengadaan Barang - ZenCare Medical

require_once __DIR__ . '/../config/koneksi.php';

$message = '';
$importResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_excel'])) {
    $file = $_FILES['file_excel'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    if (in_array(strtolower($ext), ['csv', 'txt'])) {
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle, 1000, ',');
        
        $rowNum = 1;
        $successCount = 0;
        
        $pdo->beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $rowNum++;
                if (count($data) < 10) {
                    $importResults[] = "Baris $rowNum: Gagal (Jumlah kolom kurang dari 10).";
                    continue;
                }
                
                $skuInduk     = trim($data[0]);
                $namaProduk   = trim($data[1]);
                $kategori     = trim($data[2]);
                $namaSupplier = trim($data[3]);
                $skuVariasi   = trim($data[4]);
                $namaVariasi  = trim($data[5]);
                $harga        = floatval($data[6]);
                $berat        = intval($data[7]);
                $idCabang     = intval($data[8]);
                $qty          = intval($data[9]);
                
                if (empty($skuVariasi) || $idCabang <= 0 || $qty <= 0) {
                    $importResults[] = "Baris $rowNum: Gagal (SKU Variasi, ID Cabang, atau Qty tidak valid).";
                    continue;
                }
                
                // 1. VALIDASI SUPPLIER (Foreign Key NULL jika tidak cocok)
                $idSupplier = null;
                if (!empty($namaSupplier)) {
                    $stmtSup = $pdo->prepare("SELECT id FROM supplier WHERE nama = ? AND is_active = 1");
                    $stmtSup->execute([$namaSupplier]);
                    $supData = $stmtSup->fetch();
                    if ($supData) {
                        $idSupplier = $supData['id'];
                    }
                }
                
                // 2. VALIDASI PRODUK INDUK (Foreign Key NULL jika tidak lengkap)
                $idProdukInduk = null;
                if (!empty($skuInduk) && !empty($namaProduk)) {
                    $stmtInduk = $pdo->prepare("SELECT id FROM produk_induk WHERE sku_induk = ? AND is_active = 1");
                    $stmtInduk->execute([$skuInduk]);
                    $indukData = $stmtInduk->fetch();
                    
                    if ($indukData) {
                        $idProdukInduk = $indukData['id'];
                    } else {
                        $stmtInsInduk = $pdo->prepare("INSERT INTO produk_induk (sku_induk, nama_produk, kategori, id_supplier, is_active) VALUES (?, ?, ?, ?, 1)");
                        $stmtInsInduk->execute([$skuInduk, $namaProduk, !empty($kategori) ? $kategori : 'Umum', $idSupplier]);
                        $idProdukInduk = $pdo->lastInsertId();
                    }
                }
                
                // 3. PRODUK VARIASI & STOK CABANG
                $stmtVar = $pdo->prepare("SELECT id FROM produk_variasi WHERE sku_variasi = ?");
                $stmtVar->execute([$skuVariasi]);
                $varData = $stmtVar->fetch();
                
                if ($varData) {
                    $idVariasi = $varData['id'];
                    $stmtUpVar = $pdo->prepare("UPDATE produk_variasi SET id_produk_induk = ?, harga = ?, berat = ? WHERE id = ?");
                    $stmtUpVar->execute([$idProdukInduk, $harga, $berat, $idVariasi]);
                } else {
                    $stmtInsVar = $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk, sku_variasi, nama_variasi, harga, berat, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmtInsVar->execute([$idProdukInduk, $skuVariasi, $namaVariasi, $harga, $berat]);
                    $idVariasi = $pdo->lastInsertId();
                }
                
                // Update Stok Cabang
                $stmtStok = $pdo->prepare("INSERT INTO stok_cabang (id_variasi, id_cabang, stok) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stok = stok + VALUES(stok)");
                $stmtStok->execute([$idVariasi, $idCabang, $qty]);
                
                $stmtSisa = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ?");
                $stmtSisa->execute([$idVariasi, $idCabang]);
                $sisaStok = $stmtSisa->fetchColumn();
                
                // Audit Log Kartu Stok
                $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Masuk', ?, ?, ?)");
                $supplierInfo = $idSupplier ? "Supplier: $namaSupplier" : "Supplier: (Tanpa Supplier / NULL)";
                $stmtKartu->execute([$idCabang, $idVariasi, $qty, $sisaStok, "Import Excel Pengadaan. $supplierInfo"]);
                
                $successCount++;
                $importResults[] = "Baris $rowNum: Berhasil import SKU '$skuVariasi' ($qty unit) ke Cabang $idCabang. [Supplier FK: " . ($idSupplier ?? 'NULL') . "]";
            }
            
            fclose($handle);
            $pdo->commit();
            $message = "Import selesai. Berhasil memproses $successCount baris data.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Gagal memproses import: " . $e->getMessage();
        }
    } else {
        $message = "Format file tidak didukung. Harap unggah file CSV.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ZenCare - Import Pengadaan Excel</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; color: #333; margin: 0; padding: 20px; }
        .card { background: white; padding: 25px; border-radius: 6px; border: 1px solid #ccc; max-width: 900px; margin: 0 auto; }
        h1 { border-bottom: 2px solid #000; padding-bottom: 10px; margin-top: 0; }
        .alert { background: #eee; border-left: 4px solid #000; padding: 12px; margin-bottom: 20px; }
        .btn { background: #000; color: #fff; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
        .btn:hover { background: #333; }
        ul.log { background: #fafafa; border: 1px solid #ddd; padding: 15px 15px 15px 35px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body>

<div class="card">
    <h1>Modul Inventaris: Import Pengadaan Barang</h1>
    <p>Upload file CSV pengadaan barang. Validasi ketat: Data supplier/induk yang tidak lengkap otomatis diset sebagai <strong>NULL</strong>.</p>

    <?php if (!empty($message)): ?>
        <div class="alert"><strong>Status:</strong> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label for="file_excel"><strong>Pilih File CSV Pengadaan:</strong></label><br><br>
            <input type="file" name="file_excel" id="file_excel" accept=".csv" required>
        </div>
        <button type="submit" class="btn">Proses Import Pengadaan</button>
    </form>

    <?php if (!empty($importResults)): ?>
        <h3>Log Hasil Pemrosesan:</h3>
        <ul class="log">
            <?php foreach ($importResults as $res): ?>
                <li><?= htmlspecialchars($res) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="../index.php" style="color: #000; text-decoration: underline;">&laquo; Kembali ke Dashboard Utama</a>
    </div>
</div>

</body>
</html>
