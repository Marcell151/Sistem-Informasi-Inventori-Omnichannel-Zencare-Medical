<?php
ob_start();
session_start();
require 'config/config.php';
require 'config/koneksi.php';
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
require 'pos/pos.php';
$out = ob_get_clean();
file_put_contents('pos_rendered.html', $out);
echo "Rendered!";
