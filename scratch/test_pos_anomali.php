<?php
$_SESSION['user_id'] = 4;
$_SESSION['role'] = 'kasir';
$_SESSION['nama_lengkap'] = 'Kasir Toko';

require_once __DIR__ . '/../config/koneksi.php';

$kasirUserId = $_SESSION["user_id"] ?? 0;
$userRole = $_SESSION["role"] ?? "kasir";

if ($userRole === "superadmin") {
    $stmtPendingKasir = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE status IN ('terlewat', 'sebagian')");
    $anomaliPendingKasir = (int)$stmtPendingKasir->fetchColumn();

    $stmtTotalKasir = $pdo->query("SELECT COUNT(*) FROM log_anomali_fefo WHERE (status IN ('terlewat', 'sebagian') OR DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))");
    $anomaliHariIniKasir = (int)$stmtTotalKasir->fetchColumn();
} else {
    $stmtPendingKasir = $pdo->prepare("SELECT COUNT(*) FROM log_anomali_fefo WHERE status IN ('terlewat', 'sebagian') AND id_user = ?");
    $stmtPendingKasir->execute([$kasirUserId]);
    $anomaliPendingKasir = (int)$stmtPendingKasir->fetchColumn();

    $stmtTotalKasir = $pdo->prepare("SELECT COUNT(*) FROM log_anomali_fefo WHERE (status IN ('terlewat', 'sebagian') OR DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AND id_user = ?");
    $stmtTotalKasir->execute([$kasirUserId]);
    $anomaliHariIniKasir = (int)$stmtTotalKasir->fetchColumn();
}

echo "Role: $userRole, User ID: $kasirUserId\n";
echo "Pending: $anomaliPendingKasir\n";
echo "Total (Hari ini / 30 Hari): $anomaliHariIniKasir\n";

// What if user is admin (id=2)?
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$kasirUserId = 2;
$stmtPendingKasir->execute([$kasirUserId]);
$anomaliPendingKasir = (int)$stmtPendingKasir->fetchColumn();
$stmtTotalKasir->execute([$kasirUserId]);
$anomaliHariIniKasir = (int)$stmtTotalKasir->fetchColumn();
echo "\nRole: admin, User ID: 2\n";
echo "Pending: $anomaliPendingKasir\n";
echo "Total (Hari ini / 30 Hari): $anomaliHariIniKasir\n";

