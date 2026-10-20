<?php
/**
 * Generator script for db_inventory (demo).sql
 */
require_once __DIR__ . '/../config/koneksi.php';

$outputFile = __DIR__ . '/../database/isi/db_inventory (demo).sql';

echo "Building database/isi/db_inventory (demo).sql...\n";

// Helper to escape values for SQL
function sqlVal($val) {
    if ($val === null) return 'NULL';
    if (is_numeric($val) && !is_string($val)) return $val;
    return "'" . addslashes((string)$val) . "'";
}

// 1. Fetch clean master data
$users = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$pengaturan = $pdo->query("SELECT * FROM pengaturan_web ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Exclude dummy test product: Induk 54, 56 and Variasi 59
$produkInduk = $pdo->query("SELECT * FROM produk_induk WHERE id NOT IN (54, 56) ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$produkVariasi = $pdo->query("SELECT * FROM produk_variasi WHERE id != 59 ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

echo "Loaded master data: " . count($produkInduk) . " induk, " . count($produkVariasi) . " variasi.\n";

// Start buffer
$sql = "-- phpMyAdmin SQL Dump\n";
$sql .= "-- ZenCare Medical Omnichannel Inventory System\n";
$sql .= "-- Database: db_inventory (demo)\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

$sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sql .= "START TRANSACTION;\n";
$sql .= "SET time_zone = \"+00:00\";\n\n";
$sql .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
$sql .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
$sql .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
$sql .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

// Read exact schemas from all_schemas.sql
$schemaSql = file_get_contents(__DIR__ . '/all_schemas.sql');

// Let's create an associative map of CREATE TABLE definitions
preg_match_all('/CREATE TABLE `(.*?)` \((.*?)\) ENGINE=InnoDB.*?;/s', $schemaSql, $matches, PREG_SET_ORDER);
$tableSchemas = [];
foreach ($matches as $m) {
    $tableSchemas[$m[1]] = $m[0];
}

// Order of tables to write
$tableOrder = [
    'users',
    'supplier',
    'pengaturan_web',
    'produk_induk',
    'produk_variasi',
    'stok_toko',
    'stok_batch',
    'unit_serial',
    'penerimaan_stok',
    'penerimaan_detail',
    'histori_konversi',
    'penjualan',
    'detail_penjualan',
    'kartu_stok',
    'log_anomali_fefo'
];

echo "Parsed " . count($tableSchemas) . " table schemas.\n";

// Save progress in memory and prepare inserts
file_put_contents(__DIR__ . '/gen_part1.php', 'OK');
