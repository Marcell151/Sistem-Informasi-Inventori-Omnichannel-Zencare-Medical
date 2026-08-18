<?php
// File: inventori/proses_mutasi.php
// Modul Mutasi Stok Antar Cabang – Admin & Kasir
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'kasir']);

$msg = ''; $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVariasi    = intval($_POST['id_variasi'] ?? 0);
    $cabangAsal   = intval($_POST['cabang_asal'] ?? 0);
    $cabangTujuan = intval($_POST['cabang_tujuan'] ?? 0);
    $qty          = intval($_POST['qty'] ?? 0);

    if (!$idVariasi || !$cabangAsal || !$cabangTujuan || $qty <= 0) {
        $msg = "Semua field wajib diisi dan qty harus > 0!"; $msgType = 'error';
    } elseif ($cabangAsal === $cabangTujuan) {
        $msg = "Cabang asal dan tujuan tidak boleh sama!"; $msgType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $stokAsalQ = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=? FOR UPDATE");
            $stokAsalQ->execute([$idVariasi, $cabangAsal]);
            $stokAsal = $stokAsalQ->fetchColumn();

            if ($stokAsal === false || intval($stokAsal) < $qty) {
                throw new Exception("Stok di cabang asal tidak mencukupi! Tersisa: " . ($stokAsal ?? 0) . " unit.");
            }

            // Kurangi stok cabang asal
            $pdo->prepare("UPDATE stok_cabang SET stok=stok-? WHERE id_variasi=? AND id_cabang=?")->execute([$qty, $idVariasi, $cabangAsal]);
            $sisaAsal = intval($stokAsal) - $qty;
            $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Keluar',?,?,?)")
                ->execute([$cabangAsal, $idVariasi, $qty, $sisaAsal, "Mutasi Stok Keluar → Cabang ID $cabangTujuan"]);

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
                ->execute([$cabangTujuan, $idVariasi, $qty, $sisaTujuan, "Mutasi Stok Masuk ← Cabang ID $cabangAsal"]);

            // Catat di mutasi_stok
            $pdo->prepare("INSERT INTO mutasi_stok (id_variasi,cabang_asal,cabang_tujuan,qty) VALUES (?,?,?,?)")->execute([$idVariasi, $cabangAsal, $cabangTujuan, $qty]);

            $pdo->commit();
            $msg = "Mutasi berhasil: $qty unit dipindahkan dari Cabang #$cabangAsal ke Cabang #$cabangTujuan.";
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal mutasi: " . $e->getMessage(); $msgType = 'error';
        }
    }
}

$cabangList = $pdo->query("SELECT * FROM cabang WHERE is_active=1 ORDER BY id ASC")->fetchAll();
$produkList = $pdo->query("
    SELECT v.id, CONCAT(i.nama_produk,' - ',v.nama_variasi) AS label, v.sku_variasi
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

<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

    <!-- Form Mutasi -->
    <div class="lg:col-span-5">
        <div class="bg-white border border-zcBorder rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-bold text-zcText mb-4 pb-3 border-b border-zcBorder">📦 Form Transfer Stok</h2>

            <?php if ($msg): ?>
                <div class="mb-4 p-3.5 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
                    <?= $msgType === 'success' ? '✅' : '⛔' ?> <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4" onsubmit="return confirm('Konfirmasi mutasi stok?')">
                <div>
                    <label class="block text-xs font-bold text-zcText mb-1.5">Produk / Variasi *</label>
                    <select name="id_variasi" required class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($produkList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['label']) ?> (<?= $p['sku_variasi'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-zcText mb-1.5">Cabang Asal *</label>
                        <select name="cabang_asal" required class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                            <option value="">-- Asal --</option>
                            <?php foreach ($cabangList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zcText mb-1.5">Cabang Tujuan *</label>
                        <select name="cabang_tujuan" required class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                            <option value="">-- Tujuan --</option>
                            <?php foreach ($cabangList as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcText mb-1.5">Jumlah Unit yang Dimutasi *</label>
                    <input type="number" name="qty" required min="1" placeholder="Contoh: 10"
                        class="w-full text-xs border border-zcBorder rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zcNavy bg-slate-50">
                </div>
                <button type="submit" class="w-full bg-zcNavy hover:bg-zcNavyHv text-white font-bold text-xs py-3 rounded-xl transition shadow-sm">
                    ↔️ Proses Mutasi Stok Antar Cabang
                </button>
            </form>
        </div>
    </div>

    <!-- Log Mutasi -->
    <div class="lg:col-span-7">
        <div class="bg-white border border-zcBorder rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-zcBorder">
                <h3 class="text-sm font-bold text-zcText">⚡ Riwayat Mutasi Stok (10 Terbaru)</h3>
            </div>
            <?php if (empty($mutasiLog)): ?>
                <div class="p-10 text-center text-xs text-zcMuted italic">Belum ada riwayat mutasi stok antar cabang.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-zcBorder text-zcMuted font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Produk</th>
                                <th class="px-4 py-3 text-left">Asal → Tujuan</th>
                                <th class="px-4 py-3 text-center">Qty</th>
                                <th class="px-4 py-3 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBorder/60">
                            <?php foreach ($mutasiLog as $m): ?>
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-semibold text-zcText"><?= htmlspecialchars($m['nama_item']) ?></td>
                                    <td class="px-4 py-3 text-zcMuted">
                                        <span class="font-semibold text-rose-600"><?= htmlspecialchars($m['nama_asal']) ?></span>
                                        <span class="mx-1">→</span>
                                        <span class="font-semibold text-emerald-600"><?= htmlspecialchars($m['nama_tujuan']) ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 bg-sky-100 text-sky-700 border border-sky-200 rounded-full font-bold text-[11px]"><?= $m['qty'] ?> unit</span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-zcMuted"><?= date('d/m/Y H:i', strtotime($m['tanggal'])) ?></td>
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
