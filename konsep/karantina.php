<?php
// File: inventori/karantina.php
// Gudang Karantina Barang Cacat / Garansi (Sistem Tiket Helpdesk) – Admin & Kasir
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'kasir']);

$activeCabang = $_SESSION['id_cabang'] ?? 1;
if (isset($_GET['cabang'])) $_SESSION['id_cabang'] = $activeCabang = intval($_GET['cabang']);

$msg = ''; $msgType = '';
$role = $_SESSION['role'] ?? '';
$isAdmin = ($role === 'super_admin');

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'karantina_baru') {
        $idVariasi = intval($_POST['id_variasi'] ?? 0);
        $qty       = intval($_POST['qty'] ?? 0);
        $alasan    = trim($_POST['alasan'] ?? '');

        if (!$idVariasi || $qty <= 0 || !$alasan) {
            $msg = "Semua field wajib diisi!"; $msgType = 'error';
        } else {
            $pdo->beginTransaction();
            try {
                $stokQ = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=? FOR UPDATE");
                $stokQ->execute([$idVariasi, $activeCabang]);
                $stok = $stokQ->fetchColumn();

                if ($stok === false || intval($stok) < $qty) {
                    throw new Exception("Stok tidak mencukupi untuk dikarantina! Tersisa: " . ($stok ?? 0));
                }

                // Potong stok cabang
                $pdo->prepare("UPDATE stok_cabang SET stok=stok-? WHERE id_variasi=? AND id_cabang=?")->execute([$qty, $idVariasi, $activeCabang]);
                $sisa = intval($stok) - $qty;

                // Buat no tiket bantuan unik
                $noTiket = 'TKT-' . time() . '-' . rand(10, 99);

                // Insert ke gudang_karantina
                $pdo->prepare("INSERT INTO gudang_karantina (id_cabang, id_variasi, qty, alasan, no_tiket, status_tiket) VALUES (?,?,?,?,?,'Menunggu Retur Supplier')")
                    ->execute([$activeCabang, $idVariasi, $qty, $alasan, $noTiket]);

                // Log kartu stok
                $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Karantina',?,?,?)")
                    ->execute([$activeCabang, $idVariasi, $qty, $sisa, "Karantina ($noTiket): $alasan"]);

                $pdo->commit();
                $msg = "Tiket Retur $noTiket berhasil dibuat. $qty unit dipindahkan ke Karantina."; $msgType = 'success';
            } catch (Exception $e) {
                $pdo->rollBack();
                $msg = "Gagal: " . $e->getMessage(); $msgType = 'error';
            }
        }
    }

    if ($aksi === 'update_tiket') {
        requireRole(['super_admin']);
        $idKarantina = intval($_POST['id_karantina'] ?? 0);
        $status      = trim($_POST['status_tiket'] ?? '');
        $catatan     = trim($_POST['catatan_retur'] ?? '');

        if ($idKarantina && $status) {
            try {
                $pdo->prepare("UPDATE gudang_karantina SET status_tiket = ?, catatan_retur = ? WHERE id = ?")
                    ->execute([$status, $catatan, $idKarantina]);
                $msg = "Status tiket retur berhasil diperbarui."; $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal: " . $e->getMessage(); $msgType = 'error';
            }
        }
    }
}

// Fetch lists
$produkList = $pdo->prepare("
    SELECT v.id, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS label, COALESCE(sc.stok,0) AS stok
    FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id
    LEFT JOIN stok_cabang sc ON sc.id_variasi=v.id AND sc.id_cabang=?
    WHERE v.is_active=1 AND i.is_active=1 ORDER BY i.nama_produk ASC");
$produkList->execute([$activeCabang]);
$produkList = $produkList->fetchAll();

$karantinaLog = $pdo->prepare("
    SELECT gk.*, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama
    FROM gudang_karantina gk JOIN produk_variasi v ON gk.id_variasi=v.id
    JOIN produk_induk i ON v.id_produk_induk=i.id
    WHERE gk.id_cabang=? ORDER BY gk.tanggal DESC LIMIT 30");
$karantinaLog->execute([$activeCabang]);
$karantinaLog = $karantinaLog->fetchAll();

$totalKarantina = array_sum(array_column($karantinaLog, 'qty'));

layoutHead('Gudang Karantina & Retur');
layoutBodyOpen();
layoutSidebar('karantina');
layoutHeader('Gudang Karantina & Retur Garansi', 'Tiket pelacakan barang defect/karantina aktif berdasarkan cabang');
?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

    <!-- Form Buat Tiket Karantina Baru (Kasir & Admin) -->
    <div class="lg:col-span-4">
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-bold text-zcTxt mb-4 pb-3 border-b border-zcBrd flex items-center gap-2">
                <span class="text-base">🎫</span> Buat Tiket Karantina Baru
            </h2>

            <?php if ($msg && $aksi === 'karantina_baru'): ?>
                <div class="mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                    <?= $msgType === 'success' ? '✅' : '⛔' ?> <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs">
                <span class="text-amber-800 font-semibold">Total unit terisolasi:</span>
                <span class="font-bold text-amber-900 text-base"><?= number_format($totalKarantina) ?> unit</span>
            </div>

            <form method="POST" class="space-y-4" onsubmit="return confirm('Pindahkan barang ke karantina? Stok aktif cabang akan langsung dipotong.')">
                <input type="hidden" name="aksi" value="karantina_baru">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Pilih Produk Cacat *</label>
                    <select name="id_variasi" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($produkList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['label']) ?> (Stok: <?= $p['stok'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Jumlah Unit *</label>
                    <input type="number" name="qty" required min="1" placeholder="Contoh: 1"
                        class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Alasan & Keluhan Kerusakan *</label>
                    <textarea name="alasan" required placeholder="Jelaskan deskripsi kerusakan secara detail..." rows="3"
                        class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50"></textarea>
                </div>
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs py-3 rounded-xl transition shadow-sm">
                    ⚠️ Daftarkan Tiket Defect
                </button>
            </form>
        </div>
    </div>

    <!-- Helpdesk Ticket Ledger (Tabel Log Tiket Karantina) -->
    <div class="lg:col-span-8 bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-zcTxt">📋 Helpdesk &amp; Pelacakan Tiket Retur</h3>
                <p class="text-[11px] text-zcMut mt-0.5">Status retur ke produsen/supplier untuk barang karantina</p>
            </div>
        </div>

        <?php if ($msg && $aksi === 'update_tiket'): ?>
            <div class="m-5 p-3 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                <?= $msgType === 'success' ? '✅' : '⛔' ?> <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($karantinaLog)): ?>
            <div class="p-10 text-center text-xs text-zcMut italic">Tidak ada tiket defect aktif di cabang ini.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">No Tiket / Barang</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-left">Masalah &amp; Status</th>
                            <th class="px-4 py-3 text-left">Catatan Retur</th>
                            <?php if ($isAdmin): ?>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zcBrd/60">
                        <?php foreach ($karantinaLog as $k): ?>
                            <?php 
                                $statusBadge = match($k['status_tiket']) {
                                    'Menunggu Retur Supplier' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'Retur Dikirim' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'Selesai Diganti' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'Ditolak Supplier' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-slate-100 text-slate-800'
                                };
                            ?>
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-4 py-3">
                                    <div class="font-mono font-bold text-amber-700 text-[10px]"><?= $k['no_tiket'] ?></div>
                                    <div class="font-semibold text-zcTxt text-xs mt-0.5"><?= htmlspecialchars($k['nama']) ?></div>
                                    <div class="text-[9px] text-zcMut mt-0.5"><?= date('d M Y H:i', strtotime($k['tanggal'])) ?></div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-xs"><?= $k['qty'] ?> pcs</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs text-slate-600 mb-1 leading-normal"><?= htmlspecialchars($k['alasan']) ?></div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $statusBadge ?>">
                                        ● <?= $k['status_tiket'] ?: 'Menunggu Retur' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 italic max-w-[200px] truncate-3-lines">
                                    <?= htmlspecialchars($k['catatan_retur'] ?: '— Tidak ada catatan') ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td class="px-4 py-3 text-center">
                                        <button onclick="openModalUpdate(<?= $k['id'] ?>, '<?= $k['no_tiket'] ?>', '<?= $k['status_tiket'] ?>', '<?= htmlspecialchars(addslashes($k['catatan_retur'] ?? '')) ?>')" 
                                            class="px-2.5 py-1.5 bg-zcLt hover:bg-zcLt/80 text-zc border border-zc/25 text-[10px] font-bold rounded-lg transition">
                                            Update Tiket
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL: Update Tiket Retur (Super Admin Only) -->
<div id="modal_update_tiket" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd">
            <h3 class="text-sm font-bold text-zcTxt">🎫 Update Status Tiket <span id="mdl_no_tiket" class="text-zc"></span></h3>
            <button onclick="document.getElementById('modal_update_tiket').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">✕</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="update_tiket">
            <input type="hidden" name="id_karantina" id="mdl_id_karantina">
            
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Status Tiket Retur *</label>
                <select name="status_tiket" id="mdl_status_tiket" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-semibold">
                    <option value="Menunggu Retur Supplier">Menunggu Retur Supplier</option>
                    <option value="Retur Dikirim">Retur Dikirim (Proses Kirim Balik)</option>
                    <option value="Selesai Diganti">Selesai Diganti (Barang Baru Diterima)</option>
                    <option value="Ditolak Supplier">Ditolak Supplier / Garansi Hangus</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Catatan Penyelesaian Retur</label>
                <textarea name="catatan_retur" id="mdl_catatan_retur" rows="3" placeholder="Tulis rincian retur, no resi pengiriman, atau info penggantian..."
                    class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_update_tiket').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Simpan Tiket</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalUpdate(id, noTiket, status, catatan) {
    document.getElementById('mdl_id_karantina').value = id;
    document.getElementById('mdl_no_tiket').innerText = noTiket;
    document.getElementById('mdl_status_tiket').value = status;
    document.getElementById('mdl_catatan_retur').value = catatan;
    document.getElementById('modal_update_tiket').classList.remove('hidden');
}
</script>

<?php layoutEnd(); ?>
