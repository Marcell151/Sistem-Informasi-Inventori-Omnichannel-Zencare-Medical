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
    $batches = $pdo->prepare("SELECT id, no_batch, tgl_exp, stok_sisa FROM stok_batch WHERE id_variasi = ? AND stok_sisa > 0 ORDER BY tgl_exp ASC");
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
    if (!in_array($_SESSION['role'] ?? '', ['superadmin', 'admin'])) {
        die("Hanya Superadmin dan Admin yang dapat mengubah data master produk dan harga.");
    }

    $aksi = $_POST['aksi'] ?? '';

    // --- Tambah Produk Induk ---
    if ($aksi === 'tambah_induk') {
        $sku  = trim($_POST['sku_induk'] ?? '');
        $nama = trim($_POST['nama_produk'] ?? '');
        $kat  = trim($_POST['kategori'] ?? '');
        $desc = trim($_POST['deskripsi'] ?? '');
        $bhe  = intval($_POST['batas_hari_expired'] ?? 0);
        if ($sku && $nama && $kat) {
            try {
                $pdo->prepare("INSERT INTO produk_induk (sku_induk,nama_produk,deskripsi,kategori,batas_hari_expired,is_active) VALUES (?,?,?,?,?,1)")
                    ->execute([$sku,$nama,$desc,$kat,$bhe]);
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
        $bhe  = intval($_POST['batas_hari_expired'] ?? 0);
        if ($id && $nama) {
            $pdo->prepare("UPDATE produk_induk SET nama_produk=?,deskripsi=?,kategori=?,batas_hari_expired=? WHERE id=?")
                ->execute([$nama,$desc,$kat,$bhe,$id]);
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
        $hrgKecil = floatval(preg_replace('/[^0-9]/', '', (string)($_POST['harga_jual_kecil'] ?? 0)));
        $hrgBesar = floatval(preg_replace('/[^0-9]/', '', (string)($_POST['harga_jual_besar'] ?? 0)));

        $berat    = intval($_POST['berat'] ?? 100);
        $minStokKecil  = intval($_POST['stok_minimum_kecil'] ?? 0);
        $minStokBesar  = intval($_POST['stok_minimum_besar'] ?? 0);
        
        // --- MULTIPLE IMAGE HANDLING ---
        $gambarText = trim($_POST['gambar'] ?? '');
        $uploadedFiles = [];
        if (!empty($_FILES['gambar_file']['name'][0])) {
            $targetDir = __DIR__ . '/../assets/img/produk/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            foreach ($_FILES['gambar_file']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['gambar_file']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['gambar_file']['name'][$key], PATHINFO_EXTENSION));
                    $filenameOnly = 'var_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $targetDir . $filenameOnly)) {
                        $uploadedFiles[] = 'assets/img/produk/' . $filenameOnly;
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
                $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk,sku_variasi,nama_variasi,satuan_kecil,satuan_besar,rasio_konversi,harga_jual_kecil,harga_jual_besar,berat,stok_minimum_kecil,stok_minimum_besar,gambar,tampil_di_online,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1)")
                    ->execute([$idInduk,$sku,$namaVar,$satKecil,$satBesar,$rasio,$hrgKecil,$hrgBesar,$berat,$minStokKecil,$minStokBesar,$gambar,$tampil]);
                
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
        $hrgKecil = floatval(preg_replace('/[^0-9]/', '', (string)($_POST['harga_jual_kecil'] ?? 0)));
        $hrgBesar = floatval(preg_replace('/[^0-9]/', '', (string)($_POST['harga_jual_besar'] ?? 0)));
        $berat = intval($_POST['berat'] ?? 100);
        $minStokKecil  = intval($_POST['stok_minimum_kecil'] ?? 0);
        $minStokBesar  = intval($_POST['stok_minimum_besar'] ?? 0);

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
                    $filenameOnly = 'var_' . time() . '_' . rand(100,999) . '.' . $ext;
                    if (move_uploaded_file($tmpName, $targetDir . $filenameOnly)) {
                        $uploadedFiles[] = 'assets/img/produk/' . $filenameOnly;
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
                $pdo->prepare("UPDATE produk_variasi SET sku_variasi=?, nama_variasi=?, satuan_kecil=?, satuan_besar=?, rasio_konversi=?, harga_jual_kecil=?, harga_jual_besar=?, berat=?, stok_minimum_kecil=?, stok_minimum_besar=?, gambar=?, tampil_di_online=? WHERE id=?")
                    ->execute([$sku, $nama, $satKecil, $satBesar, $rasio, $hrgKecil, $hrgBesar, $berat, $minStokKecil, $minStokBesar, $gambar, $tampil, $idVal]);
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

// Fetch data with Category Filter and Sorting: Obat (A-Z) first, then Alkes (A-Z)
$katFilter = $_GET['kategori'] ?? 'semua';
$searchQ   = trim($_GET['q'] ?? '');

$sqlInduk = "SELECT * FROM produk_induk WHERE 1=1";
$paramsInduk = [];

if ($katFilter === 'Obat') {
    $sqlInduk .= " AND kategori = 'Obat'";
} elseif ($katFilter === 'Alat Kesehatan') {
    $sqlInduk .= " AND kategori = 'Alat Kesehatan'";
}

if ($searchQ !== '') {
    $sqlInduk .= " AND (nama_produk LIKE ? OR sku_induk LIKE ? OR deskripsi LIKE ?)";
    $paramsInduk[] = "%$searchQ%";
    $paramsInduk[] = "%$searchQ%";
    $paramsInduk[] = "%$searchQ%";
}

$sqlInduk .= " ORDER BY 
    CASE WHEN kategori = 'Obat' THEN 1 WHEN kategori = 'Alat Kesehatan' THEN 2 ELSE 3 END ASC,
    nama_produk ASC";

$stmtIndukAll = $pdo->prepare($sqlInduk);
$stmtIndukAll->execute($paramsInduk);
$produkIndukAll = $stmtIndukAll->fetchAll();

$countObat  = $pdo->query("SELECT COUNT(*) FROM produk_induk WHERE kategori = 'Obat'")->fetchColumn();
$countAlkes = $pdo->query("SELECT COUNT(*) FROM produk_induk WHERE kategori = 'Alat Kesehatan'")->fetchColumn();
$countTotal = $pdo->query("SELECT COUNT(*) FROM produk_induk")->fetchColumn();

// Pagination (15 items per page)
$perPage = 15;
$page = max(1, intval($_GET['page'] ?? 1));
$totalInduk = count($produkIndukAll);
$totalPages = max(1, (int)ceil($totalInduk / $perPage));
if ($page > $totalPages) $page = $totalPages;
$produkInduk = array_slice($produkIndukAll, ($page - 1) * $perPage, $perPage);

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

<!-- CATEGORY FILTER TABS -->
<div class="flex items-center gap-2 mb-4 flex-wrap">
    <a href="?kategori=semua<?= $searchQ ? '&q=' . urlencode($searchQ) : '' ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $katFilter === 'semua' ? 'bg-zc text-white shadow-xs' : 'bg-white border border-zcBrd text-zcTxt hover:bg-slate-50' ?>">
        Semua Produk (<?= $countTotal ?>)
    </a>
    <a href="?kategori=Obat<?= $searchQ ? '&q=' . urlencode($searchQ) : '' ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $katFilter === 'Obat' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white border border-zcBrd text-indigo-700 hover:bg-indigo-50/50' ?>">
        Obat (<?= $countObat ?>)
    </a>
    <a href="?kategori=Alat+Kesehatan<?= $searchQ ? '&q=' . urlencode($searchQ) : '' ?>" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= $katFilter === 'Alat Kesehatan' ? 'bg-teal-600 text-white shadow-xs' : 'bg-white border border-zcBrd text-teal-700 hover:bg-teal-50/50' ?>">
        Alat Kesehatan (<?= $countAlkes ?>)
    </a>
</div>

<!-- ACTION HEADER -->
<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h2 class="text-base font-bold text-zcTxt">Daftar Produk Induk</h2>
        <p class="text-xs text-zcMut mt-0.5">Total: <?= $totalInduk ?> produk <?= $katFilter !== 'semua' ? "($katFilter)" : '' ?> &bull; Klik &blacktriangledown; untuk lihat variasi & tambah variasi</p>
    </div>
    <div class="flex flex-wrap gap-2 items-center justify-end w-full md:w-auto">
        <!-- Search Input -->
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </span>
            <input type="text" id="searchProduk" onkeyup="filterProduk()" placeholder="Cari nama, SKU, atau deskripsi..." 
                   class="pl-9 pr-4 py-2.5 bg-white border border-zcBrd rounded-xl text-xs focus:outline-none focus:border-zc shadow-sm min-w-[220px]">
        </div>
        
        <a href="../inventori/logistik_medis.php?tab=batch"
            class="flex items-center gap-2 px-3.5 py-2.5 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 text-xs font-bold rounded-xl transition shadow-sm"
            title="Cetak stiker barcode massal lintas berbagai produk berbeda sekaligus">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak Lintas Produk
        </a>
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
<div class="space-y-3" id="produk-list-container">
<?php foreach ($produkInduk as $pi): ?>
    <?php
    $variasiList = $pdo->prepare("SELECT * FROM produk_variasi WHERE id_produk_induk=? ORDER BY id ASC");
    $variasiList->execute([$pi['id']]);
    $variasiList = $variasiList->fetchAll();
    $aktif = $pi['is_active'] ? true : false;
    ?>
    <div class="produk-item bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden <?= !$aktif ? 'opacity-60' : '' ?>">
        <!-- Header row -->
        <div class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-slate-50 transition"
             onclick="toggleAcc('acc_<?= $pi['id'] ?>')">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl <?= $pi['kategori'] === 'Obat' ? 'bg-indigo-50 border border-indigo-100 text-indigo-600' : 'bg-teal-50 border border-teal-100 text-teal-600' ?> flex items-center justify-center shrink-0">
                    <?php if ($pi['kategori'] === 'Obat'): ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    <?php else: ?>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <?php endif; ?>
                </div>
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
                    <?php if (!empty($pi['deskripsi'])): ?>
                        <div class="text-[11px] text-slate-500 mt-1 flex items-start gap-1 line-clamp-1 max-w-xl" title="<?= htmlspecialchars($pi['deskripsi'], ENT_QUOTES) ?>">
                            <span class="text-slate-400 shrink-0 font-medium">Deskripsi E-Comm:</span>
                            <span class="truncate italic text-slate-600">"<?= htmlspecialchars($pi['deskripsi']) ?>"</span>
                        </div>
                    <?php else: ?>
                        <div class="text-[10px] text-amber-600/80 italic mt-0.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <span>Belum ada deskripsi untuk e-commerce</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0" onclick="event.stopPropagation()">
                <!-- Edit -->
                <button type="button" onclick="openEditIndukBtn(this)"
                    data-id="<?= $pi['id'] ?>"
                    data-nama="<?= htmlspecialchars($pi['nama_produk'], ENT_QUOTES) ?>"
                    data-kat="<?= htmlspecialchars($pi['kategori'], ENT_QUOTES) ?>"
                    data-desc="<?= htmlspecialchars($pi['deskripsi'] ?? '', ENT_QUOTES) ?>"
                    data-bhe="<?= intval($pi['batas_hari_expired'] ?? 0) ?>"
                    class="px-3 py-1.5 text-[11px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-xl transition">Edit</button>
                <!-- Toggle -->
                <form method="POST" onsubmit="return handleToggleSubmit(this, event, '<?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>', 'produk induk')" class="inline">
                    <input type="hidden" name="aksi" value="toggle_induk">
                    <input type="hidden" name="id_induk" value="<?= $pi['id'] ?>">
                    <button type="submit" class="px-3 py-1.5 text-[11px] font-bold <?= $aktif ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200' ?> border rounded-xl transition cursor-pointer">
                        <?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                    </button>
                </form>
                <span class="text-zcMut text-lg cursor-pointer" onclick="toggleAcc('acc_<?= $pi['id'] ?>')">&#9660;</span>
            </div>
        </div>

        <!-- Accordion Content: Variasi & Deskripsi -->
        <div id="acc_<?= $pi['id'] ?>" class="hidden border-t border-zcBrd bg-slate-50">
            <div class="p-4">
                <!-- Box Deskripsi Produk untuk E-Commerce -->
                <div class="mb-4 bg-white rounded-xl border border-slate-200/90 p-3.5 shadow-2xs">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <span>Deskripsi Produk (Tampil di Katalog &amp; Detail E-Commerce)</span>
                            </span>
                            <?php if (!empty($pi['deskripsi'])): ?>
                                <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">Terisi di Web</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-md">Belum Diisi</span>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="openEditIndukBtn(document.querySelector(`button[data-id='<?= $pi['id'] ?>']`))" 
                                class="text-[11px] font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit Deskripsi</span>
                        </button>
                    </div>
                    <div class="text-xs text-slate-700 leading-relaxed bg-slate-50/70 p-3 rounded-lg border border-slate-100 whitespace-pre-line">
                        <?php if (!empty($pi['deskripsi'])): ?>
                            <?= htmlspecialchars($pi['deskripsi']) ?>
                        <?php else: ?>
                            <span class="text-slate-400 italic">Belum ada deskripsi untuk produk ini. Klik "Edit Deskripsi" untuk mengisi informasi produk agar tampil menarik dan informatif bagi pembeli di website E-Commerce.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-zcTxt">Daftar Variasi</span>
                    <button type="button" data-id="<?= $pi['id'] ?>" data-nama="<?= htmlspecialchars($pi['nama_produk'], ENT_QUOTES) ?>" onclick="openTambahVariasiBtn(this)"
                        class="text-[11px] font-bold px-3 py-1.5 bg-zc hover:bg-zcHv text-white rounded-xl transition cursor-pointer">+ Tambah Variasi</button>
                </div>
                <?php if (empty($variasiList)): ?>
                    <p class="text-xs text-zcMut italic py-4 text-center">Belum ada variasi. Tambahkan variasi untuk produk ini.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="text-zcMut uppercase tracking-wider font-bold border-b border-zcBrd">
                                <tr>
                                    <th class="py-2 px-3 text-left">Foto &bull; SKU / Nama</th>
                                    <th class="py-2 px-3 text-center">UOM Konversi</th>
                                    <th class="py-2 px-3 text-right">Harga Ecer (POS)</th>
                                    <th class="py-2 px-3 text-right">Harga Grosir (Online)</th>
                                    <th class="py-2 px-3 text-center">Tampil E-Comm</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                    <th class="py-2 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zcBorder/50">
                                <?php foreach ($variasiList as $v): 
                                    $gbrRaw = $v['gambar'] ?? '';
                                    $arrGbr = $gbrRaw ? array_filter(array_map('trim', explode(',', $gbrRaw))) : [];
                                    $firstGbr = !empty($arrGbr) ? $arrGbr[0] : 'assets/img/no-image.png';
                                    $firstSrc = (str_starts_with($firstGbr, 'http')) ? $firstGbr : (str_contains($firstGbr, '/') ? '../' . $firstGbr : '../assets/img/produk/' . $firstGbr);
                                    $totalGbr = count($arrGbr);
                                ?>
                                    <tr class="<?= !$v['is_active'] ? 'opacity-50' : '' ?>">
                                        <td class="py-2.5 px-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="relative w-9 h-9 rounded-lg bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 p-0.5 group shadow-2xs">
                                                    <img src="<?= htmlspecialchars($firstSrc) ?>" class="w-full h-full object-contain mix-blend-multiply" onerror="this.src='../assets/img/no-image.png'">
                                                    <?php if ($totalGbr > 1): ?>
                                                        <span class="absolute bottom-0 right-0 bg-slate-900/80 text-[8px] text-white font-bold px-1 rounded-tl leading-tight" title="<?= $totalGbr ?> Foto">
                                                            <?= $totalGbr ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="font-mono text-zcMut text-[10px]"><?= htmlspecialchars($v['sku_variasi']) ?></div>
                                                    <div class="font-semibold text-zcTxt text-xs"><?= htmlspecialchars($v['nama_variasi']) ?></div>
                                                </div>
                                            </div>
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
                                            <button type="button" onclick='openEditVariasiBtn(<?= htmlspecialchars(json_encode($v), ENT_QUOTES) ?>)' 
                                                    class="text-[10px] font-bold px-2 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded-lg transition">
                                                Edit
                                            </button>
                                            <button type="button" data-id="<?= $v['id'] ?>" data-nama="<?= htmlspecialchars($v['nama_variasi'], ENT_QUOTES) ?>" onclick="openCetakModal(this.getAttribute('data-id'), this.getAttribute('data-nama'))" class="text-[10px] font-bold px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 rounded-lg transition cursor-pointer">Cetak</button>
                                            <form method="POST" class="inline" onsubmit="return handleToggleSubmit(this, event, '<?= $v['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>', 'variasi produk')">
                                                <input type="hidden" name="aksi" value="toggle_variasi">
                                                <input type="hidden" name="id_variasi" value="<?= $v['id'] ?>">
                                                <button type="submit" class="text-[10px] font-bold px-2 py-1 <?= $v['is_active'] ? 'text-rose-600 bg-rose-50 border-rose-200' : 'text-emerald-600 bg-emerald-50 border-emerald-200' ?> border rounded-lg transition cursor-pointer">
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
<?php if (empty($produkInduk)): ?>
    <div class="p-12 text-center bg-white rounded-2xl border border-zcBrd text-zcMut">
        <p class="text-sm font-semibold mb-1">Tidak ada produk ditemukan.</p>
        <p class="text-xs text-slate-400">Silakan sesuaikan filter kategori atau kata kunci pencarian Anda.</p>
    </div>
<?php endif; ?>
</div>

<!-- Pagination Produk Induk -->
<?php if ($totalInduk > 0): ?>
<div class="mt-4 p-4 bg-white rounded-2xl border border-zcBrd flex flex-col sm:flex-row items-center justify-between gap-3 text-xs shadow-xs">
    <span class="text-zcMut font-medium">
        Menampilkan <?= ($page - 1) * $perPage + 1 ?> – <?= min($page * $perPage, $totalInduk) ?> dari <?= $totalInduk ?> produk induk
    </span>
    <div class="flex items-center gap-1.5 flex-wrap">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">&larr; Sebelumnya</a>
        <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">&larr; Sebelumnya</span>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p == 1 || $p == $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border text-xs font-bold transition <?= $p == $page ? 'bg-zc border-zc text-white shadow-xs' : 'border-zcBrd bg-white text-zcTxt hover:bg-slate-50' ?>"><?= $p ?></a>
            <?php elseif ($p == 2 || $p == $totalPages - 1): ?>
                <span class="px-1 text-slate-400 font-bold">...</span>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3 py-1.5 rounded-lg border border-zcBrd bg-white text-zcTxt hover:bg-slate-50 font-semibold transition">Selanjutnya &rarr;</a>
        <?php else: ?>
            <span class="px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-100 text-slate-400 font-semibold cursor-not-allowed">Selanjutnya &rarr;</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- MODAL: Cetak Barcode                                          -->
<!-- ============================================================ -->
<div id="modal_cetak_barcode" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col max-h-[85vh]">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zcBrd bg-slate-50">
            <div>
                <h3 class="text-sm font-bold text-zcTxt">Cetak Label Batch / Serial Number</h3>
                <p class="text-[11px] text-zcMut">Cetak massal di kertas stiker A4 (Grid 4x10) atau Printer Thermal Roll</p>
            </div>
            <button onclick="document.getElementById('modal_cetak_barcode').classList.add('hidden')" class="text-zcMut hover:text-zcTxt text-lg">&times;</button>
        </div>
        
        <div class="p-6 overflow-y-auto">
            <h4 id="cetak_nama_produk" class="font-bold text-zcTxt mb-3 text-center text-sm"></h4>

            <!-- Selection Toolbar -->
            <div id="cetak_toolbar" class="hidden mb-3 p-2.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" id="check_all_barcode" onchange="toggleSelectAllBarcode(this.checked)" class="rounded text-zc focus:ring-zc h-4 w-4">
                    <span>Pilih Semua Batch / SN</span>
                </label>
                <span id="cetak_selected_count" class="text-[11px] font-semibold text-zc">0 item dipilih</span>
            </div>

            <!-- Helpful Hint -->
            <div class="mb-3 px-3 py-2 bg-blue-50/70 border border-blue-200/80 rounded-xl text-[11px] text-blue-800 flex items-center justify-between">
                <span>💡 Atur <strong>Jml</strong> untuk mencetak beberapa stiker batch yang sama.</span>
                <a href="../inventori/logistik_medis.php?tab=batch" target="_blank" class="font-bold underline text-blue-700 hover:text-blue-900 shrink-0">Cetak Lintas Produk &rarr;</a>
            </div>
            
            <div id="cetak_loading" class="text-center text-xs font-semibold text-zcMut py-6">Memuat daftar Batch/SN aktif...</div>
            <div id="cetak_empty" class="hidden text-center text-xs font-semibold text-rose-500 bg-rose-50 p-4 rounded-xl border border-rose-200">Tidak ada stok fisik aktif (Batch/SN) untuk produk ini.</div>
            
            <div id="cetak_list_container" class="space-y-2.5 hidden">
                <!-- Injected via JS -->
            </div>
        </div>

        <div class="px-6 py-3.5 border-t border-zcBrd flex items-center justify-between bg-slate-50">
            <button type="button" onclick="submitBatchPrint()" class="px-4 py-2 text-xs font-bold bg-zc hover:bg-zcHv text-white rounded-xl transition shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>🖨️ Cetak Massal (A4 Grid)</span>
            </button>
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
                        <option value="Obat">Obat</option>
                        <option value="Alat Kesehatan">Alat Kesehatan</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Nama Produk *</label>
                <input type="text" name="nama_produk" required placeholder="Contoh: Masker N95 3M" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Batas Peringatan Expired (Hari) *</label>
                <input type="number" name="batas_hari_expired" required min="0" placeholder="Contoh: 90" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-zcTxt">Deskripsi Produk</label>
                    <span class="text-[10px] text-blue-600 font-semibold">Tampil di Katalog &amp; Detail E-Commerce</span>
                </div>
                <textarea name="deskripsi" rows="3" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 placeholder:text-slate-400" placeholder="Tuliskan deskripsi lengkap, kegunaan, atau informasi penting produk yang akan dibaca pembeli di e-commerce..."></textarea>
                <span class="text-[10px] text-zcMut mt-1 block">Deskripsi ini akan muncul di kartu produk dan tab 'Deskripsi Produk' pada website belanja online e-commerce.</span>
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
                    <option value="Obat">Obat</option>
                    <option value="Alat Kesehatan">Alat Kesehatan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Batas Peringatan Expired (Hari) *</label>
                <input type="number" name="batas_hari_expired" id="edit_batas_hari_expired" required min="0" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-zcTxt">Deskripsi Produk</label>
                    <span class="text-[10px] text-blue-600 font-semibold">Tampil di Katalog &amp; Detail E-Commerce</span>
                </div>
                <textarea name="deskripsi" id="edit_deskripsi" rows="4" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 placeholder:text-slate-400" placeholder="Tuliskan deskripsi lengkap, kegunaan, atau informasi penting produk yang akan dibaca pembeli di e-commerce..."></textarea>
                <span class="text-[10px] text-zcMut mt-1 block">Deskripsi ini akan muncul di kartu produk dan tab 'Deskripsi Produk' pada website belanja online e-commerce.</span>
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
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" name="harga_jual_kecil" id="tambah_harga_jual_kecil" required oninput="formatRupiahInput(this)" placeholder="0" class="w-full pl-9 text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-semibold">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Harga Jual Besar (Grosir) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" name="harga_jual_besar" id="tambah_harga_jual_besar" required oninput="formatRupiahInput(this)" placeholder="0" class="w-full pl-9 text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-semibold">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Berat (gram) *</label>
                    <input type="number" name="berat" required min="1" placeholder="250" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div style="grid-column: span 2 / span 2;">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zcTxt mb-1.5">Batas Min (Eceran) *</label>
                            <input type="number" name="stok_minimum_kecil" required min="0" placeholder="10" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zcTxt mb-1.5">Batas Min (Grosir) *</label>
                            <input type="number" name="stok_minimum_besar" required min="0" placeholder="2" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Tampilkan di E-Commerce?</label>
                    <label class="inline-flex items-center mt-2.5">
                        <input type="checkbox" name="tampil_di_online" value="1" checked class="rounded border-slate-300 text-zc focus:ring-zc h-4 w-4">
                        <span class="ml-2 text-xs font-medium text-slate-700">Tampilkan Online</span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Teks URL/Nama Gambar</label>
                    <input type="text" name="gambar" placeholder="contoh1.jpg, contoh2.png" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                    <span class="text-[9px] text-zcMut mt-1 block">Pisahkan dengan koma jika multi-gambar (bisa URL atau path)</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Upload File Gambar (Bisa pilih banyak foto)</label>
                <input type="file" name="gambar_file[]" multiple accept="image/*" onchange="previewSelectedFiles(this, 'tambah_variasi_file_preview')" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none bg-slate-50 cursor-pointer file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-zc file:text-white hover:file:bg-zcHv transition">
                <div id="tambah_variasi_file_preview"></div>
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
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" name="harga_jual_kecil" id="edit_harga_jual_kecil" required oninput="formatRupiahInput(this)" 
                            <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full pl-9 text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none font-semibold"' : 'class="w-full pl-9 text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-semibold"' ?>>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5 flex justify-between">Harga Jual Besar (Grosir) * <?= $_SESSION['role'] !== 'superadmin' ? '<span class="text-[9px] text-rose-500 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Terkunci</span>' : '' ?></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" inputmode="numeric" name="harga_jual_besar" id="edit_harga_jual_besar" required oninput="formatRupiahInput(this)" 
                            <?= $_SESSION['role'] !== 'superadmin' ? 'readonly class="w-full pl-9 text-xs border border-slate-200 rounded-xl px-3.5 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed focus:outline-none font-semibold"' : 'class="w-full pl-9 text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-semibold"' ?>>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Berat (gram) *</label>
                    <input type="number" name="berat" id="edit_berat_variasi" required min="1" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                </div>
                <div style="grid-column: span 2 / span 2;">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zcTxt mb-1.5">Batas Min (Eceran) *</label>
                            <input type="number" name="stok_minimum_kecil" id="edit_stok_minimum_kecil" required min="0" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zcTxt mb-1.5">Batas Min (Grosir) *</label>
                            <input type="number" name="stok_minimum_besar" id="edit_stok_minimum_besar" required min="0" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Tampilkan di E-Commerce?</label>
                    <label class="inline-flex items-center mt-2.5">
                        <input type="checkbox" name="tampil_di_online" id="edit_tampil_di_online" value="1" class="rounded border-slate-300 text-zc focus:ring-zc h-4 w-4">
                        <span class="ml-2 text-xs font-medium text-slate-700">Tampilkan Online</span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-zcTxt mb-1.5">Teks URL / Path Gambar</label>
                    <input type="text" name="gambar" id="edit_gambar_variasi" oninput="renderEditImageThumbnails(this.value)" class="w-full text-xs border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-mono text-[11px]">
                    <span class="text-[9px] text-zcMut mt-1 block">Pisahkan dengan koma jika multi-gambar. Edit langsung atau gunakan tombol hapus foto di bawah.</span>
                </div>
            </div>

            <!-- Visual Preview & Gallery Strip of Current Images -->
            <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        Galeri Foto Aktif (Karusel E-Commerce)
                    </label>
                    <span class="text-[10px] text-slate-400">Klik tanda [×] untuk menghapus foto</span>
                </div>
                <div id="edit_gambar_thumbnails_box"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zcTxt mb-1.5">Tambah File Foto Baru (Bisa pilih banyak)</label>
                <input type="file" name="gambar_file[]" multiple accept="image/*" onchange="previewSelectedFiles(this, 'edit_variasi_file_preview')" class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none bg-slate-50 cursor-pointer file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-zc file:text-white hover:file:bg-zcHv transition">
                <div id="edit_variasi_file_preview"></div>
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
function openEditInduk(id, nama, kat, desc, bhe) {
    document.getElementById('edit_id_induk').value = id;
    document.getElementById('edit_nama_produk').value = nama;
    document.getElementById('edit_deskripsi').value = desc;
    document.getElementById('edit_kategori').value = kat;
    document.getElementById('edit_batas_hari_expired').value = bhe;
    document.getElementById('modal_edit_induk').classList.remove('hidden');
}

function openEditIndukBtn(btn) {
    if (!btn) return;
    openEditInduk(
        btn.getAttribute('data-id'),
        btn.getAttribute('data-nama'),
        btn.getAttribute('data-kat'),
        btn.getAttribute('data-desc'),
        btn.getAttribute('data-bhe')
    );
}

function openTambahVariasiBtn(btn) {
    if (!btn) return;
    openTambahVariasi(btn.getAttribute('data-id'), btn.getAttribute('data-nama'));
}

function openEditVariasiBtn(v) {
    if (!v) return;
    openEditVariasi(
        v.id,
        v.sku_variasi || '',
        v.nama_variasi || '',
        v.satuan_kecil || 'Pcs',
        v.satuan_besar || 'Box',
        v.rasio_konversi || 1,
        v.harga_jual_kecil || 0,
        v.harga_jual_besar || 0,
        v.berat || 100,
        v.stok_minimum_kecil || 0,
        v.stok_minimum_besar || 0,
        v.gambar || '',
        v.tampil_di_online || 0
    );
}

function handleToggleSubmit(form, e, actionText, itemType = 'item') {
    if (form.dataset.confirmed === 'true') {
        return true;
    }
    if (e) e.preventDefault();
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: actionText + ' ' + (itemType.charAt(0).toUpperCase() + itemType.slice(1)) + '?',
            text: 'Apakah Anda yakin ingin ' + actionText.toLowerCase() + ' ' + itemType + ' ini?',
            icon: actionText.toLowerCase().includes('nonaktif') ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: actionText.toLowerCase().includes('nonaktif') ? '#e11d48' : '#059669',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, ' + actionText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
    } else {
        if (confirm(actionText + ' ' + itemType + ' ini?')) {
            form.dataset.confirmed = 'true';
            form.submit();
        }
    }
    return false;
}
function openTambahVariasi(idInduk, namaInduk) {
    document.getElementById('var_id_induk').value = idInduk;
    document.getElementById('var_parent_name').innerText = namaInduk;
    document.getElementById('modal_tambah_variasi').classList.remove('hidden');
}
function formatRupiahInput(el) {
    if (!el) return;
    let clean = el.value.replace(/[^0-9]/g, '');
    if (clean === '') {
        el.value = '';
        return;
    }
    el.value = parseInt(clean, 10).toLocaleString('id-ID');
}

function openEditVariasi(id, sku, nama, satKecil, satBesar, rasio, hrgKecil, hrgBesar, berat, minStokKecil, minStokBesar, gambar, tampil) {
    document.getElementById('edit_id_variasi').value = id;
    document.getElementById('edit_sku_variasi').value = sku;
    document.getElementById('edit_nama_variasi').value = nama;
    document.getElementById('edit_satuan_kecil').value = satKecil;
    document.getElementById('edit_satuan_besar').value = satBesar;
    document.getElementById('edit_rasio_konversi').value = rasio;
    document.getElementById('edit_harga_jual_kecil').value = parseInt(hrgKecil || 0).toLocaleString('id-ID');
    document.getElementById('edit_harga_jual_besar').value = parseInt(hrgBesar || 0).toLocaleString('id-ID');
    document.getElementById('edit_berat_variasi').value = berat;
    document.getElementById('edit_stok_minimum_kecil').value = minStokKecil;
    document.getElementById('edit_stok_minimum_besar').value = minStokBesar;
    document.getElementById('edit_gambar_variasi').value = gambar;
    document.getElementById('edit_tampil_di_online').checked = (tampil == 1);
    renderEditImageThumbnails(gambar);
    const editFilePreview = document.getElementById('edit_variasi_file_preview');
    if (editFilePreview) editFilePreview.innerHTML = '';
    document.getElementById('modal_edit_variasi').classList.remove('hidden');
}

function renderEditImageThumbnails(gambarStr) {
    const container = document.getElementById('edit_gambar_thumbnails_box');
    if (!container) return;
    
    let images = gambarStr ? gambarStr.split(',').map(s => s.trim()).filter(Boolean) : [];
    
    if (images.length === 0) {
        container.innerHTML = '<div class="text-[11px] text-slate-400 italic py-1.5">Belum ada foto untuk variasi ini. Silakan upload file atau ketik tautan di atas.</div>';
        return;
    }
    
    let html = '<div class="flex flex-wrap gap-2.5">';
    images.forEach((img, idx) => {
        let src = img.startsWith('http') ? img : (img.includes('/') ? ('../' + img) : ('../assets/img/produk/' + img));
        html += `
            <div class="relative group/thumb w-14 h-14 rounded-xl border border-slate-200 bg-white p-1 shadow-2xs flex items-center justify-center overflow-hidden">
                <img src="${src}" class="w-full h-full object-contain mix-blend-multiply" onerror="this.src='../assets/img/no-image.png'">
                ${idx === 0 ? '<span class="absolute bottom-0 inset-x-0 bg-blue-600/90 text-[8px] font-bold text-white text-center py-0.5 leading-none">Utama</span>' : ''}
                <button type="button" onclick="removeEditImage(${idx})" class="absolute top-1 right-1 w-4 h-4 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold shadow opacity-80 group-hover/thumb:opacity-100 transition" title="Hapus foto ini">×</button>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function removeEditImage(idx) {
    const input = document.getElementById('edit_gambar_variasi');
    let images = input.value ? input.value.split(',').map(s => s.trim()).filter(Boolean) : [];
    images.splice(idx, 1);
    input.value = images.join(',');
    renderEditImageThumbnails(input.value);
}

function previewSelectedFiles(inputEl, previewContainerId) {
    const container = document.getElementById(previewContainerId);
    if (!container) return;
    container.innerHTML = '';
    if (!inputEl.files || inputEl.files.length === 0) return;
    
    let html = '<div class="mt-2 text-[10px] font-semibold text-emerald-700 mb-1 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> ' + inputEl.files.length + ' file baru dipilih:</div><div class="flex flex-wrap gap-2">';
    Array.from(inputEl.files).forEach(file => {
        let objUrl = URL.createObjectURL(file);
        html += `
            <div class="relative w-12 h-12 rounded-lg border border-emerald-300 bg-white p-1 flex items-center justify-center overflow-hidden shadow-2xs">
                <img src="${objUrl}" class="w-full h-full object-contain mix-blend-multiply">
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

async function openCetakModal(id, nama) {
    document.getElementById('cetak_nama_produk').innerText = nama;
    document.getElementById('modal_cetak_barcode').classList.remove('hidden');
    
    const loading = document.getElementById('cetak_loading');
    const empty = document.getElementById('cetak_empty');
    const list = document.getElementById('cetak_list_container');
    const toolbar = document.getElementById('cetak_toolbar');
    const checkAll = document.getElementById('check_all_barcode');
    
    if (checkAll) checkAll.checked = false;
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    list.classList.add('hidden');
    if (toolbar) toolbar.classList.add('hidden');
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
        if (toolbar) toolbar.classList.remove('hidden');
        
        data.batch.forEach(b => {
            const dateStr = new Date(b.tgl_exp).toLocaleDateString('id-ID');
            const stokSisa = b.stok_sisa ? `${b.stok_sisa} pcs` : '';
            list.innerHTML += `
                <div class="barcode-row flex items-center justify-between p-3 bg-white border border-zcBrd rounded-xl shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="barcode-item-check rounded text-zc focus:ring-zc h-4 w-4 cursor-pointer" data-tipe="batch" data-id="${b.id}" onchange="updateBarcodeSelectedCount()">
                        <div>
                            <div class="font-bold text-zcTxt font-mono text-xs flex items-center gap-2">
                                <span>${b.no_batch}</span>
                                ${stokSisa ? `<span class="text-[9px] font-sans px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Sisa ${stokSisa}</span>` : ''}
                            </div>
                            <div class="text-[10px] text-zcMut">EXP: ${dateStr}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 text-[11px] font-semibold text-slate-500 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200">
                            <span>Jml:</span>
                            <input type="number" min="1" max="100" value="1" class="barcode-item-qty w-12 text-center text-xs font-bold border border-slate-300 rounded bg-white py-0.5 focus:outline-none focus:border-zc">
                        </div>
                        <button type="button" onclick="printSingleBarcodeRow(this, 'batch', '${b.id}')" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-2xs" title="Cetak label batch ini saja">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Cetak
                        </button>
                    </div>
                </div>
            `;
        });
        
        data.sn.forEach(sn => {
            list.innerHTML += `
                <div class="barcode-row flex items-center justify-between p-3 bg-white border border-zcBrd rounded-xl shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="barcode-item-check rounded text-zc focus:ring-zc h-4 w-4 cursor-pointer" data-tipe="sn" data-id="${sn.serial_number}" onchange="updateBarcodeSelectedCount()">
                        <div>
                            <div class="font-bold text-purple-700 font-mono text-xs">${sn.serial_number}</div>
                            <div class="text-[10px] text-purple-700/60 font-semibold uppercase">Unit Alkes (1:1 SN)</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" class="barcode-item-qty" value="1">
                        <button type="button" onclick="printSingleBarcodeRow(this, 'sn', '${sn.serial_number}')" class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-2xs" title="Cetak label SN ini saja">
                            <svg class="w-3.5 h-3.5 text-purple-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Cetak
                        </button>
                    </div>
                </div>
            `;
        });
        updateBarcodeSelectedCount();
    } catch (e) {
        alert("Gagal mengambil data dari server.");
    }
}

function toggleSelectAllBarcode(checked) {
    const checks = document.querySelectorAll('.barcode-item-check');
    checks.forEach(c => c.checked = checked);
    updateBarcodeSelectedCount();
}

function updateBarcodeSelectedCount() {
    const checked = document.querySelectorAll('.barcode-item-check:checked');
    const badge = document.getElementById('cetak_selected_count');
    if (badge) badge.innerText = `${checked.length} item dipilih`;
}

function printSingleBarcodeRow(btn, tipe, id) {
    const row = btn.closest('.barcode-row');
    const qtyInp = row ? row.querySelector('.barcode-item-qty') : null;
    const qty = qtyInp ? Math.max(1, parseInt(qtyInp.value, 10) || 1) : 1;
    window.open(`../inventori/cetak_batch_stiker.php?tipe=${tipe}&id=${encodeURIComponent(id)}&qty=${qty}`, '_blank');
}

function submitBatchPrint() {
    const checked = document.querySelectorAll('.barcode-item-check:checked');
    if (checked.length === 0) {
        alert('Silakan centang minimal 1 nomor batch atau serial number untuk dicetak massal.');
        return;
    }
    const items = [];
    checked.forEach(chk => {
        const row = chk.closest('.barcode-row');
        const qtyInp = row ? row.querySelector('.barcode-item-qty') : null;
        const qty = qtyInp ? Math.max(1, parseInt(qtyInp.value, 10) || 1) : 1;
        items.push({
            tipe: chk.dataset.tipe,
            id: chk.dataset.id,
            qty: qty
        });
    });
    
    // Kirim via dynamic form POST ke cetak_batch_stiker.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../inventori/cetak_batch_stiker.php?mode=a4';
    form.target = '_blank';
    
    const inp = document.createElement('input');
    inp.type = 'hidden';
    inp.name = 'selected_items';
    inp.value = JSON.stringify(items);
    
    form.appendChild(inp);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function filterProduk() {
    let input = document.getElementById("searchProduk");
    let filter = input.value.toLowerCase();
    let container = document.getElementById("produk-list-container");
    let items = container.querySelectorAll('.produk-item');

    items.forEach(item => {
        let text = item.innerText.toLowerCase();
        if (text.includes(filter)) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    });
}
</script>

<?php layoutEnd(); ?>

