<?php
// File: login_customer.php – E-Commerce Customer Login
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ecommerce/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, username, password, nama_lengkap, role, is_active FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_active']) {
                if ($user['role'] === 'pelanggan') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['role'] = $user['role'];
                    
                    header('Location: ecommerce/index.php');
                    exit;
                } else {
                    $error = "Akun ini terdaftar sebagai staf medis. Silakan login melalui halaman Admin.";
                }
            } else {
                $error = "Akun Anda sedang dinonaktifkan.";
            }
        } else {
            $error = "Username atau kata sandi salah.";
        }
    } else {
        $error = "Mohon isi username dan kata sandi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Pelanggan – ZenCare Medical Store</title>
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

    <div class="w-full max-w-[400px] my-8">

        <!-- Brand Mark -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white/10 border border-white/20 mb-3">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">Selamat Datang Kembali</h1>
            <p class="text-xs text-slate-400 mt-1">Masuk untuk melanjutkan belanja alat kesehatan</p>
        </div>

        <!-- Card -->
        <div class="bg-white border border-zcBrd rounded-2xl shadow-xl overflow-hidden">

            <!-- Form -->
            <div class="p-6">
                <?php if ($error): ?>
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-semibold text-center">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-zcMut uppercase tracking-wider mb-2">Username</label>
                        <input type="text" name="username" required placeholder="Masukkan username Anda"
                            class="w-full text-sm border border-zcBrd rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/20 transition placeholder-slate-400">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-zcMut uppercase tracking-wider">Kata Sandi</label>
                            <a href="#" class="text-xs font-bold text-zc hover:underline">Lupa Sandi?</a>
                        </div>
                        <input type="password" name="password" required placeholder="Masukkan kata sandi"
                            class="w-full text-sm border border-zcBrd rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/20 transition placeholder-slate-400">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3.5 rounded-xl transition shadow-sm">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="p-5 border-t border-zcBrd bg-slate-50 text-center">
                <p class="text-xs text-zcMut font-medium">Belum punya akun? <a href="register.php" class="text-zc font-bold hover:underline">Daftar sekarang</a></p>
            </div>

        </div>
        
        <div class="mt-6 text-center">
            <a href="ecommerce/index.php" class="text-sm text-slate-400 hover:text-white font-medium inline-flex items-center gap-1.5 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Kembali ke Toko
            </a>
        </div>

    </div>

</body>
</html>
