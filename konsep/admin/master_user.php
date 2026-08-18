<?php
// File: admin/master_user.php
// Manajemen User - Super Admin Only
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // Tambah User
    if ($aksi === 'tambah_user') {
        $uname  = trim($_POST['username'] ?? '');
        $nama   = trim($_POST['nama_lengkap'] ?? '');
        $role   = $_POST['role'] ?? 'kasir';
        $cabang = ($role === 'kasir') ? intval($_POST['id_cabang'] ?? 1) : null;
        $pass   = password_hash($_POST['password'] ?? '123456', PASSWORD_BCRYPT);
        if ($uname && $nama) {
            try {
                $pdo->prepare("INSERT INTO users (username,password,nama_lengkap,role,id_cabang,is_active) VALUES (?,?,?,?,?,1)")
                    ->execute([$uname,$pass,$nama,$role,$cabang]);
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
        $id    = intval($_POST['id_user'] ?? 0);
        $nama  = trim($_POST['nama_lengkap'] ?? '');
        $role  = $_POST['role'] ?? 'kasir';
        $cabang = ($role === 'kasir') ? intval($_POST['id_cabang'] ?? 1) : null;
        if ($id && $nama) {
            $pdo->prepare("UPDATE users SET nama_lengkap=?,role=?,id_cabang=? WHERE id=?")->execute([$nama,$role,$cabang,$id]);
            $msg = "User diperbarui."; $msgType = 'success';
        }
    }
}

$users    = $pdo->query("SELECT u.*, c.nama AS nama_cabang FROM users u LEFT JOIN cabang c ON u.id_cabang=c.id ORDER BY u.id ASC")->fetchAll();
$cabangList = $pdo->query("SELECT id, nama FROM cabang WHERE is_active=1")->fetchAll();

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
        <h2 class="text-base font-bold text-gray-900">Daftar Akun User</h2>
        <p class="text-xs text-gray-500 mt-0.5">Total: <?= count($users) ?> akun terdaftar</p>
    </div>
    <button onclick="document.getElementById('modal_tambah_user').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
        + Tambah User Baru
    </button>
</div>

<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-gray-200 text-gray-500 font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">Username</th>
                    <th class="px-5 py-3 text-left">Nama Lengkap</th>
                    <th class="px-5 py-3 text-center">Role</th>
                    <th class="px-5 py-3 text-left">Cabang</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBorder/60">
                <?php foreach ($users as $u): ?>
                    <?php
                    $roleCls = match($u['role']) {
                        'super_admin' => 'bg-gray-800/10 text-gray-950 border-zcNavy/20',
                        'kasir'       => 'bg-sky-100 text-sky-700 border-sky-200',
                        'pelanggan'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        default       => 'bg-slate-100 text-slate-500 border-slate-200',
                    };
                    $roleIcon = match($u['role']) {
                        'super_admin' => 'ðŸ‘‘', 'kasir' => 'ðŸª', 'pelanggan' => 'ðŸ›’', default => 'ðŸ‘¤',
                    };
                    ?>
                    <tr class="hover:bg-slate-50/60 transition <?= !$u['is_active'] ? 'opacity-50' : '' ?>">
                        <td class="px-5 py-3.5 text-gray-500 font-mono"><?= $u['id'] ?></td>
                        <td class="px-5 py-3.5 font-bold text-gray-900"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="px-5 py-3.5 text-gray-900"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $roleCls ?>">
                                <?= $roleIcon ?> <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500"><?= htmlspecialchars($u['nama_cabang'] ?? '-') ?></td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $u['is_active'] ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' ?>">
                                <?= $u['is_active'] ? '&check; Aktif' : '&times; Nonaktif' ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <button onclick="openEditUser(<?= $u['id'] ?>, '<?= addslashes($u['nama_lengkap']) ?>', '<?= $u['role'] ?>', <?= $u['id_cabang'] ?? 'null' ?>)"
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
                                    <span class="text-[10px] text-gray-500 italic px-2">Protected</span>
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
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-900">Tambah User Baru</h3>
            <button onclick="document.getElementById('modal_tambah_user').classList.add('hidden')" class="text-gray-500 hover:text-gray-900 text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="tambah_user">
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Username * <span class="text-gray-500 font-normal">(unik, tanpa spasi)</span></label>
                <input type="text" name="username" required placeholder="kasir_baru" class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" required placeholder="Budi Santoso" class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Password (default: 123456)</label>
                <input type="password" name="password" placeholder="Kosongkan = 123456" class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Role *</label>
                <select name="role" id="add_role" onchange="toggleCabangField('add_cabang_row', this.value)"
                    class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
                    <option value="kasir">Kasir</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="pelanggan">Pelanggan</option>
                </select>
            </div>
            <div id="add_cabang_row">
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Cabang (wajib untuk Kasir)</label>
                <select name="id_cabang" class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
                    <?php foreach ($cabangList as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_tambah_user').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-gray-800 hover:bg-gray-700 text-white rounded-xl transition shadow-sm">Buat Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit User -->
<div id="modal_edit_user" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-900">Edit User</h3>
            <button onclick="document.getElementById('modal_edit_user').classList.add('hidden')" class="text-gray-500 hover:text-gray-900 text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="edit_user">
            <input type="hidden" name="id_user" id="eu_id">
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Nama Lengkap *</label>
                <input type="text" name="nama_lengkap" id="eu_nama" required class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Role *</label>
                <select name="role" id="eu_role" onchange="toggleCabangField('eu_cabang_row', this.value)"
                    class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
                    <option value="kasir">Kasir</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="pelanggan">Pelanggan</option>
                </select>
            </div>
            <div id="eu_cabang_row">
                <label class="block text-xs font-bold text-gray-900 mb-1.5">Cabang</label>
                <select name="id_cabang" id="eu_cabang" class="w-full text-xs border border-gray-200 rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-gray-400 bg-slate-50">
                    <?php foreach ($cabangList as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_edit_user').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-gray-800 hover:bg-gray-700 text-white rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUser(id, nama, role, cabangId) {
    document.getElementById('eu_id').value = id;
    document.getElementById('eu_nama').value = nama;
    document.getElementById('eu_role').value = role;
    if (cabangId) document.getElementById('eu_cabang').value = cabangId;
    toggleCabangField('eu_cabang_row', role);
    document.getElementById('modal_edit_user').classList.remove('hidden');
}
function toggleCabangField(rowId, roleVal) {
    document.getElementById(rowId).style.display = (roleVal === 'kasir') ? 'block' : 'none';
}
// Initial hide cabang for non-kasir
toggleCabangField('add_cabang_row', document.getElementById('add_role')?.value ?? 'kasir');
</script>

<?php layoutEnd(); ?>


