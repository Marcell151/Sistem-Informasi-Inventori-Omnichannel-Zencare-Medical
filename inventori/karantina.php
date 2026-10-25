<?php
// File: inventori/karantina.php
// Gudang Karantina Barang Cacat / Garansi – Admin & Karyawan
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$activeCabang = $_SESSION['id_cabang'] ?? 1;
if (isset($_GET['cabang'])) $_SESSION['id_cabang'] = $activeCabang = intval($_GET['cabang']);

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVariasi = intval($_POST['id_variasi'] ?? 0);
    $qty       = intval($_POST['qty'] ?? 0);
    $alasan    = trim($_POST['alasan'] ?? '');

    if (!$idVariasi || $qty <= 0 || !$alasan) {
        $msg = "Semua field wajib diisi!"; $msgType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $stokQ = $pdo->prepare("SELECT stok FROM stok_toko WHERE id_variasi=? AND 1=1 FOR UPDATE");
            $stokQ->execute([$idVariasi, $activeCabang]);
            $stok = $stokQ->fetchColumn();

            if ($stok === false || intval($stok) < $qty) {
                throw new Exception("Stok tidak mencukupi untuk dikarantina! Tersisa: " . ($stok ?? 0));
            }

            $pdo->prepare("UPDATE stok_toko SET stok=stok-? WHERE id_variasi=? AND 1=1")->execute([$qty, $idVariasi, $activeCabang]);
            $sisa = intval($stok) - $qty;

            $pdo->prepare("INSERT INTO gudang_karantina (id_cabang,id_variasi,qty,alasan) VALUES (?,?,?,?)")->execute([$activeCabang, $idVariasi, $qty, $alasan]);
            $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Karantina',?,?,?)")
                ->execute([$activeCabang, $idVariasi, $qty, $sisa, "Karantina: $alasan"]);

            $pdo->commit();
            $msg = "$qty unit berhasil dipindahkan ke Gudang Karantina."; $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal: " . $e->getMessage(); $msgType = 'error';
        }
    }
}

$produkList = $pdo->prepare("
    SELECT v.id, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS label, COALESCE(sc.stok,0) AS stok
    FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id
    LEFT JOIN stok_toko sc ON sc.id_variasi=v.id 
    WHERE v.is_active=1 AND i.is_active=1 ORDER BY i.nama_produk ASC");
$produkList->execute();
$produkList = $produkList->fetchAll();

$karantinaLog = $pdo->prepare("
    SELECT gk.*, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama
    FROM gudang_karantina gk JOIN produk_variasi v ON gk.id_variasi=v.id
    JOIN produk_induk i ON v.id_produk_induk=i.id
    WHERE 1=1 ORDER BY gk.tanggal DESC LIMIT 20");
$karantinaLog->execute();
$karantinaLog = $karantinaLog->fetchAll();

$totalKarantina = array_sum(array_column($karantinaLog, 'qty'));

layoutHead('Gudang Karantina');
layoutBodyOpen();
layoutSidebar('karantina');
layoutHeader('Gudang Karantina Barang Cacat', 'Isolasi barang defect/retur garansi dari stok aktif – sisa stok terpotong otomatis');
?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

    <!-- Form Karantina -->
    <div class="lg:col-span-4">
        <div class="bg-white border border-zcBorder rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-bold text-zcText mb-4 pb-3 border-b border-zcBorder flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span>Form Isolasi Barang</span>
            </h2>

            <?php if ($msg): ?>
                <div class="mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                    <?php if ($msgType === 'success'): ?>
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <?php else: ?>
                        <svg class="w-4 h-4 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
            <?php endif; ?>

            <!-- Summary Badge -->
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs">
                <span class="text-amber-800 font-semibold">Total unit dikarantina:</span>
                <span class="font-bold text-amber-900 text-base"><?= number_format($totalKarantina) ?> unit</span>
            </div>

            <form method="POST" class="space-y-4" onsubmit="handleToggleSubmit(this, event, 'Karantinakan Barang', 'barang ini ke gudang karantina (stok aktif akan otomatis dipotong)')">
                <div>
                    <label class="block text-xs font-bold text-zcText mb-1.5">Pilih Produk *</label>
                    <select name="id_variasi" required class="select2 w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                        <option value="">-- Cari & Pilih Barang --</option>
                        <?php foreach ($produkList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['label']) ?> (Stok: <?= $p['stok'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcText mb-1.5">Jumlah Unit yang Diisolasi *</label>
                    <input type="number" name="qty" required min="1" placeholder="Contoh: 2"
                        class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcText mb-1.5">Alasan Karantina *</label>
                    <select name="alasan" required class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                        <option value="">-- Pilih Alasan --</option>
                        <option value="Cacat Produksi / Kemasan Rusak">Cacat Produksi / Kemasan Rusak</option>
                        <option value="Klaim Garansi Pelanggan">Klaim Garansi Pelanggan</option>
                        <option value="Expired / Kadaluarsa">Expired / Kadaluarsa</option>
                        <option value="Retur dari Toko Online">Retur dari Toko Online</option>
                        <option value="Pemeriksaan QC Internal">Pemeriksaan QC Internal</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-3 rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Karantinakan Barang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Karantina -->
    <div class="lg:col-span-8">
        <div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-zcBorder flex items-center gap-2">
                <svg class="w-4 h-4 text-zcMuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                <h3 class="text-sm font-bold text-zcText">Riwayat Gudang Karantina (20 Terbaru)</h3>
            </div>
            <?php if (empty($karantinaLog)): ?>
                <div class="p-10 text-center text-xs text-zcMuted italic">Gudang karantina kosong – semua barang dalam kondisi normal.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-zcBorder text-zcMuted font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Produk</th>
                                <th class="px-4 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-left">Alasan</th>
                                <th class="px-4 py-3 text-right">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBorder/60">
                            <?php foreach ($karantinaLog as $k): ?>
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-semibold text-zcText"><?= htmlspecialchars($k['nama']) ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-700 border border-amber-200 rounded-full font-bold text-[11px]"><?= $k['qty'] ?> unit</span>
                                    </td>
                                    <td class="px-4 py-3 text-zcMuted"><?= htmlspecialchars($k['alasan']) ?></td>
                                    <td class="px-4 py-3 text-right text-zcMuted"><?= date('d/m/Y H:i', strtotime($k['tanggal'])) ?></td>
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
function handleToggleSubmit(form, e, actionText, itemType) {
    if (e) {
        e.preventDefault();
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Konfirmasi ' + actionText,
            text: 'Apakah Anda yakin ingin ' + actionText.toLowerCase() + ' ' + itemType + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Pindahkan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }

    if (confirm(actionText + ' ' + itemType + '?')) {
        form.submit();
        return true;
    }
    return false;
}
</script>

<?php layoutEnd(); ?>
