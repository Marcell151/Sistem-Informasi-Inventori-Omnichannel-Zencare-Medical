<?php
// File: inventori/proses_mutasi.php
// Modul Mutasi Stok Antar Cabang – Admin & Karyawan
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'karyawan']);

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVariasi    = intval($_POST['id_variasi'] ?? 0);
    $cabangAsal   = intval($_POST['cabang_asal'] ?? 0);
    $cabangTujuan = intval($_POST['cabang_tujuan'] ?? 0);
    $qty          = intval($_POST['qty'] ?? 0);

    $jenisMutasi  = $_POST['jenis_mutasi'] ?? 'Transfer';
    $snRaw        = trim($_POST['serial_number'] ?? '');

    if (!$idVariasi || !$cabangAsal || !$cabangTujuan || $qty <= 0) {
        $msg = "Semua field wajib diisi dan qty harus > 0!"; $msgType = 'error';
    } elseif ($cabangAsal === $cabangTujuan) {
        $msg = "Cabang asal dan tujuan tidak boleh sama!"; $msgType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $stmtK = $pdo->prepare("SELECT pi.kategori FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.id = ?");
            $stmtK->execute([$idVariasi]);
            $kategori = $stmtK->fetchColumn();

            if ($kategori === 'Alat Kesehatan') {
                if (empty($snRaw)) throw new Exception("Serial Number wajib diisi untuk mutasi Alat Kesehatan.");
                $snList = preg_split('/[\n,]+/', $snRaw, -1, PREG_SPLIT_NO_EMPTY);
                $snList = array_map('trim', $snList);
                if (count($snList) !== $qty) throw new Exception("Jumlah SN (" . count($snList) . ") tidak sesuai dengan Qty ($qty).");
                
                foreach ($snList as $sn) {
                    $chkSN = $pdo->prepare("SELECT id, status FROM unit_serial WHERE serial_number=? AND id_cabang=? AND id_variasi=?");
                    $chkSN->execute([$sn, $cabangAsal, $idVariasi]);
                    $rowSN = $chkSN->fetch();
                    if (!$rowSN) throw new Exception("Serial Number '$sn' tidak ditemukan di cabang asal.");
                    
                    $newStatus = ($jenisMutasi === 'Retur Garansi') ? 'Retur' : $rowSN['status'];
                    $pdo->prepare("UPDATE unit_serial SET id_cabang=?, status=? WHERE id=?")->execute([$cabangTujuan, $newStatus, $rowSN['id']]);
                }
            }

            $stokAsalQ = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=? FOR UPDATE");
            $stokAsalQ->execute([$idVariasi, $cabangAsal]);
            $stokAsal = $stokAsalQ->fetchColumn();

            if ($stokAsal === false || intval($stokAsal) < $qty) {
                // If it's a return, maybe the stock is 0 but we accept the return? Actually, if it's a return from customer, it shouldn't be mutasi stok antar cabang. But "melacak retur klaim garansi Alkes" could mean transferring broken units to warehouse. So the broken unit was probably in customer hands. Wait, if it's returned, it enters branch stock first via Tambah Stok, then Mutasi? Let's just deduct from asal.
                if ($jenisMutasi !== 'Retur Garansi' || intval($stokAsal) < $qty) {
                    throw new Exception("Stok di cabang asal tidak mencukupi! Tersisa: " . ($stokAsal ?? 0) . " unit.");
                }
            }

            // Kurangi stok cabang asal
            $pdo->prepare("UPDATE stok_cabang SET stok=stok-? WHERE id_variasi=? AND id_cabang=?")->execute([$qty, $idVariasi, $cabangAsal]);
            $sisaAsal = intval($stokAsal) - $qty;
            $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Keluar',?,?,?)")
                ->execute([$cabangAsal, $idVariasi, $qty, $sisaAsal, "$jenisMutasi Keluar → Cabang ID $cabangTujuan"]);

            // Tambah stok cabang tujuan
            $chkTujuan = $pdo->prepare("SELECT id FROM stok_cabang WHERE id_variasi=? AND id_cabang=?");
            $chkTujuan->execute([$idVariasi, $cabangTujuan]);
            if ($chkTujuan->fetchColumn()) {
                $pdo->prepare("UPDATE stok_cabang SET stok=stok+? WHERE id_variasi=? AND id_cabang=?")->execute([$qty, $idVariasi, $cabangTujuan]);
            } else {
                $pdo->prepare("INSERT INTO stok_cabang (id_variasi,id_cabang,stok) VALUES (?,?,?)")->execute([$idVariasi, $cabangTujuan, $qty]);
            }
            $sisaTujuanQ = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=?");
            $sisaTujuanQ->execute([$idVariasi, $cabangTujuan]);
            $sisaTujuan = $sisaTujuanQ->fetchColumn();
            $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Masuk',?,?,?)")
                ->execute([$cabangTujuan, $idVariasi, $qty, $sisaTujuan, "$jenisMutasi Masuk ← Cabang ID $cabangAsal"]);

            // Catat di mutasi_stok
            $pdo->prepare("INSERT INTO mutasi_stok (id_variasi,cabang_asal,cabang_tujuan,qty) VALUES (?,?,?,?)")->execute([$idVariasi, $cabangAsal, $cabangTujuan, $qty]);

            $pdo->commit();
            $msg = "$jenisMutasi berhasil: $qty unit dipindahkan dari Cabang #$cabangAsal ke Cabang #$cabangTujuan.";
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal mutasi: " . $e->getMessage(); $msgType = 'error';
        }
    }
}

$cabangList = $pdo->query("SELECT * FROM cabang WHERE is_active=1 ORDER BY id ASC")->fetchAll();
$produkList = $pdo->query("
    SELECT v.id, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS label, v.sku_variasi, i.kategori
    FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id
    WHERE v.is_active=1 AND i.is_active=1 ORDER BY i.nama_produk ASC")->fetchAll();

$mutasiLog = $pdo->query("
    SELECT m.*, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama_item,
           ca.nama AS nama_asal, ct.nama AS nama_tujuan
    FROM mutasi_stok m
    JOIN produk_variasi v ON m.id_variasi=v.id
    JOIN produk_induk i ON v.id_produk_induk=i.id
    JOIN cabang ca ON m.cabang_asal=ca.id
    JOIN cabang ct ON m.cabang_tujuan=ct.id
    ORDER BY m.tanggal DESC LIMIT 10")->fetchAll();

layoutHead('Mutasi Stok Cabang');
layoutBodyOpen();
layoutSidebar('mutasi');
layoutHeader('Mutasi Stok Antar Cabang', 'Transfer stok barang dari cabang asal ke cabang tujuan dengan atomik & audit trail');
?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Form Mutasi -->
    <div class="lg:col-span-5">
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-2.5 mb-5 pb-4 border-b border-zcBrd">
                <div class="w-8 h-8 rounded-lg bg-zcLt text-zc flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <h2 class="text-base font-bold text-zcTxt">Form Transfer Stok Antar Cabang</h2>
            </div>

            <?php if ($msg): ?>
                <div class="mb-4 p-3.5 rounded-xl border text-sm font-semibold flex items-center gap-2.5 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                    <svg class="w-4 h-4 shrink-0 <?= $msgType === 'success' ? 'text-emerald-600' : 'text-rose-600' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="<?= $msgType === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' ?>"/></svg>
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4" onsubmit="return confirm('Konfirmasi mutasi stok?')">
                <div>
                    <label class="block text-sm font-semibold text-zcTxt mb-1.5">Produk / Variasi *</label>
                    <select name="id_variasi" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50" onchange="checkKategori(this)">
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($produkList as $p): ?>
                            <option value="<?= $p['id'] ?>" data-kategori="<?= htmlspecialchars($p['kategori']) ?>"><?= htmlspecialchars($p['label']) ?> (<?= htmlspecialchars($p['sku_variasi']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-zcTxt mb-1.5">Cabang Asal *</label>
                        <select name="cabang_asal" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                            <option value="">-- Cabang Asal --</option>
                            <?php foreach ($cabangList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zcTxt mb-1.5">Cabang Tujuan *</label>
                        <select name="cabang_tujuan" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                            <option value="">-- Cabang Tujuan --</option>
                            <?php foreach ($cabangList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-zcTxt mb-1.5">Jenis Mutasi *</label>
                        <select name="jenis_mutasi" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                            <option value="Transfer">Transfer Stok Normal</option>
                            <option value="Retur Garansi">Retur Klaim Garansi Alkes</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zcTxt mb-1.5">Jumlah Unit yang Dimutasi (Pcs) *</label>
                        <input type="number" name="qty" required min="1" placeholder="Contoh: 10"
                            class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    </div>
                </div>

                <div id="field_alkes" class="hidden space-y-3 p-4 bg-indigo-50 rounded-xl border border-indigo-200">
                    <p class="text-xs font-bold text-indigo-800 border-b border-indigo-200 pb-2 mb-2">🏷️ Serial Number Alat Kesehatan</p>
                    <div>
                        <label class="block text-xs font-semibold text-zcTxt mb-1">Daftar Serial Number Mutasi / Retur *</label>
                        <textarea name="serial_number" id="inp_sn" rows="3" placeholder="Masukkan SN dipisah koma/baris untuk dipindahkan..." class="w-full text-xs border border-indigo-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-indigo-400"></textarea>
                    </div>
                </div>
                <button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Proses Mutasi Stok Antar Cabang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Log Mutasi -->
    <div class="lg:col-span-7">
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-zcBrd flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-zcTxt">Riwayat Mutasi Stok Terbaru</h3>
                    <p class="text-xs text-zcMut">10 aktivitas transfer fisik antar cabang terakhir</p>
                </div>
            </div>
            <?php if (empty($mutasiLog)): ?>
                <div class="p-10 text-center text-sm text-zcMut italic">Belum ada riwayat mutasi stok antar cabang.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-sm">
                        <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3 text-left">Produk</th>
                                <th class="px-4 py-3 text-left">Asal &rarr; Tujuan</th>
                                <th class="px-4 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBrd/60">
                            <?php foreach ($mutasiLog as $m): ?>
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-4 py-3.5 font-semibold text-zcTxt"><?= htmlspecialchars($m['nama_item']) ?></td>
                                    <td class="px-4 py-3.5 text-zcMut">
                                        <span class="font-semibold text-rose-600"><?= htmlspecialchars($m['nama_asal']) ?></span>
                                        <span class="mx-1 text-slate-400">&rarr;</span>
                                        <span class="font-semibold text-emerald-600"><?= htmlspecialchars($m['nama_tujuan']) ?></span>
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="px-2.5 py-1 bg-sky-50 text-sky-700 border border-sky-200 rounded-full font-bold text-xs"><?= $m['qty'] ?> unit</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right text-zcMut text-xs font-mono"><?= date('d/m/Y H:i', strtotime($m['tanggal'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function checkKategori(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.kategori === 'Alat Kesehatan') {
        document.getElementById('field_alkes').classList.remove('hidden');
        document.getElementById('inp_sn').required = true;
    } else {
        document.getElementById('field_alkes').classList.add('hidden');
        document.getElementById('inp_sn').required = false;
    }
}
</script>

<?php layoutEnd(); ?>
