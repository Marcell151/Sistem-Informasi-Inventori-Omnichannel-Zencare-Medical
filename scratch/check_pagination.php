<?php
$c = file_get_contents('index.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (strpos($l, 'rop-superadmin') !== false || strpos($l, 'paginate') !== false || strpos($l, 'renderPagination') !== false || strpos($l, 'next') !== false || strpos($l, 'prev') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($l) . "\n";
    }
}

