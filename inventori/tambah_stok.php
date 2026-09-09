<?php
// File: inventori/tambah_stok.php
// Modul Tambah / Penerimaan Stok (Input Masuk dari Supplier / Opname)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'karyawan']);

$msg = ''; $msgType = '';
$isAdmin = ($_SESSION['role'] === 'super_admin');
$userCabang = intval($_SESSION['id_cabang'] ?? 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVariasi  = intval($_POST['id_variasi'] ?? 0);
    $idCabang   = $isAdmin ? intval($_POST['id_cabang'] ?? 0) : $userCabang;
    $qty        = intval($_POST['qty'] ?? 0);
    $jenis      = in_array($_POST['jenis'] ?? '', ['Masuk','Opname']) ? ($_POST['jenis'] ?? 'Masuk') : 'Masuk';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (!$idVariasi || !$idCabang || $qty <= 0) {
        $msg = "Semua field wajib diisi dan qty harus > 0!"; $msgType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $stmtK = $pdo->prepare("SELECT pi.kategori FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.id = ?");
            $stmtK->execute([$idVariasi]);
            $kategori = $stmtK->fetchColumn();

            $noBatch = trim($_POST['no_batch'] ?? '');
            $tglExp = trim($_POST['tgl_exp'] ?? '');
            $snRaw = trim($_POST['serial_number'] ?? '');

            if ($kategori === 'Obat') {
                if (empty($noBatch) || empty($tglExp)) throw new Exception("No Batch dan Tgl Exp wajib diisi untuk produk Obat.");
                // Check if batch exists
                $chkBatch = $pdo->prepare("SELECT id FROM stok_batch WHERE id_variasi=? AND id_cabang=? AND no_batch=? AND tgl_exp=?");
                $chkBatch->execute([$idVariasi, $idCabang, $noBatch, $tglExp]);
                $bId = $chkBatch->fetchColumn();
                if ($bId) {
                    $pdo->prepare("UPDATE stok_batch SET stok = stok + ? WHERE id=?")->execute([$qty, $bId]);
                } else {
                    $pdo->prepare("INSERT INTO stok_batch (id_cabang, id_variasi, no_batch, tgl_exp, stok) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$idCabang, $idVariasi, $noBatch, $tglExp, $qty]);
                }
            } elseif ($kategori === 'Alat Kesehatan') {
                if (empty($snRaw)) throw new Exception("Serial Number wajib diisi untuk Alat Kesehatan.");
                $snList = preg_split('/[\n,]+/', $snRaw, -1, PREG_SPLIT_NO_EMPTY);
                $snList = array_map('trim', $snList);
                if (count($snList) !== $qty) throw new Exception("Jumlah SN (" . count($snList) . ") tidak sesuai dengan Qty yang diinput ($qty).");
                
                $stmtSN = $pdo->prepare("INSERT INTO unit_serial (id_cabang, id_variasi, serial_number, status) VALUES (?, ?, ?, 'Tersedia')");
                foreach ($snList as $sn) {
                    // Check duplicate
                    $chkSN = $pdo->prepare("SELECT id FROM unit_serial WHERE serial_number=?");
                    $chkSN->execute([$sn]);
                    if ($chkSN->fetchColumn()) throw new Exception("Serial Number '$sn' sudah terdaftar di sistem!");
                    $stmtSN->execute([$idCabang, $idVariasi, $sn]);
                }
            }

            // Check if stock row exists
            $chk = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=? FOR UPDATE");
            $chk->execute([$idVariasi, $idCabang]);
            $stokSkrg = $chk->fetchColumn();

            if ($stokSkrg === false) {
                // Insert new row
                $pdo->prepare("INSERT INTO stok_cabang (id_variasi,id_cabang,stok) VALUES (?,?,?)")
                    ->execute([$idVariasi, $idCabang, $qty]);
                $sisaStok = $qty;
            } else {
                $pdo->prepare("UPDATE stok_cabang SET stok = stok + ? WHERE id_variasi=? AND id_cabang=?")
                    ->execute([$qty, $idVariasi, $idCabang]);
                $sisaStok = intval($stokSkrg) + $qty;
            }

            // Kartu Stok entry
            $ketFinal = $keterangan ?: ($jenis === 'Opname' ? 'Stock Opname / Koreksi Stok' : 'Penerimaan Stok (PO / Pembelian Langsung)');
            $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,?,?,?,?)")
                ->execute([$idCabang, $idVariasi, $jenis, $qty, $sisaStok, $ketFinal]);

            $pdo->commit();
            $msg = "Berhasil menambahkan $qty unit stok fisik. Sisa stok sekarang: <strong>$sisaStok unit</strong>.";
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal memproses penambahan stok: " . $e->getMessage(); $msgType = 'error';
        }
    }
}

$cabangList = $pdo->query("SELECT * FROM cabang WHERE is_active=1 ORDER BY id ASC")->fetchAll();
$produkList = $pdo->query("
    SELECT v.id, CONCAT(i.nama_produk,' – ',v.nama_variasi,' (',v.satuan_kecil,')') AS label, 
           v.sku_variasi, v.satuan_kecil, v.satuan_besar, v.rasio_konversi, i.kategori
    FROM produk_variasi v 
    JOIN produk_induk i ON v.id_produk_induk=i.id
    WHERE v.is_active=1 AND i.is_active=1 ORDER BY i.nama_produk ASC")->fetchAll();

// Recent additions log
$logRecent = $pdo->prepare("
    SELECT ks.tanggal, ks.qty, ks.sisa_stok, ks.keterangan, ks.jenis_mutasi,
           CONCAT(pi.nama_produk,' – ',pv.nama_variasi) AS nama_item,
           c.nama AS nama_cabang
    FROM kartu_stok ks
    JOIN produk_variasi pv ON ks.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    JOIN cabang c ON ks.id_cabang = c.id
    WHERE ks.jenis_mutasi IN ('Masuk','Opname')
    " . (!$isAdmin ? "AND ks.id_cabang = $userCabang" : "") . "
    ORDER BY ks.tanggal DESC LIMIT 15
");
$logRecent->execute();
$recentLogs = $logRecent->fetchAll();

layoutHead('Tambah Stok (Penerimaan)');
layoutBodyOpen();
layoutSidebar('tambah_stok');
layoutHeader('Tambah / Penerimaan Stok', 'Input stok masuk dari supplier atau koreksi opname per cabang');
?>

<?php if ($msg): ?>
<div class="mb-5 p-4 rounded-xl border text-xs font-semibold <?= $msgType==='success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
    <?= $msg ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    <!-- Form Input -->
    <div class="lg:col-span-2 bg-white border border-zcBrd rounded-2xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-zcTxt mb-5 flex items-center gap-2">
            <?= icon('download', 'w-4 h-4 text-emerald-600') ?>
            Form Penerimaan Stok
        </h2>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Produk / Variasi *</label>
                <select name="id_variasi" required class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-white focus:outline-none focus:border-zc" onchange="updateSatuan(this)">
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($produkList as $p): ?>
                    <option value="<?= $p['id'] ?>" data-kategori="<?= htmlspecialchars($p['kategori']) ?>" data-sku="<?= htmlspecialchars($p['sku_variasi']) ?>" data-kecil="<?= htmlspecialchars($p['satuan_kecil']) ?>" data-besar="<?= htmlspecialchars($p['satuan_besar']) ?>" data-rasio="<?= intval($p['rasio_konversi']) ?: 1 ?>">
                        <?= htmlspecialchars($p['label']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SKU & Satuan Info -->
            <div id="produk_info" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs space-y-1">
                <div class="flex justify-between"><span class="text-zcMut">SKU:</span><strong id="info_sku">-</strong></div>
                <div class="flex justify-between"><span class="text-zcMut">Satuan Kecil:</span><strong id="info_kecil">-</strong></div>
                <div class="flex justify-between"><span class="text-zcMut">Satuan Besar:</span><strong id="info_besar">-</strong></div>
                <div class="flex justify-between"><span class="text-zcMut">Rasio Konversi:</span><strong id="info_rasio">-</strong></div>
                <div class="mt-2 pt-2 border-t border-blue-200 text-xs text-blue-800 font-medium">
                    Catatan: Input kuantitas selalu dalam <strong>satuan terkecil</strong> (Pcs/Strip/Unit). Contoh: 1 Box = 100 Pcs, input 100.
                </div>
            </div>

            <?php if ($isAdmin): ?>
            <div>
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">Cabang Tujuan *</label>
                <select name="id_cabang" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 bg-white focus:outline-none focus:border-zc">
                    <option value="">-- Pilih Cabang --</option>
                    <?php foreach ($cabangList as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <div>
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">Cabang</label>
                <div class="text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 bg-slate-50 text-zcTxt font-medium">
                    <?php $c = array_filter($cabangList, fn($c) => $c['id'] == $userCabang); echo htmlspecialchars(reset($c)['nama'] ?? 'Cabang Anda'); ?>
                </div>
            </div>
            <?php endif; ?>

            <div>
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">Jenis Mutasi *</label>
                <select name="jenis" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 bg-white focus:outline-none focus:border-zc">
                    <option value="Masuk">Pembelian Langsung / Purchase Order (PO)</option>
                    <option value="Opname">Opname – Koreksi Stok Fisik</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">Jumlah (Satuan Terkecil / Pcs) *</label>
                <input type="number" name="qty" min="1" required placeholder="Contoh: 100 (untuk 1 Box isi 100)" class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc">
                <p class="text-xs text-zcMut mt-1">Input selalu dalam satuan terkecil (Pcs). Sistem menyimpan stok fisik dalam satuan terkecil.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-zcTxt mb-1.5">No. Dokumen / Keterangan (PO/Faktur)</label>
                <input type="text" name="keterangan" placeholder="Contoh: PO-001 dari PT. Kimia Farma" class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc">
            </div>

            <!-- Dynamic Fields -->
            <div id="field_obat" class="hidden space-y-3 p-4 bg-amber-50 rounded-xl border border-amber-200">
                <p class="text-xs font-bold text-amber-800 border-b border-amber-200 pb-2 mb-2">📋 Atribut Obat (FEFO)</p>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1">No Batch *</label>
                    <input type="text" name="no_batch" id="inp_no_batch" placeholder="Maks 20 Karakter" class="w-full text-xs border border-amber-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1">Tanggal Expired *</label>
                    <input type="date" name="tgl_exp" id="inp_tgl_exp" class="w-full text-xs border border-amber-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-amber-400">
                </div>
            </div>

            <div id="field_alkes" class="hidden space-y-3 p-4 bg-indigo-50 rounded-xl border border-indigo-200">
                <p class="text-xs font-bold text-indigo-800 border-b border-indigo-200 pb-2 mb-2">🏷️ Serial Number Alat Kesehatan</p>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1">Daftar Serial Number *</label>
                    <textarea name="serial_number" id="inp_sn" rows="3" placeholder="Masukkan SN dipisah dengan koma atau baris baru..." class="w-full text-xs border border-indigo-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-indigo-400"></textarea>
                    <p class="text-[10px] text-indigo-700 mt-1">Jumlah SN yang diinput harus sama persis dengan Qty stok masuk.</p>
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-4 text-sm font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                <?= icon('download', 'w-4 h-4') ?>
                Simpan Penerimaan Stok
            </button>
        </form>
    </div>

    <!-- Log Terbaru -->
    <div class="lg:col-span-3 bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-zcBrd flex items-center gap-2">
            <?= icon('kartu_stok', 'w-4 h-4 text-emerald-600') ?>
            <h2 class="text-sm font-bold text-zcTxt">Log Penerimaan Stok Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left">Cabang</th>
                        <th class="px-4 py-3 text-right text-emerald-700">+Qty</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zcBrd/60">
                    <?php if (empty($recentLogs)): ?>
                    <tr><td colspan="5" class="px-4 py-10 text-center text-zcMut italic">Belum ada data penerimaan stok.</td></tr>
                    <?php else: ?>
                    <?php foreach ($recentLogs as $log): ?>
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-3 font-mono text-zcMut text-[10px]"><?= date('d/m/y H:i', strtotime($log['tanggal'])) ?></td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-zcTxt block leading-snug"><?= htmlspecialchars($log['nama_item']) ?></span>
                            <span class="text-[10px] text-zcMut"><?= htmlspecialchars($log['keterangan'] ?? '') ?></span>
                        </td>
                        <td class="px-4 py-3 text-zcMut"><?= htmlspecialchars($log['nama_cabang']) ?></td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">+<?= number_format($log['qty']) ?></td>
                        <td class="px-4 py-3 text-right font-bold text-zcTxt"><?= number_format($log['sisa_stok']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function updateSatuan(sel) {
    const opt = sel.options[sel.selectedIndex];
    
    // Hide Dynamic Fields
    document.getElementById('field_obat').classList.add('hidden');
    document.getElementById('field_alkes').classList.add('hidden');
    document.getElementById('inp_no_batch').required = false;
    document.getElementById('inp_tgl_exp').required = false;
    document.getElementById('inp_sn').required = false;

    if (!opt.value) { 
        document.getElementById('produk_info').classList.add('hidden'); 
        return; 
    }
    
    document.getElementById('produk_info').classList.remove('hidden');
    document.getElementById('info_sku').innerText   = opt.dataset.sku || '-';
    document.getElementById('info_kecil').innerText = opt.dataset.kecil || '-';
    document.getElementById('info_besar').innerText = opt.dataset.besar || '-';
    document.getElementById('info_rasio').innerText = '1 ' + (opt.dataset.besar||'Box') + ' = ' + (opt.dataset.rasio||'1') + ' ' + (opt.dataset.kecil||'Pcs');

    // Show Dynamic Fields based on Kategori
    if (opt.dataset.kategori === 'Obat') {
        document.getElementById('field_obat').classList.remove('hidden');
        document.getElementById('inp_no_batch').required = true;
        document.getElementById('inp_tgl_exp').required = true;
    } else if (opt.dataset.kategori === 'Alat Kesehatan') {
        document.getElementById('field_alkes').classList.remove('hidden');
        document.getElementById('inp_sn').required = true;
    }
}
</script>

<?php layoutEnd(); ?>
