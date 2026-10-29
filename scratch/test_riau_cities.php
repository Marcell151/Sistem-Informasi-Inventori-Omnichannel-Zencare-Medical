<?php
$_GET['action'] = 'cities';
$_GET['province_id'] = '25'; // RIAU
ob_start();
include 'api/rajaongkir.php';
$out = ob_get_clean();
echo "Cities for Riau (prov 25):\n" . $out . "\n";

