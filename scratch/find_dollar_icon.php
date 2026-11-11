<?php
$content = file_get_contents(__DIR__ . '/../inventori/stok_opname.php');

// search for dollar signs or money icons
preg_match_all('/(<svg[^>]*>[\s\S]*?<\/svg>)/', $content, $svgs);
echo "Total SVGs in stok_opname.php: " . count($svgs[0]) . PHP_EOL;

foreach ($svgs[0] as $idx => $svg) {
    if (strpos($svg, '$') !== false || strpos($svg, 'dollar') !== false || strpos($svg, 'circle cx="12" cy="12" r="10"') !== false) {
        // let's see if there is any dollar icon or currency icon
        $pos = strpos($content, $svg);
        echo "SVG $idx at pos $pos:\n";
        echo substr($content, max(0, $pos - 150), 300) . "\n---\n";
    }
}

