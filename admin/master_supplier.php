<?php
// File: admin/master_supplier.php
// Master Supplier – Super Admin Only
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'tambah') {
        $nama   = trim($_POST['nama'] ?? '');
        $kontak = trim($_POST['kontak'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        if ($nama) {
            $pdo->prepare("INSERT INTO supplier (nama,kontak,alamat,is_active) VALUES (?,?,?,1)")->execute([$nama,$kontak,$alamat]);
            $msg = "Supplier '$nama' ditambahkan."; $msgType = 'success';
        } else { $msg = "Nama supplier wajib!"; $msgType = 'error'; }
    }
    if ($aksi === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $nama   = trim($_POST['nama'] ?? '');
        $kontak = trim($_POST['kontak'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        if ($id && $nama) {
            $pdo->prepare("UPDATE supplier SET nama=?,kontak=?,alamat=? WHERE id=?")->execute([$nama,$kontak,$alamat,$id]);
            $msg = "Supplier diperbarui."; $msgType = 'success';
        }
    }
    if ($aksi === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE supplier SET is_active=1-is_active WHERE id=?")->execute([$id]);
        $msg = "Status supplier diperbarui."; $msgType = 'info';
    }
}

$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY id DESC")->fetchAll();

layoutHead('Master Supplier');
layoutBodyOpen();
layoutSidebar('master_supplier');
layoutHeader('Master Supplier', 'Kelola data distributor & supplier alat kesehatan');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-sky-50 border-sky-200 text-sky-800') ?>">
        <?= $msgType === 'success' ? '✅' : ($msgType === 'error' ? '⛔' : 'ℹ️') ?> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-base font-bold text-zcText">Daftar Supplier</h2>
        <p class="text-xs text-zcMuted mt-0.5"><?= count($suppliers) ?> supplier terdaftar</p>
    </div>
    <button onclick="document.getElementById('modal_tambah').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2.5 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition shadow-sm">
        + Tambah Supplier
    </button>
</div>

<div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBorder text-zcMuted font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">#</th>
                    <th class="px-5 py-3 text-left">Nama Supplier</th>
                    <th class="px-5 py-3 text-left">Kontak</th>
                    <th class="px-5 py-3 text-left">Alamat</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBorder/60">
                <?php foreach ($suppliers as $s): ?>
                    <tr class="hover:bg-slate-50/60 transition <?= !$s['is_active'] ? 'opacity-50' : '' ?>">
                        <td class="px-5 py-3.5 text-zcMuted font-mono"><?= $s['id'] ?></td>
                        <td class="px-5 py-3.5 font-bold text-zcTxt"><?= htmlspecialchars($s['nama']) ?></td>
                        <td class="px-5 py-3.5 text-zcMuted"><?= htmlspecialchars($s['kontak'] ?? '–') ?></td>
                        <td class="px-5 py-3.5 text-zcMuted max-w-xs"><?= htmlspecialchars($s['alamat'] ?? '–') ?></td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $s['is_active'] ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' ?>">
                                <?= $s['is_active'] ? '✓ Aktif' : '✕ Nonaktif' ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openEdit(<?= $s['id'] ?>, '<?= addslashes($s['nama']) ?>', '<?= addslashes($s['kontak'] ?? '') ?>', '<?= addslashes($s['alamat'] ?? '') ?>')"
                                    class="px-2.5 py-1 text-[11px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl transition">Edit</button>
                                <form method="POST" class="inline" onsubmit="return confirm('Toggle status supplier ini?')">
                                    <input type="hidden" name="aksi" value="toggle">
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="px-2.5 py-1 text-[11px] font-bold <?= $s['is_active'] ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?> border rounded-xl transition">
                                        <?= $s['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL Tambah -->
<div id="modal_tambah" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBorder">
            <h3 class="text-sm font-bold text-zcText">Tambah Supplier Baru</h3>
            <button onclick="document.getElementById('modal_tambah').classList.add('hidden')" class="text-zcMuted hover:text-zcText text-lg">✕</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="tambah">
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Nama Supplier *</label>
                <input type="text" name="nama" required placeholder="PT. Alkes Medika" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Nomor Kontak</label>
                <input type="text" name="kontak" placeholder="08xxxxxxxxxx" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Alamat</label>
                <textarea name="alamat" rows="2" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50" placeholder="Jl. Industri Medis..."></textarea></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_tambah').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL Edit -->
<div id="modal_edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBorder">
            <h3 class="text-sm font-bold text-zcText">Edit Supplier</h3>
            <button onclick="document.getElementById('modal_edit').classList.add('hidden')" class="text-zcMuted hover:text-zcText text-lg">✕</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="edit_sup_id">
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Nama Supplier *</label>
                <input type="text" name="nama" id="edit_sup_nama" required class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Kontak</label>
                <input type="text" name="kontak" id="edit_sup_kontak" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Alamat</label>
                <textarea name="alamat" id="edit_sup_alamat" rows="2" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></textarea></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_edit').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, nama, kontak, alamat) {
    document.getElementById('edit_sup_id').value = id;
    document.getElementById('edit_sup_nama').value = nama;
    document.getElementById('edit_sup_kontak').value = kontak;
    document.getElementById('edit_sup_alamat').value = alamat;
    document.getElementById('modal_edit').classList.remove('hidden');
}
</script>

<?php layoutEnd(); ?>
