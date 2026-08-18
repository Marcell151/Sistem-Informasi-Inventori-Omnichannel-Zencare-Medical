<?php
// File: register.php
// Halaman Registrasi Akun Pelanggan E-Commerce – ZenCare Medical
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

// If already logged in, redirect to store or dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'pelanggan' ? 'zencare_store.php' : 'index.php'));
    exit;
}

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $telepon  = trim($_POST['telepon'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($username) || empty($password)) {
        $error = 'Nama Lengkap, Username, dan Password wajib diisi!';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter (tanpa spasi).';
    } else {
        // Check duplicate username
        $chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $chk->execute([$username]);
        if ($chk->fetch()) {
            $error = 'Username "' . htmlspecialchars($username) . '" sudah digunakan. Silakan gunakan username lain.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                // Insert new customer account
                $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, id_cabang, is_active) VALUES (?, ?, ?, 'pelanggan', 1, 1)");
                $stmt->execute([$username, $hash, $nama]);
                $newId = $pdo->lastInsertId();

                // Auto login
                $_SESSION['user_id']      = $newId;
                $_SESSION['username']     = $username;
                $_SESSION['nama_lengkap'] = $nama;
                $_SESSION['role']         = 'pelanggan';
                $_SESSION['id_cabang']    = 1;

                header('Location: zencare_store.php');
                exit;
            } catch (Exception $e) {
                $error = 'Gagal mendaftar: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Pelanggan – ZenCare Medical</title>
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

    <div class="w-full max-w-[420px]">

        <!-- Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-zc mb-3">
                <svg viewBox="0 0 24 24" class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-zcTxt">Daftar Akun Baru</h1>
            <p class="text-xs text-zcMut mt-1">Buat akun untuk belanja alat kesehatan di ZenCare Store</p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">

            <?php if ($error): ?>
                <div class="mb-4 flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-medium">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-zcTxt mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" required value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                        placeholder="Contoh: Ahmad Fauzi"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-zcTxt mb-1">Username Akun * <span class="text-zcMut font-normal">(tanpa spasi)</span></label>
                    <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        placeholder="ahmad_fauzi"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-zcTxt mb-1">Nomor WhatsApp / HP</label>
                    <input type="text" name="telepon" value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>"
                        placeholder="081234567890"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-zcTxt mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-medium text-zcTxt mb-1">Konfirmasi Password *</label>
                    <input type="password" name="confirm_password" required placeholder="••••••••"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc focus:ring-2 focus:ring-zc/10 bg-white transition">
                </div>

                <button type="submit"
                    class="w-full bg-zc hover:bg-zcHv text-white font-semibold text-sm py-3 rounded-xl transition shadow-sm mt-2">
                    Daftar Akun Sekarang →
                </button>
            </form>

            <div class="mt-5 pt-4 border-t border-zcBrd text-center text-xs text-zcMut">
                Sudah punya akun? <a href="login.php" class="font-semibold text-zc hover:underline">Masuk ke Sistem</a>
            </div>
        </div>

        <p class="text-center text-[11px] text-zcMut mt-4">ZenCare Medical Omnichannel Store</p>
    </div>

</body>
</html>
