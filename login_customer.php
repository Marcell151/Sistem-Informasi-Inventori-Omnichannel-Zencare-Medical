<?php
// File: login_customer.php – Customer Login Portal
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
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['id_cabang']    = $user['id_cabang'] ?? 1;

            if ($user['role'] === 'pelanggan') {
                header('Location: zencare_store.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Username pelanggan tidak ditemukan atau akun nonaktif.';
        }
    } else { $error = 'Harap isi username!'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pelanggan – ZenCare Store</title>
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
            <h1 class="text-xl font-bold text-zcTxt">ZenCare Medical Store</h1>
            <p class="text-xs text-zcMut mt-1">Masuk untuk Berbelanja Alat Kesehatan</p>
        </div>

        <!-- Card -->
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">

            <!-- Quick Demo Customer Role -->
            <div class="p-5 border-b border-zcBrd">
                <p class="text-xs font-semibold text-zcMut mb-2.5">Demo Login Pelanggan</p>
                <form method="POST">
                    <input type="hidden" name="username" value="pelanggan1">
                    <button type="submit" class="w-full p-3 rounded-xl border border-zcBrd bg-zcLt/50 hover:bg-zcLt hover:border-zc transition flex items-center justify-between text-left">
                        <div>
                            <span class="text-xs font-semibold text-zc block">🛒 Budi Santoso (Demo Customer)</span>
                            <span class="text-[11px] text-zcMut">Username: pelanggan1</span>
                        </div>
                        <span class="text-xs font-bold text-zc">Masuk →</span>
                    </button>
                </form>
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
                        <label class="block text-xs font-medium text-zcTxt mb-1.5">Username Pelanggan *</label>
                        <input type="text" name="username" required placeholder="pelanggan1 / ahmad_fauzi"
                            class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zcTxt mb-1.5">Password *</label>
                        <input type="password" name="password" placeholder="••••••••"
                            class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                    </div>
                    <button type="submit"
                        class="w-full bg-zc hover:bg-zcHv text-white font-semibold text-sm py-2.5 rounded-xl transition mt-1 shadow-sm">
                        Masuk ke E-Commerce →
                    </button>
                </form>

                <div class="mt-4 pt-3 border-t border-zcBrd flex justify-between items-center text-xs text-zcMut">
                    <span>Belum punya akun?</span>
                    <a href="register.php" class="font-semibold text-zc hover:underline">Daftar Akun Baru</a>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center text-xs text-zcMut">
            Staf / Kasir ZenCare? <a href="login.php" class="font-semibold text-zcTxt hover:underline">Masuk Login Staf</a>
        </div>
    </div>

</body>
</html>
