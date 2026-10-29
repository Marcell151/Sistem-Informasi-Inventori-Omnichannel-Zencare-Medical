<?php
$c = file_get_contents('scratch/pos_diff.txt');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (strpos($l, 'needs_verification') !== false || strpos($l, 'needsVerification') !== false) {
        echo ($i + 1) . ': ' . $l . "\n";
    }
}

