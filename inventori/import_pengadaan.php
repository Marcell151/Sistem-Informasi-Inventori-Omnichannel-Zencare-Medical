<?php
// File: inventori/import_pengadaan.php
// Modul Import Pengadaan Barang (CSV/Excel) – Admin & Kasir
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin', 'kasir']);

$activeCabang = $_SESSION['id_cabang'] ?? 1;
if (isset($_GET['cabang'])) $_SESSION['id_cabang'] = $activeCabang = intval($_GET['cabang']);

$msg = ''; $msgType = ''; $importResults = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_csv'])) {
    $file = $_FILES['file_csv'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $msg = "Upload gagal. Error code: " . $file['error']; $msgType = 'error';
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            $msg = "Format file tidak didukung. Hanya .csv diizinkan."; $msgType = 'error';
        } else {
            $handle = fopen($file['tmp_name'], 'r');
            fgetcsv($handle); // skip header
            $ok = 0; $skip = 0;
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < 4) { $skip++; continue; }
                [$skuVariasi, $qtyMasuk, $namaSupplier, $hargaBeli] = $row;
                $skuVariasi = trim($skuVariasi);
                $qtyMasuk   = intval(trim($qtyMasuk));
                if (!$skuVariasi || $qtyMasuk <= 0) { $skip++; continue; }

                $varQ = $pdo->prepare("SELECT id FROM produk_variasi WHERE sku_variasi=? AND is_active=1");
                $varQ->execute([$skuVariasi]);
                $idVariasi = $varQ->fetchColumn();

                if (!$idVariasi) {
                    $importResults[] = ['status' => 'skip', 'msg' => "SKU '$skuVariasi' tidak ditemukan di sistem."];
                    $skip++; continue;
                }

                // Upsert stok_cabang
                $chk = $pdo->prepare("SELECT id FROM stok_cabang WHERE id_variasi=? AND id_cabang=?");
                $chk->execute([$idVariasi, $activeCabang]);
                if ($chk->fetchColumn()) {
                    $pdo->prepare("UPDATE stok_cabang SET stok=stok+? WHERE id_variasi=? AND id_cabang=?")->execute([$qtyMasuk, $idVariasi, $activeCabang]);
                } else {
                    $pdo->prepare("INSERT INTO stok_cabang (id_variasi,id_cabang,stok) VALUES (?,?,?)")->execute([$idVariasi, $activeCabang, $qtyMasuk]);
                }

                // Kartu stok
                $sisaQ = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi=? AND id_cabang=?");
                $sisaQ->execute([$idVariasi, $activeCabang]);
                $sisa = $sisaQ->fetchColumn();
                $pdo->prepare("INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan) VALUES (?,?,'Masuk',?,?,?)")
                    ->execute([$activeCabang, $idVariasi, $qtyMasuk, $sisa, "Import Pengadaan CSV"]);

                $importResults[] = ['status' => 'ok', 'msg' => "SKU '$skuVariasi': +$qtyMasuk unit (sisa: $sisa)"];
                $ok++;
            }
            fclose($handle);
            $msg = "Import selesai: $ok baris berhasil, $skip baris dilewati.";
            $msgType = $skip > 0 ? 'info' : 'success';
        }
    }
}

layoutHead('Import Pengadaan');
layoutBodyOpen();
layoutSidebar('import');
layoutHeader('Import Pengadaan Barang', 'Upload file CSV untuk update stok masuk secara massal');
?>

<div class="max-w-3xl space-y-5">

    <?php if ($msg): ?>
        <div class="p-4 rounded-xl border text-xs font-semibold flex items-start gap-2 <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-sky-50 border-sky-200 text-sky-800') ?>">
            <span class="shrink-0"><?= $msgType === 'success' ? '✅' : ($msgType === 'error' ? '⛔' : 'ℹ️') ?></span>
            <div><?= htmlspecialchars($msg) ?>
                <?php if (!empty($importResults)): ?>
                    <ul class="mt-2 space-y-0.5">
                        <?php foreach ($importResults as $r): ?>
                            <li class="<?= $r['status'] === 'ok' ? 'text-emerald-700' : 'text-amber-700' ?>">
                                <?= $r['status'] === 'ok' ? '✓' : '⚠' ?> <?= htmlspecialchars($r['msg']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="bg-white border border-zcBorder rounded-2xl shadow-sm p-6">
        <h2 class="text-sm font-bold text-zcText mb-1">Upload File CSV Pengadaan</h2>
        <p class="text-xs text-zcMuted mb-5">Format kolom: <code class="bg-slate-100 px-2 py-0.5 rounded font-mono">sku_variasi, qty_masuk, nama_supplier, harga_beli</code></p>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="border-2 border-dashed border-zcBorder rounded-xl p-8 text-center bg-slate-50 hover:border-zcNavy transition cursor-pointer"
                 onclick="document.getElementById('file_csv').click()">
                <div class="text-3xl mb-3">📄</div>
                <p class="text-xs font-semibold text-zcText mb-1">Klik atau Drag & Drop File CSV</p>
                <p class="text-[11px] text-zcMuted">Format: .csv (max 5MB)</p>
                <input type="file" id="file_csv" name="file_csv" accept=".csv,.txt" class="hidden"
                    onchange="document.getElementById('file_label').innerText = this.files[0]?.name ?? 'Belum dipilih'">
                <p id="file_label" class="text-[11px] text-zcNavy font-semibold mt-3">Belum ada file dipilih</p>
            </div>
            <button type="submit" class="w-full bg-zcNavy hover:bg-zcNavyHv text-white font-bold text-xs py-3 rounded-xl transition shadow-sm">
                📥 Proses Import & Update Stok Cabang
            </button>
        </form>
    </div>

    <!-- Format Template -->
    <div class="bg-white border border-zcBorder rounded-2xl shadow-sm p-6">
        <h3 class="text-sm font-bold text-zcText mb-3">📋 Contoh Format CSV</h3>
        <div class="bg-slate-50 border border-zcBorder rounded-xl p-4 font-mono text-xs text-slate-700 overflow-x-auto">
            sku_variasi,qty_masuk,nama_supplier,harga_beli<br>
            OBT-2026-0001-SYR,50,PT. Kimia Farmasindo,12000<br>
            ALK-2026-0001-SET,10,PT. Alkes Medika Utama,450000<br>
            ALK-2026-0002-UNT,5,PT. Alkes Medika Utama,800000
        </div>
        <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-800">
            ⚠️ <strong>Aturan ketat:</strong> Jika SKU tidak ditemukan di master produk, baris tersebut dilewati (NULL-safe). Data tidak akan auto-create produk baru.
        </div>
    </div>
</div>

<?php layoutEnd(); ?>
