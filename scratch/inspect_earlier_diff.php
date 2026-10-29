<?php
$c = file_get_contents('scratch/pos_diff.txt');
$pos1 = strpos($c, '@@ -768');
$pos2 = strpos($c, '@@ -1205');
if ($pos1 !== false && $pos2 !== false) {
    echo substr($c, $pos1, $pos2 - $pos1);
}

