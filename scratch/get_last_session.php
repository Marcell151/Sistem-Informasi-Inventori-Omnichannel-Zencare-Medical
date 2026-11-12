<?php
require_once __DIR__ . '/../config/koneksi.php';
$id = $pdo->query("SELECT id FROM sesi_opname ORDER BY id DESC LIMIT 1")->fetchColumn();
echo "Last session ID: " . $id . "\n";

