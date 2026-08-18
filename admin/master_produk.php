<?php
// File: admin/master_produk.php
// Master Data Produk Induk & Variasi - Super Admin Only
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$msg = ''; $msgType = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // --- Tambah Produk Induk ---
    if ($aksi === 'tambah_induk') {
        $sku  = trim($_POST['sku_induk'] ?? '');
        $nama = trim($_POST['nama_produk'] ?? '');
        $kat  = trim($_POST['kategori'] ?? '');
        $desc = trim($_POST['deskripsi'] ?? '');
        $sup  = intval($_POST['id_supplier'] ?? 0) ?: null;
        if ($sku && $nama && $kat) {
            try {
                $pdo->prepare("INSERT INTO produk_induk (sku_induk,nama_produk,deskripsi,kategori,id_supplier,is_active) VALUES (?,?,?,?,?,1)")
                    ->execute([$sku,$nama,$desc,$kat,$sup]);
                $msg = "Produk induk '$nama' berhasil ditambahkan."; $msgType = 'success';
            } catch (Exception $e) { $msg = "Error: " . $e->getMessage(); $msgType = 'error'; }
        } else { $msg = "SKU, Nama Produk, dan Kategori wajib diisi!"; $msgType = 'error'; }
    }

    // --- Edit Produk Induk ---
    if ($aksi === 'edit_induk') {
        $id   = intval($_POST['id_induk'] ?? 0);
        $nama = trim($_POST['nama_produk'] ?? '');
        $kat  = trim($_POST['kategori'] ?? '');
        $desc = trim($_POST['deskripsi'] ?? '');
        $sup  = intval($_POST['id_supplier'] ?? 0) ?: null;
        if ($id && $nama) {
            $pdo->prepare("UPDATE produk_induk SET nama_produk=?,deskripsi=?,kategori=?,id_supplier=? WHERE id=?")
                ->execute([$nama,$desc,$kat,$sup,$id]);
            $msg = "Produk '$nama' diperbarui."; $msgType = 'success';
        }
    }

    // --- Soft Delete / Restore Produk Induk ---
    if ($aksi === 'toggle_induk') {
        $id = intval($_POST['id_induk'] ?? 0);
        $pdo->prepare("UPDATE produk_induk SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
        $msg = "Status produk diperbarui (soft delete)."; $msgType = 'info';
    }

    // --- Tambah Variasi ---
    if ($aksi === 'tambah_variasi') {
        $idInduk  = intval($_POST['id_produk_induk'] ?? 0);
        $sku      = trim($_POST['sku_variasi'] ?? '');
        $namaVar  = trim($_POST['nama_variasi'] ?? '');
        $satKecil = trim($_POST['satuan_kecil'] ?? 'Pcs');
        $satBesar = trim($_POST['satuan_besar'] ?? 'Box');
        $rasio    = intval($_POST['rasio_konversi'] ?? 1) ?: 1;
        $hrgKecil = floatval($_POST['harga_jual_kecil'] ?? 0);
        $hrgBesar = floatval($_POST['harga_jual_besar'] ?? 0);

        $berat    = intval($_POST['berat'] ?? 100);
        $gambar   = trim($_POST['gambar'] ?? '');
        $tampil   = isset($_POST['tampil_di_online']) ? 1 : 0;

        if ($idInduk && $sku && $namaVar && $hrgKecil > 0) {
            try {
                $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk,sku_variasi,nama_variasi,satuan_kecil,satuan_besar,rasio_konversi,harga_jual_kecil,harga_jual_besar,berat,gambar,tampil_di_online,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,1)")
                    ->execute([$idInduk,$sku,$namaVar,$satKecil,$satBesar,$rasio,$hrgKecil,$hrgBesar,$berat,$gambar,$tampil]);
                
                // Auto-init stok 0 untuk setiap cabang
                $cabangAll = $pdo->query("SELECT id FROM cabang WHERE is_active=1")->fetchAll();
                $newVarId  = $pdo->lastInsertId();
                foreach ($cabangAll as $c) {
                    $pdo->prepare("INSERT IGNORE INTO stok_cabang (id_variasi,id_cabang,stok) VALUES (?,?,0)")
                        ->execute([$newVarId, $c['id']]);
                }
                $msg = "Variasi '$namaVar' berhasil ditambahkan."; $msgType = 'success';
            } catch (Exception $e) { $msg = "Error: " . $e->getMessage(); $msgType = 'error'; }
        } else { $msg = "Semua field variasi wajib diisi!"; $msgType = 'error'; }
    }

    // --- Edit Variasi ---
    if ($aksi === 'edit_variasi') {
        $idVal = intval($_POST['id_variasi'] ?? 0);
        $sku   = trim($_POST['sku_variasi'] ?? '');
        $nama  = trim($_POST['nama_variasi'] ?? '');
        $satKecil = trim($_POST['satuan_kecil'] ?? 'Pcs');
        $satBesar = trim($_POST['satuan_besar'] ?? 'Box');
        $rasio    = intval($_POST['rasio_konversi'] ?? 1) ?: 1;
        $hrgKecil = floatval($_POST['harga_jual_kecil'] ?? 0);
        $hrgBesar = floatval($_POST['harga_jual_besar'] ?? 0);

        $berat = intval($_POST['berat'] ?? 100);
        $gambar = trim($_POST['gambar'] ?? '');
        $tampil = isset($_POST['tampil_di_online']) ? 1 : 0;

        if ($idVal && $sku && $nama && $hrgKecil > 0) {
            try {
                $pdo->prepare("UPDATE produk_variasi SET sku_variasi=?, nama_variasi=?, satuan_kecil=?, satuan_besar=?, rasio_konversi=?, harga_jual_kecil=?, harga_jual_besar=?, berat=?, gambar=?, tampil_di_online=? WHERE id=?")
                    ->execute([$sku, $nama, $satKecil, $satBesar, $rasio, $hrgKecil, $hrgBesar, $berat, $gambar, $tampil, $idVal]);
                $msg = "Variasi '$nama' berhasil diperbarui."; $msgType = 'success';
            } catch (Exception $e) { $msg = "Error: " . $e->getMessage(); $msgType = 'error'; }
        } else { $msg = "Semua field variasi wajib diisi!"; $msgType = 'error'; }
    }

    // --- Soft Delete Variasi ---
    if ($aksi === 'toggle_variasi') {
        $id = intval($_POST['id_variasi'] ?? 0);
        $pdo->prepare("UPDATE produk_variasi SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
        $msg = "Status variasi diperbarui."; $msgType = 'info';
    }
    // --- Import CSV ---
    if ($aksi === 'import_csv') {
        if (isset($_FILES['file_csv']) && $_FILES['file_csv']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['file_csv']['tmp_name'];
            $handle = fopen($fileTmp, "r");
            if ($handle !== FALSE) {
                $header = fgetcsv($handle, 1000, ","); // Skip header
                $successCount = 0;
                $pdo->beginTransaction();
                try {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (count($data) < 12) continue; // Skip malformed rows
                        
                        $skuInduk   = trim($data[0]);
                        $namaInduk  = trim($data[1]);
                        $kategori   = trim($data[2]);
                        $deskripsi  = trim($data[3]);
                        $skuVar     = trim($data[4]);
                        $namaVar    = trim($data[5]);
                        $satKecil   = trim($data[6]) ?: 'Pcs';
                        $satBesar   = trim($data[7]) ?: 'Box';
                        $rasio      = intval($data[8]) ?: 1;
                        $hrgKecil   = floatval($data[9]);
                        $hrgBesar   = floatval($data[10]);
                        $berat      = intval($data[11]) ?: 100;
                        $cabangId   = intval($data[12]);
                        $stokAwal   = intval($data[13]);

                        if (empty($skuInduk) || empty($skuVar)) continue;

                        // Check/Insert Induk
                        $stmtInduk = $pdo->prepare("SELECT id FROM produk_induk WHERE sku_induk=?");
                        $stmtInduk->execute([$skuInduk]);
                        $idInduk = $stmtInduk->fetchColumn();
                        if (!$idInduk) {
                            $pdo->prepare("INSERT INTO produk_induk (sku_induk,nama_produk,deskripsi,kategori,is_active) VALUES (?,?,?,?,1)")->execute([$skuInduk, $namaInduk, $deskripsi, $kategori]);
                            $idInduk = $pdo->lastInsertId();
                        }

                        // Check/Insert Variasi
                        $stmtVar = $pdo->prepare("SELECT id FROM produk_variasi WHERE sku_variasi=?");
                        $stmtVar->execute([$skuVar]);
                        $idVar = $stmtVar->fetchColumn();
                        if (!$idVar) {
                            $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk,sku_variasi,nama_variasi,satuan_kecil,satuan_besar,rasio_konversi,harga_jual_kecil,harga_jual_besar,berat,tampil_di_online,is_active) VALUES (?,?,?,?,?,?,?,?,?,1,1)")
                                ->execute([$idInduk, $skuVar, $namaVar, $satKecil, $satBesar, $rasio, $hrgKecil, $hrgBesar, $berat]);
                            $idVar = $pdo->lastInsertId();

                            // Initialize zero stock for all branches
                            $cabangAll = $pdo->query("SELECT id FROM cabang WHERE is_active=1")->fetchAll();
                            foreach ($cabangAll as $c) {
                                $pdo->prepare("INSERT IGNORE INTO stok_cabang (id_variasi,id_cabang,stok) VALUES (?,?,0)")->execute([$idVar, $c['id']]);
                            }
                        } else {
                            // Update existing variasi prices
                            $pdo->prepare("UPDATE produk_variasi SET satuan_kecil=?, satuan_besar=?, rasio_konversi=?, harga_jual_kecil=?, harga_jual_besar=? WHERE id=?")->execute([$satKecil, $satBesar, $rasio, $hrgKecil, $hrgBesar, $idVar]);
                        }

                        // Initial Stock Mutation
                        if ($cabangId > 0 && $stokAwal > 0) {
                            $chkStock = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=? FOR UPDATE");
                            $chkStock->execute([$idVar, $cabangId]);
                            $currentStock = $chkStock->fetchColumn();
                            
                            if ($currentStock === false) {
                                $pdo->prepare("INSERT INTO stok_cabang (id_variasi,id_cabang,stok) VALUES (?,?,?)")->execute([$idVar, $cabangId, $stokAwal]);
                                $currentStock = 0;
                            } else {
                                $pdo->prepare("UPDATE stok_cabang SET stok = stok + ? WHERE id_variasi=? AND id_cabang=?")->execute([$stokAwal, $idVar, $cabangId]);
                            }

                            $sisaStock = $currentStock + $stokAwal;
                            $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Masuk',?,?,?)")
                                ->execute([$cabangId, $idVar, $stokAwal, $sisaStock, "Import Excel / Stok Awal"]);
                        }
                        
                        $successCount++;
                    }
                    $pdo->commit();
                    $msg = "Import berhasil! $successCount baris data produk diproses."; $msgType = 'success';
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $msg = "Gagal Import: " . $e->getMessage(); $msgType = 'error';
                }
                fclose($handle);
            }
        } else {
            $msg = "Gagal upload file CSV."; $msgType = 'error';
        }
    }
}

// Fetch data
$produkInduk = $pdo->query("SELECT pi.*, s.nama AS nama_supplier FROM produk_induk pi LEFT JOIN supplier s ON pi.id_supplier=s.id ORDER BY pi.id DESC")->fetchAll();
$supplierList = $pdo->query("SELECT id, nama FROM supplier WHERE is_active=1 ORDER BY nama ASC")->fetchAll();

layoutHead('Master Produk');
layoutBodyOpen();
layoutSidebar('master_produk');
layoutHeader('Master Produk & Variasi', 'Kelola data produk induk dan variasi alkes/obat (Super Admin Only)');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-sky-50 border-sky-200 text-sky-800') ?>">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- ACTION HEADER -->
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h2 class="text-base font-bold text-zcTxt">Daftar Produk Induk</h2>
        <p class="text-xs text-zcMut mt-0.5">Total: <?= count($produkInduk) ?> produk &bull; Klik &blacktriangledown; untuk lihat variasi & tambah variasi</p>
    </div>
    <div class="flex gap-2">
        <button onclick="document.getElementById('modal_import_csv').classList.remove('hidden')"
            class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl transition shadow-sm">
            <?= icon('download', 'w-4 h-4') ?> Import CSV
        </button>
        <button onclick="document.getElementById('modal_tambah_induk').classList.remove('hidden')"
            class="flex items-center gap-2 px-4 py-2.5 bg-zc hover:bg-zcHv text-white text-xs font-bold rounded-xl transition shadow-sm">
            + Tambah Produk Induk
        </button>
    </div>
</div>

<!-- PRODUK LIST ACCORDION -->
<div class="space-y-3">
<?php foreach ($produkInduk as $pi): ?>
    <?php
    $variasiList = $pdo->prepare("SELECT * FROM produk_variasi WHERE id_produk_induk=? ORDER BY id ASC");
    $variasiList->execute([$pi['id']]);
    $variasiList = $variasiList->fetchAll();
    $aktif = $pi['is_active'] ? true : false;
    ?>
    <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden <?= !$aktif ? 'opacity-60' : '' ?>">
        <!-- Header row -->
        <div class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition"
             onclick="toggleAcc('acc_<?= $pi['id'] ?>')">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-zcMut"><?= icon('pill') ?></span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-bold text-zcTxt"><?= htmlspecialchars($pi['nama_produk']) ?></span>
                        <span class="px-2 py-0.5 bg-slate-100 border border-zcBrd rounded-lg text-[10px] font-semibold"><?= htmlspecialchars($pi['kategori']) ?></span>
                        <?= !$aktif ? '<span class="px-2 py-0.5 bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-bold">NONAKTIF</span>' : '' ?>
                    </div>
                    <div class="text-[11px] text-zcMut mt-0.5">
                        SKU: <code class="font-mono"><?= htmlspecialchars($pi['sku_induk']) ?></code>
                        &bull; Supplier: <strong><?= htmlspecialchars($pi['nama_supplier'] ?? '–') ?></strong>
                        &bull; <?= count($variasiList) ?> Variasi
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <!-- Edit -->
                <button onclick="event.stopPropagation(); openEditInduk(<?= $pi['id'] ?>, '<?= addslashes($pi['nama_produk']) ?>', '<?= addslashes($pi['kategori']) ?>', '<?= addslashes($pi['deskripsi'] ?? '') ?>', <?= $pi['id_supplier'] ?? 'null' ?>)"
                    class="px-3 py-1.5 text-[11px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl transition">Edit</button>
                <!-- Toggle -->
                <form method="POST" onsubmit="return confirm('<?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?> produk ini?')" class="inline">
                    <input type="hidden" name="aksi" value="toggle_induk">
                    <input type="hidden" name="id_induk" value="<?= $pi['id'] ?>">
                    <button type="submit" onclick="event.stopPropagation();" class="px-3 py-1.5 text-[11px] font-bold <?= $aktif ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200' ?> border rounded-xl transition">
                        <?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                    </button>
                </form>
                <span class="text-zcMut text-lg">&#9660;</span>
            </div>
        </div>

        <!-- Accordion Content: Variasi -->
        <div id="acc_<?= $pi['id'] ?>" class="hidden border-t border-zcBrd bg-slate-50">
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-zcTxt">Daftar Variasi</span>
                    <button onclick="openTambahVariasi(<?= $pi['id'] ?>, '<?= addslashes($pi['nama_produk']) ?>')"
                        class="text-[11px] font-bold px-3 py-1.5 bg-zc hover:bg-zcHv text-white rounded-xl transition">+ Tambah Variasi</button>
                </div>
                <?php if (empty($variasiList)): ?>
                    <p class="text-xs text-zcMut italic py-4 text-center">Belum ada variasi. Tambahkan variasi untuk produk ini.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="text-zcMut uppercase tracking-wider font-bold border-b border-zcBrd">
                                <tr>
                                    <th class="py-2 px-3 text-left">SKU / Nama</th>
                                    <th class="py-2 px-3 text-center">UOM Konversi</th>
                                    <th class="py-2 px-3 text-right">Harga Ecer (POS)</th>
                                    <th class="py-2 px-3 text-right">Harga Grosir (Online)</th>
                                    <th class="py-2 px-3 text-center">Tampil E-Comm</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                    <th class="py-2 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zcBorder/50">
                                <?php foreach ($variasiList as $v): ?>
                                    <tr class="<?= !$v['is_active'] ? 'opacity-50' : '' ?>">
                                        <td class="py-2.5 px-3">
                                            <div class="font-mono text-zcMut text-[10px]"><?= htmlspecialchars($v['sku_variasi']) ?></div>
                                            <div class="font-semibold text-zcTxt text-xs"><?= htmlspecialchars($v['nama_variasi']) ?></div>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <div class="text-[10px] text-zcMut">1 <?= htmlspecialchars($v['satuan_besar']) ?> = <?= $v['rasio_konversi'] ?> <?= htmlspecialchars($v['satuan_kecil']) ?></div>
                                        </td>
                                        <td class="py-2.5 px-3 text-right font-bold text-slate-700">Rp <?= number_format($v['harga_jual_kecil'], 0, ',', '.') ?> <span class="text-[9px] font-normal text-zcMut">/ <?= htmlspecialchars($v['satuan_kecil']) ?></span></td>
                                        <td class="py-2.5 px-3 text-right font-bold text-emerald-600">
                                            Rp <?= number_format($v['harga_jual_besar'], 0, ',', '.') ?> <span class="text-[9px] font-normal text-emerald-800/60">/ <?= htmlspecialchars($v['satuan_besar']) ?></span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $v['tampil_di_online'] ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-500' ?>">
                                                <?= $v['tampil_di_online'] ? 'Ya' : 'Tidak (POS Only)' ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $v['is_active'] ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200' ?>">
                                                <?= $v['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center space-x-1">
                                            <button onclick="openEditVariasi(<?= $v['id'] ?>, '<?= htmlspecialchars($v['sku_variasi']) ?>', '<?= htmlspecialchars($v['nama_variasi']) ?>', '<?= htmlspecialchars($v['satuan_kecil']) ?>', '<?= htmlspecialchars($v['satuan_besar']) ?>', <?= $v['rasio_konversi'] ?>, <?= $v['harga_jual_kecil'] ?>, <?= $v['harga_jual_besar'] ?>, <?= $v['berat'] ?>, '<?= htmlspecialchars($v['gambar'] ?? '') ?>', <?= $v['tampil_di_online'] ?>)" 
                                                    class="text-[10px] font-bold px-2 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded-lg">
                                                Edit
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirm('Toggle status variasi ini?')">
                                                <input type="hidden" name="aksi" value="toggle_variasi">
                                                <input type="hidden" name="id_variasi" value="<?= $v['id'] ?>">
                                                <button type="submit" class="text-[10px] font-bold px-2 py-1 <?= $v['is_active'] ? 'text-rose-600 bg-rose-50 border-rose-200' : 'text-emerald-600 bg-emerald-50 border-emerald-200' ?> border rounded-lg transition">
                                                    <?= $v['is_active'] ? 'Nonaktif' : 'Aktif' ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- ============================================================ -->
<!-- MODAL: Tambah Produk Induk                                    -->
<!-- ============================================================ -->
<div id="modal_tambah_induk" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd">
            <h3 class="text-sm font-bold text-zcTxt">Tambah Produk Induk Baru</h3>
            <button onclick="document.getElementById('modal_tambah_induk').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="tambah_induk">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">SKU Induk *</label>
                    <input type="text" name="sku_induk" required placeholder="OBT-2026-0004-IND" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Kategori *</label>
                    <select name="kategori" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="">-- Pilih --</option>
                        <option value="Obat-obatan">Obat-obatan</option>
                        <option value="Alat Monitor">Alat Monitor</option>
                        <option value="Alat Bantu Jalan">Alat Bantu Jalan</option>
                        <option value="Perawatan Luka">Perawatan Luka</option>
                        <option value="Suplemen">Suplemen</option>
                        <option value="Alat Bedah">Alat Bedah</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Produk *</label>
                <input type="text" name="nama_produk" required placeholder="Contoh: Masker N95 3M" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" rows="2" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50" placeholder="Keterangan singkat produk..."></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Supplier</label>
                <select name="id_supplier" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <option value="">-- Pilih Supplier (Opsional) --</option>
                    <?php foreach ($supplierList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_tambah_induk').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Produk Induk -->
<div id="modal_edit_induk" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd">
            <h3 class="text-sm font-bold text-zcTxt">Edit Produk Induk</h3>
            <button onclick="document.getElementById('modal_edit_induk').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="edit_induk">
            <input type="hidden" name="id_induk" id="edit_id_induk">
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Produk *</label>
                <input type="text" name="nama_produk" id="edit_nama_produk" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Kategori *</label>
                    <select name="kategori" id="edit_kategori" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="Obat-obatan">Obat-obatan</option>
                        <option value="Alat Monitor">Alat Monitor</option>
                        <option value="Alat Bantu Jalan">Alat Bantu Jalan</option>
                        <option value="Perawatan Luka">Perawatan Luka</option>
                        <option value="Suplemen">Suplemen</option>
                        <option value="Alat Bedah">Alat Bedah</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Supplier</label>
                    <select name="id_supplier" id="edit_supplier" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="">- Tanpa Supplier -</option>
                        <?php foreach ($supplierList as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_edit_induk').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Tambah Variasi -->
<div id="modal_tambah_variasi" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd">
            <h3 class="text-sm font-bold text-zcTxt">Tambah Variasi - <span id="var_parent_name" class="text-zcNavy"></span></h3>
            <button onclick="document.getElementById('modal_tambah_variasi').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="tambah_variasi">
            <input type="hidden" name="id_produk_induk" id="var_id_induk">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">SKU Variasi *</label>
                    <input type="text" name="sku_variasi" required placeholder="OBT-2026-0004-100ML" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Variasi *</label>
                    <input type="text" name="nama_variasi" required placeholder="100ml / Merah / etc" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Satuan Kecil (Ecer) *</label>
                    <input type="text" name="satuan_kecil" required placeholder="Pcs/Strip" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Satuan Besar (Grosir) *</label>
                    <input type="text" name="satuan_besar" required placeholder="Box/Karton" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Rasio (1 Besar = ? Kecil)</label>
                    <input type="number" name="rasio_konversi" value="1" min="1" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Harga Jual Kecil (Ecer) *</label>
                    <input type="number" name="harga_jual_kecil" required min="0" step="500" placeholder="5000" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Harga Jual Besar (Grosir) *</label>
                    <input type="number" name="harga_jual_besar" required min="0" step="500" placeholder="450000" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Berat (gram) *</label>
                    <input type="number" name="berat" required min="1" placeholder="250" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Tampilkan di E-Commerce?</label>
                    <label class="inline-flex items-center mt-2.5">
                        <input type="checkbox" name="tampil_di_online" value="1" checked class="rounded border-slate-300 text-zc focus:ring-zc h-4 w-4">
                        <span class="ml-2 text-xs font-medium text-slate-700">Tampilkan Online</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">URL Gambar Produk</label>
                <input type="text" name="gambar" placeholder="https://..." class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_tambah_variasi').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zcEm hover:bg-emerald-700 text-white rounded-xl transition shadow-sm">Simpan Variasi</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Edit Variasi -->
<div id="modal_edit_variasi" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd">
            <h3 class="text-sm font-bold text-zcTxt">Edit Variasi</h3>
            <button onclick="document.getElementById('modal_edit_variasi').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        <form method="POST" class="p-6 space-y-4">
            <input type="hidden" name="aksi" value="edit_variasi">
            <input type="hidden" name="id_variasi" id="edit_id_variasi">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">SKU Variasi *</label>
                    <input type="text" name="sku_variasi" id="edit_sku_variasi" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Variasi *</label>
                    <input type="text" name="nama_variasi" id="edit_nama_variasi" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Satuan Kecil (Ecer) *</label>
                    <input type="text" name="satuan_kecil" id="edit_satuan_kecil" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Satuan Besar (Grosir) *</label>
                    <input type="text" name="satuan_besar" id="edit_satuan_besar" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Rasio (1 Besar = ? Kecil)</label>
                    <input type="number" name="rasio_konversi" id="edit_rasio_konversi" min="1" required class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Harga Jual Kecil (Ecer) *</label>
                    <input type="number" name="harga_jual_kecil" id="edit_harga_jual_kecil" required min="0" step="500" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Harga Jual Besar (Grosir) *</label>
                    <input type="number" name="harga_jual_besar" id="edit_harga_jual_besar" required min="0" step="500" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Berat (gram) *</label>
                    <input type="number" name="berat" id="edit_berat_variasi" required min="1" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Tampilkan di E-Commerce?</label>
                    <label class="inline-flex items-center mt-2.5">
                        <input type="checkbox" name="tampil_di_online" id="edit_tampil_di_online" value="1" class="rounded border-slate-300 text-zc focus:ring-zc h-4 w-4">
                        <span class="ml-2 text-xs font-medium text-slate-700">Tampilkan Online</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">URL Gambar Produk</label>
                <input type="text" name="gambar" id="edit_gambar_variasi" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_edit_variasi').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPORT CSV -->
<div id="modal_import_csv" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">
        <h3 class="text-sm font-bold text-zcTxt mb-4 flex items-center gap-2">
            <?= icon('download', 'w-5 h-5 text-emerald-600') ?> Import Produk & Variasi (CSV)
        </h3>
        <p class="text-xs text-zcMut mb-4">
            Upload file CSV. Kolom wajib: <code>SKU_INDUK, NAMA_PRODUK, KATEGORI, DESKRIPSI, SKU_VARIASI, NAMA_VARIASI, SATUAN_KECIL, SATUAN_BESAR, RASIO, HARGA_KECIL, HARGA_BESAR, BERAT, CABANG_ID, STOK_AWAL</code>.
        </p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="import_csv">
            <div class="mb-4">
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Pilih File (.csv) *</label>
                <input type="file" name="file_csv" accept=".csv" required class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 bg-slate-50 focus:outline-none focus:border-zc">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal_import_csv').classList.add('hidden')" class="px-5 py-2 text-xs font-bold text-zcMut hover:bg-slate-100 rounded-xl transition">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-sm">Upload & Proses</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAcc(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
function openEditInduk(id, nama, kat, desc, supId) {
    document.getElementById('edit_id_induk').value = id;
    document.getElementById('edit_nama_produk').value = nama;
    document.getElementById('edit_deskripsi').value = desc;
    document.getElementById('edit_kategori').value = kat;
    if (supId) document.getElementById('edit_supplier').value = supId;
    document.getElementById('modal_edit_induk').classList.remove('hidden');
}
function openTambahVariasi(idInduk, namaInduk) {
    document.getElementById('var_id_induk').value = idInduk;
    document.getElementById('var_parent_name').innerText = namaInduk;
    document.getElementById('modal_tambah_variasi').classList.remove('hidden');
}
function openEditVariasi(id, sku, nama, satKecil, satBesar, rasio, hrgKecil, hrgBesar, berat, gambar, tampil) {
    document.getElementById('edit_id_variasi').value = id;
    document.getElementById('edit_sku_variasi').value = sku;
    document.getElementById('edit_nama_variasi').value = nama;
    document.getElementById('edit_satuan_kecil').value = satKecil;
    document.getElementById('edit_satuan_besar').value = satBesar;
    document.getElementById('edit_rasio_konversi').value = rasio;
    document.getElementById('edit_harga_jual_kecil').value = hrgKecil;
    document.getElementById('edit_harga_jual_besar').value = hrgBesar;
    document.getElementById('edit_berat_variasi').value = berat;
    document.getElementById('edit_gambar_variasi').value = gambar;
    document.getElementById('edit_tampil_di_online').checked = (tampil == 1);
    document.getElementById('modal_edit_variasi').classList.remove('hidden');
}
</script>

<?php layoutEnd(); ?>

