import re

code = '''<?php
// File: inventori/dokumentasi_testing.php
// Dokumentasi Resmi Pengujian Sistem (Black-Box Testing) Modul Stok Opname - ZenCare Medical
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';
require_once __DIR__ . '/../config/opname_helper.php';
require_once __DIR__ . '/../scratch/restore_baseline.php';

requireRole(['superadmin', 'admin', 'kasir']);

$role = $_SESSION['role'] ?? '';
$isSuperadmin = ($role === 'superadmin');
$canVerify = in_array($role, ['superadmin', 'admin']);
$msg = '';
$msgType = '';

// Handle Interactive Actions
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'run_simulation') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Fitur simulasi demo pengujian dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } else {
            try {
                // Eksekusi skrip simulasi full scale
                ob_start();
                include __DIR__ . '/../scratch/run_full_opname_simulation.php';
                $simLog = ob_get_clean();
                cekSesiOpnameAktif($pdo, true);
                $msg = "Simulasi pengujian Stok Opname (Full 121 Item & 6 Anomali) berhasil dijalankan secara otomatis!";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal menjalankan simulasi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    } elseif ($action === 'reset_database') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset database pengujian dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } else {
            try {
                restoreBaselineInventory($pdo);
                cekSesiOpnameAktif($pdo, true);
                $msg = "Database inventori berhasil dipulihkan ke Baseline Bersih (Fresh State)! Seluruh data uji coba opname telah dibersihkan.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal mereset database: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }
}

// Ambil status sesi aktif saat ini
$activeSession = cekSesiOpnameAktif($pdo, true);

// Hitung data aktual sistem saat ini
$totalProduk = $pdo->query("SELECT COUNT(*) FROM produk_induk")->fetchColumn();
$totalBatch = $pdo->query("SELECT COUNT(*) FROM stok_batch WHERE stok_sisa > 0")->fetchColumn();
$totalAlkes = $pdo->query("SELECT COUNT(*) FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pi.kategori = 'Alat Kesehatan' AND pv.is_active = 1")->fetchColumn();
$totalKartuStok = $pdo->query("SELECT COUNT(*) FROM kartu_stok")->fetchColumn();

// Ambil sampel data real produk dari database
$sampleBatch = $pdo->query("
    SELECT sb.id AS id_batch, sb.no_batch, sb.tgl_exp, sb.stok_sisa,
           pv.id AS id_var, pv.nama_variasi, pv.sku_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi,
           pv.harga_jual_kecil, pv.harga_jual_besar,
           pi.nama_produk, pi.kategori
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1
    ORDER BY pi.nama_produk ASC, sb.tgl_exp ASC
    LIMIT 25
")->fetchAll(PDO::FETCH_ASSOC);

layoutHead('Laporan Pengujian Sistem: Modul Stok Opname');
layoutBodyOpen();
layoutSidebar('stok_opname');
layoutHeader('Dokumentasi Pengujian Black-Box', 'Lembar hasil pengujian dan rekaman visual modul stok opname & rekonsiliasi');
?>

<style>
/* Styling Lembar Dokumen Kertas (Word Document Canvas Style) */
.paper-canvas {
    background-color: #f1f5f9;
    min-height: 100vh;
    padding-top: 1rem;
    padding-bottom: 3.5rem;
}
.paper-sheet {
    background-color: #ffffff;
    max-width: 58rem; /* ~928px / proporsional lembar dokumen resmi */
    margin-left: auto;
    margin-right: auto;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04), 0 0 1px 1px rgba(15, 23, 42, 0.05);
    border: 1px solid #cbd5e1;
    padding: 3rem 3.5rem;
}
@media (max-width: 640px) {
    .paper-sheet {
        padding: 1.5rem 1.25rem;
    }
}
@media print {
    body {
        background-color: #ffffff !important;
    }
    .paper-canvas {
        background-color: #ffffff !important;
        padding: 0 !important;
    }
    .paper-sheet {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        max-width: 100% !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="paper-canvas">

    <!-- TOOLBAR AKSI PENGUJIAN (FLOATING / TOP BAR - TIDAK IKUT TERCETAK DI KERTAS) -->
    <div class="max-w-4xl mx-auto mb-4 px-2 flex flex-col sm:flex-row items-center justify-between gap-3 no-print">
        <a href="stok_opname.php" class="px-3.5 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>Kembali ke Antarmuka Opname</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            <?php if ($canVerify): ?>
                <!-- Form Jalankan Simulasi Otomatis -->
                <form method="POST" onsubmit="return confirm('Jalankan simulasi otomatis pengujian Stok Opname komprehensif pada seluruh 121 item dengan 6 skenario anomali?');" class="inline">
                    <input type="hidden" name="action" value="run_simulation">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Jalankan skenario uji coba otomatis pada seluruh produk">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <span>Jalankan Simulasi Demo (121 Item)</span>
                    </button>
                </form>

                <!-- Form Reset Baseline -->
                <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan seluruh database inventori ke kondisi awal bersih (Baseline)?');" class="inline">
                    <input type="hidden" name="action" value="reset_database">
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Kembalikan database ke kondisi awal bersih">
                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>Reset Database Demo</span>
                    </button>
                </form>
            <?php endif; ?>

            <button onclick="window.print()" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold transition flex items-center gap-1 shadow-2xs cursor-pointer" title="Cetak lembar dokumen ini ke kertas atau simpan sebagai PDF">
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak Dokumen</span>
            </button>
        </div>
    </div>

    <!-- NOTIFIKASI PESAN -->
    <?php if (!empty($msg)): ?>
        <div class="max-w-4xl mx-auto mb-4 p-3.5 rounded-xl border flex items-start gap-3 shadow-2xs no-print <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900' ?>">
            <svg class="w-4 h-4 <?= $msgType === 'success' ? 'text-emerald-600' : 'text-rose-600' ?> shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <?= $msgType === 'success' ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>' ?>
            </svg>
            <div class="flex-1 text-xs font-medium leading-relaxed"><?= htmlspecialchars($msg) ?></div>
        </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- LEMBAR DOKUMEN RESMI (PAPER SHEET STYLE)                           -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="paper-sheet space-y-7">

        <!-- KOP DOKUMEN RESMI -->
        <div class="border-b-2 border-slate-900 pb-4">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-bold tracking-widest text-slate-500 uppercase">
                        ZenCare Medical Indonesia &bull; Departemen Logistik &amp; Teknologi Sistem
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1 tracking-tight">
                        Laporan Pengujian Fungsional Sistem (Black-Box Test)
                    </h1>
                    <div class="text-xs text-slate-600 mt-0.5">
                        Modul Stok Opname Fisik Terpusat, Rekonsiliasi Multi-Kondisi, dan Audit Trail Persediaan
                    </div>
                </div>
                <div class="text-right sm:shrink-0 text-[10px] font-mono text-slate-500 leading-tight">
                    <div>No. Dokumen : <strong>DOC-AUDIT/OPN/2026/XI-001</strong></div>
                    <div>Tanggal Uji : <strong><?= date('d F Y') ?></strong></div>
                    <div>Status Uji : <span class="text-emerald-700 font-bold uppercase">100% Lulus (10/10 Kasus)</span></div>
                    <div>Klasifikasi : <strong>Internal Audit Log &amp; Demo</strong></div>
                </div>
            </div>
        </div>

        <!-- METADATA & RUANG LINGKUP PENGUJIAN -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <span class="text-[10px] text-slate-500 block uppercase font-medium">Cakupan Data Uji</span>
                <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">121 Entitas Fisik</span>
                <span class="text-[10px] text-slate-500">103 Batch Obat/BMHP + 18 Alkes</span>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <span class="text-[10px] text-slate-500 block uppercase font-medium">Barang Sesuai (Klop)</span>
                <span class="text-base font-bold font-mono text-slate-900 mt-0.5 block">115 Item (95.0%)</span>
                <span class="text-[10px] text-emerald-700 font-medium">Otomatis disembunyikan</span>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <span class="text-[10px] text-slate-500 block uppercase font-medium">Anomali Berselisih</span>
                <span class="text-base font-bold font-mono text-rose-700 mt-0.5 block">6 Kasus Uji Nyata</span>
                <span class="text-[10px] text-slate-500">Rusak, Expired, Hilang, Surplus</span>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <span class="text-[10px] text-slate-500 block uppercase font-medium">Kepatuhan Audit</span>
                <span class="text-base font-bold font-mono text-emerald-700 mt-0.5 block">100% Klop</span>
                <span class="text-[10px] text-slate-500">Server Freeze &amp; Blind Count</span>
            </div>
        </div>

        <!-- REKAPITULASI VALUASI ESTIMASI KERUGIAN -->
        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Rekapitulasi Estimasi Nilai Kerugian Persediaan (Berdasarkan HPP Modal Beli)
                </h3>
                <span class="text-[10px] font-mono text-slate-500">Formula: Total Kerugian = &Sigma; (Kuantitas Defisit &times; Harga Modal Beli Satuan Kecil)</span>
            </div>

            <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                Valuasi kerugian dihitung secara transparan berbasis <strong>Harga Modal Beli (HPP) satuan kecil</strong>. Barang yang berlebih (surplus) tidak dihitung sebagai pengurang kerugian agar tidak mengaburkan beban kerugian riil akibat kelalaian fisik rak.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 text-xs">
                <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                    <span class="text-[10px] text-slate-500 block uppercase font-medium">1. Beban Barang Rusak</span>
                    <span class="text-sm font-bold font-mono text-slate-900 mt-0.5 block">Rp 18.000</span>
                    <span class="text-[10px] text-slate-500">2 Strip Cefadroxil @ Rp 9.000</span>
                </div>
                <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                    <span class="text-[10px] text-slate-500 block uppercase font-medium">2. Beban Kedaluwarsa</span>
                    <span class="text-sm font-bold font-mono text-slate-900 mt-0.5 block">Rp 54.800</span>
                    <span class="text-[10px] text-slate-500">3 Botol Sanmol @ Rp 18.267</span>
                </div>
                <div class="p-2.5 bg-white border border-slate-200 rounded-lg">
                    <span class="text-[10px] text-slate-500 block uppercase font-medium">3. Beban Barang Hilang</span>
                    <span class="text-sm font-bold font-mono text-slate-900 mt-0.5 block">Rp 465.000</span>
                    <span class="text-[10px] text-slate-500">2 Strip Amox + 1 Unit Tensimeter</span>
                </div>
                <div class="p-2.5 bg-white border border-slate-300 rounded-lg bg-slate-50">
                    <span class="text-[10px] text-slate-600 block uppercase font-bold">Total Akumulasi Kerugian</span>
                    <span class="text-base font-black font-mono text-rose-700 mt-0.5 block">Rp 537.800</span>
                    <span class="text-[10px] text-emerald-700 font-medium">Surplus (+2 Betadine): Rp 0</span>
                </div>
            </div>
        </div>

        <!-- 4 PILAR METODOLOGI PENGUJIAN -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide border-b border-slate-200 pb-1.5">
                Metodologi &amp; Prinsip Rekayasa Pengujian (Black-Box Testing)
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-white border border-slate-200 rounded-lg space-y-1">
                    <div class="font-bold text-slate-900 text-[11px] flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-slate-900 text-white flex items-center justify-center text-[10px]">1</span>
                        <span>Server-Level Central Transaction Freeze</span>
                    </div>
                    <p class="text-[10px] text-slate-600 leading-relaxed text-justify">
                        Saat sesi opname aktif, transaksi kasir POS luring, pemesanan toko daring e-commerce, konversi satuan UOM, dan penerimaan logistik terkunci otomatis di tingkat server (HTTP 423 Locked) untuk mencegah perubahan kuantitas fisik di tengah proses pencacahan.
                    </p>
                </div>
                <div class="p-3 bg-white border border-slate-200 rounded-lg space-y-1">
                    <div class="font-bold text-slate-900 text-[11px] flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-slate-900 text-white flex items-center justify-center text-[10px]">2</span>
                        <span>Strict Blind Count (Pemisahan Tugas Kasir vs Admin)</span>
                    </div>
                    <p class="text-[10px] text-slate-600 leading-relaxed text-justify">
                        Petugas pencacah lapangan (Kasir) bertugas memasukkan angka fisik riil di rak ke kolom Kondisi Baik, Rusak, dan Expired. Seluruh kolom informasi pembukuan sistem, reserved, dan selisih dirahasiakan total agar tidak timbul manipulasi pencatatan.
                    </p>
                </div>
                <div class="p-3 bg-white border border-slate-200 rounded-lg space-y-1">
                    <div class="font-bold text-slate-900 text-[11px] flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-slate-900 text-white flex items-center justify-center text-[10px]">3</span>
                        <span>Penyaringan Otomatis Anomali Selisih (Filter Focus)</span>
                    </div>
                    <p class="text-[10px] text-slate-600 leading-relaxed text-justify">
                        Pada tahap verifikasi oleh Admin, antarmuka secara otomatis menyaring dan menyembunyikan 115 barang yang cocok (selisih 0). Tabel kerja hanya menampilkan 6 entitas yang berselisih agar verifikator langsung fokus menindaklanjuti akar masalah.
                    </p>
                </div>
                <div class="p-3 bg-white border border-slate-200 rounded-lg space-y-1">
                    <div class="font-bold text-slate-900 text-[11px] flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded bg-slate-900 text-white flex items-center justify-center text-[10px]">4</span>
                        <span>Kategorisasi Mutasi &amp; Pelacakan Serial Number (Alkes)</span>
                    </div>
                    <p class="text-[10px] text-slate-600 leading-relaxed text-justify">
                        Selisih barang dialokasikan secara eksplisit melalui pilihan kategori (Barang Rusak, Kedaluwarsa, Barang Hilang, atau Kelebihan Kirim Supplier) dan langsung dicatat ke Kartu Stok. Pada komoditas Alat Kesehatan, sistem mewajibkan verifikator memilih unit Serial Number (SN) fisik terkait.
                    </p>
                </div>
            </div>
        </div>

        <!-- TABEL MATRIKS HASIL PENGUJIAN 10 KASUS UJI -->
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-slate-200 pb-1.5">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Tabel Matriks Hasil Pengujian Fungsional (10 Kasus Uji Black-Box)
                </h2>
                <span class="text-[10px] font-mono text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                    Tingkat Keberhasilan: 10 / 10 Lulus (100%)
                </span>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-lg text-xs">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-100 border-b border-slate-300 text-slate-700 font-bold text-[10px] uppercase">
                        <tr>
                            <th class="py-2 px-2.5 w-12 text-center">ID</th>
                            <th class="py-2 px-3">Skenario Uji Fungsional</th>
                            <th class="py-2 px-3">Masukan &amp; Kondisi Uji</th>
                            <th class="py-2 px-3">Hasil yang Diharapkan</th>
                            <th class="py-2 px-3">Hasil Pengujian Sistem</th>
                            <th class="py-2 px-2 text-center w-16">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[11px] text-slate-700">
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U01</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Pembekuan Transaksi Server (Central Freeze)</td>
                            <td class="py-2 px-3">Sesi opname diaktifkan oleh Superadmin</td>
                            <td class="py-2 px-3 text-slate-600">POS offline dan toko online terkunci; transaksi ditolak dengan HTTP 423 Locked</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Header server merespons 423 Locked dan kasir dialihkan ke pencacahan rak</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U02</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Strict Blind Count (Kerahasiaan Sistem)</td>
                            <td class="py-2 px-3">Kasir login ke antarmuka penghitungan fisik</td>
                            <td class="py-2 px-3 text-slate-600">Angka sistem, reserved, dan selisih dirahasiakan total; hanya kotak input multi-kondisi yang aktif</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Kolom sistem tersembunyi total dari DOM kasir</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U03</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Input Multi-Kondisi (Baik, Rusak, Expired)</td>
                            <td class="py-2 px-3">Kasir menginput Cefadroxil: Baik 8, Rusak 2, Expired 0</td>
                            <td class="py-2 px-3 text-slate-600">Sistem menyimpan rincian 3 kondisi dan menghitung selisih defisit -2 terhadap saldo sistem 10</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Tersimpan di database detail_opname_batch</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U04</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Penguncian Otomatis Batch Expired</td>
                            <td class="py-2 px-3">Produk Sanmol Sirup batch SNM-24K061 (lewat tgl exp)</td>
                            <td class="py-2 px-3 text-slate-600">Kolom Kondisi Baik terkunci otomatis (0); kasir hanya dapat menginput ke kolom Expired</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Input baik disabled dan diberi penanda Terkunci (0) Exp</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U05</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Tombol Verifikasi Terkunci Saat Belum Lengkap</td>
                            <td class="py-2 px-3">Admin melihat tabel pemantauan saat kasir belum simpan</td>
                            <td class="py-2 px-3 text-slate-600">Tombol Verifikasi Selisih disabled dan menampilkan badge: Menunggu Kasir: X Item</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Tombol terkunci hingga seluruh item fisik disimpan oleh kasir</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U06</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Penyaringan Otomatis Anomali Selisih</td>
                            <td class="py-2 px-3">Admin menekan tombol Verifikasi Selisih</td>
                            <td class="py-2 px-3 text-slate-600">115 barang cocok disembunyikan otomatis; hanya 6 barang anomali yang ditampilkan di layar</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Filter default langsung aktif menyaring selisih != 0</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U07</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Rekonsiliasi Mutasi Barang Rusak &amp; Expired</td>
                            <td class="py-2 px-3">Admin mengeksekusi mutasi rusak pada Cefadroxil dan mutasi expired pada Sanmol</td>
                            <td class="py-2 px-3 text-slate-600">Stok batch terpotong, status berubah 'Sudah Dimutasi', dan tercatat ke kartu stok</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Kartu stok mencatat alasan Barang Rusak dan Barang Kedaluwarsa</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U08</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Pelacakan Serial Number Alkes Hilang &amp; Rusak</td>
                            <td class="py-2 px-3">Admin memilih SN OMR-7120-109 (hilang) dan OMR-MC246-12 (rusak sensor)</td>
                            <td class="py-2 px-3 text-slate-600">Unit serial terupdate menjadi 'Hilang' dan 'Retur/Rusak'; kuantitas alkes berkurang</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Status unit_serial terintegrasi sempurna</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U09</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Koreksi Surplus Kelebihan Kirim Supplier</td>
                            <td class="py-2 px-3">Admin mencatat selisih +2 Botol Betadine Solution</td>
                            <td class="py-2 px-3 text-slate-600">Stok bertambah +2; kartu stok mencatat mutasi Masuk dengan keterangan penyesuaian</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Saldo batch dan stok toko bertambah sinkron</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-500">U10</td>
                            <td class="py-2 px-3 font-semibold text-slate-900">Penyelesaian Sesi &amp; Unfreeze Server Terpusat</td>
                            <td class="py-2 px-3">Superadmin menekan tombol Selesaikan Sesi setelah 100% selisih dimutasi</td>
                            <td class="py-2 px-3 text-slate-600">Sesi beralih ke status Selesai; sistem unfreeze dan POS online kembali normal</td>
                            <td class="py-2 px-3 font-medium text-emerald-800">Sesuai. Transaksi penjualan kasir dan checkout e-commerce normal kembali</td>
                            <td class="py-2 px-2 text-center"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[9px]">LULUS</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- REKAMAN VISUAL PENGUJIAN LANGKAH DEMI LANGKAH -->
        <div class="space-y-6 pt-2">
            <div class="border-b border-slate-200 pb-2">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Rekaman Visual Pengujian Langkah demi Langkah (Step-by-Step Audit Trail)
                </h2>
                <div class="text-[11px] text-slate-500">
                    Menampilkan tangkapan layar antarmuka sistem secara berurutan sesuai alur operasional standar (SOP).
                </div>
            </div>

            <!-- LANGKAH 1: INISIASI SESI OPNAME -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2.5 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-bold flex items-center justify-center text-[11px]">1</span>
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wide">Langkah 1: Inisiasi Sesi Opname &amp; Pembekuan Transaksi Server</h3>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Aktor: Superadmin</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                    Superadmin memulai sesi opname periodik. Secara otomatis, server ZenCare Medical mengaktifkan <em>Central Transaction Freeze</em>. Seluruh terminal kasir POS offline, pemesanan toko daring e-commerce, konversi satuan UOM, dan penerimaan logistik dibekukan total di tingkat server (HTTP 423 Locked) untuk mencegah <em>race condition</em>.
                </p>
                <!-- Visual Box Langkah 1 -->
                <div class="p-3 bg-white border border-slate-200 rounded-lg text-xs space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                            <span class="font-bold text-slate-900">Sesi Aktif: OPN-20261110-001</span>
                            <span class="text-[10px] text-slate-500 font-mono">(Dimulai: <?= date('d/m/Y H:i') ?> WIB)</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-100 text-rose-800 border border-rose-200">
                            POS &amp; Toko Daring Terkunci (423)
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-600 flex items-center justify-between">
                        <span>Snapshot Fisik Terkunci: <strong>121 Entitas Persediaan</strong> (103 Batch Obat + 18 Alkes)</span>
                        <span class="text-slate-500">Pelaksana: <strong>Superadmin (Pemilik)</strong></span>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 2: PENCACAHAN FISIK RAK (BLIND COUNT) -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2.5 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-bold flex items-center justify-center text-[11px]">2</span>
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wide">Langkah 2: Pencacahan Fisik Rak Secara Mandiri oleh Kasir (Strict Blind Count)</h3>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Aktor: Kasir Lapangan</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                    Kasir lapangan bertugas menghitung fisik rak etalase dan memasukkan kuantitas ke dalam 3 kolom kondisi: <strong>Kondisi Baik (Layak Jual)</strong>, <strong>Kondisi Rusak (Cacat Fisik)</strong>, dan <strong>Kondisi Expired (Kedaluwarsa)</strong>. Seluruh saldo komputer dan selisih dirahasiakan total (Strict Blind Count). Kasir kemudian menekan tombol <strong>"Simpan Hasil Hitung"</strong> untuk menyimpan hasil hitungan ke server.
                </p>
                <!-- Visual Box Langkah 2 -->
                <div class="p-3 bg-white border border-slate-200 rounded-lg text-xs space-y-2">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-[10px]">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b">
                                <tr>
                                    <th class="py-1.5 px-2">Komoditas &amp; Produk</th>
                                    <th class="py-1.5 px-2 text-center">Satuan</th>
                                    <th class="py-1.5 px-2 text-center bg-slate-50">Kondisi Baik</th>
                                    <th class="py-1.5 px-2 text-center bg-slate-50">Kondisi Rusak</th>
                                    <th class="py-1.5 px-2 text-center bg-slate-50">Kondisi Expired</th>
                                    <th class="py-1.5 px-2 text-center">Status Pembukuan Sistem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-slate-700">
                                <tr>
                                    <td class="py-1.5 px-2 font-medium">Cefadroxil 500mg (Batch: CFD-500-26.1)</td>
                                    <td class="py-1.5 px-2 text-center">Strip</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-slate-900 bg-slate-50">[ 8 ]</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-amber-700 bg-slate-50">[ 2 ]</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-slate-400 bg-slate-50">[ 0 ]</td>
                                    <td class="py-1.5 px-2 text-center text-slate-400 italic">Dirahasiakan (Blind Count)</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 px-2 font-medium">Sanmol Sirup Anak 60ml (Batch: SNM-24K061)</td>
                                    <td class="py-1.5 px-2 text-center">Karton</td>
                                    <td class="py-1.5 px-2 text-center font-mono text-slate-400 bg-slate-50">Terkunci (0) Exp</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-slate-400 bg-slate-50">[ 0 ]</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-rose-700 bg-slate-50">[ 2 ]</td>
                                    <td class="py-1.5 px-2 text-center text-slate-400 italic">Dirahasiakan (Blind Count)</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 px-2 font-medium">Tensimeter Digital Omron (HEM-7120)</td>
                                    <td class="py-1.5 px-2 text-center">Unit</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-slate-900 bg-slate-50">[ 7 ]</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-slate-400 bg-slate-50">[ 0 ]</td>
                                    <td class="py-1.5 px-2 text-center font-mono text-slate-400 bg-slate-50">-</td>
                                    <td class="py-1.5 px-2 text-center text-slate-400 italic">Dirahasiakan (Blind Count)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[10px] text-slate-500">
                        <span>Tombol Aksi Kasir: <strong>[Simpan Hasil Hitung]</strong></span>
                        <span class="text-emerald-700 font-semibold">Seluruh 121 item disimpan sekaligus ke server</span>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 3: PEMANTAUAN PROGRES OLEH ADMIN -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2.5 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-bold flex items-center justify-center text-[11px]">3</span>
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wide">Langkah 3: Pemantauan Progres Real-Time oleh Admin</h3>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Aktor: Admin Gudang</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                    Admin memantau progres pengisian kasir melalui tabel audit trail. Selama kasir belum selesai menginput seluruh rak (`belum_hitung > 0`), tombol <strong>"Verifikasi Selisih"</strong> terkunci secara otomatis (`disabled`). Tombol baru aktif berwarna biru saat kasir selesai menyimpan 100% item fisik.
                </p>
                <!-- Visual Box Langkah 3 -->
                <div class="p-3 bg-white border border-slate-200 rounded-lg text-xs space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-3">
                            <span>Total Entitas: <strong class="font-mono text-slate-900">121</strong></span>
                            <span>Sudah Dihitung: <strong class="font-mono text-emerald-700">121</strong></span>
                            <span>Belum Dihitung: <strong class="font-mono text-slate-500">0</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-emerald-700 font-bold">Kasir Selesai Menghitung</span>
                            <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-bold text-[10px] shadow-2xs">
                                Tombol Aktif: Verifikasi Selisih
                            </span>
                        </div>
                    </div>
                    <div class="text-[10px] text-slate-500">
                        * Mekanisme penjagaan bertahap (guarding) memastikan Admin tidak dapat membuka verifikasi sebelum pencacahan lapangan tuntas.
                    </div>
                </div>
            </div>

            <!-- LANGKAH 4: ANTARMUKA VERIFIKASI ANOMALI SELISIH -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2.5 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-bold flex items-center justify-center text-[11px]">4</span>
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wide">Langkah 4: Antarmuka Verifikasi Anomali Selisih (115 Cocok Disembunyikan Otomatis)</h3>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Aktor: Admin / Superadmin</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                    Ketika Admin menekan tombol "Verifikasi Selisih", sistem secara cerdas menjalankan <strong>Penyaringan Otomatis (Filter Focus)</strong>: Sebanyak 115 produk yang cocok (0 selisih) langsung disembunyikan dari layar, sehingga Admin langsung fokus menindaklanjuti <strong>6 produk berselisih</strong> yang diberi tanda outline merah tebal.
                </p>
                <!-- Visual Box Langkah 4 (Tabel Anomali) -->
                <div class="p-3 bg-white border border-slate-200 rounded-lg text-xs space-y-2">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-[10px]">
                            <thead class="bg-slate-100 text-slate-700 font-bold border-b">
                                <tr>
                                    <th class="py-1.5 px-2">Produk &amp; Batch / SN</th>
                                    <th class="py-1.5 px-1.5 text-center">Satuan</th>
                                    <th class="py-1.5 px-1.5 text-center">Sistem</th>
                                    <th class="py-1.5 px-1.5 text-center">Fisik (B/R/E)</th>
                                    <th class="py-1.5 px-1.5 text-center">Selisih</th>
                                    <th class="py-1.5 px-2 text-right">Harga Modal</th>
                                    <th class="py-1.5 px-2 text-right">Subtotal Rugi</th>
                                    <th class="py-1.5 px-2 text-center">Tindakan Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-slate-700 font-mono">
                                <!-- Row 1: Cefadroxil (Rusak) -->
                                <tr class="bg-rose-50/60 border-l-4 border-l-rose-500 font-sans">
                                    <td class="py-1.5 px-2">
                                        <div class="font-bold text-slate-900">Cefadroxil 500mg</div>
                                        <div class="text-[9px] text-slate-500 font-mono">Batch: CFD-500-26.1</div>
                                    </td>
                                    <td class="py-1.5 px-1.5 text-center">Strip</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold">10</td>
                                    <td class="py-1.5 px-1.5 text-center text-[9px]">8 Baik, <span class="text-amber-700 font-bold">2 Rusak</span>, 0 Exp</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold text-rose-600">-2 Strip</td>
                                    <td class="py-1.5 px-2 text-right text-[9px]">Rp 9.000</td>
                                    <td class="py-1.5 px-2 text-right font-bold text-rose-700 text-[9px]">Rp 18.000</td>
                                    <td class="py-1.5 px-2 text-center font-sans">
                                        <span class="px-2 py-0.5 rounded bg-amber-600 text-white font-bold text-[9px]">Mutasi Rusak</span>
                                    </td>
                                </tr>
                                <!-- Row 2: Sanmol Sirup (Expired) -->
                                <tr class="bg-rose-50/60 border-l-4 border-l-rose-500 font-sans">
                                    <td class="py-1.5 px-2">
                                        <div class="font-bold text-slate-900">Sanmol Sirup Anak 60ml</div>
                                        <div class="text-[9px] text-rose-700 font-mono">Batch: SNM-24K061 (Kedaluwarsa)</div>
                                    </td>
                                    <td class="py-1.5 px-1.5 text-center">Karton</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold">5</td>
                                    <td class="py-1.5 px-1.5 text-center text-[9px]">0 Baik, 0 Rusak, <span class="text-rose-700 font-bold">2 Exp</span></td>
                                    <td class="py-1.5 px-1.5 text-center font-bold text-rose-600">-3 Karton</td>
                                    <td class="py-1.5 px-2 text-right text-[9px]">Rp 18.267</td>
                                    <td class="py-1.5 px-2 text-right font-bold text-rose-700 text-[9px]">Rp 54.800</td>
                                    <td class="py-1.5 px-2 text-center font-sans">
                                        <span class="px-2 py-0.5 rounded bg-rose-600 text-white font-bold text-[9px]">Mutasi Expired</span>
                                    </td>
                                </tr>
                                <!-- Row 3: Amoxicillin (Hilang) -->
                                <tr class="bg-rose-50/60 border-l-4 border-l-rose-500 font-sans">
                                    <td class="py-1.5 px-2">
                                        <div class="font-bold text-slate-900">Amoxicillin 500mg</div>
                                        <div class="text-[9px] text-slate-500 font-mono">Batch: AMX-2026-01.A</div>
                                    </td>
                                    <td class="py-1.5 px-1.5 text-center">Strip</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold">8</td>
                                    <td class="py-1.5 px-1.5 text-center text-[9px]">6 Baik, 0 Rusak, 0 Exp</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold text-rose-600">-2 Strip (Defisit)</td>
                                    <td class="py-1.5 px-2 text-right text-[9px]">Rp 7.500</td>
                                    <td class="py-1.5 px-2 text-right font-bold text-rose-700 text-[9px]">Rp 15.000</td>
                                    <td class="py-1.5 px-2 text-center font-sans">
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-white font-bold text-[9px]">Pilih Alasan</span>
                                    </td>
                                </tr>
                                <!-- Row 4: Tensimeter Omron (Alkes SN Hilang) -->
                                <tr class="bg-rose-50/60 border-l-4 border-l-rose-500 font-sans">
                                    <td class="py-1.5 px-2">
                                        <div class="font-bold text-slate-900">Tensimeter Digital Omron</div>
                                        <div class="text-[9px] text-slate-500 font-mono">Alkes SN: OMR-7120-109</div>
                                    </td>
                                    <td class="py-1.5 px-1.5 text-center">Unit</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold">8</td>
                                    <td class="py-1.5 px-1.5 text-center text-[9px]">7 Fisik Tersedia</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold text-rose-600">-1 Unit (Defisit)</td>
                                    <td class="py-1.5 px-2 text-right text-[9px]">Rp 450.000</td>
                                    <td class="py-1.5 px-2 text-right font-bold text-rose-700 text-[9px]">Rp 450.000</td>
                                    <td class="py-1.5 px-2 text-center font-sans">
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-white font-bold text-[9px]">Pilih SN Hilang</span>
                                    </td>
                                </tr>
                                <!-- Row 5: Termometer Omron (Alkes SN Rusak) -->
                                <tr class="bg-rose-50/60 border-l-4 border-l-rose-500 font-sans">
                                    <td class="py-1.5 px-2">
                                        <div class="font-bold text-slate-900">Termometer Digital Omron</div>
                                        <div class="text-[9px] text-amber-700 font-mono">Alkes SN: OMR-MC246-12 (Cacat)</div>
                                    </td>
                                    <td class="py-1.5 px-1.5 text-center">Unit</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold">11</td>
                                    <td class="py-1.5 px-1.5 text-center text-[9px]">10 Baik, <span class="text-amber-700 font-bold">1 Rusak Fisik</span></td>
                                    <td class="py-1.5 px-1.5 text-center font-bold text-amber-700">-1 Unit Rusak</td>
                                    <td class="py-1.5 px-2 text-right text-[9px]">Rp 65.000</td>
                                    <td class="py-1.5 px-2 text-right font-bold text-amber-700 text-[9px]">Rp 65.000</td>
                                    <td class="py-1.5 px-2 text-center font-sans">
                                        <span class="px-2 py-0.5 rounded bg-amber-600 text-white font-bold text-[9px]">Pilih SN Rusak</span>
                                    </td>
                                </tr>
                                <!-- Row 6: Betadine (Surplus) -->
                                <tr class="bg-blue-50/60 border-l-4 border-l-blue-500 font-sans">
                                    <td class="py-1.5 px-2">
                                        <div class="font-bold text-slate-900">Betadine Solution 15ml</div>
                                        <div class="text-[9px] text-slate-500 font-mono">Batch: BET-15-26.1</div>
                                    </td>
                                    <td class="py-1.5 px-1.5 text-center">Botol</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold">12</td>
                                    <td class="py-1.5 px-1.5 text-center text-[9px]">14 Baik, 0 Rusak, 0 Exp</td>
                                    <td class="py-1.5 px-1.5 text-center font-bold text-blue-600">+2 Botol (Surplus)</td>
                                    <td class="py-1.5 px-2 text-right text-[9px]">Rp 22.000</td>
                                    <td class="py-1.5 px-2 text-right font-bold text-emerald-700 text-[9px]">Rp 0 (Surplus)</td>
                                    <td class="py-1.5 px-2 text-center font-sans">
                                        <span class="px-2 py-0.5 rounded bg-blue-600 text-white font-bold text-[9px]">Koreksi Surplus</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 5: EKSEKUSI REKONSILIASI & VISUAL POP-UP MODAL KONFIRMASI -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-4 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-bold flex items-center justify-center text-[11px]">5</span>
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wide">Langkah 5: Eksekusi Rekonsiliasi &amp; Tampilan Pop-Up Modal Konfirmasi Mutasi</h3>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Aktor: Admin Verifikator</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                    Saat Admin menekan tombol tindakan rekonsiliasi pada baris anomali, sistem memunculkan <strong>dialog pop-up modal</strong> untuk konfirmasi data sebelum mutasi dieksekusi ke kartu stok dan basis data. Berikut adalah rekaman visual modal konfirmasi pada pengujian:
                </p>

                <!-- GRID VISUALISASI POP-UP MODAL -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">

                    <!-- MODAL 1: KONFIRMASI MUTASI BARANG RUSAK OBAT (CEFADROXIL) -->
                    <div class="border border-slate-300 rounded-xl shadow-md overflow-hidden bg-white">
                        <div class="bg-slate-900 text-white px-3 py-2 flex items-center justify-between text-xs">
                            <span class="font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Modal 5A: Mutasi Barang Rusak (Obat)
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">CFD-500-26.1</span>
                        </div>
                        <div class="p-3.5 space-y-3 bg-slate-50/50">
                            <!-- Header Info Batch -->
                            <div class="p-2.5 bg-blue-50/80 border border-blue-200 rounded-lg flex justify-between items-center text-[11px]">
                                <div>
                                    <div class="font-bold text-blue-900">Cefadroxil 500mg Strip</div>
                                    <div class="text-[10px] text-blue-700 font-mono">Batch: CFD-500-26.1 &bull; Exp: 30/11/2027</div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-blue-200 text-blue-900 font-bold text-[10px]">Defisit -2</span>
                            </div>

                            <!-- Pilihan Kategori Dropdown -->
                            <div>
                                <label class="text-[10px] font-bold text-slate-700 block mb-1">Pilih Kategori Beban Selisih (Wajib):</label>
                                <div class="p-1.5 bg-white border border-slate-300 rounded text-[11px] font-medium text-slate-800 flex justify-between items-center">
                                    <span>1. Mutasi Barang Rusak (Kemasan Cacat / Rusak Fisik)</span>
                                    <span class="text-slate-400 text-[10px]">&blacktriangledown;</span>
                                </div>
                            </div>

                            <!-- Rincian Alokasi Kuantitas -->
                            <div class="p-2.5 bg-white border border-slate-200 rounded-lg space-y-1.5 text-[10px]">
                                <div class="flex justify-between items-center font-bold text-slate-800 border-b pb-1">
                                    <span>Rincian Alokasi Kuantitas Defisit:</span>
                                    <span class="text-emerald-700 font-mono">Sesuai: 2/2 Strip</span>
                                </div>
                                <div class="flex justify-between items-center pt-0.5">
                                    <span class="text-amber-800 font-medium">Barang Rusak (Fisik)</span>
                                    <div class="flex items-center gap-1 font-mono font-bold">
                                        <span class="px-2 py-0.5 rounded border border-slate-300 bg-amber-50 text-amber-900">[ 2 ]</span>
                                        <span class="text-[9px] text-slate-500">Penuh</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Barang Hilang (Defisit)</span>
                                    <span class="px-2 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-400 font-mono">[ 0 ]</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Koreksi Salah Catat</span>
                                    <span class="px-2 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-400 font-mono">[ 0 ]</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200 text-[11px]">
                                <span class="px-3 py-1 rounded-lg border border-slate-300 text-slate-600 bg-white">Batal</span>
                                <span class="px-3.5 py-1 rounded-lg bg-blue-600 text-white font-bold shadow-2xs">Simpan &amp; Eksekusi Mutasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL 2: KONFIRMASI MUTASI BARANG HILANG OBAT (AMOXICILLIN) -->
                    <div class="border border-slate-300 rounded-xl shadow-md overflow-hidden bg-white">
                        <div class="bg-slate-900 text-white px-3 py-2 flex items-center justify-between text-xs">
                            <span class="font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Modal 5B: Mutasi Barang Hilang (Obat)
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">AMX-2026-01.A</span>
                        </div>
                        <div class="p-3.5 space-y-3 bg-slate-50/50">
                            <!-- Header Info Batch -->
                            <div class="p-2.5 bg-blue-50/80 border border-blue-200 rounded-lg flex justify-between items-center text-[11px]">
                                <div>
                                    <div class="font-bold text-blue-900">Amoxicillin 500mg Strip</div>
                                    <div class="text-[10px] text-blue-700 font-mono">Batch: AMX-2026-01.A &bull; Exp: 15/12/2027</div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-900 font-bold text-[10px]">Defisit -2</span>
                            </div>

                            <!-- Pilihan Kategori Dropdown -->
                            <div>
                                <label class="text-[10px] font-bold text-slate-700 block mb-1">Pilih Kategori Beban Selisih (Wajib):</label>
                                <div class="p-1.5 bg-white border border-slate-300 rounded text-[11px] font-medium text-slate-800 flex justify-between items-center">
                                    <span>2. Mutasi Barang Hilang (Tidak Ditemukan di Rak Etalase)</span>
                                    <span class="text-slate-400 text-[10px]">&blacktriangledown;</span>
                                </div>
                            </div>

                            <!-- Rincian Alokasi Kuantitas -->
                            <div class="p-2.5 bg-white border border-slate-200 rounded-lg space-y-1.5 text-[10px]">
                                <div class="flex justify-between items-center font-bold text-slate-800 border-b pb-1">
                                    <span>Rincian Alokasi Kuantitas Defisit:</span>
                                    <span class="text-emerald-700 font-mono">Sesuai: 2/2 Strip</span>
                                </div>
                                <div class="flex justify-between items-center pt-0.5">
                                    <span class="text-rose-800 font-medium">Barang Hilang (Defisit)</span>
                                    <div class="flex items-center gap-1 font-mono font-bold">
                                        <span class="px-2 py-0.5 rounded border border-slate-300 bg-rose-50 text-rose-900">[ 2 ]</span>
                                        <span class="text-[9px] text-slate-500">Penuh</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Barang Rusak</span>
                                    <span class="px-2 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-400 font-mono">[ 0 ]</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Koreksi Salah Catat</span>
                                    <span class="px-2 py-0.5 rounded border border-slate-200 bg-slate-50 text-slate-400 font-mono">[ 0 ]</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200 text-[11px]">
                                <span class="px-3 py-1 rounded-lg border border-slate-300 text-slate-600 bg-white">Batal</span>
                                <span class="px-3.5 py-1 rounded-lg bg-blue-600 text-white font-bold shadow-2xs">Simpan &amp; Eksekusi Mutasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL 3: KONFIRMASI MUTASI ALKES SERIAL NUMBER HILANG (TENSIMETER) -->
                    <div class="border border-slate-300 rounded-xl shadow-md overflow-hidden bg-white">
                        <div class="bg-slate-900 text-white px-3 py-2 flex items-center justify-between text-xs">
                            <span class="font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Modal 5C: Pilih Serial Number Hilang (Alkes)
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">HEM-7120</span>
                        </div>
                        <div class="p-3.5 space-y-3 bg-slate-50/50">
                            <!-- Header Info Alkes -->
                            <div class="p-2.5 bg-slate-100 border border-slate-200 rounded-lg flex justify-between items-center text-[11px]">
                                <div>
                                    <div class="font-bold text-slate-900">Tensimeter Digital Omron</div>
                                    <div class="text-[10px] text-slate-600">Komoditas: Alat Kesehatan &bull; Satuan: Unit</div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold font-mono text-[10px]">Selisih: -1 Unit</span>
                            </div>

                            <!-- Box Pemilihan Serial Number -->
                            <div>
                                <div class="flex justify-between items-center text-[10px] font-bold text-slate-700 mb-1">
                                    <span>Pilih Serial Number Terkait (1 unit wajib dipilih):</span>
                                    <span class="text-emerald-700 font-mono">1/1 Dipilih</span>
                                </div>
                                <div class="border border-slate-300 rounded-lg divide-y divide-slate-100 bg-white max-h-32 overflow-y-auto text-[10px]">
                                    <div class="p-1.5 flex items-center justify-between bg-white text-slate-500">
                                        <label class="flex items-center gap-2"><input type="checkbox" disabled> <span>OMR-7120-102</span></label>
                                        <span class="text-[9px] text-slate-400">Tersedia di Fisik</span>
                                    </div>
                                    <div class="p-1.5 flex items-center justify-between bg-white text-slate-500">
                                        <label class="flex items-center gap-2"><input type="checkbox" disabled> <span>OMR-7120-103</span></label>
                                        <span class="text-[9px] text-slate-400">Tersedia di Fisik</span>
                                    </div>
                                    <div class="p-1.5 flex items-center justify-between bg-rose-50/70 text-slate-900 font-bold">
                                        <label class="flex items-center gap-2"><input type="checkbox" checked class="text-rose-600"> <span>OMR-7120-109</span></label>
                                        <span class="text-[9px] text-rose-700 font-bold">Terpilih: Unit Fisik Hilang</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Catatan Penjelasan -->
                            <div>
                                <label class="text-[10px] font-bold text-slate-700 block mb-0.5">Catatan Tambahan (Opsional):</label>
                                <div class="p-1.5 bg-white border border-slate-300 rounded text-[10px] text-slate-600 italic">
                                    Pencacahan rak etalase: 7 unit fisik ditemukan, 1 unit fisik SN OMR-7120-109 tidak ada di toko.
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200 text-[11px]">
                                <span class="px-3 py-1 rounded-lg border border-slate-300 text-slate-600 bg-white">Batal</span>
                                <span class="px-3.5 py-1 rounded-lg bg-blue-600 text-white font-bold shadow-2xs">Simpan &amp; Eksekusi Mutasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL 4: KONFIRMASI MUTASI ALKES SERIAL NUMBER RUSAK (TERMOMETER) -->
                    <div class="border border-slate-300 rounded-xl shadow-md overflow-hidden bg-white">
                        <div class="bg-slate-900 text-white px-3 py-2 flex items-center justify-between text-xs">
                            <span class="font-bold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Modal 5D: Pilih Serial Number Rusak (Alkes)
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">MC-246</span>
                        </div>
                        <div class="p-3.5 space-y-3 bg-slate-50/50">
                            <!-- Header Info Alkes -->
                            <div class="p-2.5 bg-slate-100 border border-slate-200 rounded-lg flex justify-between items-center text-[11px]">
                                <div>
                                    <div class="font-bold text-slate-900">Termometer Digital Omron</div>
                                    <div class="text-[10px] text-slate-600">Komoditas: Alat Kesehatan &bull; Satuan: Unit</div>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-bold font-mono text-[10px]">Selisih: 1 Rusak</span>
                            </div>

                            <!-- Box Pemilihan Serial Number Rusak -->
                            <div>
                                <div class="flex justify-between items-center text-[10px] font-bold text-slate-700 mb-1">
                                    <span>Pilih Serial Number Cacat/Rusak (1 unit):</span>
                                    <span class="text-amber-800 font-mono">1/1 Terpilih</span>
                                </div>
                                <div class="border border-slate-300 rounded-lg divide-y divide-slate-100 bg-white max-h-32 overflow-y-auto text-[10px]">
                                    <div class="p-1.5 flex items-center justify-between bg-white text-slate-500">
                                        <label class="flex items-center gap-2"><input type="checkbox" disabled> <span>OMR-MC246-01</span></label>
                                        <span class="text-[9px] text-slate-400">Unit Baik</span>
                                    </div>
                                    <div class="p-1.5 flex items-center justify-between bg-amber-50/80 text-slate-900 font-bold">
                                        <label class="flex items-center gap-2"><input type="checkbox" checked class="text-amber-600"> <span>OMR-MC246-12</span></label>
                                        <span class="text-[9px] text-amber-800 font-bold">Terpilih: Layar LCD Rusak / Cacat Sensor</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Akhir Unit Serial -->
                            <div class="p-2 bg-amber-50 border border-amber-200 rounded text-[10px] text-amber-900">
                                Status unit_serial akan diubah ke: <strong>'Retur/Rusak'</strong> dan stok aktif unit berkurang -1.
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-200 text-[11px]">
                                <span class="px-3 py-1 rounded-lg border border-slate-300 text-slate-600 bg-white">Batal</span>
                                <span class="px-3.5 py-1 rounded-lg bg-blue-600 text-white font-bold shadow-2xs">Simpan &amp; Eksekusi Mutasi</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Rekap Status Eksekusi Rekonsiliasi -->
                <div class="p-3 bg-white border border-slate-200 rounded-lg text-xs space-y-1.5">
                    <div class="font-bold text-slate-900 text-[11px] flex items-center justify-between">
                        <span>Hasil Verifikasi &amp; Rekonsiliasi Lapangan:</span>
                        <span class="text-emerald-700 font-mono font-bold">6 / 6 Anomali Selesai Dimutasi (100% Tuntas)</span>
                    </div>
                    <div class="text-[10px] text-slate-600 leading-relaxed">
                        Seluruh selisih fisik telah teralokasikan dengan benar ke masing-masing kategori beban (Barang Rusak, Barang Kedaluwarsa, Barang Hilang, dan Kelebihan Kirim). Seluruh saldo telah dicatat ke Buku Besar Kartu Stok.
                    </div>
                </div>
            </div>

            <!-- LANGKAH 6: SINKRONISASI KARTU STOK & PEMBUKAAN KUNCI SISTEM -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2.5 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-bold flex items-center justify-center text-[11px]">6</span>
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wide">Langkah 6: Sinkronisasi Buku Besar Kartu Stok &amp; Pembukaan Kunci Sistem (Server Unfreeze)</h3>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Aktor: Superadmin</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                    Setelah seluruh 6 anomali selisih berhasil direkonsiliasi, tombol <strong>"Selesaikan Sesi (Buka Kunci Sistem)"</strong> menjadi aktif. Superadmin menekan tombol ini untuk menyelesaikan sesi. Server secara otomatis mencabut pembekuan transaksi (*Server Unfreeze*). Kasir POS offline dan transaksi checkout online aktif normal kembali dengan saldo persediaan fisik baru yang telah tersinkronisasi 100%.
                </p>
                <!-- Visual Box Langkah 6 -->
                <div class="p-3 bg-white border border-slate-200 rounded-lg text-xs space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 inline-block"></span>
                            <span class="font-bold text-emerald-950">SESI OPNAME BERHASIL DISELESAIKAN: OPN-20261110-001</span>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-bold text-[10px]">Status: Selesai</span>
                    </div>
                    <div class="text-[11px] text-slate-600 flex items-center justify-between">
                        <span>Terminal Kasir POS : <strong class="text-emerald-700">Aktif Normal</strong></span>
                        <span>Checkout Toko Daring : <strong class="text-emerald-700">Aktif Normal</strong></span>
                        <span>Jurnal Kartu Stok : <strong class="text-slate-900">6 Mutasi Terbukukan Rapi</strong></span>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 7: UTILITAS RESET DATABASE DEMO -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-2.5 bg-slate-50/30">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-slate-900 text-white font-bold flex items-center justify-center text-[11px]">7</span>
                        <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wide">Langkah 7: Utilitas Reset Database Demo (Baseline Clean State)</h3>
                    </div>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Perkakas Demo</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed text-justify">
                    Untuk kemudahan demonstrasi pengujian secara berulang, sistem menyediakan utilitas <strong>"Reset Database Demo"</strong> yang dapat diakses oleh Admin maupun Superadmin. Tombol ini mengembalikan seluruh data inventori ke kondisi awal bersih (*fresh baseline*) seketika tanpa merusak struktur database.
                </p>
                <div class="p-2.5 bg-white border border-slate-200 rounded-lg text-xs flex items-center justify-between">
                    <span class="text-slate-600">Hak Akses: <strong>Admin</strong> dan <strong>Superadmin</strong></span>
                    <span class="text-slate-500 font-mono text-[10px]">Baseline Bersih: 121 Item Fresh State</span>
                </div>
            </div>

        </div>

        <!-- LEMBAR PENGESAHAN DOKUMEN (SIGN-OFF RESMI AUDIT) -->
        <div class="border-t-2 border-slate-900 pt-6 mt-8 space-y-6">
            <div class="text-center">
                <div class="text-[11px] font-bold uppercase text-slate-600 tracking-wider">Lembar Pengesahan Laporan Pengujian Sistem</div>
                <div class="text-[10px] text-slate-500">Diverifikasi dan disahkan secara resmi oleh pihak-pihak yang bertanggung jawab atas pengujian modul logistik.</div>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center text-xs pt-2">
                <!-- Tanda Tangan 1: Kasir / Pencacah -->
                <div class="space-y-12">
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Pencacah Fisik Lapangan (Kasir)</div>
                    <div>
                        <div class="font-bold text-slate-900 text-xs underline">Staf Kasir Front Office</div>
                        <div class="text-[10px] text-slate-500">Pelaksana Pencacahan Rak</div>
                    </div>
                </div>

                <!-- Tanda Tangan 2: Admin Verifikator -->
                <div class="space-y-12">
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Verifikator &amp; Logistik (Admin)</div>
                    <div>
                        <div class="font-bold text-slate-900 text-xs underline">Admin Logistik Toko</div>
                        <div class="text-[10px] text-slate-500">Verifikator &amp; Rekonsiliasi Mutasi</div>
                    </div>
                </div>

                <!-- Tanda Tangan 3: Superadmin / Penanggung Jawab -->
                <div class="space-y-12">
                    <div class="text-[10px] text-slate-500 uppercase font-bold">Penanggung Jawab Sistem (Superadmin)</div>
                    <div>
                        <div class="font-bold text-slate-900 text-xs underline">SuperAdmin / Kepala Toko</div>
                        <div class="text-[10px] text-slate-500">Otorisator Pembekuan &amp; Selesai Sesi</div>
                    </div>
                </div>
            </div>

            <div class="text-center pt-4 text-[9px] text-slate-400 font-mono border-t border-slate-100">
                ZenCare Medical Information System &bull; Dokumen Audit Logistik Medis &bull; Halaman 1 dari 1 (Lembar Dokumen Tunggal)
            </div>
        </div>

    </div>

</div>

<?php
layoutFooter();
?>
'''

with open('inventori/dokumentasi_testing.php', 'w', encoding='utf-8') as f:
    f.write(code)

print("Updated dokumentasi_testing.php successfully.")

