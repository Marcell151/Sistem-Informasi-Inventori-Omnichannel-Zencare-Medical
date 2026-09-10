<?php
// File: admin/master_uom.php
// Master Data Satuan & UOM (Unit of Measure) — ZenCare Medical v3.0
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = '';

// ─── POST Handler ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $idVariasi = intval($_POST['id_variasi'] ?? 0);

    if ($action === 'update_uom' && $idVariasi) {
        $satuanKecil  = trim($_POST['satuan_kecil'] ?? '');
        $satuanBesar  = trim($_POST['satuan_besar'] ?? '');
        $rasio        = max(1, intval($_POST['rasio_konversi'] ?? 1));
        $hargaKecil   = floatval(str_replace(['.', ','], ['', '.'], $_POST['harga_jual_kecil'] ?? 0));
        $hargaBesar   = floatval(str_replace(['.', ','], ['', '.'], $_POST['harga_jual_besar'] ?? 0));
        $stokMin      = max(0, intval($_POST['stok_minimum'] ?? 0));
        $berat        = max(0, intval($_POST['berat'] ?? 0));

        if (!$satuanKecil || !$satuanBesar) {
            $msg = 'Satuan kecil dan satuan besar wajib diisi.'; $msgType = 'error';
        } else {
            $pdo->prepare("
                UPDATE produk_variasi SET 
                    satuan_kecil = ?, satuan_besar = ?, rasio_konversi = ?,
                    harga_jual_kecil = ?, harga_jual_besar = ?,
                    stok_minimum = ?, berat = ?
                WHERE id = ?
            ")->execute([$satuanKecil, $satuanBesar, $rasio, $hargaKecil, $hargaBesar, $stokMin, $berat, $idVariasi]);
            $msg = 'Satuan & UOM berhasil diperbarui.'; $msgType = 'success';
        }
    }
}

// ─── FETCH DATA ──────────────────────────────────────────────────────────
$produkList = $pdo->query("
    SELECT pv.*, pi.nama_produk, pi.kategori, pi.sku_induk
    FROM produk_variasi pv
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    WHERE pv.is_active = 1 AND pi.is_active = 1
    ORDER BY pi.kategori ASC, pi.nama_produk ASC, pv.nama_variasi ASC
")->fetchAll();

// Group by kategori
$grouped = ['Obat' => [], 'Alat Kesehatan' => []];
foreach ($produkList as $p) {
    $grouped[$p['kategori']][] = $p;
}

layoutHead('Master Satuan & UOM');
layoutBodyOpen();
layoutSidebar('master_uom');
layoutHeader('Master Satuan & UOM', 'Pengaturan Unit of Measure & Harga per Variasi Produk');
?>

<?php if ($msg): ?>
<div class="mb-5 flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium <?= $msgType === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700' ?>">
    <?= $msg ?>
</div>
<?php endif; ?>

<!-- Info Box -->
<div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 flex gap-3">
    <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <div class="text-xs text-blue-700">
        <p class="font-bold mb-1">Aturan UOM:</p>
        <ul class="space-y-0.5 text-blue-600">
            <li>• <strong>Obat</strong>: Rasio konversi &gt; 1 (misal: 1 Box = 10 Strip). POS menggunakan satuan kecil, E-Commerce satuan besar.</li>
            <li>• <strong>Alat Kesehatan</strong>: Rasio konversi = 1 (Single-UOM). Harga kecil = harga besar.</li>
        </ul>
    </div>
</div>

<?php foreach ($grouped as $kategori => $items): if (empty($items)) continue; ?>
<div class="mb-8">
    <h2 class="text-sm font-bold text-zcTxt mb-3 flex items-center gap-2">
        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold <?= $kategori === 'Obat' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
            <?= $kategori ?>
        </span>
        <span class="text-zcMut font-medium"><?= count($items) ?> variasi</span>
    </h2>

    <div class="bg-white rounded-2xl border border-zcBrd shadow-sm overflow-hidden">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-zcBrd">
                <tr>
                    <th class="text-left px-4 py-3 font-bold text-zcMut w-[22%]">Produk</th>
                    <th class="text-left px-4 py-3 font-bold text-zcMut">SKU Variasi</th>
                    <th class="text-center px-4 py-3 font-bold text-zcMut">Satuan Kecil</th>
                    <th class="text-center px-4 py-3 font-bold text-zcMut">Satuan Besar</th>
                    <th class="text-center px-4 py-3 font-bold text-zcMut">Rasio</th>
                    <th class="text-right px-4 py-3 font-bold text-zcMut">Harga Kecil (POS)</th>
                    <th class="text-right px-4 py-3 font-bold text-zcMut">Harga Besar (Web)</th>
                    <th class="text-right px-4 py-3 font-bold text-zcMut">Stok Min</th>
                    <th class="text-right px-4 py-3 font-bold text-zcMut">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zcBrd">
            <?php foreach ($items as $p): ?>
                <tr class="hover:bg-slate-50 transition" id="row-<?= $p['id'] ?>">
                    <td class="px-4 py-3">
                        <div class="font-bold text-zcTxt"><?= htmlspecialchars($p['nama_produk']) ?></div>
                        <div class="text-zcMut"><?= htmlspecialchars($p['nama_variasi']) ?></div>
                    </td>
                    <td class="px-4 py-3 font-mono text-zcMut"><?= htmlspecialchars($p['sku_variasi']) ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-zcTxt"><?= htmlspecialchars($p['satuan_kecil']) ?></td>
                    <td class="px-4 py-3 text-center font-semibold text-zcTxt"><?= htmlspecialchars($p['satuan_besar']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-bold">1:<?= $p['rasio_konversi'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-zcTxt">Rp <?= number_format($p['harga_jual_kecil'],0,',','.') ?></td>
                    <td class="px-4 py-3 text-right font-semibold text-zcTxt">Rp <?= number_format($p['harga_jual_besar'],0,',','.') ?></td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-bold <?= $p['stok_minimum'] > 0 ? 'text-amber-600' : 'text-slate-400' ?>"><?= $p['stok_minimum'] ?></span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)"
                            class="px-3 py-1.5 bg-zcLt text-zc rounded-lg hover:bg-blue-100 font-bold text-[11px]">
                            Edit UOM
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal Edit UOM -->
<div id="modal-edit-uom" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
        <div class="p-5 border-b border-zcBrd flex items-center justify-between">
            <h3 class="text-sm font-bold text-zcTxt" id="modal-title">Edit UOM</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-red-500">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" class="p-5 space-y-4">
            <input type="hidden" name="action" value="update_uom">
            <input type="hidden" name="id_variasi" id="edit_id_variasi">

            <div id="alkes-note" class="hidden bg-purple-50 border border-purple-200 rounded-xl p-3 text-xs text-purple-700 font-medium">
                <strong>Alat Kesehatan (Single-UOM)</strong>: Rasio konversi otomatis = 1. Harga besar = harga kecil.
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Satuan Kecil *</label>
                    <input type="text" name="satuan_kecil" id="edit_satuan_kecil" required placeholder="Strip, Botol, Unit..."
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Satuan Besar *</label>
                    <input type="text" name="satuan_besar" id="edit_satuan_besar" required placeholder="Box, Karton, Unit..."
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-zcTxt mb-1.5">Rasio Konversi (1 Besar = ? Kecil) *</label>
                <input type="number" name="rasio_konversi" id="edit_rasio" min="1" required
                    class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                <p class="text-[10px] text-zcMut mt-1">Obat: sesuai kemasan (mis. 10 = 1 Box isi 10 Strip). Alkes: wajib 1.</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Harga Jual Kecil (POS) *</label>
                    <input type="number" name="harga_jual_kecil" id="edit_harga_kecil" min="0" step="100" required
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Harga Jual Besar (E-Commerce) *</label>
                    <input type="number" name="harga_jual_besar" id="edit_harga_besar" min="0" step="100" required
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Stok Minimum (Alert Kritis)</label>
                    <input type="number" name="stok_minimum" id="edit_stok_min" min="0"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                    <p class="text-[10px] text-zcMut mt-1">Dalam satuan kecil</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Berat (gram)</label>
                    <input type="number" name="berat" id="edit_berat" min="0"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                    <p class="text-[10px] text-zcMut mt-1">Untuk kalkulasi ongkir</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="px-5 py-2 rounded-xl border border-zcBrd text-zcMut text-xs font-semibold">Batal</button>
                <button type="submit" class="px-6 py-2 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold shadow-sm shadow-blue-500/20">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(data) {
    document.getElementById('edit_id_variasi').value = data.id;
    document.getElementById('modal-title').textContent = 'Edit UOM: ' + data.nama_produk;
    document.getElementById('edit_satuan_kecil').value = data.satuan_kecil;
    document.getElementById('edit_satuan_besar').value = data.satuan_besar;
    document.getElementById('edit_rasio').value = data.rasio_konversi;
    document.getElementById('edit_harga_kecil').value = data.harga_jual_kecil;
    document.getElementById('edit_harga_besar').value = data.harga_jual_besar;
    document.getElementById('edit_stok_min').value = data.stok_minimum;
    document.getElementById('edit_berat').value = data.berat;
    // Show Alkes note if Single-UOM
    const isAlkes = (parseInt(data.rasio_konversi) === 1);
    document.getElementById('alkes-note').classList.toggle('hidden', !isAlkes);
    document.getElementById('modal-edit-uom').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('modal-edit-uom').classList.add('hidden');
}
</script>

<?php layoutFooter(); ?>
