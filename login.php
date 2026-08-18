<?php
// File: login.php – Dedicated Admin & Kasir Login Portal
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'pelanggan') {
        header('Location: zencare_store.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    if ($username) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user) {
            if ($user['role'] === 'pelanggan') {
                $error = 'Akun Anda adalah akun Pelanggan E-Commerce. Silakan masuk melalui Portal Login Pelanggan.';
            } else {
                $_SESSION['user_id']      = $user['id'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role']         = $user['role'];
                $_SESSION['id_cabang']    = $user['id_cabang'] ?? 1;
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Username staf tidak ditemukan atau akun nonaktif.';
        }
    } else { $error = 'Harap isi username staf!'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login Staf – ZenCare Medical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: { zc: '#1a75d2', zcHv: '#1562b3', zcLt: '#e8f2ff', zcBrd: '#e4e9f0', zcTxt: '#1e293b', zcMut: '#64748b' }
          }
        }
      }
    </script>
</head>
<body class="font-sans antialiased bg-[#f5f7fa] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-[400px]">

        <!-- Brand Mark -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-zc mb-3">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-zcTxt">ZenCare Admin &amp; Kasir</h1>
            <p class="text-xs text-zcMut mt-1">Portal Internal Operasional Inventaris &amp; POS</p>
        </div>

        <!-- Card -->
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">

            <!-- Quick Demo Staf Roles -->
            <div class="p-5 border-b border-zcBrd">
                <p class="text-xs font-semibold text-zcMut mb-3">Quick Login (Akun Internal Staf)</p>
                <div class="grid grid-cols-3 gap-2">
                    <form method="POST"><input type="hidden" name="username" value="admin">
                        <button type="submit" class="w-full text-center p-2.5 rounded-xl border border-zcBrd bg-white hover:bg-zcLt hover:border-zc transition">
                            <span class="text-xs font-semibold text-zcTxt block">Super Admin</span>
                        </button>
                    </form>
                    <form method="POST"><input type="hidden" name="username" value="kasir_muharto">
                        <button type="submit" class="w-full text-center p-2.5 rounded-xl border border-zcBrd bg-white hover:bg-zcLt hover:border-zc transition">
                            <span class="text-xs font-semibold text-zcTxt block">Kasir Muharto</span>
                        </button>
                    </form>
                    <form method="POST"><input type="hidden" name="username" value="kasir_borobudur">
                        <button type="submit" class="w-full text-center p-2.5 rounded-xl border border-zcBrd bg-white hover:bg-zcLt hover:border-zc transition">
                            <span class="text-xs font-semibold text-zcTxt block">Kasir Borobudur</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Form -->
            <div class="p-5">
                <?php if ($error): ?>
                    <div class="mb-4 flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-medium">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-zcTxt mb-1.5">Username Staf *</label>
                        <input type="text" name="username" required placeholder="admin / kasir_muharto"
                            class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zcTxt mb-1.5">Password *</label>
                        <input type="password" name="password" placeholder="••••••••"
                            class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                    </div>
                    <button type="submit"
                        class="w-full bg-zc hover:bg-zcHv text-white font-semibold text-sm py-2.5 rounded-xl transition mt-1">
                        Masuk ke Dashboard Operasional →
                    </button>
                </form>

                <div class="mt-4 pt-3 border-t border-zcBrd text-center text-xs text-zcMut">
                    Anda pembeli / pelanggan? <a href="zencare_store.php" class="font-semibold text-zc hover:underline">Masuk ke E-Commerce</a>
                </div>
            </div>
        </div>

        <p class="text-center text-[11px] text-zcMut mt-5">ZenCare Medical Omnichannel System v2.0</p>
    </div>

</body>
</html>
