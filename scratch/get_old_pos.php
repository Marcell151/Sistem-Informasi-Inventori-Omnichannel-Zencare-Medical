<?php
exec('git show c89b495:pos/pos.php', $out);
$content = implode("\n", $out);
file_put_contents('scratch/pos_c89b495.php', $content);
echo "Wrote pos_c89b495.php: " . strlen($content) . " bytes\n";

