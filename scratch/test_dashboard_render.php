<?php
// Test rendering dashboard for both superadmin and admin
function testDashboard($role, $userId = 1) {
    echo "=======================================================\n";
    echo "TESTING DASHBOARD AS ROLE: $role (User ID: $userId)\n";
    echo "=======================================================\n";

    session_start();
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = $role;
    $_SESSION['nama_lengkap'] = ($role === 'superadmin') ? 'Superadmin Pemilik' : 'Admin Operasional';

    // Set server vars
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/inventory_zencare/index.php';

    ob_start();
    try {
        include __DIR__ . '/../index.php';
        $output = ob_get_clean();
        echo "Length of output: " . strlen($output) . " bytes\n";
        
        // Check for error keywords in output
        $errorsFound = [];
        if (preg_match_all('/(Fatal error|Parse error|Warning|Notice|Deprecated|Uncaught Exception|SQLSTATE\[[0-9A-Z]+\]):.*?(<br|<\/td|<\/div|\n)/i', $output, $m)) {
            $errorsFound = $m[0];
        }
        if (!empty($errorsFound)) {
            echo "❌ PHP ERRORS / WARNINGS DETECTED:\n";
            foreach ($errorsFound as $err) {
                echo "   " . strip_tags($err) . "\n";
            }
        } else {
            echo "✅ No PHP errors or warnings detected in output!\n";
        }
    } catch (Throwable $e) {
        ob_end_clean();
        echo "❌ THROWABLE CAUGHT: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo $e->getTraceAsString() . "\n";
    }

    session_destroy();
}

testDashboard('superadmin', 1);
testDashboard('admin', 2);

