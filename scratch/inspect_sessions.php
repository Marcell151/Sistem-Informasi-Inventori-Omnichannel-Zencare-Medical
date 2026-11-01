<?php
require 'config/koneksi.php';
$sessions = $pdo->query("SELECT * FROM sesi_opname ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
print_r($sessions);
