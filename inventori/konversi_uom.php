<?php
// File: inventori/konversi_uom.php
// Modul Pemecahan Box ke Satuan Kecil (Multi-UOM) - ZenCare Medical
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = ''; $newBatchId = null;
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVariasi = intval($_POST['id_variasi'] ?? 0);
    $noBatchAsal = trim($_POST['no_batch_asal'] ?? '');
    $qtyBox = intval($_POST['qty_box'] ?? 0);
    
    if (!$idVariasi || !$noBatchAsal || $qtyBox <= 0) {
        $msg = "Semua field wajib diisi dan Jumlah Box harus > 0!"; $msgType = 'error';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Dapatkan info variasi
            $stmtVar = $pdo->prepare("SELECT pv.*, pi.nama_produk, pi.kategori FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.id = ?");
            $stmtVar->execute([$idVariasi]);
            $var = $stmtVar->fetch();
            
            if (!$var || $var['kategori'] !== 'Obat') {
                throw new Exception("Produk tidak valid atau bukan Obat.");
            }
            
            $rasio = intval($var['rasio_konversi']);
            if ($rasio <= 1) {
                throw new Exception("Rasio konversi 1:1, tidak perlu dipecah.");
            }
            
            $qtyPcs = $qtyBox * $rasio;
            
            // Cek batch asal
            $stmtBatch = $pdo->prepare("SELECT * FROM stok_batch WHERE id_variasi = ? AND no_batch = ? FOR UPDATE");
            $stmtBatch->execute([$idVariasi, $noBatchAsal]);
            $batchAsal = $stmtBatch->fetch();
            
            if (!$batchAsal) throw new Exception("Batch asal tidak ditemukan.");
            if ($batchAsal['stok_sisa'] < $qtyPcs) {
                throw new Exception("Stok batch asal tidak mencukupi. Butuh $qtyPcs " . $var['satuan_kecil'] . " (Setara $qtyBox " . $var['satuan_besar'] . "). Tersedia: " . $batchAsal['stok_sisa'] . " " . $var['satuan_kecil']);
            }
            
            // Kurangi batch asal
            $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyPcs, $batchAsal['id']]);
            
            // Generate Sub-Batch ID
            $stmtCekSub = $pdo->prepare("SELECT no_batch FROM stok_batch WHERE id_variasi = ? AND no_batch LIKE ? ORDER BY no_batch DESC LIMIT 1");
            $stmtCekSub->execute([$idVariasi, $noBatchAsal . '.%']);
            $lastSub = $stmtCekSub->fetchColumn();
            
            $subBatchSuffix = '.A';
            if ($lastSub) {
                $parts = explode('.', $lastSub);
                $lastChar = end($parts);
                if (strlen($lastChar) === 1 && ctype_alpha($lastChar)) {
                    $subBatchSuffix = '.' . chr(ord($lastChar) + 1);
                } else {
                    $subBatchSuffix = '.' . uniqid();
                }
            }
            
            $newBatchName = $noBatchAsal . $subBatchSuffix;
            
            // Insert or Update new Sub Batch
            $stmtNew = $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE stok_sisa = stok_sisa + ?");
            $stmtNew->execute([$idVariasi, $newBatchName, $batchAsal['tgl_exp'], $qtyPcs, $qtyPcs]);
            
            // Get ID of new batch for printing
            $stmtGetNew = $pdo->prepare("SELECT id FROM stok_batch WHERE id_variasi = ? AND no_batch = ?");
            $stmtGetNew->execute([$idVariasi, $newBatchName]);
            $newBatchId = $stmtGetNew->fetchColumn();
            
            // Catat di kartu stok
            $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();
            $catatan = "Pemecahan $qtyBox " . $var['satuan_besar'] . " menjadi $qtyPcs " . $var['satuan_kecil'] . " (Dari Batch $noBatchAsal menjadi Sub-Batch $newBatchName)";
            
            $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, 'Penyesuaian', 'Manual', 'Koreksi Manual', 0, ?, ?, ?)");
            $stmtKartu->execute([$idVariasi, $sisaStok, $catatan, $userId]);
            
            $pdo->commit();
            $msg = "Berhasil memecah box! Sub-Batch baru $newBatchName telah dibuat dengan stok $qtyPcs " . $var['satuan_kecil'] . ".";
            $msgType = 'success';
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal melakukan pemecahan: ' . $e->getMessage();
            $msgType = 'error';
        }
    }
}

// Fetch Obat dengan rasio > 1
$produkList = $pdo->query("
    SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pv.is_active = 1 AND pi.kategori = 'Obat' AND pv.rasio_konversi > 1
    ORDER BY pi.nama_produk ASC
")->fetchAll();

layoutHead('Konversi UOM (Pemecahan Box)');
layoutBodyOpen();
layoutSidebar('konversi_uom');
layoutHeader('Konversi UOM', 'Pemecahan Box Kardus Utuh ke Satuan Eceran (Sub-Batch)');
?>

<?php if ($msg): ?>
<div class="mb-5 flex flex-col sm:flex-row items-center gap-3 p-4 rounded-xl text-sm font-medium border <?= $msgType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
    <div class="flex items-center gap-3 flex-1">
        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="<?= $msgType === 'error' ? 'M12 8v4m0 4h.01' : 'M9 12l2 2 4-4' ?>"/></svg>
        <span><?= htmlspecialchars($msg) ?></span>
    </div>
    <?php if ($msgType === 'success' && $newBatchId): ?>
        <button type="button" onclick="window.open('cetak_stiker.php?tipe=batch&id=<?= $newBatchId ?>', '_blank')" class="px-4 py-2 bg-white text-emerald-700 border border-emerald-300 rounded-lg shadow-sm hover:bg-emerald-100 transition flex items-center gap-2 font-bold whitespace-nowrap">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Stiker Sub-Batch
        </button>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">
        <div class="mb-6 pb-4 border-b border-zcBrd">
            <h3 class="text-lg font-extrabold text-zcTxt mb-1">Form Pemecahan Box (Konversi UOM)</h3>
            <p class="text-xs text-zcMut">Fitur ini digunakan saat Anda membuka segel kardus/box utuh (Batch Induk) untuk diecer menjadi satuan kecil (Sub-Batch) untuk di-display di etalase / apotek.</p>
        </div>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">Pilih Obat (Dengan Multi-UOM) *</label>
                <select name="id_variasi" id="id_variasi" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <option value="">-- Pilih Obat --</option>
                    <?php foreach ($produkList as $p): ?>
                        <option value="<?= $p['id'] ?>" data-satbesar="<?= htmlspecialchars($p['satuan_besar']) ?>" data-satkecil="<?= htmlspecialchars($p['satuan_kecil']) ?>" data-rasio="<?= $p['rasio_konversi'] ?>">
                            <?= htmlspecialchars($p['label']) ?> (1 <?= htmlspecialchars($p['satuan_besar']) ?> = <?= $p['rasio_konversi'] ?> <?= htmlspecialchars($p['satuan_kecil']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="batch_container" class="hidden">
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">Pilih Batch Asal (Kardus Utuh) *</label>
                <select name="no_batch_asal" id="no_batch_asal" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-white">
                    <option value="">-- Memuat Batch... --</option>
                </select>
            </div>
            
            <div id="qty_container" class="hidden">
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">Jumlah Box yang Akan Dipecah *</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="qty_box" id="qty_box" required min="1" placeholder="Contoh: 1" class="w-32 text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <span id="satuan_besar_lbl" class="text-sm font-bold text-zcTxt bg-slate-100 px-3 py-2 rounded-lg border border-slate-200">Box</span>
                    <span class="text-zcMut mx-1">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                    <input type="text" id="hasil_konversi" readonly class="w-32 text-sm border border-transparent bg-emerald-50 text-emerald-700 font-bold rounded-xl px-3.5 py-2.5" placeholder="0">
                    <span id="satuan_kecil_lbl" class="text-sm font-bold text-emerald-700">Pcs</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-2 italic">* Sistem otomatis akan mengurangi stok dari Batch asal dan membuat Sub-Batch baru (akhiran .A / .B dst) dengan isi satuan kecil.</p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-[.98]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span>Proses Pemecahan (Mutasi Konversi)</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selVariasi = document.getElementById('id_variasi');
    const batchContainer = document.getElementById('batch_container');
    const selBatch = document.getElementById('no_batch_asal');
    const qtyContainer = document.getElementById('qty_container');
    const inpQtyBox = document.getElementById('qty_box');
    const lblBesar = document.getElementById('satuan_besar_lbl');
    const lblKecil = document.getElementById('satuan_kecil_lbl');
    const outHasil = document.getElementById('hasil_konversi');
    
    let currentRasio = 1;

    selVariasi.addEventListener('change', function() {
        const val = this.value;
        const opt = this.options[this.selectedIndex];
        
        if (!val) {
            batchContainer.classList.add('hidden');
            qtyContainer.classList.add('hidden');
            selBatch.required = false;
            return;
        }
        
        const satBesar = opt.getAttribute('data-satbesar');
        const satKecil = opt.getAttribute('data-satkecil');
        currentRasio = parseInt(opt.getAttribute('data-rasio')) || 1;
        
        lblBesar.textContent = satBesar;
        lblKecil.textContent = satKecil;
        inpQtyBox.value = '';
        outHasil.value = '';
        
        // Fetch Batches
        batchContainer.classList.remove('hidden');
        qtyContainer.classList.remove('hidden');
        selBatch.required = true;
        selBatch.innerHTML = '<option value="">-- Memuat Batch... --</option>';
        
        fetch(`../api/get_batch_sn.php?id_variasi=${val}`)
            .then(res => res.json())
            .then(data => {
                if (data.error || !data.items || data.items.length === 0) {
                    selBatch.innerHTML = '<option value="">(Tidak ada Batch Aktif)</option>';
                    return;
                }
                
                let html = '<option value="">-- Pilih Batch Asal --</option>';
                data.items.forEach(i => {
                    // Hanya tampilkan batch utama atau jika stoknya cukup untuk dipecah (minimal rasio)
                    if (i.stok_sisa >= currentRasio) {
                        const boxSisa = Math.floor(i.stok_sisa / currentRasio);
                        html += `<option value="${i.no_batch}">Batch: ${i.no_batch} (Isi Tersedia: setara ${boxSisa} ${satBesar} / ${i.stok_sisa} ${satKecil}) | Exp: ${i.tgl_exp}</option>`;
                    }
                });
                selBatch.innerHTML = html;
            });
    });
    
    inpQtyBox.addEventListener('input', function() {
        const val = parseInt(this.value) || 0;
        outHasil.value = val * currentRasio;
    });
});
</script>

<?php layoutFooter(); ?>
