<?php
// File: admin/master_cabang.php
// Master Cabang – Super Admin Only
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
        $alamat = trim($_POST['alamat'] ?? '');
        $kotaId = intval($_POST['kota_id'] ?? 256);
        if ($nama && $alamat) {
            $pdo->prepare("INSERT INTO cabang (nama,alamat,kota_id,is_active) VALUES (?,?,?,1)")->execute([$nama,$alamat,$kotaId]);
            $msg = "Cabang '$nama' ditambahkan."; $msgType = 'success';
        } else { $msg = "Nama & Alamat wajib!"; $msgType = 'error'; }
    }
    if ($aksi === 'edit') {
        $id     = intval($_POST['id'] ?? 0);
        $nama   = trim($_POST['nama'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $kotaId = intval($_POST['kota_id'] ?? 256);
        if ($id && $nama) {
            $pdo->prepare("UPDATE cabang SET nama=?,alamat=?,kota_id=? WHERE id=?")->execute([$nama,$alamat,$kotaId,$id]);
            $msg = "Cabang diperbarui."; $msgType = 'success';
        }
    }
    if ($aksi === 'toggle') {
        $id = intval($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE cabang SET is_active=1-is_active WHERE id=?")->execute([$id]);
        $msg = "Status cabang diperbarui."; $msgType = 'info';
    }
}

$cabangList = $pdo->query("SELECT * FROM cabang ORDER BY id ASC")->fetchAll();

layoutHead('Master Cabang');
layoutBodyOpen();
layoutSidebar('master_cabang');
layoutHeader('Master Cabang', 'Kelola data cabang fisik ZenCare Medical (2 Cabang: Muharto & Borobudur)');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-sky-50 border-sky-200 text-sky-800') ?>">
        <?= $msgType === 'success' ? '✅' : ($msgType === 'error' ? '⛔' : 'ℹ️') ?> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-<?= count($cabangList) ?> gap-4 mb-6">
    <?php foreach ($cabangList as $c): ?>
        <?php
        $stokQuery = $pdo->prepare("SELECT COALESCE(SUM(stok),0) FROM stok_cabang WHERE id_cabang=?");
        $stokQuery->execute([$c['id']]);
        $totalStok = $stokQuery->fetchColumn();
        $userCount = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id_cabang=? AND is_active=1");
        $userCount->execute([$c['id']]);
        $totalUser = $userCount->fetchColumn();
        ?>
        <div class="bg-white border border-zcBorder rounded-2xl p-5 shadow-sm <?= !$c['is_active'] ? 'opacity-60' : '' ?>">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-zcNavy/10 flex items-center justify-center text-xl">🏥</div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border <?= $c['is_active'] ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' ?>">
                    <?= $c['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                </span>
            </div>
            <h3 class="text-sm font-bold text-zcText"><?= htmlspecialchars($c['nama']) ?></h3>
            <p class="text-[11px] text-zcMuted mt-1 leading-relaxed"><?= htmlspecialchars($c['alamat']) ?></p>
            <div class="mt-3 pt-3 border-t border-zcBorder flex justify-between text-xs">
                <span class="text-zcMuted">Total Stok: <strong class="text-zcText"><?= number_format($totalStok) ?> unit</strong></span>
                <span class="text-zcMuted">Kasir: <strong class="text-zcText"><?= $totalUser ?></strong></span>
            </div>
            <div class="mt-2 text-[11px] text-zcMuted">RajaOngkir Kota ID: <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded"><?= $c['kota_id'] ?></code></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="flex items-center justify-between mb-5">
    <div>
        <h2 class="text-base font-bold text-zcText">Manajemen Cabang</h2>
        <p class="text-xs text-zcMuted mt-0.5"><?= count($cabangList) ?> cabang terdaftar di sistem</p>
    </div>
    <button onclick="document.getElementById('modal_tambah').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2.5 bg-zcNavy hover:bg-zcNavyHv text-white text-xs font-bold rounded-xl transition shadow-sm">
        + Tambah Cabang Baru
    </button>
</div>

<div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBorder text-zcMuted font-bold uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left">ID</th>
                    <th class="px-5 py-3 text-left">Nama Cabang</th>
                    <th class="px-5 py-3 text-left">Alamat Fisik</th>
                    <th class="px-5 py-3 text-center">Kota ID (RajaOngkir)</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBorder/60">
                <?php foreach ($cabangList as $c): ?>
                    <tr class="hover:bg-slate-50/60 transition <?= !$c['is_active'] ? 'opacity-50' : '' ?>">
                        <td class="px-5 py-3.5 font-mono text-zcMuted"><?= $c['id'] ?></td>
                        <td class="px-5 py-3.5 font-bold text-zcText">🏥 <?= htmlspecialchars($c['nama']) ?></td>
                        <td class="px-5 py-3.5 text-zcMuted"><?= htmlspecialchars($c['alamat']) ?></td>
                        <td class="px-5 py-3.5 text-center"><code class="font-mono bg-slate-100 px-2 py-0.5 rounded text-zcText"><?= $c['kota_id'] ?></code></td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $c['is_active'] ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' ?>">
                                <?= $c['is_active'] ? '✓ Aktif' : '✕ Nonaktif' ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="openEdit(<?= $c['id'] ?>, '<?= addslashes($c['nama']) ?>', '<?= addslashes($c['alamat']) ?>', <?= $c['kota_id'] ?>)"
                                    class="px-2.5 py-1 text-[11px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl transition">Edit</button>
                                <form method="POST" class="inline" onsubmit="return confirm('Toggle status cabang ini?')">
                                    <input type="hidden" name="aksi" value="toggle">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="px-2.5 py-1 text-[11px] font-bold <?= $c['is_active'] ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?> border rounded-xl transition">
                                        <?= $c['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
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
            <h3 class="text-sm font-bold text-zcText">Tambah Cabang Baru</h3>
            <button onclick="document.getElementById('modal_tambah').classList.add('hidden')" class="text-zcMuted hover:text-zcText text-lg">✕</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="tambah">
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Nama Cabang *</label>
                <input type="text" name="nama" required placeholder="ZenCare Sukun" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Alamat Fisik Lengkap *</label>
                <textarea name="alamat" required rows="2" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50" placeholder="Jl. Sukun No. 12, Kota Malang"></textarea></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Kota ID RajaOngkir <span class="font-normal text-zcMuted">(256 = Kota Malang)</span></label>
                <input type="number" name="kota_id" value="256" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_tambah').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zcNavy hover:bg-zcNavyHv text-white rounded-xl transition shadow-sm">Simpan Cabang</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL Edit -->
<div id="modal_edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBorder">
            <h3 class="text-sm font-bold text-zcText">Edit Data Cabang</h3>
            <button onclick="document.getElementById('modal_edit').classList.add('hidden')" class="text-zcMuted hover:text-zcText text-lg">✕</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="ec_id">
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Nama Cabang *</label>
                <input type="text" name="nama" id="ec_nama" required class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Alamat Fisik</label>
                <textarea name="alamat" id="ec_alamat" rows="2" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></textarea></div>
            <div><label class="block text-xs font-bold text-zcText mb-1.5">Kota ID RajaOngkir</label>
                <input type="number" name="kota_id" id="ec_kota" class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_edit').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zcSky hover:bg-zcCyan text-white rounded-xl transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, nama, alamat, kotaId) {
    document.getElementById('ec_id').value = id;
    document.getElementById('ec_nama').value = nama;
    document.getElementById('ec_alamat').value = alamat;
    document.getElementById('ec_kota').value = kotaId;
    document.getElementById('modal_edit').classList.remove('hidden');
}
</script>

<?php layoutEnd(); ?>
