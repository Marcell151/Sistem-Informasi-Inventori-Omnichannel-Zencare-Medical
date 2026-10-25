<?php
require_once __DIR__ . '/../config/koneksi.php';
$stmt = $pdo->query("SELECT id, username FROM users WHERE id = 1");
$u = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($u['id']);
var_dump($u['id'] !== 1);
var_dump((int)$u['id'] !== 1);
var_dump($u['id'] != 1);
