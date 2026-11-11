<?php
require_once __DIR__ . '/../config/db.php';
$pdo = DB::connect();

$delStmt = $pdo->prepare("
    DELETE FROM detail_opname_batch 
    WHERE id_sesi = 28 
      AND id_batch IS NULL 
      AND id_variasi IN (SELECT DISTINCT id_variasi FROM stok_batch)
");
$delStmt->execute();
$deleted = $delStmt->rowCount();
echo "Deleted $deleted duplicate Alkes rows in session 28.\n";

$countStmt = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = 28");
echo "Remaining items in session 28: " . $countStmt->fetchColumn() . "\n";

