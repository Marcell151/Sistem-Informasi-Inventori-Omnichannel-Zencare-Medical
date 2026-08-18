<?php
// File: admin/pengaturan_web.php
// CMS Pengaturan Toko E-Commerce
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$msg = ''; $msgType = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_toko'] ?? '');
    $logo = trim($_POST['logo_url'] ?? '');
    $desc = trim($_POST['deskripsi_toko'] ?? '');
    $warna = trim($_POST['warna_tema'] ?? '');

    try {
        $pdo->prepare("UPDATE pengaturan_web SET nama_toko=?, logo_url=?, deskripsi_toko=?, warna_tema=? WHERE id=1")
            ->execute([$nama, $logo, $desc, $warna]);
        $msg = "✅ Pengaturan Web CMS berhasil diperbarui.";
        $msgType = 'success';
    } catch (Exception $e) {
        $msg = "❌ Gagal memperbarui: " . $e->getMessage();
        $msgType = 'error';
    }
}

// Ensure row exists
$chk = $pdo->query("SELECT id FROM pengaturan_web WHERE id=1");
if (!$chk->fetch()) {
    $pdo->query("INSERT INTO pengaturan_web (id, nama_toko) VALUES (1, 'ZenCare Medical')");
}

$webCfg = $pdo->query("SELECT * FROM pengaturan_web WHERE id=1")->fetch();

layoutHead('Pengaturan Web CMS');
layoutBodyOpen();
layoutSidebar('pengaturan_web');
layoutHeader('Pengaturan Web CMS', 'Kustomisasi identitas toko online E-Commerce');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden p-6 max-w-2xl">
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Toko / Klinik *</label>
            <input type="text" name="nama_toko" required value="<?= htmlspecialchars($webCfg['nama_toko'] ?? '') ?>" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
        </div>
        <div>
            <label class="block text-xs font-bold text-zcTxt mb-1.5">Deskripsi Singkat</label>
            <textarea name="deskripsi_toko" rows="3" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc"><?= htmlspecialchars($webCfg['deskripsi_toko'] ?? '') ?></textarea>
            <p class="text-[10px] text-zcMut mt-1">Ditampilkan di Header E-Commerce dan Struk POS.</p>
        </div>
        <div>
            <label class="block text-xs font-bold text-zcTxt mb-1.5">URL Logo Toko</label>
            <input type="text" name="logo_url" value="<?= htmlspecialchars($webCfg['logo_url'] ?? '') ?>" placeholder="https://..." class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
            <?php if (!empty($webCfg['logo_url'])): ?>
                <div class="mt-2 p-2 bg-slate-100 rounded inline-block">
                    <img src="<?= htmlspecialchars($webCfg['logo_url']) ?>" alt="Logo Toko" class="h-10 object-contain">
                </div>
            <?php endif; ?>
        </div>
        <div>
            <label class="block text-xs font-bold text-zcTxt mb-1.5">Warna Tema (Hex Code) - <span class="text-zcMut font-normal">Akan datang di Fase 6</span></label>
            <input type="text" name="warna_tema" disabled value="<?= htmlspecialchars($webCfg['warna_tema'] ?? '') ?>" placeholder="#1a75d2" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2 bg-slate-100 text-slate-400 cursor-not-allowed">
        </div>
        <div class="pt-4 flex justify-end">
            <button type="submit" class="px-6 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl shadow-sm transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php layoutEnd(); ?>
