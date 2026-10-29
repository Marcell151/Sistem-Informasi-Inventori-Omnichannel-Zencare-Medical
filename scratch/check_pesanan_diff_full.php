<?php
exec('git diff admin/pesanan.php', $out);
$diff = implode("\n", $out);
file_put_contents('scratch/pesanan_diff.txt', $diff);
echo "Pesanan diff size: " . strlen($diff) . " bytes\n";

