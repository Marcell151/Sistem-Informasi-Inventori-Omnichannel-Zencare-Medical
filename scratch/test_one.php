<?php
$role = $argv[1] ?? 'superadmin';
$userId = ($role === 'superadmin') ? 1 : 2;

session_start();
$_SESSION['user_id'] = $userId;
$_SESSION['role'] = $role;
$_SESSION['nama_lengkap'] = ($role === 'superadmin') ? 'Superadmin Pemilik' : 'Admin Operasional';

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/inventory_zencare/index.php';

ob_start();
try {
    include __DIR__ . '/../index.php';
    $output = ob_get_clean();
    echo "SUCCESS: Dashboard rendered! Output length: " . strlen($output) . " bytes\n";
    
    // Check for errors in output
    if (preg_match_all('/(Fatal error|Parse error|Warning|Notice|Deprecated|Uncaught Exception|SQLSTATE\[[0-9A-Z]+\]):.*?(<br|<\/td|<\/div|\n)/i', $output, $m)) {
        echo "ERRORS FOUND:\n";
        foreach ($m[0] as $err) {
            echo "   " . strip_tags($err) . "\n";
        }
    } else {
        echo "NO ERRORS IN OUTPUT!\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "FATAL THROWABLE: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

