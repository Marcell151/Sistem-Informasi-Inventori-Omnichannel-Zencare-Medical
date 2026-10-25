<?php
$files = glob('c:/xampp/tmp/sess_*');
foreach ($files as $f) {
    if (filemtime($f) > time() - 3600) {
        echo basename($f) . ' (' . date('H:i:s', filemtime($f)) . '): ' . file_get_contents($f) . PHP_EOL;
    }
}
