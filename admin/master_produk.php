<?php
// File: admin/master_produk.php
// Master Data Produk Induk & Variasi
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

// --- AJAX Handler for Cetak Barcode ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_barcode_list') {
    $idVariasi = intval($_GET['id_variasi'] ?? 0);
    $batches = $pdo->prepare("SELECT id, no_batch, tgl_exp FROM stok_batch WHERE id_variasi = ? AND stok_sisa > 0 ORDER BY tgl_exp ASC");
    $batches->execute([$idVariasi]);
    $batchList = $batches->fetchAll(PDO::FETCH_ASSOC);
    
    $sns = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id_variasi = ? AND status = 'Tersedia'");
    $sns->execute([$idVariasi]);
    $snList = $sns->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['batch' => $batchList, 'sn' => $snList]);
    exit;
}

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SESSION['role'] ?? '') !== 'superadmin') {
        die("Hanya Superadmin yang dapat mengubah data master produk dan harga.");
    }

    $aksi = $_POST['aksi'] ?? '';

    // --- Tambah Produk Induk ---
    if ($aksi === 'tambah_induk') {
        $sku  = trim($_POST['sku_induk'] ?? '');
        $nama = trim($_POST['nama_produk'] ?? '');
        $kat  = trim($_POST['kategori'] ?? '');
        $desc = trim($_POST['deskripsi'] ?? '');
        if ($sku && $nama && $kat) {
            try {
                $pdo->prepare("INSERT INTO produk_induk (sku_induk,nama_produk,deskripsi,kategori,is_active) VALUES (?,?,?,?,1)")
                    ->execute([$sku,$nama,$desc,$kat]);
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
        if ($id && $nama) {
            $pdo->prepare("UPDATE produk_induk SET nama_produk=?,deskripsi=?,kategori=? WHERE id=?")
                ->execute([$nama,$desc,$kat,$id]);
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
        
        // --- MULTIPLE IMAGE HANDLING ---
        $gambarText = trim($_POST['gambar'] ?? '');
        $uploadedFiles = [];
        if (!empty($_FILES['gambar_file']['name'][0])) {
            $targetDir = __DIR__ . '/../assets/img/produk/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            foreach ($_FILES['gambar_file']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['gambar_file']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['gambar_file']['name'][$key], PATHINFO_EXTENSION));
                    $newName = 'var_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $targetDir . $newName)) {
                        $uploadedFiles[] = $newName;
                    }
                }
            }
        }
        $arrGambar = $gambarText ? array_map('trim', explode(',', $gambarText)) : [];
        if (!empty($uploadedFiles)) $arrGambar = array_merge($arrGambar, $uploadedFiles);
        $gambar = implode(',', array_filter($arrGambar));
        // -------------------------------
        
        $tampil   = isset($_POST['tampil_di_online']) ? 1 : 0;

        if ($idInduk && $sku && $namaVar && $hrgKecil > 0) {
            try {
                $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk,sku_variasi,nama_variasi,satuan_kecil,satuan_besar,rasio_konversi,harga_jual_kecil,harga_jual_besar,berat,gambar,tampil_di_online,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,1)")
                    ->execute([$idInduk,$sku,$namaVar,$satKecil,$satBesar,$rasio,$hrgKecil,$hrgBesar,$berat,$gambar,$tampil]);
                
                // Auto-init stok 0
                $newVarId  = $pdo->lastInsertId();
                $pdo->prepare("INSERT IGNORE INTO stok_toko (id_variasi, stok) VALUES (?,0)")
                    ->execute([$newVarId]);
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

        if ($_SESSION['role'] !== 'superadmin') {
            $existing = $pdo->prepare("SELECT rasio_konversi, harga_jual_kecil, harga_jual_besar FROM produk_variasi WHERE id = ?");
            $existing->execute([$idVal]);
            if ($ex = $existing->fetch()) {
                $rasio = $ex['rasio_konversi'];
                $hrgKecil = $ex['harga_jual_kecil'];
                $hrgBesar = $ex['harga_jual_besar'];
            }
        }
        
        // --- MULTIPLE IMAGE HANDLING ---
        $gambarText = trim($_POST['gambar'] ?? '');
        $uploadedFiles = [];
        if (!empty($_FILES['gambar_file']['name'][0])) {
            $targetDir = __DIR__ . '/../assets/img/produk/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            foreach ($_FILES['gambar_file']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['gambar_file']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['gambar_file']['name'][$key], PATHINFO_EXTENSION));
                    $newName = 'var_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $targetDir . $newName)) {
                        $uploadedFiles[] = $newName;
                    }
                }
            }
        }
        $arrGambar = $gambarText ? array_map('trim', explode(',', $gambarText)) : [];
        if (!empty($uploadedFiles)) $arrGambar = array_merge($arrGambar, $uploadedFiles);
        $gambar = implode(',', array_filter($arrGambar));
        // -------------------------------
        
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
                        $stokAwal   = intval($data[12] ?? 0);

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

                            // Initialize zero stock
                            $pdo->prepare("INSERT IGNORE INTO stok_toko (id_variasi, stok) VALUES (?,0)")->execute([$idVar]);
                        } else {
                            // Update existing variasi prices
                            $pdo->prepare("UPDATE produk_variasi SET satuan_kecil=?, satuan_besar=?, rasio_konversi=?, harga_jual_kecil=?, harga_jual_besar=? WHERE id=?")->execute([$satKecil, $satBesar, $rasio, $hrgKecil, $hrgBesar, $idVar]);
                        }

                        // Initial Stock Mutation
                        if ($stokAwal > 0) {
                            $chkStock = $pdo->prepare("SELECT stok FROM stok_toko WHERE id_variasi=? FOR UPDATE");
                            $chkStock->execute([$idVar]);
                            $currentStock = $chkStock->fetchColumn();
                            
                            if ($currentStock === false) {
                                $pdo->prepare("INSERT INTO stok_toko (id_variasi, stok) VALUES (?,?)")->execute([$idVar, $stokAwal]);
                                $currentStock = 0;
                            } else {
                                $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi=?")->execute([$stokAwal, $idVar]);
                            }

                            $sisaStock = $currentStock + $stokAwal;
                            $pdo->prepare("INSERT INTO kartu_stok (id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,'Masuk',?,?,?)")
                                ->execute([$idVar, $stokAwal, $sisaStock, "Import Excel / Stok Awal"]);
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
$produkInduk = $pdo->query("SELECT * FROM produk_induk ORDER BY id DESC")->fetchAll();

layoutHead('Master Produk');
layoutBodyOpen();
layoutSidebar('master_produk');
layoutHeader('Master Produk & Variasi', 'Kelola data produk induk dan variasi alkes/obat');
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
                        &bull; <?= count($variasiList) ?> Variasi
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <!-- Edit -->
                <button onclick="event.stopPropagation(); openEditInduk(<?= $pi['id'] ?>, '<?= addslashes($pi['nama_produk']) ?>', '<?= addslashes($pi['kategori']) ?>', '<?= addslashes($pi['deskripsi'] ?? '') ?>')"
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
                                            <button type="button" onclick="openCetakModal(<?= $v['id'] ?>, '<?= htmlspecialchars($v['nama_variasi']) ?>')" class="text-[10px] font-bold px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 rounded-lg transition">Cetak</button>
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
<!-- MODAL: Cetak Barcode                                          -->
<!-- ============================================================ -->
<div id="modal_cetak_barcode" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[80vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd bg-slate-50">
            <h3 class="text-sm font-bold text-zcTxt">Cetak Label Batch/SN</h3>
            <button onclick="document.getElementById('modal_cetak_barcode').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        <div class="p-6 overflow-y-auto">
            <h4 id="cetak_nama_produk" class="font-bold text-zcTxt mb-4 text-center"></h4>
            
            <div id="cetak_loading" class="text-center text-xs font-semibold text-zcMut">Memuat daftar Batch/SN aktif...</div>
            <div id="cetak_empty" class="hidden text-center text-xs font-semibold text-rose-500 bg-rose-50 p-3 rounded-xl border border-rose-200">Tidak ada stok fisik aktif (Batch/SN) untuk produk ini.</div>
            
            <div id="cetak_list_container" class="space-y-3 hidden">
                <!-- Injected via JS -->
            </div>
        </div>
        <div class="px-6 py-4 border-t border-zcBrd flex justify-end bg-slate-50">
            <button type="button" onclick="document.getElementById('modal_cetak_barcode').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold bg-white border border-zcBrd hover:bg-slate-100 text-slate-700 rounded-xl transition shadow-sm">Tutup</button>
        </div>
    </div>
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
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Teks URL/Nama Gambar</label>
                    <input type="text" name="gambar" placeholder="contoh1.jpg, contoh2.png" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <span class="text-[9px] text-zcMut mt-1 block">Pisahkan dengan koma jika multi-gambar</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Atau Upload File (Bisa pilih banyak)</label>
                    <input type="file" name="gambar_file[]" multiple accept="image/*" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2 focus:outline-none bg-slate-50 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zc file:text-white hover:file:bg-zcHv">
                </div>
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
        <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
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
                    <label class="block text-xs font-bold text-zcTxt mb-1.5 flex justify-between">Rasio (1 Besar = ? Kecil) <?= $_SESSION['role'] !== 'superadmin' ? '<span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Terkunci</span>' : '' ?></label>
                    <input type="number" name="rasio_konversi" id="edit_rasio_konversi" min="1" required 
                        <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none"' : 'class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50"' ?>>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5 flex justify-between">Harga Jual Kecil (Ecer) * <?= $_SESSION['role'] !== 'superadmin' ? '<span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Terkunci</span>' : '' ?></label>
                    <input type="number" name="harga_jual_kecil" id="edit_harga_jual_kecil" required min="0" step="500" 
                        <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none"' : 'class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50"' ?>>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5 flex justify-between">Harga Jual Besar (Grosir) * <?= $_SESSION['role'] !== 'superadmin' ? '<span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Terkunci</span>' : '' ?></label>
                    <input type="number" name="harga_jual_besar" id="edit_harga_jual_besar" required min="0" step="500" 
                        <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none"' : 'class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50"' ?>>
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Teks URL/Nama Gambar</label>
                    <input type="text" name="gambar" id="edit_gambar_variasi" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <span class="text-[9px] text-zcMut mt-1 block">Pisahkan dengan koma jika multi-gambar</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Atau Upload File Tambahan</label>
                    <input type="file" name="gambar_file[]" multiple accept="image/*" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2 focus:outline-none bg-slate-50 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zc file:text-white hover:file:bg-zcHv">
                </div>
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
            Upload file CSV. Kolom format: <code>SKU_INDUK, NAMA_PRODUK, KATEGORI, DESKRIPSI, SKU_VARIASI, NAMA_VARIASI, SATUAN_KECIL, SATUAN_BESAR, RASIO_KONVERSI, HARGA_JUAL_KECIL, HARGA_JUAL_BESAR, BERAT_GRAM, STOK_AWAL</code>.
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
function openEditInduk(id, nama, kat, desc) {
    document.getElementById('edit_id_induk').value = id;
    document.getElementById('edit_nama_produk').value = nama;
    document.getElementById('edit_deskripsi').value = desc;
    document.getElementById('edit_kategori').value = kat;
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

async function openCetakModal(id, nama) {
    document.getElementById('cetak_nama_produk').innerText = nama;
    document.getElementById('modal_cetak_barcode').classList.remove('hidden');
    
    const loading = document.getElementById('cetak_loading');
    const empty = document.getElementById('cetak_empty');
    const list = document.getElementById('cetak_list_container');
    
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');
    list.innerHTML = '';
    
    try {
        const res = await fetch(`?ajax=get_barcode_list&id_variasi=${id}`);
        const data = await res.json();
        
        loading.classList.add('hidden');
        
        if (data.batch.length === 0 && data.sn.length === 0) {
            empty.classList.remove('hidden');
            return;
        }
        
        list.classList.remove('hidden');
        
        data.batch.forEach(b => {
            const dateStr = new Date(b.tgl_exp).toLocaleDateString('id-ID');
            list.innerHTML += `
                <div class="flex items-center justify-between p-3 bg-white border border-zcBrd rounded-xl shadow-sm">
                    <div>
                        <div class="font-bold text-zcTxt font-mono text-xs">${b.no_batch}</div>
                        <div class="text-[10px] text-zcMut">EXP: ${dateStr}</div>
                    </div>
                    <button type="button" onclick="window.open('../inventori/cetak_stiker.php?tipe=batch&id=${b.id}', '_blank')" class="px-3 py-1.5 bg-zc hover:bg-zcHv text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg> Cetak
                    </button>
                </div>
            `;
        });
        
        data.sn.forEach(sn => {
            list.innerHTML += `
                <div class="flex items-center justify-between p-3 bg-white border border-zcBrd rounded-xl shadow-sm">
                    <div>
                        <div class="font-bold text-purple-700 font-mono text-xs">${sn.serial_number}</div>
                        <div class="text-[10px] text-purple-700/60 font-semibold uppercase">Serial Number</div>
                    </div>
                    <button type="button" onclick="window.open('../inventori/cetak_stiker.php?tipe=sn&id=${sn.serial_number}', '_blank')" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg> Cetak
                    </button>
                </div>
            `;
        });
        
    } catch (e) {
        alert("Gagal mengambil data dari server.");
    }
}
</script>

<?php layoutEnd(); ?>

