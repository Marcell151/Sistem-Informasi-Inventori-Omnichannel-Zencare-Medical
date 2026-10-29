<?php
$files = ['admin/pesanan.php', 'ecommerce/profil.php', 'api/checkout.php'];
foreach ($files as $f) {
    if (file_exists($f)) {
        $c = file_get_contents($f);
        if (stripos($c, 'resi') !== false) {
            echo "Found resi in $f\n";
        }
    }
}

