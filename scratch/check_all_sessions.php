<?php
require 'config/koneksi.php';
$hs = $pdo->query('SELECT * FROM sesi_opname ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
print_r($hs);

