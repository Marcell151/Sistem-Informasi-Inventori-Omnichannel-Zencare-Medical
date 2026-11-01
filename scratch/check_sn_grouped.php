<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("
    SELECT id_variasi, 
           GROUP_CONCAT(CONCAT(serial_number, ':', status) ORDER BY id ASC SEPARATOR '|') as sn_list
    FROM unit_serial
    GROUP BY id_variasi
");
$map = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($map as $idVar => $snList) {
    echo "Variasi ID $idVar: " . $snList . "\n";
}

