<?php
$content = file_get_contents(__DIR__ . '/../pos/pos.php');
// Find the main script tag
preg_match('/<script>\s*let cart = \[\];(.*?)<\/script>/s', $content, $m);
if (!empty($m[1])) {
    $js = "let cart = [];" . $m[1];
    // Remove PHP tags in JS if any
    $jsClean = preg_replace('/<\?php.*?\?>/s', '""', $js);
    $jsClean = preg_replace('/<\?=.*?\?>/s', '""', $jsClean);
    file_put_contents(__DIR__ . '/pos_main.js', $jsClean);
    echo "Extracted " . strlen($jsClean) . " bytes of main JS\n";
} else {
    echo "Not found!\n";
}

