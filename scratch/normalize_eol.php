<?php
$file = $argv[1] ?? '';
if ($file && file_exists($file)) {
    $c = file_get_contents($file);
    $c = str_replace("\r\n", "\n", $c);
    file_put_contents($file, $c);
    echo "Normalized $file to LF\n";
}

