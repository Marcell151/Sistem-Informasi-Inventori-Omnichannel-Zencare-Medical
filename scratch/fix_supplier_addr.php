<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
$pdo->query("UPDATE supplier SET alamat = REPLACE(alamat, 'Farmasi', 'Kesehatan')");
echo "Updated supplier address in DB.\n";

