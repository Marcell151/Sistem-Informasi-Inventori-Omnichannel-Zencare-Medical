<?php
// File: inventori/proses_mutasi.php
// Modul Mutasi Stok (Penyesuaian Manual) - ZenCare Medical
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = '';
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idVariasi   = intval($_POST['id_variasi'] ?? 0);
    $jenisMutasi = $_POST['jenis_mutasi'] ?? '';
    $qty         = intval($_POST['qty'] ?? 0);
    $alasan      = trim($_POST['alasan'] ?? '');
    $catatan     = trim($_POST['catatan'] ?? '');
    
    if (!$idVariasi || !in_array($jenisMutasi, ['Penambahan', 'Pengurangan']) || $qty <= 0 || !$alasan) {
        $msg = "Semua field wajib diisi dan Qty harus > 0!"; $msgType = 'error';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Update stok fisik produk
            $operator = $jenisMutasi === 'Penambahan' ? '+' : '-';
            $stmtStok = $pdo->prepare("UPDATE stok_toko SET stok = stok $operator ? WHERE id_variasi = ?");
            $stmtStok->execute([$qty, $idVariasi]);
            
            // Catat di kartu stok
            $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();
            
            $jMutasi = $jenisMutasi === 'Penambahan' ? 'Masuk' : 'Keluar';
            
            $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Manual', 'Koreksi Manual', ?, ?, ?, ?)");
            $stmtKartu->execute([$idVariasi, $jMutasi, $qty, $sisaStok, $catatan, $userId]);

            $pdo->commit();
            $msg = "Berhasil mencatat $jenisMutasi sebanyak $qty unit.";
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal memproses mutasi: ' . $e->getMessage();
            $msgType = 'error';
        }
    }
}

// Fetch produk untuk dropdown
$produkList = $pdo->query("
    SELECT pv.id, CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS label, pi.kategori
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pv.is_active = 1 
    ORDER BY pi.nama_produk ASC
")->fetchAll();

// Ambil riwayat mutasi terbaru dari kartu_stok
$mutasiLog = $pdo->query("
    SELECT ks.tanggal AS created_at, ks.jenis_mutasi, ks.qty, ks.sisa_stok, ks.keterangan AS alasan,
           CONCAT(pi.nama_produk, ' — ', pv.nama_variasi) AS nama_item,
           u.nama_lengkap AS pembuat
    FROM kartu_stok ks
    JOIN produk_variasi pv ON pv.id = ks.id_variasi
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk
    LEFT JOIN users u ON u.id = ks.dibuat_oleh
    WHERE ks.kanal = 'Manual'
    ORDER BY ks.tanggal DESC
    LIMIT 20
")->fetchAll();

layoutHead('Mutasi Stok');
layoutBodyOpen();
layoutSidebar('mutasi_stok');
layoutHeader('Mutasi Stok Manual', 'Penyesuaian stok gudang karena selisih, retur, atau barang rusak.');
?>

<?php if ($msg): ?>
<div class="mb-5 flex items-start gap-3 p-3.5 rounded-xl text-sm font-medium border <?= $msgType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?>">
    <svg class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
    <span><?= htmlspecialchars($msg) ?></span>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Form Mutasi -->
    <div class="lg:col-span-5">
        <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm sticky top-24">
            <h3 class="text-lg font-extrabold text-zcTxt mb-1">Form Penyesuaian Stok</h3>
            <p class="text-xs text-zcMut mb-5">Hanya gunakan ini jika ada perubahan fisik stok tanpa transaksi POS/Pesanan.</p>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-zcTxt mb-1.5">Pilih Produk *</label>
                    <select name="id_variasi" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($produkList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-zcTxt mb-1.5">Arah Mutasi *</label>
                        <select name="jenis_mutasi" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                            <option value="">-- Arah --</option>
                            <option value="Penambahan">Tambah Stok (+)</option>
                            <option value="Pengurangan">Kurangi Stok (-)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zcTxt mb-1.5">Qty Fisik *</label>
                        <input type="number" name="qty" required min="1" placeholder="Contoh: 5"
                            class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zcTxt mb-1.5">Alasan *</label>
                    <select name="alasan" required class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="">-- Pilih Alasan --</option>
                        <option value="Selisih Stock Opname">Selisih Stock Opname</option>
                        <option value="Barang Rusak / Kadaluwarsa">Barang Rusak / Kadaluwarsa</option>
                        <option value="Retur ke Supplier">Retur ke Supplier</option>
                        <option value="Klaim Garansi">Klaim Garansi Pelanggan</option>
                        <option value="Sampel / Bonus">Sampel / Penggunaan Internal</option>
                        <option value="Lainnya">Lainnya...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-zcTxt mb-1.5">Keterangan Opsional</label>
                    <textarea name="catatan" rows="2" placeholder="Detail tambahan..." class="w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50"></textarea>
                </div>

                <button type="submit" class="w-full bg-zc hover:bg-zcHv text-white font-bold text-sm py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer active:scale-[.98]">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10M18 20V4M6 20v-4"/></svg>
                    <span>Simpan Penyesuaian Stok</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Log Mutasi -->
    <div class="lg:col-span-7">
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-zcBrd flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-zcTxt">Riwayat Penyesuaian Manual</h3>
                    <p class="text-xs text-zcMut">20 aktivitas terakhir di gudang</p>
                </div>
            </div>
            
            <?php if (empty($mutasiLog)): ?>
                <div class="p-10 text-center text-sm text-zcMut italic">Belum ada riwayat mutasi stok.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-zcBrd text-zcMut font-bold">
                            <tr>
                                <th class="px-4 py-3 text-left">Waktu</th>
                                <th class="px-4 py-3 text-left">Produk</th>
                                <th class="px-4 py-3 text-left">Jenis</th>
                                <th class="px-4 py-3 text-left">Alasan</th>
                                <th class="px-4 py-3 text-right">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBrd/60">
                            <?php foreach ($mutasiLog as $m): ?>
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-4 py-3 text-zcMut font-mono"><?= date('d/m/y H:i', strtotime($m['created_at'])) ?></td>
                                    <td class="px-4 py-3 font-semibold text-zcTxt"><?= htmlspecialchars($m['nama_item']) ?></td>
                                    <td class="px-4 py-3">
                                        <?php if ($m['jenis_mutasi'] === 'Penambahan'): ?>
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md font-bold text-[10px]">+ <?= $m['qty'] ?> unit</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-md font-bold text-[10px]">- <?= $m['qty'] ?> unit</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-zcTxt text-[11px]">
                                        <div class="font-bold"><?= htmlspecialchars($m['alasan']) ?></div>
                                        <?php if ($m['catatan']): ?>
                                            <div class="text-zcMut italic"><?= htmlspecialchars($m['catatan']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right text-zcMut text-[11px]"><?= htmlspecialchars($m['pembuat']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php layoutFooter(); ?>
