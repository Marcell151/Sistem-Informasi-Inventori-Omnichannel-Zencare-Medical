<?php
// File: login.php — Portal Login Staf ZenCare Medical
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header($_SESSION['role'] === 'pelanggan' ? 'Location: ecommerce/index.php' : 'Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $isQuickLogin = empty($password);

    if ($username) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['role'] === 'pelanggan') {
                $error = 'Akun ini adalah akun Pelanggan. Gunakan halaman Login Pelanggan.';
            } else {
                $passwordValid = $isQuickLogin || password_verify($password, $user['password']);
                if ($passwordValid) {
                    $_SESSION['user_id']      = $user['id'];
                    $_SESSION['username']     = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['role']         = $user['role'];
                    // Single-branch
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Password salah. Silakan coba lagi.';
                }
            }
        } else {
            $error = 'Username tidak ditemukan atau akun tidak aktif.';
        }
    } else {
        $error = 'Harap isi username terlebih dahulu!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Staf – ZenCare Medical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
      tailwind.config = {
        theme: { extend: { fontFamily: { sans: ['Inter','sans-serif'] } } }
      }
    </script>
    <style>
        body { background-color: #eef2f7; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
        .quick-btn { background:#f0f4fa; border:1px solid #dde4ef; border-radius:6px; padding:7px 12px; font-size:12px; font-weight:600; color:#334155; cursor:pointer; transition:all .15s; width:100%; text-align:center; }
        .quick-btn:hover { background:#e0e8f5; border-color:#a0b4d4; }
        .field { width:100%; border:1px solid #dde4ef; border-radius:6px; padding:9px 12px; font-size:13px; color:#1e293b; outline:none; transition:border .15s; background:#fafbfd; }
        .field:focus { border-color:#1a75d2; background:#fff; }
        .btn-submit { width:100%; background:#1a75d2; color:#fff; font-weight:700; font-size:13px; padding:10px; border-radius:6px; border:none; cursor:pointer; transition:background .15s; }
        .btn-submit:hover { background:#1562b3; }
        .logo-box { width:44px; height:44px; background:#1a75d2; border-radius:10px; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center font-sans p-4">

    <div style="width:100%;max-width:380px;">

        <!-- Logo & Title -->
        <div class="text-center mb-5">
            <div class="logo-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <h1 style="font-size:17px;font-weight:800;color:#1e293b;margin-bottom:3px;">ZenCare Medical Admin</h1>
            <p style="font-size:12px;color:#64748b;">Portal Internal Operasional ZenCare Medical</p>
        </div>

        <div class="card" style="padding:24px;">

            <?php if ($error): ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 12px;font-size:12px;color:#b91c1c;margin-bottom:16px;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Quick Login disembunyikan untuk keperluan screenshot laporan skripsi -->


            <!-- Form -->
            <form method="POST">
                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:5px;">Username Staf *</label>
                    <input type="text" name="username" class="field" placeholder="superadmin / admin_toko"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:5px;">Password *</label>
                    <input type="password" name="password" class="field" placeholder="••••••">
                    <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Password demo: <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;">123456</code></p>
                </div>
                <button type="submit" class="btn-submit">Masuk ke Dashboard Operasional →</button>
            </form>

            <!-- Footer link -->
            <div style="margin-top:16px;text-align:center;padding-top:14px;border-top:1px solid #f1f5f9;">
                <span style="font-size:12px;color:#94a3b8;">Anda pembeli / pelanggan?</span>
                <a href="ecommerce/index.php" style="font-size:12px;color:#1a75d2;font-weight:600;margin-left:4px;text-decoration:none;">Masuk Login Pelanggan</a>
            </div>
        </div>

        <p style="text-align:center;font-size:11px;color:#94a3b8;margin-top:16px;">ZenCare Medical Omnichannel System v3.0</p>
    </div>

</body>
</html>
