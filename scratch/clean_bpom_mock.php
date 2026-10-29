<?php
$f = 'api/lookup_bpom.php';
$c = file_get_contents($f);
$c = str_replace('PT INDUSTRI JAMU DAN FARMASI SIDO MUNCUL TBK', 'PT INDUSTRI JAMU DAN HERBAL SIDO MUNCUL TBK', $c);
$c = str_replace('PT DISTRIBUTOR FARMASI NASIONAL', 'PT DISTRIBUTOR MEDIS NASIONAL', $c);
file_put_contents($f, $c);
echo "Updated lookup_bpom.php\n";

