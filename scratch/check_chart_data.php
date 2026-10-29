<?php
$c = file_get_contents('index.php');
$lines = explode("\n", $c);
for ($i = 190; $i < 300; $i++) {
    if (isset($lines[$i])) {
        if (strpos($lines[$i], 'Chart') !== false || strpos($lines[$i], 'chart') !== false || strpos($lines[$i], 'labels') !== false || strpos($lines[$i], 'dates') !== false || strpos($lines[$i], 'vol') !== false) {
            echo ($i + 1) . ': ' . trim($lines[$i]) . "\n";
        }
    }
}

