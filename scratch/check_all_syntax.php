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
    // Exclude vendor, .git
    if (strpos($path, DIRECTORY_SEPARATOR . '.git') !== false ||
        strpos($path, DIRECTORY_SEPARATOR . 'vendor') !== false ||
        strpos($path, DIRECTORY_SEPARATOR . 'node_modules') !== false) {
        continue;
    }
    
    $total++;
    $output = [];
    $return_var = 0;
    exec("C:\\xampp\\php\\php.exe -l \"$path\" 2>&1", $output, $return_var);
    if ($return_var !== 0) {
        $errors[] = "$path:\n" . implode("\n", $output);
    }
}

echo "Total PHP files checked: $total\n";
if (empty($errors)) {
    echo "SUCCESS: ALL PHP FILES PASS SYNTAX CHECK!\n";
} else {
    echo "SYNTAX ERRORS FOUND IN " . count($errors) . " FILES:\n";
    echo implode("\n====================\n", $errors) . "\n";
}
