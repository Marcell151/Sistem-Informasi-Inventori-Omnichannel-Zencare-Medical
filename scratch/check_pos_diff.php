<?php
exec('git diff pos/pos.php', $out);
$diff = implode("\n", $out);
file_put_contents('scratch/pos_diff.txt', $diff);
echo "Diff size: " . strlen($diff) . " bytes\n";

