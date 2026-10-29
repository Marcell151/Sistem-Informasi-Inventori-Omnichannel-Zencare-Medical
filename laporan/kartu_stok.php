<?php
// File: laporan/kartu_stok.php - Laporan Kartu Stok (Audit Trail Omnichannel)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$id_variasi = intval($_GET['id_variasi'] ?? 0);
$dari       = $_GET['dari'] ?? date('Y-m-01');
$sampai     = $_GET['sampai'] ?? date('Y-m-d');
$filterKonversi = isset($_GET['filter_konversi']) && $_GET['filter_konversi'] == '1';
$filterKanal = trim($_GET['kanal'] ?? 'semua');
if ($filterKonversi) {
    // Aktivitas konversi UOM merupakan pergerakan internal inventori (tidak terikat kanal penjualan)
    $filterKanal = 'semua';
} elseif (!in_array($filterKanal, ['semua', 'POS', 'E-Commerce'])) {
    $filterKanal = 'semua';
}

$variasiList = $pdo->query("
    SELECT pv.id, pv.sku_variasi, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS label 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pi.is_active = 1 AND pv.is_active = 1
    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
")->fetchAll();

$produkData = null;
$logDataBesar = [];
$logDataKecil = [];

if ($id_variasi > 0) {
    // Info Barang
    $stmtProd = $pdo->prepare("
        SELECT pv.sku_variasi, pi.nama_produk, pv.nama_variasi, pi.kategori,
               pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pv.stok_minimum_kecil, pv.stok_minimum_besar, sc.stok
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_toko sc ON pv.id = sc.id_variasi 
        WHERE pv.id = ?
    ");
    $stmtProd->execute([$id_variasi]);
    $produkData = $stmtProd->fetch();
    
    if ($produkData) {
        if ($produkData['kategori'] === 'Alat Kesehatan') {
            $produkData['stok_riil_besar'] = floor(intval($produkData['stok']) / max(1, intval($produkData['rasio_konversi'])));
            $produkData['stok_riil_kecil'] = intval($produkData['stok']);
        } else {
            $stokDus = $pdo->prepare("SELECT COALESCE(SUM(stok_sisa),0) FROM stok_batch WHERE id_variasi=? AND no_batch NOT LIKE '%.%'");
            $stokDus->execute([$id_variasi]);
            $produkData['stok_riil_besar'] = floor(intval($stokDus->fetchColumn()) / max(1, intval($produkData['rasio_konversi'])));

            $stokEceran = $pdo->prepare("SELECT COALESCE(SUM(stok_sisa),0) FROM stok_batch WHERE id_variasi=? AND no_batch LIKE '%.%'");
            $stokEceran->execute([$id_variasi]);
            $produkData['stok_riil_kecil'] = intval($stokEceran->fetchColumn());
        }
    }

    // Ambil SEMUA data mutasi untuk hitung running balance
    $stmtLog = $pdo->prepare("
        SELECT ks.id, ks.tanggal, ks.keterangan as deskripsi, ks.jenis_mutasi, ks.qty, ks.no_ref_dokumen, ks.kanal, ks.alasan_mutasi,
               COALESCE(u.nama_lengkap, up.nama_lengkap, 'Sistem') AS nama_lengkap, ks.satuan_tipe, ks.id_batch, sb.no_batch AS direct_batch_no
        FROM kartu_stok ks
        LEFT JOIN stok_batch sb ON ks.id_batch = sb.id
        LEFT JOIN users u ON ks.dibuat_oleh = u.id
        LEFT JOIN penjualan p ON (p.no_invoice = ks.no_ref_dokumen OR (ks.keterangan LIKE '%Penjualan Luring%' AND ks.keterangan LIKE CONCAT('%', p.no_invoice, '%')))
        LEFT JOIN users up ON p.id_user = up.id
        WHERE ks.id_variasi = ? 
        ORDER BY ks.tanggal ASC, ks.id ASC
    ");
    $stmtLog->execute([$id_variasi]);
    $allLogs = $stmtLog->fetchAll();
    
    // BACKWARD CALCULATION RUNNING BALANCE
    $allLogsBesar = [];
    $allLogsKecil = [];
    foreach ($allLogs as $log) {
        if ($log['satuan_tipe'] === 'besar') $allLogsBesar[] = $log;
        else $allLogsKecil[] = $log;
    }
    
    // Helper function untuk memformat tampilan batch pada tabel kartu stok (MURNI NOMOR BATCH SAJA, TANPA KURUNG / SATUAN)
    function formatKartuStokBatch($batchStr) {
        if (empty($batchStr) || $batchStr === '-') return '-';
        $parts = is_array($batchStr) ? $batchStr : array_map('trim', explode(',', $batchStr));
        $cleanBatches = [];
        foreach ($parts as $p) {
            $c = trim(preg_replace('/\s*\(.*?\)/', '', $p));
            if ($c !== '' && !in_array($c, $cleanBatches)) {
                $cleanBatches[] = $c;
            }
        }
        if (empty($cleanBatches)) return '-';
        if (count($cleanBatches) > 1) {
            $htmlParts = array_map(function($p) {
                return '<div class="whitespace-nowrap font-mono text-xs font-bold text-slate-800">' . htmlspecialchars($p) . '</div>';
            }, $cleanBatches);
            return '<div class="space-y-0.5">' . implode('', $htmlParts) . '</div>';
        } else {
            return '<span class="font-mono text-xs font-bold text-slate-800">' . htmlspecialchars($cleanBatches[0]) . '</span>';
        }
    }

    // Helper function untuk memformat badge kanal penjualan / mutasi (Omnichannel)
    function formatKanalBadge($kanal) {
        $k = trim($kanal ?? '');
        if ($k === 'POS') {
            return '<span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-300 mr-1.5 print:border-none print:bg-transparent print:p-0">POS</span>';
        } elseif ($k === 'E-Commerce') {
            return '<span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 mr-1.5 print:border-none print:bg-transparent print:p-0">E-COMMERCE</span>';
        } elseif ($k === 'Penerimaan' || $k === 'PO') {
            return '<span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300 mr-1.5 print:border-none print:bg-transparent print:p-0">INBOUND</span>';
        } elseif ($k === 'Manual') {
            return '<span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-700 border border-slate-300 mr-1.5 print:border-none print:bg-transparent print:p-0">MANUAL</span>';
        }
        return '';
    }

    // Helper function untuk mengekstrak atau mencari nomor batch serta rincian logistik
    $getBatchForLog = function(&$log) use ($pdo, $id_variasi) {
        $desc = $log['deskripsi'] ?? '';
        $batchDetail = '';
        
        // 0. Prioritaskan nomor batch langsung dari kolom id_batch jika tersedia
        if (!empty($log['direct_batch_no'])) {
            $batchDetail = trim($log['direct_batch_no']);
        }
        
        // 1. Ekstrak dari teks deskripsi [Batch: ...] atau [SN: ...] atau Sub-Batch ...
        if (empty($batchDetail)) {
            if (preg_match('/\[(?:Batch|SN)[:\s]+(.*?)\]/i', $desc, $m)) {
                $batchDetail = trim($m[1]);
            } elseif (preg_match('/(?:Lahir Sub-Batch|Sub-Batch|Bongkar Dus|Batch|SN)[:\s]+([A-Za-z0-9\-\.]+)/i', $desc, $m)) {
                $batchDetail = trim($m[1]);
            }
        }
        
        // 2. Fallback: Cari dari detail_penjualan (POS / E-Commerce) jika kosong
        if (empty($batchDetail)) {
            $invoiceNo = $log['no_ref_dokumen'] ?? '';
            if (empty($invoiceNo) && preg_match('/(POS-\d+-\d+|INV-\d+-\d+|ZEN-\d+-\d+|WEB-\d+-\d+)/i', $desc, $mInv)) {
                $invoiceNo = $mInv[1];
            }
            if (!empty($invoiceNo)) {
                $qLogistik = $pdo->prepare("
                    SELECT dp.catatan_logistik 
                    FROM detail_penjualan dp 
                    JOIN penjualan p ON p.id = dp.id_penjualan 
                    WHERE p.no_invoice = ? AND dp.id_variasi = ?
                ");
                $qLogistik->execute([$invoiceNo, $id_variasi]);
                $allLogistik = $qLogistik->fetchAll(PDO::FETCH_COLUMN);

                $tipeTarget = $log['satuan_tipe'] ?? '';
                $picked = '';
                foreach ($allLogistik as $cStr) {
                    if (empty($cStr)) continue;
                    if ($tipeTarget === 'besar') {
                        if (preg_match('/(Karton|Dus|Box)/i', $cStr) || !preg_match('/\.[A-Za-z0-9]/i', $cStr)) {
                            $picked = $cStr;
                            break;
                        }
                    } elseif ($tipeTarget === 'kecil') {
                        if (preg_match('/(Botol|Strip|Pcs|Tablet|\.[A-Za-z0-9])/i', $cStr)) {
                            $picked = $cStr;
                            break;
                        }
                    }
                }
                $batchDetail = $picked ?: ($allLogistik[0] ?? '');
            }
        }
        
        // 3. Fallback: Cari dari penerimaan_detail jika dokumen penerimaan
        if (empty($batchDetail)) {
            $refPO = $log['no_ref_dokumen'] ?? '';
            if (empty($refPO) && preg_match('/Ref:\s*([A-Za-z0-9\-\_]+)/i', $desc, $mRef)) {
                $refPO = $mRef[1];
            }
            if (!empty($refPO)) {
                $qPO = $pdo->prepare("
                    SELECT pd.no_batch 
                    FROM penerimaan_detail pd 
                    JOIN penerimaan_stok ps ON ps.id = pd.id_penerimaan 
                    WHERE ps.no_referensi = ? AND pd.id_variasi = ? LIMIT 1
                ");
                $qPO->execute([$refPO, $id_variasi]);
                $val = $qPO->fetchColumn();
                if ($val) $batchDetail = $val;
            }
        }
        
        // Rincian Aktivitas: Jika deskripsi belum memiliki [Batch: ...], tambahkan rincian batch + satuan di dalam kurung
        if (!empty($batchDetail) && stripos($desc, '[Batch:') === false && stripos($desc, '[SN:') === false && stripos($desc, 'Sub-Batch') === false) {
            $log['deskripsi_tampil'] = $desc . ' [Batch: ' . $batchDetail . ']';
        } else {
            $log['deskripsi_tampil'] = $desc;
        }

        return $batchDetail;
    };

    // Proses Besar (Mundur dari stok sekarang)
    $allLogsBesar = array_reverse($allLogsBesar);
    $currentBalanceBesar = intval($produkData['stok_riil_besar']);
    $logDataBesar = [];
    
    foreach ($allLogsBesar as $log) {
        $q = intval($log['qty']);
        
        $log['qty_calc'] = $q;
        $log['sisa_stok_calc'] = $currentBalanceBesar; 
        
        // Hitung mundur untuk transaksi sebelumnya
        if ($log['jenis_mutasi'] === 'Masuk' || $log['jenis_mutasi'] === 'Pengembalian/Batal') {
            $currentBalanceBesar -= $q; 
        } else if ($log['jenis_mutasi'] === 'Keluar' || $log['jenis_mutasi'] === 'Karantina') {
            $currentBalanceBesar += $q; 
        }
        
        $logDate = date('Y-m-d', strtotime($log['tanggal']));
        if ($logDate >= $dari && $logDate <= $sampai) {
            $log['no_batch'] = $getBatchForLog($log);
            $logDataBesar[] = $log;
        }
    }
    $logDataBesar = array_reverse($logDataBesar);
    
    // Proses Kecil (Mundur dari stok sekarang)
    $allLogsKecil = array_reverse($allLogsKecil);
    $currentBalanceKecil = intval($produkData['stok_riil_kecil']);
    $logDataKecil = [];
    
    foreach ($allLogsKecil as $log) {
        $q = intval($log['qty']);
        
        $log['qty_calc'] = $q;
        $log['sisa_stok_calc'] = $currentBalanceKecil; 
        
        // Hitung mundur
        if ($log['jenis_mutasi'] === 'Masuk' || $log['jenis_mutasi'] === 'Pengembalian/Batal') {
            $currentBalanceKecil -= $q; 
        } else if ($log['jenis_mutasi'] === 'Keluar' || $log['jenis_mutasi'] === 'Karantina') {
            $currentBalanceKecil += $q; 
        }
        
        $logDate = date('Y-m-d', strtotime($log['tanggal']));
        if ($logDate >= $dari && $logDate <= $sampai) {
            $log['no_batch'] = $getBatchForLog($log);
            $logDataKecil[] = $log;
        }
    }
    $logDataKecil = array_reverse($logDataKecil);

    // Filter khusus audit pembongkaran dus (Konversi UOM)
    $totalDusDibongkar = 0;
    $totalPcsHasilBongkar = 0;
    if ($filterKonversi) {
        $logDataBesar = array_values(array_filter($logDataBesar, function($l) use (&$totalDusDibongkar) {
            $isBongkar = (stripos($l['deskripsi'], 'Bongkar') !== false || stripos($l['deskripsi'], 'Konversi') !== false);
            if ($isBongkar && $l['jenis_mutasi'] === 'Keluar') {
                $totalDusDibongkar += $l['qty_calc'];
            }
            return $isBongkar;
        }));

        $logDataKecil = array_values(array_filter($logDataKecil, function($l) use (&$totalPcsHasilBongkar) {
            $isHasil = (stripos($l['deskripsi'], 'Bongkar') !== false || stripos($l['deskripsi'], 'Sub-Batch') !== false || stripos($l['deskripsi'], 'Konversi') !== false);
            if ($isHasil && $l['jenis_mutasi'] === 'Masuk') {
                $totalPcsHasilBongkar += $l['qty_calc'];
            }
            return $isHasil;
        }));
    }

    // Filter berdasarkan Kanal Transaksi (Omnichannel)
    if ($filterKanal === 'POS') {
        $logDataBesar = array_values(array_filter($logDataBesar, function($l) {
            return ($l['kanal'] === 'POS' || stripos($l['alasan_mutasi'], 'POS') !== false || stripos($l['deskripsi'], 'POS-') !== false || stripos($l['deskripsi'], 'Penjualan Luring') !== false);
        }));
        $logDataKecil = array_values(array_filter($logDataKecil, function($l) {
            return ($l['kanal'] === 'POS' || stripos($l['alasan_mutasi'], 'POS') !== false || stripos($l['deskripsi'], 'POS-') !== false || stripos($l['deskripsi'], 'Penjualan Luring') !== false);
        }));
    } elseif ($filterKanal === 'E-Commerce') {
        $logDataBesar = array_values(array_filter($logDataBesar, function($l) {
            return ($l['kanal'] === 'E-Commerce' || stripos($l['alasan_mutasi'], 'E-Commerce') !== false || stripos($l['deskripsi'], 'E-Commerce') !== false || stripos($l['deskripsi'], 'INV-') !== false || stripos($l['deskripsi'], 'ZEN-') !== false || stripos($l['deskripsi'], 'WEB-') !== false);
        }));
        $logDataKecil = array_values(array_filter($logDataKecil, function($l) {
            return ($l['kanal'] === 'E-Commerce' || stripos($l['alasan_mutasi'], 'E-Commerce') !== false || stripos($l['deskripsi'], 'E-Commerce') !== false || stripos($l['deskripsi'], 'INV-') !== false || stripos($l['deskripsi'], 'ZEN-') !== false || stripos($l['deskripsi'], 'WEB-') !== false);
        }));
    }
}

layoutHead('Kartu Stok');
layoutBodyOpen();
layoutSidebar('laporan_kartu_stok');
layoutHeader('Kartu Stok Barang', 'Log mutasi fisik keluar/masuk (Pusat - Muharto)');
?>

<div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm mb-6">
    <form method="GET" onsubmit="var sk = document.getElementById('select_kanal'); if(sk) sk.disabled = false;" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[250px]">
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Pilih Barang *</label>
            <select name="id_variasi" required class="select2 w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                <option value="">-- Pilih Barang / SKU --</option>
                <?php foreach($variasiList as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $id_variasi==$v['id']?'selected':'' ?>>
                    <?= htmlspecialchars($v['label']) ?> [<?= htmlspecialchars($v['sku_variasi']) ?>]
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Dari Tanggal</label>
            <input type="date" name="dari" value="<?= htmlspecialchars($dari) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sampai Tanggal</label>
            <input type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Kanal Transaksi</label>
            <select name="kanal" id="select_kanal" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-bold transition <?= $filterKonversi ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : '' ?>" <?= $filterKonversi ? 'disabled' : '' ?>>
                <option value="semua" <?= $filterKanal === 'semua' ? 'selected' : '' ?>>Semua Kanal (Omnichannel)</option>
                <option value="POS" <?= $filterKanal === 'POS' ? 'selected' : '' ?>>Hanya POS (Kasir Offline)</option>
                <option value="E-Commerce" <?= $filterKanal === 'E-Commerce' ? 'selected' : '' ?>>Hanya E-Commerce (Online)</option>
            </select>
            <div id="hint_kanal_disabled" class="text-[10px] text-amber-700 font-semibold mt-1 <?= $filterKonversi ? '' : 'hidden' ?>"></div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-zcTxt mb-1.5">Kategori Aktivitas</label>
            <select name="filter_konversi" id="select_konversi" onchange="handleKonversiChange(this.value)" class="text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50 font-bold">
                <option value="0" <?= !$filterKonversi ? 'selected' : '' ?>>Semua Aktivitas</option>
                <option value="1" <?= $filterKonversi ? 'selected' : '' ?>>Semua Aktivitas Konversi</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-zc hover:bg-zcHv text-white font-bold text-sm rounded-xl transition flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Tampilkan
            </button>
            <?php if ($produkData): ?>
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl transition flex items-center gap-2 print:hidden">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Cetak Format Fisik
            </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($id_variasi > 0 && $produkData && $filterKanal !== 'semua'): ?>
<!-- BANNER FILTER KANAL OMNICHANNEL -->
<div class="mb-6 p-4 rounded-2xl bg-blue-50 border-2 border-blue-300 text-blue-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm print:hidden">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-xs shrink-0 shadow-xs">
            <?= $filterKanal === 'POS' ? 'POS' : 'WEB' ?>
        </div>
        <div>
            <h4 class="text-sm font-black uppercase tracking-wide">Filter Kanal Aktif: <?= $filterKanal === 'POS' ? 'Kasir Offline (POS)' : 'Penjualan Daring (E-Commerce)' ?></h4>
            <p class="text-xs text-blue-800 mt-0.5">Menampilkan pergerakan log stok khusus yang keluar melalui kanal <strong><?= htmlspecialchars($filterKanal) ?></strong>.</p>
        </div>
    </div>
    <div class="flex items-center gap-2 self-stretch sm:self-auto">
        <a href="kartu_stok.php?id_variasi=<?= $id_variasi ?>&dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>&filter_konversi=<?= $filterKonversi ? '1' : '0' ?>&kanal=semua" class="px-3.5 py-1.5 bg-white hover:bg-slate-100 border border-blue-200 text-blue-700 text-xs font-bold rounded-xl transition shadow-2xs">
            ✕ Tampilkan Semua Kanal
        </a>
    </div>
</div>
<?php endif; ?>

<?php if ($id_variasi > 0 && $produkData && $filterKonversi): ?>
<!-- BANNER LOG KHUSUS PEMBONGKARAN SATUAN BESAR -->
<div class="mb-6 p-4 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm print:hidden">
    <div class="flex items-center gap-3">
        <div>
            <h4 class="text-sm font-black uppercase tracking-wide">Khusus Konversi Satuan (Bongkar Satuan Besar)</h4>
            <p class="text-xs text-amber-800 mt-0.5">Memfilter mutasi hanya pada aktivitas pemecahan Satuan Besar menjadi Satuan Kecil.</p>
        </div>
    </div>
    <div class="flex items-center gap-2 self-stretch sm:self-auto">
        <div class="bg-white px-4 py-2 rounded-xl border border-amber-200 text-center shadow-xs flex-1 sm:flex-initial">
            <span class="block text-[10px] uppercase font-bold text-slate-500">Total Dibongkar (Satuan Besar)</span>
            <span class="text-base font-black text-rose-600">-<?= $totalDusDibongkar ?> <?= htmlspecialchars($produkData['satuan_besar'] ?? 'Besar') ?></span>
        </div>
        <div class="bg-white px-4 py-2 rounded-xl border border-amber-200 text-center shadow-xs flex-1 sm:flex-initial">
            <span class="block text-[10px] uppercase font-bold text-slate-500">Total Tercipta (Satuan Kecil)</span>
            <span class="text-base font-black text-emerald-600">+<?= $totalPcsHasilBongkar ?> <?= htmlspecialchars($produkData['satuan_kecil'] ?? 'Kecil') ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($id_variasi > 0 && $produkData): ?>
<!-- TAB NAVIGATION -->
<div class="flex border-b border-slate-200 mb-6 print:hidden">
    <?php if ($produkData['kategori'] !== 'Alat Kesehatan'): ?>
    <button onclick="switchTab('besar')" id="tab_btn_besar" class="px-6 py-3 font-bold text-sm border-b-4 border-amber-600 text-amber-700 transition">Kartu Stok Gudang (Satuan Besar)</button>
    <?php endif; ?>
    <button onclick="switchTab('kecil')" id="tab_btn_kecil" class="px-6 py-3 font-bold text-sm border-b-4 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition"><?= ($produkData['kategori'] === 'Alat Kesehatan') ? 'Kartu Stok Alat Kesehatan (Unit Fisik)' : 'Kartu Stok Etalase (Satuan Kecil)' ?></button>
</div>
<div id="print-container" class="space-y-8 print:space-y-0 w-full">
    <?php if ($produkData['kategori'] !== 'Alat Kesehatan'): ?>
    <!-- TABEL KARTU STOK GROSIR (BESAR) -->
    <div id="tab_content_besar" class="bg-white p-8 md:p-12 shadow-sm rounded-none border border-slate-300 w-full max-w-5xl mx-auto font-sans print:m-0 print:border-none print:shadow-none print:p-0 print:block page-break-after">
        <!-- Kop Surat Resmi Bersama Logo ZenCare (Khusus Print) -->
        <div class="hidden print:flex border-b-2 border-slate-900 pb-2 mb-4 justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" style="height: 44px; width: auto; object-fit: contain;" onerror="this.src='../gambar/Logo.png'">
                <div>
                    <h1 style="font-size: 15pt; font-weight: 800; color: #0f2d5a; margin: 0; line-height: 1.1;">ZENCARE MEDICAL</h1>
                    <p style="font-size: 8.5pt; color: #475569; margin: 2px 0 0 0; font-weight: 600;">Distributor Alat Kesehatan dan Obat Omnichannel</p>
                </div>
            </div>
            <div style="text-align: right; font-size: 8pt; color: #475569; line-height: 1.4;">
                <div style="font-weight: bold; color: #0f2d5a;">Dokumen Resmi Kartu Stok Gudang</div>
                <div>Dicetak: <?= date('d/m/Y H:i') ?> WIB</div>
                <div>Oleh: <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Superadmin') ?></div>
            </div>
        </div>

        <h1 class="text-center text-xl md:text-2xl font-black mb-6 tracking-wide text-black uppercase">Kartu Stok Gudang (Satuan Besar)</h1>
        
        <!-- Informasi Header Kartu -->
        <div class="grid grid-cols-2 gap-x-12 mb-6 text-sm font-semibold text-black">
            <div class="grid grid-cols-[130px_10px_auto] gap-y-1">
                <div>Nama Barang</div><div>:</div><div><?= htmlspecialchars($produkData['nama_produk'] . ' - ' . $produkData['nama_variasi']) ?></div>
                <div>Kode Barang</div><div>:</div><div><?= htmlspecialchars($produkData['sku_variasi']) ?></div>
                <div>Satuan (Grosir)</div><div>:</div><div class="font-black text-amber-700 print:text-black uppercase"><?= htmlspecialchars($produkData['satuan_besar'] ?? '-') ?></div>
            </div>
            <div class="grid grid-cols-[130px_10px_auto] gap-y-1">
                <div>Minimum Stok</div><div>:</div><div><?= htmlspecialchars($produkData['stok_minimum_besar']) ?> <?= htmlspecialchars($produkData['satuan_besar']) ?></div>
                <div>Stok Riil (Sisa)</div><div>:</div><div class="text-rose-600 print:text-black font-bold"><?= htmlspecialchars($produkData['stok_riil_besar']) ?></div>
                <div>Lokasi (Toko)</div><div>:</div><div>Pusat (Muharto)</div>
            </div>
        </div>

        <!-- Tabel Kartu Stok Besar -->
        <table class="w-full border-collapse border border-slate-900 text-sm print:text-xs">
            <thead class="bg-amber-600 text-white border-b-2 border-slate-900">
                <tr>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-24">Tanggal</th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-36">No. Batch</th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle">Jenis Mutasi & Aktivitas</th>
                    <th colspan="3" class="border border-slate-900 px-3 py-1.5 text-center">Kuantitas</th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-32">Penanggung Jawab</th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-32">Keterangan</th>
                </tr>
                <tr>
                    <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Masuk</th>
                    <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Keluar</th>
                    <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Sisa</th>
                </tr>
            </thead>
            <tbody class="text-black bg-white">
                <?php if (empty($logDataBesar)): ?>
                    <tr>
                        <td colspan="7" class="border border-slate-900 px-3 py-8 text-center italic text-slate-500">Tidak ada mutasi kardus utuh / barang grosir.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logDataBesar as $log): ?>
                    <tr class="hover:bg-slate-50 print:hover:bg-transparent">
                        <td class="border border-slate-900 px-2 py-1.5 text-center whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($log['tanggal'])) ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center"><?= formatKartuStokBatch($log['no_batch'] ?? '-') ?></td>
                        <td class="border border-slate-900 px-3 py-1.5"><?= formatKanalBadge($log['kanal']) ?><b><?= htmlspecialchars($log['jenis_mutasi']) ?></b> - <?= htmlspecialchars($log['deskripsi_tampil'] ?? $log['deskripsi'] ?? '-') ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center font-bold <?= $log['jenis_mutasi']=='Masuk'?'text-emerald-700':'' ?>"><?= $log['jenis_mutasi']=='Masuk' ? number_format($log['qty_calc']) : '' ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center font-bold <?= $log['jenis_mutasi']=='Keluar'?'text-rose-700':'' ?>"><?= $log['jenis_mutasi']=='Keluar' ? number_format($log['qty_calc']) : '' ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center font-black bg-slate-50"><?= number_format($log['sisa_stok_calc']) ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center text-xs"><?= htmlspecialchars($log['nama_lengkap'] ?? 'Sistem') ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-xs text-center text-slate-500">
                            <?= ($log['no_batch'] ? 'Batch Terdata' : '-') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- TABEL KARTU STOK ECERAN (KECIL) -->
    <?php if ($produkData['kategori'] === 'Alat Kesehatan'): ?>
    <div class="mb-5 flex items-start gap-3 p-4 rounded-xl text-sm font-medium bg-blue-50 border border-blue-200 text-blue-800 max-w-5xl mx-auto print:hidden">
        <svg class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span><strong>Info:</strong> Kartu Stok Satuan Besar tidak ditampilkan untuk kategori Alat Kesehatan. Setiap unit fisik dikelola dan dicatat secara individual (per Serial Number) sejak proses Penerimaan Barang, sehingga rasio konversinya adalah 1:1 dan tidak melalui proses mutasi pembongkaran kemasan.</span>
    </div>
    <?php endif; ?>
    
    <div id="tab_content_kecil" class="hidden bg-white p-8 md:p-12 shadow-sm rounded-none border border-slate-300 w-full max-w-5xl mx-auto font-sans print:m-0 print:border-none print:shadow-none print:p-0 print:block">
        <!-- Kop Surat Resmi Bersama Logo ZenCare (Khusus Print) -->
        <div class="hidden print:flex border-b-2 border-slate-900 pb-2 mb-4 justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" style="height: 44px; width: auto; object-fit: contain;" onerror="this.src='../gambar/Logo.png'">
                <div>
                    <h1 style="font-size: 15pt; font-weight: 800; color: #0f2d5a; margin: 0; line-height: 1.1;">ZENCARE MEDICAL</h1>
                    <p style="font-size: 8.5pt; color: #475569; margin: 2px 0 0 0; font-weight: 600;">Distributor Alat Kesehatan dan Obat Omnichannel</p>
                </div>
            </div>
            <div style="text-align: right; font-size: 8pt; color: #475569; line-height: 1.4;">
                <div style="font-weight: bold; color: #0f2d5a;">Dokumen Resmi Kartu Stok Etalase</div>
                <div>Dicetak: <?= date('d/m/Y H:i') ?> WIB</div>
                <div>Oleh: <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Superadmin') ?></div>
            </div>
        </div>

        <h1 class="text-center text-xl md:text-2xl font-black mb-6 tracking-wide text-black uppercase"><?= ($produkData['kategori'] === 'Alat Kesehatan') ? 'Kartu Stok Alat Kesehatan (Unit Fisik)' : 'Kartu Stok Etalase (Satuan Kecil)' ?></h1>
        
        <!-- Informasi Header Kartu -->
        <div class="grid grid-cols-2 gap-x-12 mb-6 text-sm font-semibold text-black">
            <div class="grid grid-cols-[130px_10px_auto] gap-y-1">
                <div>Nama Barang</div><div>:</div><div><?= htmlspecialchars($produkData['nama_produk'] . ' - ' . $produkData['nama_variasi']) ?></div>
                <div>Kode Barang</div><div>:</div><div><?= htmlspecialchars($produkData['sku_variasi']) ?></div>
                <div>Satuan (<?= ($produkData['kategori'] === 'Alat Kesehatan') ? 'Alkes' : 'Eceran' ?>)</div><div>:</div><div class="font-black text-emerald-700 print:text-black uppercase"><?= htmlspecialchars($produkData['satuan_kecil'] ?? '-') ?></div>
            </div>
            <div class="grid grid-cols-[130px_10px_auto] gap-y-1">
                <div>Minimum Stok</div><div>:</div><div><?= htmlspecialchars($produkData['stok_minimum_kecil']) ?> <?= htmlspecialchars($produkData['satuan_kecil']) ?></div>
                <div>Stok Riil (Sisa)</div><div>:</div><div class="text-rose-600 print:text-black font-bold"><?= htmlspecialchars($produkData['stok_riil_kecil']) ?></div>
                <div>Lokasi (Toko)</div><div>:</div><div>Pusat (Muharto)</div>
            </div>
        </div>

        <!-- Tabel Kartu Stok Kecil -->
        <table class="w-full border-collapse border border-slate-900 text-sm print:text-xs">
            <thead class="bg-emerald-600 text-white border-b-2 border-slate-900">
                <tr>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-24">Tanggal</th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-36"><?= ($produkData['kategori'] === 'Alat Kesehatan') ? 'Serial Number' : 'No. Batch' ?></th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle">Jenis Mutasi & Aktivitas</th>
                    <th colspan="3" class="border border-slate-900 px-3 py-1.5 text-center">Kuantitas</th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-32">Penanggung Jawab</th>
                    <th rowspan="2" class="border border-slate-900 px-3 py-2 text-center align-middle w-32">Keterangan</th>
                </tr>
                <tr>
                    <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Masuk</th>
                    <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Keluar</th>
                    <th class="border border-slate-900 px-2 py-1.5 text-center w-16">Sisa</th>
                </tr>
            </thead>
            <tbody class="text-black bg-white">
                <?php if (empty($logDataKecil)): ?>
                    <tr>
                        <td colspan="7" class="border border-slate-900 px-3 py-8 text-center italic text-slate-500">Tidak ada mutasi eceran.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logDataKecil as $log): ?>
                    <tr class="hover:bg-slate-50 print:hover:bg-transparent">
                        <td class="border border-slate-900 px-2 py-1.5 text-center whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($log['tanggal'])) ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center"><?= formatKartuStokBatch($log['no_batch'] ?? '-') ?></td>
                        <td class="border border-slate-900 px-3 py-1.5"><?= formatKanalBadge($log['kanal']) ?><b><?= htmlspecialchars($log['jenis_mutasi']) ?></b> - <?= htmlspecialchars($log['deskripsi_tampil'] ?? $log['deskripsi'] ?? '-') ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center font-bold <?= $log['jenis_mutasi']=='Masuk'?'text-emerald-700':'' ?>"><?= $log['jenis_mutasi']=='Masuk' ? number_format($log['qty_calc']) : '' ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center font-bold <?= $log['jenis_mutasi']=='Keluar'?'text-rose-700':'' ?>"><?= $log['jenis_mutasi']=='Keluar' ? number_format($log['qty_calc']) : '' ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center font-black bg-slate-50"><?= number_format($log['sisa_stok_calc']) ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-center text-xs"><?= htmlspecialchars($log['nama_lengkap'] ?? 'Sistem') ?></td>
                        <td class="border border-slate-900 px-2 py-1.5 text-xs text-center text-slate-500">
                            <?= ($log['no_batch'] ? ($produkData['kategori'] === 'Alat Kesehatan' ? 'SN Terdata' : 'Batch Terdata') : '-') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    /* Hide everything in the body by default */
    body * { visibility: hidden; }
    
    /* Only show the print container and its children */
    #print-container, #print-container * { visibility: visible; }
    
    /* Position the print container at the top left */
    #print-container { 
        position: absolute; 
        left: 0; 
        top: 0; 
        width: 100% !important; 
        padding: 0 !important;
        margin: 0 !important;
        box-sizing: border-box;
    }
    
    /* Force BOTH tabs to display, overriding the Tailwind 'hidden' class */
    #tab_content_besar, #tab_content_kecil {
        display: block !important;
        margin: 0 0 15mm 0 !important;
        border: none !important;
        box-shadow: none !important;
        width: 100% !important;
    }
    
    /* Break page after the first table */
    .page-break-after { page-break-after: always; }
    
    /* Page Setup */
    @page { 
        size: A4 portrait;
        margin: 15mm 12mm 18mm 12mm;
    }
    
    body, main, .flex-1, .min-w-0 {
        display: block !important;
        position: static !important;
        width: 100% !important;
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #000000 !important;
    }
    
    table thead { 
        display: table-header-group !important; 
        background-color: #f1f5f9 !important;
    }
    table thead th { 
        background-color: #f1f5f9 !important;
        color: #000000 !important;
        font-weight: 800 !important;
        border: 1px solid #000000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        padding: 5px 6px !important;
    }
    table tbody td {
        color: #000000 !important;
        border: 1px solid #334155 !important;
        padding: 4px 6px !important;
    }
    table tfoot { display: table-row-group !important; }
    table tr, table th, table td { break-inside: avoid !important; page-break-inside: avoid !important; }
    table td > *, table th > * { break-inside: avoid !important; page-break-inside: avoid !important; }
}
</style>
<?php endif; ?>

<script>
function switchTab(tab) {
    if(tab === 'besar') {
        document.getElementById('tab_content_besar').classList.remove('hidden');
        document.getElementById('tab_content_kecil').classList.add('hidden');
        document.getElementById('tab_btn_besar').className = 'px-6 py-3 font-bold text-sm border-b-4 border-amber-600 text-amber-700 transition';
        document.getElementById('tab_btn_kecil').className = 'px-6 py-3 font-bold text-sm border-b-4 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition';
    } else {
        document.getElementById('tab_content_kecil').classList.remove('hidden');
        document.getElementById('tab_content_besar').classList.add('hidden');
        document.getElementById('tab_btn_kecil').className = 'px-6 py-3 font-bold text-sm border-b-4 border-emerald-600 text-emerald-700 transition';
        document.getElementById('tab_btn_besar').className = 'px-6 py-3 font-bold text-sm border-b-4 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition';
    }
}

function handleKonversiChange(val) {
    var selKanal = document.getElementById('select_kanal');
    var hint = document.getElementById('hint_kanal_disabled');
    if (!selKanal) return;
    if (val === '1') {
        selKanal.value = 'semua';
        selKanal.disabled = true;
        selKanal.classList.add('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
        selKanal.classList.remove('bg-slate-50', 'text-zcTxt');
        if (hint) hint.classList.remove('hidden');
    } else {
        selKanal.disabled = false;
        selKanal.classList.remove('bg-slate-200', 'text-slate-400', 'cursor-not-allowed');
        selKanal.classList.add('bg-slate-50', 'text-zcTxt');
        if (hint) hint.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($produkData) && ($produkData['kategori'] ?? '') === 'Alat Kesehatan'): ?>
    switchTab('kecil');
    <?php endif; ?>
    
    let dari = document.querySelector('input[name="dari"]');
    let sampai = document.querySelector('input[name="sampai"]');
    
    if (dari && sampai) {
        function validateDates() {
            sampai.min = dari.value;
            dari.max = sampai.value;
        }

        dari.addEventListener('change', function() {
            if (sampai.value && sampai.value < dari.value) sampai.value = dari.value;
            validateDates();
        });
        
        sampai.addEventListener('change', function() {
            if (dari.value && sampai.value < dari.value) dari.value = sampai.value;
            validateDates();
        });
        
        validateDates();
    }
});
</script>

<?php layoutFooter(); ?>
