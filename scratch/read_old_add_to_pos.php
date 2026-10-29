<?php
$lines = file('scratch/pos_c89b495.php');
$start = 0; $end = 0;
foreach ($lines as $i => $l) {
    if (strpos($l, 'function addToPos(') !== false) $start = $i;
    if ($start > 0 && strpos($l, 'function triggerManualBongkar(') !== false) {
        $end = $i;
        break;
    }
}
echo "Lines $start to $end:\n";
for ($j = $start; $j <= $end; $j++) {
    echo ($j + 1) . ': ' . $lines[$j];
}

