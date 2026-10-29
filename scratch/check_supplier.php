<?php
require 'config/koneksi.php';
print_r($pdo->query("SHOW COLUMNS FROM supplier")->fetchAll(PDO::FETCH_ASSOC));

