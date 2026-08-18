<?php
// File: inventori/karantina.php
// Gudang Karantina Barang Cacat / Garansi – Admin & Kasir
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'kasir']);

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
            $stokQ = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=? FOR UPDATE");
            $stokQ->execute([$idVariasi, $activeCabang]);
            $stok = $stokQ->fetchColumn();

            if ($stok === false || intval($stok) < $qty) {
                throw new Exception("Stok tidak mencukupi untuk dikarantina! Tersisa: " . ($stok ?? 0));
            }

            $pdo->prepare("UPDATE stok_cabang SET stok=stok-? WHERE id_variasi=? AND id_cabang=?")->execute([$qty, $idVariasi, $activeCabang]);
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
    LEFT JOIN stok_cabang sc ON sc.id_variasi=v.id AND sc.id_cabang=?
    WHERE v.is_active=1 AND i.is_active=1 ORDER BY i.nama_produk ASC");
$produkList->execute([$activeCabang]);
$produkList = $produkList->fetchAll();

$karantinaLog = $pdo->prepare("
    SELECT gk.*, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS nama
    FROM gudang_karantina gk JOIN produk_variasi v ON gk.id_variasi=v.id
    JOIN produk_induk i ON v.id_produk_induk=i.id
    WHERE gk.id_cabang=? ORDER BY gk.tanggal DESC LIMIT 20");
$karantinaLog->execute([$activeCabang]);
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
                <span class="text-base">⚠️</span> Form Isolasi Barang
            </h2>

            <?php if ($msg): ?>
                <div class="mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                    <?= $msgType === 'success' ? '✅' : '⛔' ?> <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <!-- Summary Badge -->
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs">
                <span class="text-amber-800 font-semibold">Total unit dikarantina:</span>
                <span class="font-bold text-amber-900 text-base"><?= number_format($totalKarantina) ?> unit</span>
            </div>

            <form method="POST" class="space-y-4" onsubmit="return confirm('Pindahkan barang ke karantina? Stok akan dikurangi.')">
                <div>
                    <label class="block text-xs font-bold text-zcText mb-1.5">Pilih Produk *</label>
                    <select name="id_variasi" required class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                        <option value="">-- Pilih Barang --</option>
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
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-3 rounded-xl transition shadow-sm">
                    ⚠️ Karantinakan Barang
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Karantina -->
    <div class="lg:col-span-8">
        <div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-zcBorder">
                <h3 class="text-sm font-bold text-zcText">📋 Riwayat Gudang Karantina (20 Terbaru)</h3>
                <p class="text-[11px] text-zcMuted mt-0.5">Barang terisolasi yang belum dikembalikan/dihapuskan</p>
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

<?php layoutEnd(); ?>
