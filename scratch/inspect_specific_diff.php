<?php
$c = file_get_contents('scratch/pos_diff.txt');
$pos1 = strpos($c, '@@ -1205');
$pos2 = strpos($c, '@@ -1462');
if ($pos1 !== false && $pos2 !== false) {
    echo substr($c, $pos1, $pos2 - $pos1);
}

