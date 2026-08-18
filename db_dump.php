<?php require 'config/koneksi.php'; print_r($pdo->query('SELECT * FROM pengaturan_api')->fetchAll()); ?> 
