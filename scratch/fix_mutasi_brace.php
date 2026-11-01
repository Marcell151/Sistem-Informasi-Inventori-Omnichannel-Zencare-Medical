<?php
$content = file_get_contents(__DIR__ . '/../inventori/proses_mutasi.php');
$target = "\$msg = 'Gagal memproses mutasi: ' . \$e->getMessage();\r\n            \$msgType = 'error';\r\n        }\r\n    }\r\n}";
if (strpos($content, $target) !== false) {
    $content = str_replace($target, $target . "\r\n}", $content);
    file_put_contents(__DIR__ . '/../inventori/proses_mutasi.php', $content);
    echo "SUCCESS\n";
} else {
    // Try LF
    $targetLF = "\$msg = 'Gagal memproses mutasi: ' . \$e->getMessage();\n            \$msgType = 'error';\n        }\n    }\n}";
    if (strpos($content, $targetLF) !== false) {
        $content = str_replace($targetLF, $targetLF . "\n}", $content);
        file_put_contents(__DIR__ . '/../inventori/proses_mutasi.php', $content);
        echo "SUCCESS LF\n";
    } else {
        echo "NOT FOUND\n";
    }
}

