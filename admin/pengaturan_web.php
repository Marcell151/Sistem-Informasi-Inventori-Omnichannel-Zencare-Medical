<?php
// File: admin/pengaturan_web.php
// CMS Pengaturan Toko E-Commerce
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin']);

$msg = ''; $msgType = '';
$uploadDir = __DIR__ . '/../uploads/cms/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_toko'] ?? '');
    $desc = trim($_POST['deskripsi'] ?? '');
    $telp = trim($_POST['telepon'] ?? '');
    $wa = trim($_POST['whatsapp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $email_cs = trim($_POST['email_cs'] ?? '');

    // Get current files
    $current = $pdo->query("SELECT logo, banner_promosi FROM pengaturan_web WHERE id=1")->fetch();
    $logoPath = $current['logo'];
    $bannerPath = $current['banner_promosi'];

    // Handle Logo Upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logoName = 'logo_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName)) {
            $logoPath = '/inventory_zencare/uploads/cms/' . $logoName;
        }
    }

    // Handle Banner Upload
    if (isset($_FILES['banner_promosi']) && $_FILES['banner_promosi']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['banner_promosi']['name'], PATHINFO_EXTENSION);
        $bannerName = 'banner_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['banner_promosi']['tmp_name'], $uploadDir . $bannerName)) {
            $bannerPath = '/inventory_zencare/uploads/cms/' . $bannerName;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE pengaturan_web SET nama_toko=?, deskripsi=?, telepon=?, whatsapp=?, alamat=?, email_cs=?, logo=?, banner_promosi=? WHERE id=1");
        $stmt->execute([$nama, $desc, $telp, $wa, $alamat, $email_cs, $logoPath, $bannerPath]);
        $msg = "Pengaturan Web CMS berhasil diperbarui.";
        $msgType = 'success';
    } catch (Exception $e) {
        $msg = "Gagal memperbarui: " . $e->getMessage();
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
layoutHeader('Pengaturan Web CMS', 'Kustomisasi Tampilan E-Commerce dan Info Toko');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2
        <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
        <span><?= $msgType === 'success' ? '✓' : '✗' ?></span>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Form Pengaturan -->
    <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden p-6">
        <h2 class="text-sm font-bold text-zcTxt mb-5 border-b pb-3">Informasi Umum & Kontak</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Toko *</label>
                    <input type="text" name="nama_toko" required value="<?= htmlspecialchars($webCfg['nama_toko'] ?? '') ?>" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Email CS</label>
                    <input type="email" name="email_cs" value="<?= htmlspecialchars($webCfg['email_cs'] ?? '') ?>" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Telepon (PSTN)</label>
                    <input type="text" name="telepon" value="<?= htmlspecialchars($webCfg['telepon'] ?? '') ?>" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">WhatsApp (628...)</label>
                    <input type="text" name="whatsapp" value="<?= htmlspecialchars($webCfg['whatsapp'] ?? '') ?>" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Alamat Fisik Toko</label>
                <textarea name="alamat" rows="2" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc"><?= htmlspecialchars($webCfg['alamat'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Deskripsi Singkat (SEO & Footer)</label>
                <textarea name="deskripsi" rows="3" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc"><?= htmlspecialchars($webCfg['deskripsi'] ?? '') ?></textarea>
            </div>

            <h2 class="text-sm font-bold text-zcTxt mb-5 border-b pb-3 mt-8">Media Visual E-Commerce</h2>
            
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Upload Logo (1:1 / PNG)</label>
                <input type="file" name="logo" accept="image/*" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
                <?php if (!empty($webCfg['logo'])): ?>
                    <p class="text-[10px] text-emerald-600 mt-1 font-semibold flex items-center gap-1">✓ Logo saat ini sudah terpasang.</p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Upload Banner Promosi E-Commerce (16:9)</label>
                <input type="file" name="banner_promosi" accept="image/*" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
                <p class="text-[10px] text-zcMut mt-1">Ditampilkan paling atas di halaman E-Commerce pelanggan.</p>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl shadow-sm transition">
                    Simpan Perubahan Web
                </button>
            </div>
        </form>
    </div>

    <!-- Live Preview Panel -->
    <div>
        <h2 class="text-sm font-bold text-zcTxt mb-4 px-2">Preview Halaman Depan E-Commerce</h2>
        
        <div class="bg-white rounded-2xl shadow-sm border border-zcBrd overflow-hidden sticky top-20 flex flex-col h-[500px]">
            <!-- Fake Browser Header -->
            <div class="bg-slate-100 border-b border-zcBrd px-4 py-3 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                <div class="mx-auto bg-white border border-zcBrd rounded-md px-3 text-[10px] text-slate-400 font-mono">
                    zencare.id/store
                </div>
            </div>
            
            <!-- Storefront Preview -->
            <div class="flex-1 bg-slate-50 overflow-y-auto relative">
                <!-- Header -->
                <div class="bg-white px-4 py-3 shadow-sm flex items-center justify-between sticky top-0 z-10">
                    <div class="flex items-center gap-2">
                        <?php if (!empty($webCfg['logo'])): ?>
                            <img src="<?= htmlspecialchars($webCfg['logo']) ?>" class="h-6 w-6 rounded object-cover" alt="Logo">
                        <?php else: ?>
                            <div class="h-6 w-6 bg-zc text-white flex items-center justify-center font-bold text-[10px] rounded">ZC</div>
                        <?php endif; ?>
                        <span class="text-[11px] font-black text-zcTxt"><?= htmlspecialchars($webCfg['nama_toko'] ?? 'ZenCare Medical') ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-zcMut">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    </div>
                </div>

                <!-- Banner Area -->
                <?php if (!empty($webCfg['banner_promosi'])): ?>
                <div class="w-full aspect-[21/9] bg-slate-200 relative">
                    <img src="<?= htmlspecialchars($webCfg['banner_promosi']) ?>" class="w-full h-full object-cover" alt="Banner">
                </div>
                <?php else: ?>
                <div class="w-full aspect-[21/9] bg-gradient-to-br from-blue-500 to-zc flex flex-col items-center justify-center text-white px-4 text-center">
                    <span class="text-sm font-bold mb-1">Banner Promo Belum Diupload</span>
                    <span class="text-[9px] opacity-80">Upload banner untuk menarik perhatian pelanggan</span>
                </div>
                <?php endif; ?>

                <!-- Content Dummy -->
                <div class="p-4">
                    <h3 class="text-xs font-bold text-zcTxt mb-2">Rekomendasi Produk</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white p-2 rounded-xl border border-zcBrd aspect-square"></div>
                        <div class="bg-white p-2 rounded-xl border border-zcBrd aspect-square"></div>
                    </div>
                </div>

                <!-- Footer Preview -->
                <div class="bg-slate-900 text-slate-400 p-4 text-[9px] mt-4">
                    <span class="text-white font-bold block mb-1"><?= htmlspecialchars($webCfg['nama_toko'] ?? '') ?></span>
                    <p class="mb-2 line-clamp-2"><?= htmlspecialchars($webCfg['deskripsi'] ?? '') ?></p>
                    <p class="flex items-center gap-1 mb-1">☎ <?= htmlspecialchars($webCfg['telepon'] ?? '-') ?></p>
                    <p class="flex items-center gap-1">✉ <?= htmlspecialchars($webCfg['email_cs'] ?? '-') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php layoutEnd(); ?>
