<?php
// File: config/auth.php
// Auth Guard Functions – ZenCare Medical System

define('BASE_URL', '/inventory_zencare');

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireRole(array $roles) {
    requireLogin();
    $currentRole = $_SESSION['role'] ?? '';
    
    if (!in_array($currentRole, $roles)) {
        // If customer tries to access admin/kasir pages, redirect to store catalog
        if ($currentRole === 'pelanggan') {
            header('Location: ' . BASE_URL . '/zencare_store.php');
            exit;
        }

        http_response_code(403);
        die('
            <!DOCTYPE html><html lang="id"><head>
            <meta charset="UTF-8"><title>403 – Akses Ditolak</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
            </head>
            <body class="bg-[#f5f7fa] font-[Inter] flex items-center justify-center min-h-screen p-4">
            <div class="text-center p-8 bg-white border border-[#e4e9f0] rounded-2xl shadow-sm max-w-md w-full">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold mx-auto mb-4 border border-rose-100">🔒</div>
                <h1 class="text-lg font-bold text-[#1e293b] mb-1">Akses Ditolak (403)</h1>
                <p class="text-xs text-[#64748b] mb-6">Akun Anda (' . htmlspecialchars($currentRole) . ') tidak memiliki izin untuk mengakses halaman ini.</p>
                <a href="' . BASE_URL . '/index.php" class="inline-block bg-[#1a75d2] hover:bg-[#1562b3] text-white text-xs font-semibold px-5 py-2.5 rounded-xl transition">
                    &larr; Kembali ke Dashboard
                </a>
            </div></body></html>
        ');
    }
}

function isAdmin(): bool {
    return ($_SESSION['role'] ?? '') === 'super_admin';
}

function isKasir(): bool {
    return ($_SESSION['role'] ?? '') === 'kasir';
}

function isPelanggan(): bool {
    return ($_SESSION['role'] ?? '') === 'pelanggan';
}

function currentRole(): string {
    return $_SESSION['role'] ?? 'guest';
}

function roleLabel(): string {
    $map = [
        'super_admin' => 'Super Admin',
        'kasir'       => 'Kasir',
        'pelanggan'   => 'Pelanggan',
    ];
    return $map[$_SESSION['role'] ?? ''] ?? 'Guest';
}
?>
