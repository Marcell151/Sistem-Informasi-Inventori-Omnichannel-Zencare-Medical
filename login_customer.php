<?php
// File: login_customer.php – E-Commerce Customer Login
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
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    if ($username) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user) {
            if ($user['role'] !== 'pelanggan') {
                $error = 'Akun Anda adalah akun Staf/Admin. Silakan masuk melalui Portal Internal.';
            } else {
                $_SESSION['user_id']      = $user['id'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role']         = $user['role'];
                
                if ($redirect === 'checkout') {
                    header('Location: zencare_checkout.php');
                } else {
                    header('Location: zencare_store.php');
                }
                exit;
            }
        } else {
            $error = 'Username tidak ditemukan atau akun nonaktif.';
        }
    } else { $error = 'Harap isi username Anda!'; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pelanggan – ZenCare Medical Store</title>
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
<body class="font-sans antialiased bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-[400px]">

        <!-- Brand Mark -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white/10 border border-white/20 mb-3">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">ZenCare Medical Store</h1>
            <p class="text-xs text-slate-400 mt-1">Portal Login Pelanggan E-Commerce</p>
        </div>

        <!-- Card -->
        <div class="bg-white border border-zcBrd rounded-2xl shadow-xl overflow-hidden">

            <!-- Quick Demo Customer -->
            <div class="p-5 border-b border-zcBrd bg-slate-50">
                <p class="text-[11px] font-bold uppercase tracking-wider text-zcMut mb-3 text-center">Quick Login (Demo Akun Pelanggan)</p>
                <form method="POST">
                    <input type="hidden" name="username" value="pelanggan1">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <button type="submit" class="w-full text-center py-3 rounded-xl bg-white hover:bg-zcLt text-zc border border-zcBrd hover:border-zc transition font-semibold text-xs shadow-sm">
                        Masuk sebagai Budi Santoso (Pelanggan)
                    </button>
                </form>
            </div>

            <!-- Form -->
            <div class="p-6">
                <?php if ($error): ?>
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-semibold text-center">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <div>
                        <label class="block text-[11px] font-bold text-zcMut uppercase tracking-wider mb-2">Username</label>
                        <input type="text" name="username" required placeholder="Masukkan username"
                            class="w-full text-xs border border-zcBrd rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/20 transition placeholder-slate-400">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zcMut uppercase tracking-wider mb-2">Kata Sandi</label>
                        <input type="password" name="password" required placeholder="••••••••" value="123456"
                            class="w-full text-xs border border-zcBrd rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/20 transition placeholder-slate-400">
                        <p class="text-[10px] text-zcMut mt-1.5 text-right"><a href="#" class="hover:text-zc hover:underline">Lupa sandi?</a></p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3.5 rounded-xl transition shadow-sm">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="p-5 border-t border-zcBrd bg-slate-50 text-center">
                <p class="text-xs text-zcMut font-medium">Belum punya akun? <a href="register.php" class="text-zc font-bold hover:underline">Daftar Sekarang</a></p>
            </div>

        </div>
        
        <div class="mt-6 text-center">
            <a href="zencare_store.php" class="text-xs text-slate-400 hover:text-white font-medium inline-flex items-center gap-1 transition">
                &larr; Kembali ke Katalog Toko
            </a>
        </div>

    </div>

</body>
</html>
