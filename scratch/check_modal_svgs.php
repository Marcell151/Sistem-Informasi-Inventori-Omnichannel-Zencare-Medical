<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');
$posModal = strpos($content, 'id="modal_mutasi"');
$posModalEnd = strpos($content, '</form>', $posModal);
$modalHtml = substr($content, $posModal, $posModalEnd - $posModal + 10);

preg_match_all('/<svg[^>]*>[\s\S]*?<\/svg>/', $modalHtml, $svgs);
echo "SVGs in modal_mutasi: " . count($svgs[0]) . PHP_EOL;
foreach ($svgs[0] as $s) {
    echo $s . PHP_EOL . "---" . PHP_EOL;
}

