<?php
session_start();
require_once __DIR__ . "/../config/koneksi.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$idVariasi = intval($_POST["id_variasi"] ?? 0);
$noBatchAsal = trim($_POST["batch_asal"] ?? "");
$userId = $_SESSION["user_id"];

if (!$idVariasi || !$noBatchAsal) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Dapatkan info variasi
    $stmtVar = $pdo->prepare("SELECT pv.*, pi.nama_produk, pi.kategori FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.id = ?");
    $stmtVar->execute([$idVariasi]);
    $var = $stmtVar->fetch();
    
    if (!$var) throw new Exception("Produk tidak ditemukan.");
    
    $rasio = intval($var["rasio_konversi"]);
    $qtyPcs = 1 * $rasio; // Selalu 1 Box
    
    // Cek batch asal
    $stmtBatch = $pdo->prepare("SELECT * FROM stok_batch WHERE id_variasi = ? AND no_batch = ? FOR UPDATE");
    $stmtBatch->execute([$idVariasi, $noBatchAsal]);
    $batchAsal = $stmtBatch->fetch();
    
    if (!$batchAsal || $batchAsal["stok_sisa"] < $qtyPcs) {
        throw new Exception("Stok box asal tidak mencukupi.");
    }
    
    // Kurangi batch asal
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyPcs, $batchAsal["id"]]);
    
    // Generate Sub-Batch ID
    $stmtCekSub = $pdo->prepare("SELECT no_batch FROM stok_batch WHERE id_variasi = ? AND no_batch LIKE ? ORDER BY no_batch DESC LIMIT 1");
    $stmtCekSub->execute([$idVariasi, $noBatchAsal . ".*"]);
    $lastSub = $stmtCekSub->fetchColumn();
    
    $subBatchSuffix = ".A";
    if ($lastSub) {
        $parts = explode(".", $lastSub);
        $lastChar = end($parts);
        if (strlen($lastChar) === 1 && ctype_alpha($lastChar)) {
            $subBatchSuffix = "." . chr(ord($lastChar) + 1);
        } else {
            $subBatchSuffix = "." . uniqid();
        }
    }
    
    $newBatchName = $noBatchAsal . $subBatchSuffix;
    
    // Insert new Sub Batch
    $stmtNew = $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE stok_sisa = stok_sisa + ?");
    $stmtNew->execute([$idVariasi, $newBatchName, $batchAsal["tgl_exp"], $qtyPcs, $qtyPcs]);
    
    // Catat di kartu stok secara terpisah (Keluar Dus, Masuk Pcs)
    $sisaStokEceran = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();
    $sisaStokDus = floor($sisaStokEceran / $rasio);
    
    // 1. Kartu Stok DUS (Keluar)
    $catatanKeluar = "Bongkar 1 " . $var["satuan_besar"] . " (Rp0 - Konversi POS)";
    $stmtKartu1 = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, 'Keluar', 'POS', 'Bongkar Dus', 1, ?, ?, ?)");
    $stmtKartu1->execute([$idVariasi, $sisaStokDus, $catatanKeluar, $userId]);
    
    // 2. Kartu Stok ECERAN (Masuk)
    $catatanMasuk = "Lahir Sub-Batch $newBatchName (Masuk $qtyPcs " . $var["satuan_kecil"] . ")";
    $stmtKartu2 = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, 'Masuk', 'POS', 'Hasil Bongkar', ?, ?, ?, ?)");
    $stmtKartu2->execute([$idVariasi, $qtyPcs, $sisaStokEceran, $catatanMasuk, $userId]);
    
    // 3. Histori Konversi
    $stmtHist = $pdo->prepare("INSERT INTO histori_konversi (id_variasi, batch_asal, batch_hasil, qty_box_buka, qty_pcs_hasil, dibuat_oleh) VALUES (?, ?, ?, 1, ?, ?)");
    $stmtHist->execute([$idVariasi, $noBatchAsal, $newBatchName, $qtyPcs, $userId]);
    
    $pdo->commit();
    echo json_encode([
        "status" => "success", 
        "message" => "Berhasil memecah box!",
        "new_batch" => $newBatchName,
        "qty_pcs" => $qtyPcs
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
