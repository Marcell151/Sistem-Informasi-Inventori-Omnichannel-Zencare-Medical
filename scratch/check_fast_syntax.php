<?php
$root = 'c:/xampp/htdocs/inventory_zencare';
$dir = new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS);
$ite = new RecursiveIteratorIterator($dir);

$errors = [];
$total = 0;

foreach ($ite as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    // Exclude vendor, .git, cache
    if (strpos($path, DIRECTORY_SEPARATOR . '.git') !== false ||
        strpos($path, DIRECTORY_SEPARATOR . 'vendor') !== false ||
        strpos($path, DIRECTORY_SEPARATOR . 'node_modules') !== false) {
        continue;
    }
    
    $total++;
    $content = file_get_contents($path);
    try {
        token_get_all($content, TOKEN_PARSE);
    } catch (\ParseError $e) {
        $errors[] = [
            'file' => $path,
            'line' => $e->getLine(),
            'message' => $e->getMessage()
        ];
    } catch (\Throwable $e) {
        $errors[] = [
            'file' => $path,
            'line' => $e->getLine(),
            'message' => $e->getMessage()
        ];
    }
}

echo "Total PHP files analyzed: $total\n";
if (empty($errors)) {
    echo "SUCCESS: 100% CLEAN. NO SYNTAX/PARSE ERRORS IN ANY PHP FILE.\n";
} else {
    echo "ATTENTION! Found " . count($errors) . " file(s) with errors:\n";
    foreach ($errors as $err) {
        echo "- File: {$err['file']} (Line {$err['line']}): {$err['message']}\n";
    }
}

