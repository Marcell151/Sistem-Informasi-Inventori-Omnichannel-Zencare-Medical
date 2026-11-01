<?php
$content = file_get_contents(__DIR__ . '/../inventori/konversi_uom.php');
$target = "\$msgType = 'error';\r\n        }\r\n    }\r\n}";
if (strpos($content, $target) !== false) {
    $content = str_replace($target, $target . "\r\n}", $content);
    file_put_contents(__DIR__ . '/../inventori/konversi_uom.php', $content);
    echo "SUCCESS\n";
} else {
    $targetLF = "\$msgType = 'error';\n        }\n    }\n}";
    if (strpos($content, $targetLF) !== false) {
        $content = str_replace($targetLF, $targetLF . "\n}", $content);
        file_put_contents(__DIR__ . '/../inventori/konversi_uom.php', $content);
        echo "SUCCESS LF\n";
    } else {
        echo "NOT FOUND\n";
    }
}

