<?php
$files = ['zencare_checkout.php', 'ecommerce/checkout.php', 'api/checkout.php'];
foreach ($files as $f) {
    if (file_exists($f)) {
        echo "Found $f (" . filesize($f) . " bytes)\n";
    } else {
        echo "Not found: $f\n";
    }
}

