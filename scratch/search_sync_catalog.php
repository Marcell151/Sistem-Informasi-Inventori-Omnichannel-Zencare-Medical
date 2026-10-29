<?php
$lines = file('pos/pos.php');
foreach ($lines as $i => $l) {
    if (strpos($l, 'syncPosCatalogLive') !== false) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}

