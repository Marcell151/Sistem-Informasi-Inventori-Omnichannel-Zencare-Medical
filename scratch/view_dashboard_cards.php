<?php
$c = file_get_contents('index.php');
$lines = explode("\n", $c);
for ($i = 320; $i < 380; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ': ' . $lines[$i] . "\n";
    }
}

