<?php
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['username'] = 'admin_toko';
$_SESSION['role'] = 'admin';
$_SESSION['nama_lengkap'] = 'Admin Toko';
include __DIR__ . '/../admin/master_produk.php';

