<?php
// File: admin/master_user.php
// Manajemen User - Super Admin Only
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // Tambah User
    if ($aksi === 'tambah_user') {
        $uname   = trim($_POST['username'] ?? '');
        $nama    = trim($_POST['nama_lengkap'] ?? '');
        $role    = $_POST['role'] ?? 'admin';
        $email   = trim($_POST['email'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $pass    = password_hash($_POST['password'] ?? '123456', PASSWORD_BCRYPT);
        if ($uname && $nama) {
            try {
                $pdo->prepare("INSERT INTO users (username,password,nama_lengkap,role,email,telepon,is_active) VALUES (?,?,?,?,?,?,1)")
                    ->execute([$uname,$pass,$nama,$role,$email,$telepon]);
                $msg = "User '$uname' ($role) berhasil ditambahkan."; $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Error: " . $e->getMessage(); $msgType = 'error';
            }
        } else { $msg = "Username & Nama Lengkap wajib diisi!"; $msgType = 'error'; }
    }

    // Toggle aktif
    if ($aksi === 'toggle_user') {
        $id = intval($_POST['id_user'] ?? 0);
        if ($id === 1) { $msg = "Super Admin utama tidak bisa dinonaktifkan!"; $msgType = 'error'; }
        else {
            $pdo->prepare("UPDATE users SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
            $msg = "Status user diperbarui."; $msgType = 'info';
        }
    }

    // Reset password
    if ($aksi === 'reset_pass') {
        $id = intval($_POST['id_user'] ?? 0);
        $newPass = password_hash('123456', PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newPass, $id]);
        $msg = "Password user berhasil direset ke '123456'."; $msgType = 'success';
    }

    // Edit User
    if ($aksi === 'edit_user') {
        $id      = intval($_POST['id_user'] ?? 0);
        $nama    = trim($_POST['nama_lengkap'] ?? '');
        $role    = $_POST['role'] ?? 'admin';
        $email   = trim($_POST['email'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        if ($id && $nama) {
            $pdo->prepare("UPDATE users SET nama_lengkap=?, role=?, email=?, telepon=? WHERE id=?")->execute([$nama, $role, $email, $telepon, $id]);
            $msg = "User diperbarui."; $msgType = 'success';
        }
    }
}

$users = $pdo->query("SELECT u.* FROM users u ORDER BY u.id ASC")->fetchAll();


layoutHead('Manajemen User');
layoutBodyOpen();
layoutSidebar('master_user');
layoutHeader('Manajemen User & Hak Akses', 'Kelola akun kasir, admin, dan pelanggan - dengan soft delete (Super Admin Only)');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-sky-50 border-sky-200 text-sky-800') ?>">
        <?= $msgType === 'success' ? '&#10004;' : ($msgType === 'error' ? '&#9940;' : '&#8505;') ?> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-base font-bold text-zcTxt">Daftar Akun User</h2>
        <p class="text-xs text-zcMut mt-0.5">Total: <?= count($users) ?> akun terdaftar</p>
    </div>
    <button onclick="document.getElementById('modal_tambah_user').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2.5 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition shadow-sm">
        + Tambah User Baru
    </button>
</div>

<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">Username</th>
                    <th class="px-5 py-3 text-left">Nama & Kontak</th>
                    <th class="px-5 py-3 text-center">Role</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBorder/60">
                <?php foreach ($users as $u): ?>
                    <?php
                    $roleCls = match($u['role']) {
                        'superadmin' => 'bg-zc/10 text-zcNavy border-zcNavy/20',
                        'admin'    => 'bg-sky-100 text-sky-700 border-sky-200',
                        'pelanggan'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        default       => 'bg-slate-100 text-slate-500 border-slate-200',
                    };
                    ?>
                    <tr class="hover:bg-slate-50/60 transition <?= !$u['is_active'] ? 'opacity-50' : '' ?>">
                        <td class="px-5 py-3.5 text-zcMut font-mono text-sm"><?= $u['id'] ?></td>
                        <td class="px-5 py-3.5 font-bold text-zcTxt text-sm"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="px-5 py-3.5 text-sm">
                            <div class="font-bold text-zcTxt mb-0.5"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
                            <?php if(!empty($u['email']) || !empty($u['telepon'])): ?>
                                <div class="text-[11px] text-slate-500 font-medium">
                                    <?= !empty($u['email']) ? htmlspecialchars($u['email']) : '' ?>
                                    <?= !empty($u['email']) && !empty($u['telepon']) ? ' • ' : '' ?>
                                    <?= !empty($u['telepon']) ? htmlspecialchars($u['telepon']) : '' ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $roleCls ?>">
                                <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $u['is_active'] ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' ?>">
                                <?= $u['is_active'] ? '&check; Aktif' : '&times; Nonaktif' ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <button onclick="openEditUser(<?= $u['id'] ?>, '<?= addslashes($u['nama_lengkap']) ?>', '<?= $u['role'] ?>', '<?= addslashes($u['email'] ?? '') ?>', '<?= addslashes($u['telepon'] ?? '') ?>')"
                                    class="px-2.5 py-1 text-[11px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl transition">Edit</button>
                                <?php if ($u['id'] !== 1): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('<?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?> user ini?')">
                                        <input type="hidden" name="aksi" value="toggle_user">
                                        <input type="hidden" name="id_user" value="<?= $u['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-bold <?= $u['is_active'] ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?> border rounded-xl transition">
                                            <?= $u['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline" onsubmit="return confirm('Reset password user ini ke 123456?')">
                                        <input type="hidden" name="aksi" value="reset_pass">
                                        <input type="hidden" name="id_user" value="<?= $u['id'] ?>">
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-xl transition">Reset Pass</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-[10px] text-zcMut italic px-2">Protected</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Tambah User -->
<div id="modal_tambah_user" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd">
            <h3 class="text-sm font-bold text-zcTxt">Tambah User Baru</h3>
            <button onclick="document.getElementById('modal_tambah_user').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="tambah_user">
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Username * <span class="text-zcMut font-normal">(unik, tanpa spasi)</span></label>
                <input type="text" name="username" required placeholder="kasir_baru" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" required placeholder="Budi Santoso" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Alamat Email (Opsional)</label>
                <input type="email" name="email" placeholder="email@contoh.com" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">No HP / WhatsApp (Opsional)</label>
                <input type="text" name="telepon" placeholder="0812xxxxxx" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Password (default: 123456)</label>
                <input type="password" name="password" placeholder="Kosongkan = 123456" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Role *</label>
                <select name="role" id="add_role"
                    class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <option value="karyawan">Karyawan</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                    <option value="pelanggan">Pelanggan</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_tambah_user').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Buat Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit User -->
<div id="modal_edit_user" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd">
            <h3 class="text-sm font-bold text-zcTxt">Edit User</h3>
            <button onclick="document.getElementById('modal_edit_user').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="edit_user">
            <input type="hidden" name="id_user" id="eu_id">
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" id="eu_nama" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Alamat Email (Opsional)</label>
                <input type="email" name="email" id="eu_email" placeholder="email@contoh.com" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">No HP / WhatsApp (Opsional)</label>
                <input type="text" name="telepon" id="eu_telepon" placeholder="0812xxxxxx" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Role *</label>
                <select name="role" id="eu_role"
                    class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <option value="karyawan">Karyawan</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                    <option value="pelanggan">Pelanggan</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_edit_user').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUser(id, nama, role, email, telepon) {
    document.getElementById('eu_id').value = id;
    document.getElementById('eu_nama').value = nama;
    document.getElementById('eu_role').value = role;
    document.getElementById('eu_email').value = email || '';
    document.getElementById('eu_telepon').value = telepon || '';
    document.getElementById('modal_edit_user').classList.remove('hidden');
}

function toggleCabangField(rowId, role) {
    // Legacy function, kept so it doesn't break external scripts if any
}
</script>

<?php layoutEnd(); ?>
