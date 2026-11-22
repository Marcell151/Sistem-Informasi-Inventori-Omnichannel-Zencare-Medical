<?php
// File: demo/index.php
// Panel Manajemen Preset Data Demo & Snapshot Database - ZenCare Medical (Superadmin / Admin)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';
require_once __DIR__ . '/../config/opname_helper.php';

requireRole(['superadmin', 'admin']);

$presetDir   = __DIR__ . '/presets';
$snapshotDir = __DIR__ . '/snapshots';
$metaFile    = $snapshotDir . '/meta.json';
$backupDir   = 'C:\\xampp\\backup_data';
$mysqlPath   = 'C:\\xampp\\mysql\\bin\\mysql.exe';
$dumpPath    = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

if (!is_dir($presetDir))   @mkdir($presetDir, 0777, true);
if (!is_dir($snapshotDir)) @mkdir($snapshotDir, 0777, true);
if (!is_dir($backupDir))   @mkdir($backupDir, 0777, true);

$dbName  = defined('DB_NAME') ? DB_NAME : 'db_inventory';
$dbUser  = defined('DB_USER') ? DB_USER : 'root';
$passArg = (defined('DB_PASS') && DB_PASS !== '') ? '-p"' . DB_PASS . '"' : '';

// Daftar Preset Resmi
$defaultSqlPath = file_exists(__DIR__ . '/../Demo_Bersih.sql')
    ? realpath(__DIR__ . '/../Demo_Bersih.sql')
    : ($backupDir . '\\Demo_Bersih.sql');

$presets = [
    'default_demo4' => [
        'title'   => '1. Default — Siap Demo Sidang (All-in-One)',
        'badge'   => 'DEFAULT UTAMA',
        'color'   => 'blue',
        'desc'    => 'Database default resmi (Demo_Bersih.sql) yang juga dipakai tombol Reset Stok Opname: Hufagrip Merah tepat 2 Karton + 50 Botol, Hufagrip Biru eceran 5 Botol, Tensimeter 2 Unit, Termometer 5 Unit, POS & E-Com selaras (pelanggan1/2/3), 92 konversi, dan siap uji Stok Opname 6 anomali.',
        'path'    => $defaultSqlPath,
        'tags'    => ['Hufagrip 2 Ktn / Ecer 5', 'Tensi 2 & Termo 5', '92 Konversi', 'Stok Opname Ready']
    ],
    'stok_normal' => [
        'title'   => '2. Preset Stok Normal (Baseline Sebelumnya)',
        'badge'   => 'STOK NORMAL',
        'color'   => 'emerald',
        'desc'    => 'Database Demo_Bersih versi sebelumnya dengan stok melimpah pada seluruh 59 produk aktif (sebelum penyesuaian stok kritis sidang), lengkap dengan 92 riwayat konversi berpasangan di Kartu Stok, akun pelanggan1/2/3, batch kadaluarsa SNM-24K061, serta data SN Alkes lengkap.',
        'path'    => $presetDir . '/demo_stok_normal.sql',
        'tags'    => ['Stok Melimpah', '92 Konversi', 'pelanggan1/2/3', '0 Mismatch']
    ],
    'omnichannel' => [
        'title'   => '3. Preset Omnichannel (POS & E-Com Barengan)',
        'badge'   => 'OMNICHANNEL',
        'color'   => 'indigo',
        'desc'    => 'Khusus demo transaksi POS & E-Commerce bersamaan + Race Condition: Hufagrip Merah 2 Karton + 50 Botol (1 POS vs 2 E-Com), Hufagrip Biru eceran 5 Botol (beli 10 -> Bongkar Box -> scan .A & .D), Tensimeter 2 Unit, Termometer 5 Unit (3 E-Com + 2 POS = 0), plus 4 order online aktif.',
        'path'    => $presetDir . '/demo_omnichannel.sql',
        'tags'    => ['1 POS vs 2 E-Com', 'Termo 3+2=0', '4 Order Aktif', 'Siap Packing']
    ],
    'fefo_konversi' => [
        'title'   => '4. Preset FEFO, Expired & Konversi Darurat',
        'badge'   => 'FEFO & BONGKAR BOX',
        'color'   => 'amber',
        'desc'    => '4 obat populer (Paracetamol 500mg, Panadol Extra, Tolak Angin, Promag) stok ecerannya habis terjual via transaksi POS nyata sehingga langsung memicu popup Bongkar Box Darurat; dilengkapi penerimaan batch ED +25/+28 hari (PCT-25C014 & BDR-25C022) untuk uji FEFO & Anomali FEFO.',
        'path'    => $presetDir . '/demo_fefo_konversi.sql',
        'tags'    => ['Eceran Habis (Trigger Bongkar)', 'Batch PCT-25C014 & BDR-25C022', 'Uji Anomali FEFO']
    ],
    'sudah_penerimaan' => [
        'title'   => '5. Preset Sudah Penerimaan & Retur (KUL-20261122-001)',
        'badge'   => 'PASCA PENERIMAAN',
        'color'   => 'rose',
        'desc'    => 'Sudah tercatat faktur KUL-20261122-001 (PT Kimia Farma + lampiran PDF nota): Hufagrip Biru 2 Ktn baik + 1 Ktn rusak menunggu pengganti (HUFPIL-2027-02), Hufagrip Merah 2 Ktn (HUF-TMP-28), Tensimeter (OMR-7120-201/202), & Termometer (OMR-MC246-21/22). Langsung siap demo retur & scan stiker tambahan!',
        'path'    => $presetDir . '/demo_sudah_penerimaan.sql',
        'tags'    => ['Faktur PDF Terlampir', 'Retur Menunggu Pengganti', 'Stiker Baru Siap Scan']
    ],
];

function loadSnapshotMeta(string $metaFile): array {
    if (!file_exists($metaFile)) return [];
    $dec = json_decode(file_get_contents($metaFile), true);
    return is_array($dec) ? $dec : [];
}

function saveSnapshotMeta(string $metaFile, array $meta): void {
    file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function backupBeforeSwitch(string $dumpPath, string $backupDir, string $dbUser, string $passArg, string $dbName): void {
    if (!file_exists($dumpPath)) return;
    $bakFile = $backupDir . '\\backup_sebelum_switch_' . date('Ymd_His') . '.sql';
    exec("\"$dumpPath\" -u $dbUser $passArg --routines --triggers --add-drop-table --default-character-set=utf8mb4 --result-file=\"$bakFile\" $dbName 2>&1");
}

function restoreSqlFile(string $mysqlPath, string $dbUser, string $passArg, string $dbName, string $sqlFile): array {
    if (!file_exists($mysqlPath)) {
        return [false, 'mysql.exe tidak ditemukan di C:\\xampp\\mysql\\bin\\mysql.exe'];
    }
    if (!file_exists($sqlFile)) {
        return [false, 'File SQL tidak ditemukan: ' . basename($sqlFile)];
    }
    $escaped = str_replace('\\', '/', realpath($sqlFile));
    $out = [];
    $ret = 0;
    exec("\"$mysqlPath\" -u $dbUser $passArg $dbName -e \"source $escaped\" 2>&1", $out, $ret);
    return [$ret === 0, implode("\n", $out)];
}

// Download handler untuk snapshot
if (isset($_GET['download'])) {
    $fn = basename($_GET['download']);
    $target = $snapshotDir . '/' . $fn;
    if (preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/', $fn) && file_exists($target)) {
        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fn . '"');
        header('Content-Length: ' . filesize($target));
        readfile($target);
        exit;
    }
}

$msg = $_GET['msg'] ?? '';
$msgType = $_GET['type'] ?? 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'aktifkan_preset') {
        $key = $_POST['preset_key'] ?? '';
        if (!isset($presets[$key])) {
            $msg = 'Preset tidak dikenali.';
            $msgType = 'error';
        } else {
            backupBeforeSwitch($dumpPath, $backupDir, $dbUser, $passArg, $dbName);
            [$ok, $log] = restoreSqlFile($mysqlPath, $dbUser, $passArg, $dbName, $presets[$key]['path']);
            if ($ok) {
                cekSesiOpnameAktif($pdo, true);
                header('Location: /inventory_zencare/demo/index.php?type=success&msg=' . urlencode("Database berhasil diganti ke: {$presets[$key]['title']}. Cadangan kondisi sebelumnya tersimpan di C:\\xampp\\backup_data."));
                exit;
            } else {
                $msg = 'Gagal mengaktifkan preset: ' . $log;
                $msgType = 'error';
            }
        }
    } elseif ($aksi === 'simpan_snapshot') {
        $nama = trim($_POST['nama_snapshot'] ?? '');
        $catatan = trim($_POST['catatan_snapshot'] ?? '');
        if ($nama === '') {
            $nama = 'Snapshot ' . date('d M Y H:i');
        }
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $nama));
        $slug = trim($slug, '_') ?: 'snapshot';
        $slug = substr($slug, 0, 32);
        $filename = 'snap_' . $slug . '_' . date('Ymd_His') . '.sql';
        $fullPath = $snapshotDir . '/' . $filename;

        if (!file_exists($dumpPath)) {
            $msg = 'mysqldump.exe tidak ditemukan di C:\\xampp\\mysql\\bin\\mysqldump.exe';
            $msgType = 'error';
        } else {
            $out = [];
            $ret = 0;
            exec("\"$dumpPath\" -u $dbUser $passArg --routines --triggers --add-drop-table --default-character-set=utf8mb4 --result-file=\"$fullPath\" $dbName 2>&1", $out, $ret);
            if ($ret === 0 && file_exists($fullPath) && filesize($fullPath) > 500) {
                $meta = loadSnapshotMeta($metaFile);
                $meta[$filename] = [
                    'filename'   => $filename,
                    'nama'       => $nama,
                    'catatan'    => $catatan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $_SESSION['nama_lengkap'] ?? 'Admin',
                    'size'       => filesize($fullPath),
                ];
                saveSnapshotMeta($metaFile, $meta);
                header('Location: /inventory_zencare/demo/index.php?type=success&msg=' . urlencode("Snapshot \"$nama\" berhasil disimpan ($filename)."));
                exit;
            } else {
                $msg = 'Gagal menyimpan snapshot database: ' . implode(' ', $out);
                $msgType = 'error';
            }
        }
    } elseif ($aksi === 'pakai_snapshot') {
        $fn = basename($_POST['filename'] ?? '');
        $target = $snapshotDir . '/' . $fn;
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/', $fn) || !file_exists($target)) {
            $msg = 'File snapshot tidak ditemukan.';
            $msgType = 'error';
        } else {
            backupBeforeSwitch($dumpPath, $backupDir, $dbUser, $passArg, $dbName);
            [$ok, $log] = restoreSqlFile($mysqlPath, $dbUser, $passArg, $dbName, $target);
            if ($ok) {
                cekSesiOpnameAktif($pdo, true);
                $meta = loadSnapshotMeta($metaFile);
                $dispName = $meta[$fn]['nama'] ?? $fn;
                header('Location: /inventory_zencare/demo/index.php?type=success&msg=' . urlencode("Database berhasil dipulihkan dari snapshot \"$dispName\"."));
                exit;
            } else {
                $msg = 'Gagal memulihkan snapshot: ' . $log;
                $msgType = 'error';
            }
        }
    } elseif ($aksi === 'hapus_snapshot') {
        $fn = basename($_POST['filename'] ?? '');
        $target = $snapshotDir . '/' . $fn;
        if (preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/', $fn) && file_exists($target)) {
            @unlink($target);
            $meta = loadSnapshotMeta($metaFile);
            unset($meta[$fn]);
            saveSnapshotMeta($metaFile, $meta);
            header('Location: /inventory_zencare/demo/index.php?type=success&msg=' . urlencode("Snapshot $fn telah dihapus."));
            exit;
        }
    }
}

// Kumpulkan daftar snapshot dari folder demo/snapshots/
$metaAll = loadSnapshotMeta($metaFile);
$snapshots = [];
foreach (glob($snapshotDir . '/*.sql') ?: [] as $fPath) {
    $fn = basename($fPath);
    $m = $metaAll[$fn] ?? [];
    $snapshots[] = [
        'filename'   => $fn,
        'nama'       => $m['nama'] ?? pathinfo($fn, PATHINFO_FILENAME),
        'catatan'    => $m['catatan'] ?? '',
        'created_at' => $m['created_at'] ?? date('Y-m-d H:i:s', filemtime($fPath)),
        'created_by' => $m['created_by'] ?? '-',
        'size'       => filesize($fPath),
    ];
}
usort($snapshots, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

// Statistik live database saat ini
$statProduk   = (int)$pdo->query("SELECT COUNT(*) FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id WHERE v.is_active=1 AND i.is_active=1")->fetchColumn();
$statBatch    = (int)$pdo->query("SELECT COUNT(*) FROM stok_batch")->fetchColumn();
$statSn       = (int)$pdo->query("SELECT COUNT(*) FROM unit_serial WHERE status='Tersedia'")->fetchColumn();
$statKonversi = (int)$pdo->query("SELECT COUNT(*) FROM histori_konversi")->fetchColumn();
$statJual     = (int)$pdo->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
$statOpname   = cekSesiOpnameAktif($pdo, true);

// Cek konsistensi stok
$chkRows = $pdo->query("
    SELECT v.id, COALESCE(st.stok,0) AS stok_toko,
           (SELECT COALESCE(SUM(stok_sisa),0) FROM stok_batch sb WHERE sb.id_variasi=v.id) AS sum_batch,
           (SELECT COUNT(*) FROM stok_batch sb WHERE sb.id_variasi=v.id) AS batch_cnt,
           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi=v.id AND us.status='Tersedia') AS sn_tersedia,
           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi=v.id) AS sn_cnt
    FROM produk_variasi v JOIN produk_induk i ON v.id_produk_induk=i.id
    LEFT JOIN stok_toko st ON st.id_variasi=v.id
    WHERE v.is_active=1 AND i.is_active=1
")->fetchAll(PDO::FETCH_ASSOC);
$mismatchCount = 0;
foreach ($chkRows as $r) {
    if ($r['batch_cnt'] > 0 && intval($r['stok_toko']) !== intval($r['sum_batch'])) $mismatchCount++;
    elseif ($r['sn_cnt'] > 0 && $r['batch_cnt'] == 0 && intval($r['stok_toko']) !== intval($r['sn_tersedia'])) $mismatchCount++;
}

layoutHead('Panel Demo & Snapshot Database');
layoutBodyOpen();
layoutSidebar('demo');
layoutHeader('Panel Demo & Snapshot Database', 'Utilitas cepat mengganti preset skenario demo serta menyimpan/memulihkan snapshot database');
?>

<div class="space-y-5 max-w-6xl mx-auto">

    <?php if (!empty($msg)): ?>
        <div class="p-4 rounded-xl border text-xs font-semibold flex items-center gap-2.5 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-blue-50 border-blue-200 text-blue-800') ?>">
            <span><?= htmlspecialchars($msg) ?></span>
        </div>
    <?php endif; ?>

    <!-- Live Telemetry Database Saat Ini -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white border border-zcBrd rounded-xl p-3.5 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Produk Aktif</span>
            <span class="text-lg font-extrabold text-[#0f2d5a]"><?= $statProduk ?></span>
        </div>
        <div class="bg-white border border-zcBrd rounded-xl p-3.5 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Batch</span>
            <span class="text-lg font-extrabold text-[#0f2d5a]"><?= $statBatch ?></span>
        </div>
        <div class="bg-white border border-zcBrd rounded-xl p-3.5 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">SN Tersedia</span>
            <span class="text-lg font-extrabold text-[#0f2d5a]"><?= $statSn ?></span>
        </div>
        <div class="bg-white border border-zcBrd rounded-xl p-3.5 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Riwayat Konversi</span>
            <span class="text-lg font-extrabold text-[#1a75d2]"><?= $statKonversi ?></span>
        </div>
        <div class="bg-white border border-zcBrd rounded-xl p-3.5 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Transaksi Jual</span>
            <span class="text-lg font-extrabold text-[#0f2d5a]"><?= $statJual ?></span>
        </div>
        <div class="bg-white border border-zcBrd rounded-xl p-3.5 shadow-2xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Integritas Stok</span>
            <?php if ($mismatchCount === 0): ?>
                <span class="inline-flex items-center gap-1 text-xs font-extrabold text-emerald-700 mt-1">● 0 Selisih</span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1 text-xs font-extrabold text-rose-700 mt-1">● <?= $mismatchCount ?> Mismatch</span>
            <?php endif; ?>
            <?php if ($statOpname): ?>
                <span class="block text-[10px] text-amber-600 font-bold mt-0.5">Opname Aktif</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 1: Preset Data Demo Resmi -->
    <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-zcBrd">
            <div>
                <h2 class="text-sm font-extrabold text-[#0f2d5a]">1. Preset Data Demo Siap Pakai</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pilih skenario database untuk sidang/presentasi. Kondisi database sebelum diganti otomatis dicadangkan di <code class="bg-slate-100 px-1 rounded">C:\xampp\backup_data</code>.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <?php foreach ($presets as $pKey => $p):
                $exists = file_exists($p['path']);
                $sizeKb = $exists ? round(filesize($p['path']) / 1024, 1) : 0;
                $badgeCls = match($p['color']) {
                    'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'amber'   => 'bg-amber-50 text-amber-700 border-amber-200',
                    'indigo'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'rose'    => 'bg-rose-50 text-rose-700 border-rose-200',
                    default   => 'bg-blue-50 text-blue-700 border-blue-200',
                };
                $btnCls = match($p['color']) {
                    'emerald' => 'bg-emerald-600 hover:bg-emerald-700 text-white',
                    'amber'   => 'bg-amber-600 hover:bg-amber-700 text-white',
                    'indigo'  => 'bg-indigo-600 hover:bg-indigo-700 text-white',
                    'rose'    => 'bg-rose-600 hover:bg-rose-700 text-white',
                    default   => 'bg-[#0f2d5a] hover:bg-[#1a75d2] text-white',
                };
            ?>
            <div class="border border-slate-200 rounded-xl p-4 flex flex-col justify-between bg-[#f8fafc]/60">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold border <?= $badgeCls ?>"><?= htmlspecialchars($p['badge']) ?></span>
                        <span class="text-[10px] font-mono text-slate-400"><?= $exists ? ($sizeKb . ' KB') : 'Belum ada' ?></span>
                    </div>
                    <h3 class="text-sm font-bold text-[#0f2d5a]"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed"><?= htmlspecialchars($p['desc']) ?></p>
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        <?php foreach ($p['tags'] as $tg): ?>
                            <span class="px-2 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-semibold text-slate-600"><?= htmlspecialchars($tg) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form method="POST" class="mt-4 pt-3 border-t border-slate-200" onsubmit="return confirm('Aktifkan <?= addslashes($p['title']) ?>? Database aktif akan ditimpa (backup otomatis tetap dibuat).');">
                    <input type="hidden" name="aksi" value="aktifkan_preset">
                    <input type="hidden" name="preset_key" value="<?= htmlspecialchars($pKey) ?>">
                    <button type="submit" <?= !$exists ? 'disabled' : '' ?> class="w-full py-2 px-3 rounded-lg text-xs font-bold transition shadow-2xs cursor-pointer <?= $exists ? $btnCls : 'bg-slate-200 text-slate-400 cursor-not-allowed' ?>">
                        Gunakan Preset Ini
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Section 2: Simpan & Kelola Snapshot Save Slots -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Form Simpan Snapshot Baru -->
        <div class="lg:col-span-5 bg-white border border-zcBrd rounded-2xl p-5 shadow-2xs">
            <h2 class="text-sm font-extrabold text-[#0f2d5a] pb-3 mb-4 border-b border-zcBrd">2. Simpan Database Saat Ini (Save Slot)</h2>
            <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                Sudah mengubah data, menambah produk/transaksi, atau menyiapkan kondisi khusus? Simpan sebagai file <code class="bg-slate-100 px-1 rounded">.sql</code> di folder <code class="bg-slate-100 px-1 rounded">demo/snapshots/</code> agar bisa dipanggil kembali kapan saja (mendukung banyak save slot).
            </p>
            <form method="POST" class="space-y-3.5">
                <input type="hidden" name="aksi" value="simpan_snapshot">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Snapshot *</label>
                    <input type="text" name="nama_snapshot" required placeholder="Contoh: Sebelum Demo Sidang / Setelah Uji POS"
                        class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#1a75d2]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                    <textarea name="catatan_snapshot" rows="2" placeholder="Keterangan singkat isi kondisi database ini..."
                        class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:border-[#1a75d2]"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#1a75d2] hover:bg-[#0f2d5a] text-white text-xs font-bold transition shadow-2xs cursor-pointer">
                    + Simpan Snapshot Database Sekarang
                </button>
            </form>
        </div>

        <!-- Daftar Snapshot Tersimpan -->
        <div class="lg:col-span-7 bg-white border border-zcBrd rounded-2xl p-5 shadow-2xs">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-zcBrd">
                <div>
                    <h2 class="text-sm font-extrabold text-[#0f2d5a]">3. Daftar Snapshot Tersimpan (<?= count($snapshots) ?>)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Klik <strong>Pakai</strong> untuk mengaktifkan kembali kondisi database yang pernah Anda simpan.</p>
                </div>
            </div>

            <?php if (empty($snapshots)): ?>
                <div class="py-10 text-center text-xs text-slate-400 italic border border-dashed border-slate-200 rounded-xl">
                    Belum ada snapshot tersimpan. Gunakan formulir di samping untuk menyimpan kondisi database saat ini.
                </div>
            <?php else: ?>
                <div class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                    <?php foreach ($snapshots as $sn): ?>
                    <div class="border border-slate-200 rounded-xl p-3.5 bg-[#f8fafc] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-[#0f2d5a]"><?= htmlspecialchars($sn['nama']) ?></span>
                                <span class="text-[10px] font-mono text-slate-400 bg-white px-1.5 py-0.5 rounded border border-slate-200"><?= round($sn['size'] / 1024, 1) ?> KB</span>
                            </div>
                            <?php if (!empty($sn['catatan'])): ?>
                                <p class="text-[11px] text-slate-600 mt-1"><?= htmlspecialchars($sn['catatan']) ?></p>
                            <?php endif; ?>
                            <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                <?= htmlspecialchars($sn['created_at']) ?> &bull; oleh <?= htmlspecialchars($sn['created_by']) ?> &bull; <?= htmlspecialchars($sn['filename']) ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <form method="POST" onsubmit="return confirm('Pulihkan database ke snapshot \"<?= addslashes($sn['nama']) ?>\"?');" class="inline">
                                <input type="hidden" name="aksi" value="pakai_snapshot">
                                <input type="hidden" name="filename" value="<?= htmlspecialchars($sn['filename']) ?>">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#0f2d5a] hover:bg-[#1a75d2] text-white text-[11px] font-bold transition cursor-pointer">
                                    Pakai
                                </button>
                            </form>
                            <a href="?download=<?= urlencode($sn['filename']) ?>" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 text-[11px] font-bold transition">
                                .sql
                            </a>
                            <form method="POST" onsubmit="return confirm('Hapus snapshot \"<?= addslashes($sn['nama']) ?>\" secara permanen?');" class="inline">
                                <input type="hidden" name="aksi" value="hapus_snapshot">
                                <input type="hidden" name="filename" value="<?= htmlspecialchars($sn['filename']) ?>">
                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-rose-50 border border-rose-200 text-rose-600 text-[11px] font-bold transition cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 3: Pintasan Cepat Halaman Demo -->
    <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-2xs flex flex-wrap items-center justify-between gap-2">
        <span class="text-xs font-bold text-[#0f2d5a]">Pintasan Cepat Demo:</span>
        <div class="flex flex-wrap gap-2">
            <a href="/inventory_zencare/pos/pos.php" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#eef6fb] hover:bg-sky-100 border border-[#cde3f5] text-[#0f2d5a] text-xs font-bold transition">POS Kasir</a>
            <a href="/inventory_zencare/ecommerce/index.php" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#eef6fb] hover:bg-sky-100 border border-[#cde3f5] text-[#0f2d5a] text-xs font-bold transition">E-Commerce</a>
            <a href="/inventory_zencare/admin/pesanan.php" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Pesanan & Packing</a>
            <a href="/inventory_zencare/inventori/tambah_stok.php" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Penerimaan Barang</a>
            <a href="/inventory_zencare/inventori/konversi_uom.php" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Konversi UOM</a>
            <a href="/inventory_zencare/inventori/stok_opname.php" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Stok Opname</a>
            <a href="/inventory_zencare/laporan/kartu_stok.php" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Kartu Stok</a>
        </div>
    </div>

</div>

<?php layoutEnd(); ?>
