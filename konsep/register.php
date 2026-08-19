<?php
// File: register.php – E-Commerce Customer Sign-Up
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header('Location: zencare_store.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $telp     = trim($_POST['telp'] ?? '');
    
    if ($username && $password && $nama) {
        $stmtChk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmtChk->execute([$username]);
        if ($stmtChk->fetch()) {
            $error = "Username '$username' sudah terdaftar, silakan gunakan username lain.";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            try {
                // Because users table has 'role' enum which includes 'pelanggan'
                $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, is_active) VALUES (?, ?, ?, 'pelanggan', 1)")
                    ->execute([$username, $hashed, $nama]);
                $success = "Pendaftaran berhasil! Silakan masuk menggunakan akun baru Anda.";
            } catch (Exception $e) {
                $error = "Gagal mendaftar: " . $e->getMessage();
            }
        }
    } else {
        $error = "Kolom Username, Password, dan Nama Lengkap wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Pelanggan – ZenCare Medical Store</title>
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

    <div class="w-full max-w-[450px] my-8">

        <!-- Brand Mark -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white/10 border border-white/20 mb-3">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white">Daftar Pelanggan ZenCare</h1>
            <p class="text-xs text-slate-400 mt-1">Dapatkan akses penuh ke pembelian alkes secara e-commerce</p>
        </div>

        <!-- Card -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-xl overflow-hidden">

            <!-- Form -->
            <div class="p-6">
                <?php if ($error): ?>
                    <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-semibold text-center">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold text-center">
                        ? <?= htmlspecialchars($success) ?><br>
                        <a href="login_customer.php" class="inline-block mt-3 px-4 py-2 bg-emerald-600 text-white rounded-lg">Masuk Sekarang</a>
                    </div>
                <?php else: ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" required placeholder="Sesuai KTP / Nama Instansi"
                            class="w-full text-xs border border-gray-200 rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-500/20 transition placeholder-slate-400">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Username *</label>
                        <input type="text" name="username" required placeholder="Pilih username unik"
                            class="w-full text-xs border border-gray-200 rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-500/20 transition placeholder-slate-400">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Kata Sandi *</label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter"
                            class="w-full text-xs border border-gray-200 rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-500/20 transition placeholder-slate-400">
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200">
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Nomor WhatsApp (Opsional)</label>
                        <input type="text" name="telp" placeholder="0812xxxxxx"
                            class="w-full text-xs border border-gray-200 rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-500/20 transition placeholder-slate-400">
                    </div>
                    
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Alamat Pengiriman Default (Opsional)</label>
                        <textarea name="alamat" rows="2" placeholder="Nama Jalan, Kota, dll"
                            class="w-full text-xs border border-gray-200 rounded-xl px-4 py-3 bg-slate-50 focus:bg-white focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-500/20 transition placeholder-slate-400"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-gray-800 hover:bg-gray-700 text-white font-bold text-sm py-3.5 rounded-xl transition shadow-sm">
                            Buat Akun Pelanggan
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
            
            <div class="p-5 border-t border-gray-200 bg-slate-50 text-center">
                <p class="text-xs text-gray-500 font-medium">Sudah punya akun? <a href="login_customer.php" class="text-gray-800 font-bold hover:underline">Masuk di sini</a></p>
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

