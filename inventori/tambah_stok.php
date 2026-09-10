<?php
// File: inventori/tambah_stok.php
// Modul Penerimaan Barang (Masuk dari Supplier) - ZenCare Medical
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = '';
$userId = $_SESSION['user_id'];
$idCabang = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sumber      = $_POST['sumber'] ?? 'Pembelian Langsung';
    $noReferensi = trim($_POST['no_referensi'] ?? '');
    $idSupplier  = intval($_POST['id_supplier'] ?? 0) ?: null;
    $tglTerima   = $_POST['tanggal_terima'] ?? date('Y-m-d');
    $catatan     = trim($_POST['catatan'] ?? '');
    
    // Auto-generate No Referensi jika kosong & Pembelian Langsung
    if ($sumber === 'Pembelian Langsung' && empty($noReferensi)) {
        $prefix = 'KLK-' . date('ymd') . '-';
        $stmtSeq = $pdo->query("SELECT COUNT(*) FROM penerimaan_stok WHERE no_referensi LIKE '$prefix%'");
        $count = $stmtSeq->fetchColumn() + 1;
        $noReferensi = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
    
    // Handle Upload File Nota (Opsional)
    $fileNotaName = null;
    if (isset($_FILES['file_nota']) && $_FILES['file_nota']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['file_nota']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['file_nota']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','pdf'];
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../uploads/nota/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fileNotaName = 'NOTA_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($tmpName, $uploadDir . $fileNotaName);
        }
    }

    $idVariasis = $_POST['id_variasi'] ?? [];
    $qtys       = $_POST['qty'] ?? [];
    $batchNos   = $_POST['no_batch'] ?? [];
    $tglExps    = $_POST['tgl_exp'] ?? [];
    $snList     = $_POST['serial_number'] ?? [];

    if (empty($idVariasis) || !$noReferensi) {
        $msg = "No Referensi dan minimal 1 produk wajib diisi."; $msgType = 'error';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Insert Header Penerimaan
            $stmtHeader = $pdo->prepare("INSERT INTO penerimaan_stok (no_referensi, sumber, id_supplier, tanggal_terima, catatan, file_nota, dibuat_oleh) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtHeader->execute([$noReferensi, $sumber, $idSupplier, $tglTerima, $catatan, $fileNotaName, $userId]);
            $idPenerimaan = $pdo->lastInsertId();

            $stmtDetail = $pdo->prepare("INSERT INTO penerimaan_detail (id_penerimaan, id_variasi, qty_terima, no_batch, tgl_exp) VALUES (?, ?, ?, ?, ?)");
            $stmtStok   = $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ? AND 1=1");
            $stmtBatch  = $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE stok_sisa = stok_sisa + ?");
            $stmtSN     = $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number) VALUES (?, ?)");
            $stmtKartu  = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, 'Masuk', 'Penerimaan', 'Penerimaan Barang', ?, ?, ?, ?, ?)");

            foreach ($idVariasis as $i => $idV) {
                $qty = intval($qtys[$i] ?? 0);
                if ($idV && $qty > 0) {
                    $batch = trim($batchNos[$i] ?? '') ?: null;
                    $exp   = trim($tglExps[$i] ?? '') ?: null;
                    
                    // Insert Detail
                    $stmtDetail->execute([$idPenerimaan, $idV, $qty, $batch, $exp]);
                    
                    // Update Stok Fisik
                    $stmtStok->execute([$qty, $idV]);
                    
                    // Jika Obat (Ada Batch & Exp)
                    if ($batch && $exp) {
                        $stmtBatch->execute([$idV, $batch, $exp, $qty, $qty]);
                    }
                    
                    // Jika Alkes (Ada Serial Number dipisah koma)
                    $snStr = trim($snList[$i] ?? '');
                    if ($snStr) {
                        $snArr = array_filter(array_map('trim', explode(',', $snStr)));
                        foreach ($snArr as $sn) {
                            $stmtSN->execute([$idV, $sn]);
                        }
                    }

                    // Log Kartu Stok
                    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idV AND 1=1")->fetchColumn();
                    $deskripsi = "Penerimaan Barang [$sumber] - Ref: $noReferensi";
                    $stmtKartu->execute([$idV, $idPenerimaan, $qty, $sisaStok, $deskripsi, $userId]);
                }
            }

            $pdo->commit();
            $msg = "Penerimaan Barang (Ref: $noReferensi) berhasil disimpan."; $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal menyimpan penerimaan: ' . $e->getMessage(); $msgType = 'error';
        }
    }
}

$supplierList = $pdo->query("SELECT id, nama FROM supplier WHERE is_active = 1 ORDER BY nama")->fetchAll();
$produkList   = $pdo->query("
    SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label, pi.kategori
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pv.is_active = 1 ORDER BY pi.nama_produk
")->fetchAll();

layoutHead('Penerimaan Barang');
layoutBodyOpen();
layoutSidebar('tambah_stok');
layoutHeader('Penerimaan Barang', 'Catat barang masuk fisik ke dalam sistem dari Supplier / PO');
?>

<?php if ($msg): ?>
<div class="mb-5 flex items-center gap-3 p-3.5 rounded-xl text-sm font-medium border <?= $msgType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
    <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>

<div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- Header Info -->
        <div>
            <h3 class="text-sm font-bold text-zcTxt mb-4 border-b border-zcBrd pb-2">Informasi Dokumen Penerimaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sumber *</label>
                    <select name="sumber" id="sumber_select" required class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="PO">Purchase Order (PO)</option>
                        <option value="Pembelian Langsung">Pembelian Langsung</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5" id="label_referensi">No. Referensi (PO/Nota) *</label>
                    <input type="text" name="no_referensi" id="inp_referensi" placeholder="INV/PO/..." 
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                    <div id="hint_referensi" class="hidden text-[10px] text-zcMut mt-1 leading-tight">Kosongkan jika ingin digenerate otomatis oleh sistem (KLK-YYMMDD-XXX).</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Supplier (Opsional)</label>
                    <select name="id_supplier" class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                        <option value="">Umum / Tidak Terdaftar</option>
                        <?php foreach($supplierList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Tanggal Terima *</label>
                    <input type="date" name="tanggal_terima" required value="<?= date('Y-m-d') ?>"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Upload Bukti Nota Fisik (Opsional)</label>
                    <input type="file" name="file_nota" accept=".jpg,.jpeg,.png,.pdf" 
                        class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc">
                    <div class="text-[10px] text-zcMut mt-1">Format: JPG, PNG, PDF (Maks. 2MB)</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Keterangan / Catatan Tambahan</label>
                    <input type="text" name="catatan" placeholder="Kondisi barang saat diterima..." 
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc">
                </div>
            </div>
        </div>

        <!-- Detail Produk -->
        <div>
            <div class="flex items-center justify-between border-b border-zcBrd pb-2 mb-4">
                <h3 class="text-sm font-bold text-zcTxt">Daftar Barang Masuk Fisik</h3>
                <button type="button" onclick="addRow()" class="px-3 py-1.5 bg-zcLt text-zc rounded-lg text-xs font-bold hover:bg-blue-100 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Baris
                </button>
            </div>
            
            <div class="border border-zcBrd rounded-xl overflow-x-auto">
                <table class="w-full text-xs min-w-[800px]">
                    <thead class="bg-slate-50 border-b border-zcBrd">
                        <tr>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[25%]">Produk *</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[10%]">Qty Fisik *</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[15%]">No. Batch (Obat)</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[15%]">Tgl Exp (Obat)</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[30%]">Serial Number (Alkes)</th>
                            <th class="px-2 py-2.5 w-[5%]"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr id="row-0" class="border-b border-zcBrd/50">
                            <td class="px-2 py-2 align-top">
                                <select name="id_variasi[]" required onchange="checkProduk(this, 0)" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($produkList as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-kat="<?= htmlspecialchars($p['kategori']) ?>"><?= htmlspecialchars($p['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="px-2 py-2 align-top"><input type="number" name="qty[]" min="1" required class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs"></td>
                            <td class="px-2 py-2 align-top"><input type="text" name="no_batch[]" id="b-0" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="Wajib untuk obat"></td>
                            <td class="px-2 py-2 align-top"><input type="date" name="tgl_exp[]" id="e-0" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly></td>
                            <td class="px-2 py-2 align-top"><textarea name="serial_number[]" id="s-0" rows="1" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="SN dipisah koma (Alkes)"></textarea></td>
                            <td class="px-2 py-2 align-top text-center"><button type="button" onclick="removeRow(0)" class="text-slate-300 hover:text-red-500 font-bold text-lg leading-none">&times;</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-[10px] text-zcMut flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                Pilih produk terlebih dahulu untuk mengaktifkan kolom Batch/Exp (khusus Obat) atau Serial Number (khusus Alkes).
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-8 py-3 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-md shadow-blue-500/20 active:scale-95 transition flex items-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan & Update Stok
            </button>
        </div>
    </form>
</div>

<script>
let rC = 1;
const optStr = `<?php foreach ($produkList as $p): ?><option value="<?= $p['id'] ?>" data-kat="<?= htmlspecialchars($p['kategori']) ?>"><?= htmlspecialchars(addslashes($p['label'])) ?></option><?php endforeach; ?>`;

document.getElementById('sumber_select').addEventListener('change', function() {
    const inp = document.getElementById('inp_referensi');
    const lbl = document.getElementById('label_referensi');
    const hint = document.getElementById('hint_referensi');
    if (this.value === 'Pembelian Langsung') {
        inp.required = false;
        lbl.innerText = 'No. Referensi (PO/Nota) (Opsional)';
        hint.classList.remove('hidden');
    } else {
        inp.required = true;
        lbl.innerText = 'No. Referensi (PO/Nota) *';
        hint.classList.add('hidden');
    }
});
document.getElementById('sumber_select').dispatchEvent(new Event('change'));

function addRow() {
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    tr.id = 'row-' + rC;
    tr.className = 'border-b border-zcBrd/50';
    tr.innerHTML = `
        <td class="px-2 py-2 align-top">
            <select name="id_variasi[]" required onchange="checkProduk(this, ${rC})" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs">
                <option value="">-- Pilih --</option>${optStr}
            </select>
        </td>
        <td class="px-2 py-2 align-top"><input type="number" name="qty[]" min="1" required class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs"></td>
        <td class="px-2 py-2 align-top"><input type="text" name="no_batch[]" id="b-${rC}" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly></td>
        <td class="px-2 py-2 align-top"><input type="date" name="tgl_exp[]" id="e-${rC}" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly></td>
        <td class="px-2 py-2 align-top"><textarea name="serial_number[]" id="s-${rC}" rows="1" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly></textarea></td>
        <td class="px-2 py-2 align-top text-center"><button type="button" onclick="removeRow(${rC})" class="text-slate-300 hover:text-red-500 font-bold text-lg leading-none">&times;</button></td>
    `;
    tbody.appendChild(tr);
    rC++;
}

function removeRow(n) {
    const row = document.getElementById('row-' + n);
    if (row) row.remove();
}

function checkProduk(sel, i) {
    const opt = sel.options[sel.selectedIndex];
    const kat = opt ? opt.getAttribute('data-kat') : '';
    const b = document.getElementById('b-'+i);
    const e = document.getElementById('e-'+i);
    const s = document.getElementById('s-'+i);
    
    // Reset state
    [b,e,s].forEach(el => { el.readOnly = true; el.classList.add('bg-slate-100'); el.required = false; });
    
    if (kat === 'Obat') {
        b.readOnly = false; b.classList.remove('bg-slate-100'); b.required = true;
        e.readOnly = false; e.classList.remove('bg-slate-100'); e.required = true;
    } else if (kat === 'Alat Kesehatan') {
        s.readOnly = false; s.classList.remove('bg-slate-100'); s.required = true;
    }
}
</script>

<?php layoutFooter(); ?>
