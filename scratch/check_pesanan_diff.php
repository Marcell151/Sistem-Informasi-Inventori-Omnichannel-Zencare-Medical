<?php
exec('git diff admin/pesanan.php', $out);
echo implode("\n", array_slice($out, 0, 100));

