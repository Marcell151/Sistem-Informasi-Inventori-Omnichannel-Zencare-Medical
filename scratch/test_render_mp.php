<?php
session_id('n5hdq5eras3nr35hnabs07ntvq');
session_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
include __DIR__ . '/../admin/master_produk.php';
$html = ob_get_clean();
echo "Rendered length: " . strlen($html) . PHP_EOL;

// Find toggle buttons in the rendered HTML
preg_match_all('/<button[^>]*>.*?Nonaktifkan.*?<\/button>/is', $html, $matches);
echo "Found Nonaktifkan buttons: " . count($matches[0]) . PHP_EOL;
foreach (array_slice($matches[0], 0, 3) as $b) {
    echo "Button HTML: " . htmlspecialchars($b) . PHP_EOL;
}
