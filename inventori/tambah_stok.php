<?php
// File: inventori/tambah_stok.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = '';
$userId = $_SESSION['user_id'];
$idCabang = 1;

// Range tanggal penerimaan yang diizinkan (maksimal 30 hari ke belakang dan 30 hari ke depan)
$minTglTerima = date('Y-m-d', strtotime('-30 days'));
$maxTglTerima = date('Y-m-d', strtotime('+30 days'));

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
    $hargaBelis = $_POST['harga_beli'] ?? [];
    $batchNos   = $_POST['no_batch'] ?? [];
    $tglExps    = $_POST['tgl_exp'] ?? [];
    $snList     = $_POST['serial_number'] ?? [];

    if (empty($idVariasis) || !$noReferensi) {
        $msg = "No Referensi dan minimal 1 produk wajib diisi."; $msgType = 'error';
    } elseif ($tglTerima < $minTglTerima || $tglTerima > $maxTglTerima) {
        $msg = "Tanggal penerimaan tidak valid. Batas jangkauan tanggal hanya diperbolehkan maksimal 1 bulan ke belakang (" . date('d/m/Y', strtotime($minTglTerima)) . ") hingga 1 bulan ke depan (" . date('d/m/Y', strtotime($maxTglTerima)) . ").";
        $msgType = 'error';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Insert Header Penerimaan
            $stmtHeader = $pdo->prepare("INSERT INTO penerimaan_stok (no_referensi, sumber, id_supplier, tanggal_terima, catatan, file_nota, dibuat_oleh) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtHeader->execute([$noReferensi, $sumber, $idSupplier, $tglTerima, $catatan, $fileNotaName, $userId]);
            $idPenerimaan = $pdo->lastInsertId();

            $stmtDetail = $pdo->prepare("INSERT INTO penerimaan_detail (id_penerimaan, id_variasi, qty_terima, harga_beli, no_batch, tgl_exp) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtStok   = $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ? AND 1=1");
            $stmtBatch  = $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE stok_sisa = stok_sisa + ?");
            $stmtSN     = $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number) VALUES (?, ?)");
            $stmtKartu  = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Masuk', 'Penerimaan', 'Penerimaan Barang', ?, ?, ?, ?, ?)");

            $createdBatches = [];
            $createdSns = [];
            $minExpLimit = date('Y-m-d', strtotime('+90 days'));

            foreach ($idVariasis as $i => $idV) {
                $qty = intval($qtys[$i] ?? 0);
                if ($idV && $qty > 0) {
                    $batch = trim($batchNos[$i] ?? '') ?: null;
                    $exp   = trim($tglExps[$i] ?? '') ?: null;
                    $hargaBeli = floatval(preg_replace('/[^0-9]/', '', (string)($hargaBelis[$i] ?? '0'))) ?: null;
                    
                    $stmtInfo = $pdo->query("SELECT pv.rasio_konversi, pi.kategori, pi.nama_produk, pv.nama_variasi, pv.satuan_besar, pv.satuan_kecil FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.id = $idV")->fetch();
                    $rasio = max(1, (int)$stmtInfo['rasio_konversi']);
                    $kategori = $stmtInfo['kategori'];
                    $namaLengkap = $stmtInfo['nama_produk'] . ' - ' . $stmtInfo['nama_variasi'];
                    $qtyPcs = $qty * $rasio;

                    // Validasi ketat Expired Date untuk Obat (SOP Farmasi: Minimal 90 hari / 3 bulan)
                    if ($kategori === 'Obat') {
                        if (!$exp) {
                            throw new Exception("Produk Obat '$namaLengkap' wajib mencantumkan Tanggal Kedaluwarsa.");
                        }
                        if ($exp < $minExpLimit) {
                            $tglMinFmt = date('d/m/Y', strtotime($minExpLimit));
                            $tglExpFmt = date('d/m/Y', strtotime($exp));
                            throw new Exception("Masa kedaluwarsa Batch '$batch' ($tglExpFmt) ditolak! Standar SOP Logistik Farmasi mewajibkan masa kedaluwarsa minimal 3 bulan (90 hari) ke depan (Minimal tanggal $tglMinFmt). Barang wajib diretur ke PBF/Distributor.");
                        }
                    }
                    
                    // Insert Detail (qty in satuan besar untuk penerimaan_detail)
                    $stmtDetail->execute([$idPenerimaan, $idV, $qty, $hargaBeli, $batch, $exp]);
                    
                    // Update Stok Fisik (qtyPcs untuk stok_toko)
                    $stmtStok->execute([$qtyPcs, $idV]);
                    
                    // Jika Obat (Ada Batch & Exp) - stok_batch menyimpan pcs
                    if ($batch && $exp) {
                        $stmtBatch->execute([$idV, $batch, $exp, $qtyPcs, $qtyPcs]);
                        $batchRow = $pdo->query("SELECT id FROM stok_batch WHERE id_variasi = $idV AND no_batch = " . $pdo->quote($batch))->fetch();
                        if ($batchRow) {
                            $createdBatches[] = [
                                'id' => $batchRow['id'],
                                'batch' => $batch,
                                'nama' => $namaLengkap
                            ];
                        }
                    }
                    
                    // Jika Alkes (Ada Serial Number dipisah koma)
                    $snStr = trim($snList[$i] ?? '');
                    if ($snStr) {
                        $snArr = array_filter(array_map('trim', explode(',', $snStr)));
                        foreach ($snArr as $sn) {
                            $stmtSN->execute([$idV, $sn]);
                            $createdSns[] = [
                                'sn' => $sn,
                                'nama' => $namaLengkap
                            ];
                        }
                    }

                    // Log Kartu Stok dengan nomor referensi dokumen dan nomor batch akurat
                    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idV AND 1=1")->fetchColumn();
                    $identitasKet = $batch ? " [Batch: $batch]" : ($snStr ? " [SN: $snStr]" : "");
                    $deskripsi = "Penerimaan Barang [$sumber] - Ref: $noReferensi$identitasKet";
                    
                    $satuanTipe = ($kategori === 'Alat Kesehatan') ? 'kecil' : 'besar';
                    $qtyKartu   = ($kategori === 'Alat Kesehatan') ? $qtyPcs : $qty;
                    
                    $stmtKartu->execute([$idV, $satuanTipe, $noReferensi, $qtyKartu, $sisaStok, $deskripsi, $userId]);
                }
            }

            $pdo->commit();
            $msg = "Penerimaan Barang (Ref: $noReferensi) berhasil disimpan ke dalam stok sistem."; 
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal menyimpan penerimaan: ' . $e->getMessage(); $msgType = 'error';
        }
    }
}

// Data Produk untuk Form
$supplierList = $pdo->query("SELECT id, nama FROM supplier WHERE is_active = 1 ORDER BY nama")->fetchAll();
$produkList   = $pdo->query("
    SELECT pv.id, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS label, pi.kategori, pv.harga_jual_besar,
           pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pv.is_active = 1 ORDER BY pi.nama_produk, pv.nama_variasi
")->fetchAll();

// Ambil Histori Penerimaan Barang (7 Hari Terakhir)
$historiPenerimaan = $pdo->query("
    SELECT ps.*, u.nama_lengkap AS pembuat, s.nama AS nama_supplier
    FROM penerimaan_stok ps
    LEFT JOIN users u ON u.id = ps.dibuat_oleh
    LEFT JOIN supplier s ON s.id = ps.id_supplier
    WHERE ps.tanggal_terima >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
       OR ps.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY ps.id DESC
")->fetchAll();

$penerimaanIds = array_column($historiPenerimaan, 'id');
$historiDetails = [];
if (!empty($penerimaanIds)) {
    $inIds = implode(',', array_map('intval', $penerimaanIds));
    $stmtDet = $pdo->query("
        SELECT pd.*, pv.nama_variasi, pi.nama_produk, pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
               sb.id AS id_stok_batch
        FROM penerimaan_detail pd
        JOIN produk_variasi pv ON pv.id = pd.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN stok_batch sb ON sb.id_variasi = pd.id_variasi AND sb.no_batch = pd.no_batch
        WHERE pd.id_penerimaan IN ($inIds)
        ORDER BY pd.id ASC
    ");
    while ($row = $stmtDet->fetch()) {
        $historiDetails[$row['id_penerimaan']][] = $row;
    }
}

layoutHead('Penerimaan Barang');
layoutBodyOpen();
layoutSidebar('tambah_stok');
layoutHeader('Penerimaan Barang', 'Catat barang masuk fisik ke dalam sistem dari Supplier / PO');
?>

<?php if ($msg): ?>
<div class="mb-5 p-4 rounded-xl text-sm font-medium border <?= $msgType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
            <span><?= htmlspecialchars($msg) ?></span>
        </div>
        <a href="#histori_section" class="px-3 py-1.5 bg-white border border-emerald-300 text-emerald-700 rounded-lg shadow-sm hover:bg-emerald-100 transition flex items-center gap-1.5 text-xs font-bold shrink-0">
            Lihat Histori & Cetak Stiker ↓
        </a>
    </div>
</div>
<?php endif; ?>

<!-- FORM INPUT PENERIMAAN -->
<div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm mb-8">
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- Header Info -->
        <div>
            <h3 class="text-sm font-bold text-zcTxt mb-4 border-b border-zcBrd pb-2">Informasi Dokumen Penerimaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sumber *</label>
                    <select name="sumber" id="sumber_select" required class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="PO">Purchase Order (PO)</option>
                        <option value="Pembelian Langsung">Pembelian Langsung / Kulakan</option>
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
                    <select name="id_supplier" class="select2 w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                        <option value="">Umum / Tidak Terdaftar</option>
                        <?php foreach($supplierList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Tanggal Terima *</label>
                    <input type="date" name="tanggal_terima" required value="<?= date('Y-m-d') ?>"
                        min="<?= $minTglTerima ?>" max="<?= $maxTglTerima ?>"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-white">
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
                <button type="button" onclick="addRow()" class="px-3 py-1.5 bg-zcLt text-zc rounded-lg text-xs font-bold hover:bg-blue-100 flex items-center gap-1 transition">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Baris
                </button>
            </div>
            
            <div class="border border-zcBrd rounded-xl overflow-x-auto">
                <table class="w-full text-xs min-w-[950px]">
                    <thead class="bg-slate-50 border-b border-zcBrd">
                        <tr>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[22%]">Produk * (Ketik untuk Cari)</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[11%]">Qty (Satuan Besar)*</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[14%]">Nilai Beli Satuan *</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[13%]">Subtotal Fisik PO</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[13%]">No. Batch (Obat)</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[12%]">
                                Tgl Exp (Obat)
                            </th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[13%]">Serial Number (Alkes)</th>
                            <th class="px-2 py-2.5 w-[3%]"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr id="row-0" class="border-b border-zcBrd/50">
                            <td class="px-2 py-2 align-top">
                                <select name="id_variasi[]" id="sel-0" data-row-idx="0" required onchange="checkProduk(this, 0); calcSubtotal(0);" class="select2-produk w-full border border-zcBrd rounded-lg px-2 py-1.5 text-xs">
                                    <option value="">-- Cari &amp; Pilih Produk --</option>
                                    <?php foreach ($produkList as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-kat="<?= htmlspecialchars($p['kategori']) ?>" data-satbesar="<?= htmlspecialchars($p['satuan_besar'] ?? 'Dus') ?>" data-satkecil="<?= htmlspecialchars($p['satuan_kecil'] ?? 'Pcs') ?>" data-rasio="<?= $p['rasio_konversi'] ?>" data-hargajual="<?= $p['harga_jual_besar'] ?>"><?= htmlspecialchars($p['label']) ?> (<?= htmlspecialchars($p['satuan_besar'] ?? 'Dus') ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="px-2 py-2 align-top">
                                <div class="flex items-center gap-1">
                                    <input type="number" name="qty[]" id="qty-0" min="1" required placeholder="Jml" oninput="calcSubtotal(0)" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
                                    <span id="badge-uom-0" class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-1 rounded whitespace-nowrap border border-slate-200">Dus</span>
                                </div>
                            </td>
                            <td class="px-2 py-2 align-top">
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1.5 text-xs font-bold text-slate-400">Rp</span>
                                    <input type="text" inputmode="numeric" name="harga_beli[]" id="harga-0" placeholder="0" oninput="formatRupiahInput(this); calcSubtotal(0);" 
                                        class="w-full pl-8 border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
                                </div>
                                <div class="text-[9px] text-zcMut mt-0.5">Per 1 Satuan Besar</div>
                            </td>
                            <td class="px-2 py-2 align-top">
                                <div id="subtotal-0" class="text-xs font-bold text-slate-800 pt-1.5 whitespace-nowrap">Rp 0</div>
                                <div class="text-[9px] text-zcMut">Subtotal Fisik PO</div>
                            </td>
                            <td class="px-2 py-2 align-top"><input type="text" name="no_batch[]" id="b-0" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="Wajib untuk obat"></td>
                            <td class="px-2 py-2 align-top"><input type="date" name="tgl_exp[]" id="e-0" min="<?= date('Y-m-d', strtotime('+90 days')) ?>" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly></td>
                            <td class="px-2 py-2 align-top"><textarea name="serial_number[]" id="s-0" rows="1" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="SN dipisah koma"></textarea></td>
                            <td class="px-2 py-2 align-top text-center"><button type="button" onclick="removeRow(0)" class="text-slate-300 hover:text-red-500 font-bold text-lg leading-none">&times;</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2.5 text-[11px] text-zcMut flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <span class="flex items-center gap-1.5 text-slate-600">
                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Pilih produk terlebih dahulu untuk mengaktifkan kolom Batch/Exp (khusus Obat) atau Serial Number (khusus Alkes). Qty diinput dalam <strong>Satuan Besar (Dus/Karton/Box)</strong>.
                </span>
                <span class="flex items-center gap-1.5 text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2.5 py-1 font-semibold text-[10.5px]">
                    💡 SOP Logistik: Sistem menolak obat dengan masa kedaluwarsa kurang dari 3 Bulan (&lt; 90 Hari). Barang wajib diretur ke PBF.
                </span>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-8 py-3 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-md shadow-blue-500/20 active:scale-95 transition flex items-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan &amp; Update Stok
            </button>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- SECTION HISTORI PENERIMAAN BARANG & CETAK STIKER (7 HARI TERAKHIR)         -->
<!-- ========================================================================= -->
<div id="histori_section" class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-zcBrd pb-4 mb-6">
        <div>
            <h3 class="text-base font-extrabold text-zcTxt flex items-center gap-2">
                <svg class="w-5 h-5 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Histori Penerimaan Barang &amp; Cetak Stiker (7 Hari Terakhir)
            </h3>
            <p class="text-xs text-zcMut mt-0.5">Daftar dokumen PO/Kulakan barang masuk fisik lengkap dengan rincian batch, tgl exp, dan tombol cetak barcode fisik.</p>
        </div>

        <!-- Pagination Controls (< dan >) -->
        <?php if (!empty($historiPenerimaan)): ?>
        <div class="flex items-center gap-2">
            <span id="page_info" class="text-xs font-semibold text-zcMut">Halaman 1</span>
            <div class="flex items-center rounded-xl border border-zcBrd overflow-hidden shadow-xs">
                <button type="button" id="btn_prev" onclick="prevHistoryPage()" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed font-bold text-xs transition border-r border-zcBrd flex items-center gap-1">
                    &lt; Sebelumnya
                </button>
                <button type="button" id="btn_next" onclick="nextHistoryPage()" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed font-bold text-xs transition flex items-center gap-1">
                    Berikutnya &gt;
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (empty($historiPenerimaan)): ?>
        <div class="py-12 text-center text-zcMut">
            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <p class="text-sm font-semibold">Belum ada dokumen penerimaan barang dalam 7 hari terakhir.</p>
            <p class="text-xs text-slate-400 mt-1">Data akan otomatis tersimpan dan siap dicetak stikernya saat Anda menginput faktur/PO baru.</p>
        </div>
    <?php else: ?>
        <div id="history_cards_container" class="space-y-5">
            <?php foreach ($historiPenerimaan as $idx => $p): 
                $items = $historiDetails[$p['id']] ?? [];
                $sumberCls = $p['sumber'] === 'PO' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200';
                $totalNilaiPO = 0;
                $totalQtyBesarPO = 0;
                foreach ($items as $it) {
                    $totalNilaiPO += ((int)$it['qty_terima'] * (float)($it['harga_beli'] ?? 0));
                    $totalQtyBesarPO += (int)$it['qty_terima'];
                }
            ?>
            <div class="history-card bg-slate-50/60 border border-zcBrd rounded-2xl p-5 shadow-sm transition hover:shadow-md" data-idx="<?= $idx ?>">
                <!-- Top Info Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-zcBrd gap-2 mb-3">
                    <div class="flex items-center flex-wrap gap-2.5">
                        <span class="px-2.5 py-1 text-xs font-black rounded-lg border bg-white text-slate-800 font-mono shadow-xs">
                            <?= htmlspecialchars($p['no_referensi']) ?>
                        </span>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md border <?= $sumberCls ?>">
                            <?= htmlspecialchars($p['sumber']) ?>
                        </span>
                        <span class="text-xs font-semibold text-slate-600 flex items-center gap-1">
                            📅 <?= date('d M Y', strtotime($p['tanggal_terima'])) ?>
                        </span>
                        <?php if ($p['nama_supplier']): ?>
                            <span class="text-xs text-zcMut">
                                • Supplier: <strong class="text-slate-700"><?= htmlspecialchars($p['nama_supplier']) ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-[11px] text-zcMut flex items-center gap-2">
                        <span>Diterima oleh: <strong class="text-slate-700"><?= htmlspecialchars($p['pembuat'] ?? 'Petugas Gudang') ?></strong></span>
                        <span>(<?= date('H:i', strtotime($p['created_at'])) ?> WIB)</span>
                        <?php if ($p['file_nota']): ?>
                            <a href="../uploads/nota/<?= htmlspecialchars($p['file_nota']) ?>" target="_blank" class="px-2 py-0.5 bg-white border border-zcBrd text-zc text-[10px] font-bold rounded hover:bg-zcLt transition">
                                📎 Bukti Nota
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($p['catatan']): ?>
                    <div class="text-xs text-slate-600 italic mb-3 bg-white p-2 rounded-lg border border-slate-200/70">
                        Catatan: <?= htmlspecialchars($p['catatan']) ?>
                    </div>
                <?php endif; ?>

                <!-- Table Item di dalam 1 PO yang sama -->
                <div class="overflow-x-auto bg-white rounded-xl border border-zcBrd">
                    <table class="w-full text-xs min-w-[780px]">
                        <thead class="bg-slate-100/70 border-b border-zcBrd text-zcMut font-bold">
                            <tr>
                                <th class="text-left px-3 py-2.5">Produk &amp; Variasi</th>
                                <th class="text-left px-3 py-2.5">Kategori</th>
                                <th class="text-center px-3 py-2.5">Qty Diterima</th>
                                <th class="text-left px-3 py-2.5">Nilai Beli Satuan</th>
                                <th class="text-left px-3 py-2.5">Total Nilai Fisik PO</th>
                                <th class="text-left px-3 py-2.5">No. Batch / Exp (Obat)</th>
                                <th class="text-center px-3 py-2.5">Cetak Stiker Barcode Fisik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBrd/60">
                            <?php foreach ($items as $it): 
                                $isObat = ($it['kategori'] === 'Obat');
                                $qtyBesar = (int)$it['qty_terima'];
                                $hargaBeli = (float)($it['harga_beli'] ?? 0);
                                $subtotalFisik = $qtyBesar * $hargaBeli;
                                $totalPcs = $qtyBesar * (int)($it['rasio_konversi'] ?? 1);
                            ?>
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-3 py-2.5 font-bold text-slate-800">
                                    <?= htmlspecialchars($it['nama_produk'] . ' — ' . $it['nama_variasi']) ?>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $isObat ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' ?>">
                                        <?= htmlspecialchars($it['kategori']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center font-bold text-slate-800">
                                    <?= $qtyBesar ?> <?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?>
                                    <span class="block text-[10px] font-normal text-zcMut">(Total <?= number_format($totalPcs) ?> <?= htmlspecialchars($it['satuan_kecil'] ?? 'Pcs') ?>)</span>
                                </td>
                                <td class="px-3 py-2.5 font-semibold text-slate-700">
                                    Rp <?= number_format($hargaBeli, 0, ',', '.') ?>
                                    <span class="block text-[10px] text-zcMut font-normal">/<?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?></span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="font-bold text-slate-800">Rp <?= number_format($subtotalFisik, 0, ',', '.') ?></div>
                                    <div class="text-[10px] text-zcMut font-normal"><?= $qtyBesar ?> <?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?> &times; Rp <?= number_format($hargaBeli, 0, ',', '.') ?></div>
                                </td>
                                <td class="px-3 py-2.5">
                                    <?php if ($it['no_batch']): ?>
                                        <div class="font-mono font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($it['no_batch']) ?></div>
                                        <div class="text-[10px] text-emerald-700 font-semibold">Exp: <?= date('d/m/Y', strtotime($it['tgl_exp'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">— Non Obat —</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <?php if ($isObat && $it['no_batch'] && !empty($it['id_stok_batch'])): ?>
                                        <a href="cetak_stiker.php?tipe=batch&id=<?= $it['id_stok_batch'] ?>" target="_blank" 
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                            🖨️ Cetak Stiker Batch: <?= htmlspecialchars($it['no_batch']) ?>
                                        </a>
                                    <?php elseif ($it['kategori'] === 'Alat Kesehatan'): ?>
                                        <a href="../laporan/logistik_medis.php?tab=sn&id_variasi=<?= $it['id_variasi'] ?>" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                            🏷️ Cetak Stiker SN
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px]">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-slate-50/80 border-t border-zcBrd text-xs">
                            <tr>
                                <td colspan="4" class="px-3 py-2.5 text-right font-bold text-slate-600">
                                    Total Nilai Faktur/Nota (<?= $totalQtyBesarPO ?> Satuan Besar):
                                </td>
                                <td class="px-3 py-2.5 font-black text-emerald-700">
                                    Rp <?= number_format($totalNilaiPO, 0, ',', '.') ?>
                                </td>
                                <td colspan="2" class="px-3 py-2.5 text-[10px] text-zcMut italic">
                                    *Pencocokan faktur fisik
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="px-3 py-2 bg-slate-50/50 border-t border-zcBrd/60 flex items-center justify-between text-[11px] text-zcMut">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span>Total harga dihitung dari <em>Qty Satuan Besar &times; Harga Beli</em> untuk verifikasi kesesuaian faktur/nota saat barang masuk.</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
let rC = 1;
const rawProdukList = <?= json_encode($produkList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const minExpStr = '<?= date('Y-m-d', strtotime('+90 days')) ?>';

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function getOptionsHtml() {
    let html = '<option value="">-- Cari &amp; Pilih Produk --</option>';
    if (Array.isArray(rawProdukList)) {
        rawProdukList.forEach(p => {
            const satBesar = p.satuan_besar || 'Dus';
            const satKecil = p.satuan_kecil || 'Pcs';
            const rasio = p.rasio_konversi || 1;
            const hargaJual = p.harga_jual_besar || 0;
            const label = `${p.label} (${satBesar})`;
            html += `<option value="${p.id}" data-kat="${escapeHtml(p.kategori)}" data-satbesar="${escapeHtml(satBesar)}" data-satkecil="${escapeHtml(satKecil)}" data-rasio="${rasio}" data-hargajual="${hargaJual}">${escapeHtml(label)}</option>`;
        });
    }
    return html;
}

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

function formatRupiahInput(el) {
    if (!el) return;
    let clean = el.value.replace(/[^0-9]/g, '');
    if (clean === '') {
        el.value = '';
        return;
    }
    el.value = parseInt(clean, 10).toLocaleString('id-ID');
}

function calcSubtotal(idx) {
    const qtyEl = document.getElementById('qty-' + idx);
    const hargaEl = document.getElementById('harga-' + idx);
    const subtotalEl = document.getElementById('subtotal-' + idx);
    if (!subtotalEl) return;
    
    const qty = parseFloat(qtyEl ? qtyEl.value : 0) || 0;
    const rawHarga = hargaEl ? hargaEl.value.replace(/[^0-9]/g, '') : '0';
    const harga = parseFloat(rawHarga) || 0;
    const total = qty * harga;
    
    subtotalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
}

function initSelect2(id) {
    if (window.jQuery && $.fn.select2) {
        const $el = $('#' + id);
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.select2({
            width: '100%',
            dropdownAutoWidth: true,
            language: {
                noResults: function() {
                    return "Produk tidak ditemukan";
                }
            }
        }).on('change', function() {
            const idx = this.getAttribute('data-row-idx');
            checkProduk(this, idx);
            calcSubtotal(idx);
        });
    }
}

// Inisialisasi Select2 pada baris pertama saat halaman selesai dimuat
$(document).ready(function() {
    initSelect2('sel-0');
});

function addRow() {
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    tr.id = 'row-' + rC;
    tr.className = 'border-b border-zcBrd/50';
    tr.innerHTML = `
        <td class="px-2 py-2 align-top">
            <select name="id_variasi[]" id="sel-${rC}" data-row-idx="${rC}" required onchange="checkProduk(this, ${rC}); calcSubtotal(${rC});" class="select2-produk w-full border border-zcBrd rounded-lg px-2 py-1.5 text-xs">
                ${getOptionsHtml()}
            </select>
        </td>
        <td class="px-2 py-2 align-top">
            <div class="flex items-center gap-1">
                <input type="number" name="qty[]" id="qty-${rC}" min="1" required placeholder="Jml" oninput="calcSubtotal(${rC})" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
                <span id="badge-uom-${rC}" class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-1 rounded whitespace-nowrap border border-slate-200">Dus</span>
            </div>
        </td>
        <td class="px-2 py-2 align-top">
            <div class="relative">
                <span class="absolute left-2.5 top-1.5 text-xs font-bold text-slate-400">Rp</span>
                <input type="text" inputmode="numeric" name="harga_beli[]" id="harga-${rC}" min="0" placeholder="0" oninput="formatRupiahInput(this); calcSubtotal(${rC});" 
                    class="w-full pl-8 border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
            </div>
            <div class="text-[9px] text-zcMut mt-0.5">Per 1 Satuan Besar</div>
        </td>
        <td class="px-2 py-2 align-top">
            <div id="subtotal-${rC}" class="text-xs font-bold text-slate-800 pt-1.5 whitespace-nowrap">Rp 0</div>
            <div class="text-[9px] text-zcMut">Subtotal Fisik PO</div>
        </td>
        <td class="px-2 py-2 align-top"><input type="text" name="no_batch[]" id="b-${rC}" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="Wajib untuk obat"></td>
        <td class="px-2 py-2 align-top"><input type="date" name="tgl_exp[]" id="e-${rC}" min="${minExpStr}" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly></td>
        <td class="px-2 py-2 align-top"><textarea name="serial_number[]" id="s-${rC}" rows="1" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="SN dipisah koma"></textarea></td>
        <td class="px-2 py-2 align-top text-center"><button type="button" onclick="removeRow(${rC})" class="text-slate-300 hover:text-red-500 font-bold text-lg leading-none">&times;</button></td>
    `;
    tbody.appendChild(tr);
    initSelect2('sel-' + rC);
    rC++;
}

function removeRow(n) {
    const row = document.getElementById('row-' + n);
    if (row) {
        const sel = $('#sel-' + n);
        if (sel.length && sel.data('select2')) {
            sel.select2('destroy');
        }
        row.remove();
    }
}

function checkProduk(sel, i) {
    const opt = sel.options[sel.selectedIndex];
    const kat = opt ? opt.getAttribute('data-kat') : '';
    const satBesar = opt ? (opt.getAttribute('data-satbesar') || 'Dus') : 'Dus';
    const b = document.getElementById('b-'+i);
    const e = document.getElementById('e-'+i);
    const s = document.getElementById('s-'+i);
    const badge = document.getElementById('badge-uom-'+i);
    
    if (badge) {
        badge.innerText = satBesar;
    }
    
    // Reset state
    [b,e,s].forEach(el => { if (el) { el.readOnly = true; el.classList.add('bg-slate-100'); el.required = false; } });
    
    if (kat === 'Obat') {
        if (b) { b.readOnly = false; b.classList.remove('bg-slate-100'); b.required = true; }
        if (e) { e.readOnly = false; e.classList.remove('bg-slate-100'); e.required = true; }
    } else if (kat === 'Alat Kesehatan') {
        if (s) { s.readOnly = false; s.classList.remove('bg-slate-100'); s.required = true; }
    }
}

// =========================================================================
// PAGINASI CLIENT-SIDE HISTORI PENERIMAAN (FORWARD / BACKWARD < & >)
// =========================================================================
let currentHistPage = 1;
const itemsPerPage = 3;
const allCards = document.querySelectorAll('.history-card');
const totalCards = allCards.length;
const totalPages = Math.ceil(totalCards / itemsPerPage) || 1;

function updateHistoryPagination() {
    allCards.forEach((card, index) => {
        const start = (currentHistPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        if (index >= start && index < end) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    const pageInfo = document.getElementById('page_info');
    if (pageInfo) {
        pageInfo.innerText = `Halaman ${currentHistPage} dari ${totalPages} (Total ${totalCards} Dokumen)`;
    }

    const btnPrev = document.getElementById('btn_prev');
    const btnNext = document.getElementById('btn_next');
    if (btnPrev) btnPrev.disabled = (currentHistPage <= 1);
    if (btnNext) btnNext.disabled = (currentHistPage >= totalPages);
}

function prevHistoryPage() {
    if (currentHistPage > 1) {
        currentHistPage--;
        updateHistoryPagination();
    }
}

function nextHistoryPage() {
    if (currentHistPage < totalPages) {
        currentHistPage++;
        updateHistoryPagination();
    }
}

if (totalCards > 0) {
    updateHistoryPagination();
}
</script>

<?php layoutFooter(); ?>
