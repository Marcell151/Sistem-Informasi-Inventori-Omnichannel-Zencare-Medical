<?php
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

requireRole(['superadmin', 'admin', 'kasir']);

$activeSession = cekSesiOpnameAktif($pdo, true);
$modeTampilan = $_GET['mode'] ?? 'screenshot'; // 'screenshot' atau 'placeholder'

layoutHead('Laporan Pengujian Sistem: Modul Stok Opname');
layoutBodyOpen();
layoutSidebar('stok_opname');
layoutHeader('Dokumentasi Pengujian Black-Box', 'Lembar hasil pengujian fungsional modul stok opname & rekonsiliasi persediaan');
?>

<style>
/* Format Kertas Formal A4 Lanskap (Landscape) */
.paper-canvas {
    background-color: #f1f5f9;
    min-height: 100vh;
    padding-top: 1.25rem;
    padding-bottom: 3.5rem;
}
.paper-sheet {
    background-color: #ffffff;
    width: 100%;
    max-width: 297mm;
    margin-left: auto;
    margin-right: auto;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
    border: 1px solid #cbd5e1;
    border-radius: 0.25rem;
    padding: 15mm 18mm;
}
.screenshot-box {
    border: 2px dashed #94a3b8;
    background-color: #f8fafc;
    border-radius: 0.375rem;
    padding: 0.85rem;
    text-align: center;
}
.screenshot-placeholder-empty {
    min-height: 220px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 600;
}
@media (max-width: 768px) {
    .paper-sheet {
        padding: 1.25rem 1rem;
    }
}
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm;
    }
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
        border-radius: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="paper-canvas">

    <!-- TOOLBAR NAVIGASI ATAS (TIDAK IKUT TERCETAK DI KERTAS) -->
    <div style="max-width: 297mm;" class="mx-auto mb-4 px-2 flex flex-col sm:flex-row items-center justify-between gap-3 no-print">
        <a href="stok_opname.php" class="px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>Kembali ke Antarmuka Opname</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            <?php if (in_array($_SESSION['role'] ?? '', ['superadmin', 'admin'])): ?>
                <form method="POST" action="stok_opname.php" onsubmit="return confirm('Jalankan skenario uji coba testing otomatis dari awal hingga selesai (Mulai Sesi 111 Item -> Hitung Fisik 3 Kondisi -> Verifikasi & Mutasi 6 Produk Anomali -> Tutup Sesi Opname -> Terbitkan Berita Acara)?');" class="inline">
                    <input type="hidden" name="aksi" value="jalankan_simulasi_demo">
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Khusus keperluan demo pengujian: Jalankan skenario uji coba otomatis dari awal hingga penutupan sesi dan penerbitan Berita Acara">
                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <span>[Utilitas Demo] Jalankan Uji Coba Testing</span>
                    </button>
                </form>

                <form method="POST" action="stok_opname.php" onsubmit="return confirm('Khusus keperluan demo: Apakah Anda yakin ingin memulihkan seluruh database inventori ke kondisi awal bersih (Demo_Bersih.sql)?');" class="inline">
                    <input type="hidden" name="aksi" value="reset_database_demo">
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Khusus keperluan demo pengujian: Memulihkan database ke kondisi awal bersih (Demo_Bersih.sql)">
                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>[Utilitas Demo] Reset Data Demo</span>
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($modeTampilan === 'placeholder'): ?>
                <a href="?mode=screenshot" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                    Tampilkan Tangkapan Layar Asli
                </a>
            <?php else: ?>
                <a href="?mode=placeholder" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                    Mode Placeholder Kosong (Tempel Manual)
                </a>
            <?php endif; ?>

            <button onclick="window.print()" class="px-3.5 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak Dokumen (A4 Lanskap / PDF)</span>
            </button>
        </div>
    </div>

    <!-- LEMBAR DOKUMEN KERTAS FORMAL A4 -->
    <div class="paper-sheet space-y-6">

        <!-- KOP DOKUMEN -->
        <div class="border-b-2 border-slate-900 pb-4">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div>
                    <div class="text-[11px] font-bold tracking-widest text-slate-500 uppercase">
                        ZenCare Medical Indonesia &bull; Sistem Informasi Inventori &amp; POS
                    </div>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-900 mt-1 tracking-tight">
                        Laporan Pengujian Fungsional Sistem (Black-Box Testing)
                    </h1>
                    <div class="text-xs text-slate-600 mt-0.5">
                        Modul Stok Opname Fisik Terpusat, Pencacahan Tiga Kondisi, dan Rekonsiliasi Selisih Persediaan
                    </div>
                </div>
                <div class="text-right sm:shrink-0 text-[10px] font-mono text-slate-600 leading-relaxed">
                    <div>No. Dokumen : <strong>DOC-TEST/OPN/2026/XI-001</strong></div>
                    <div>Tanggal Uji : <strong><?= date('d F Y') ?></strong></div>
                    <div>Hasil Uji : <span class="text-emerald-700 font-bold uppercase">100% Sesuai (10/10 Kasus Uji)</span></div>
                </div>
            </div>
        </div>

        <!-- 1. RINGKASAN PARAMETER PENGUJIAN -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded">
                <span class="text-[10px] text-slate-500 block uppercase font-semibold">Cakupan Data Uji</span>
                <span class="text-sm font-bold font-mono text-slate-900 mt-0.5 block">111 Entitas Fisik</span>
                <span class="text-[10px] text-slate-500">103 Batch Obat/BMHP + 8 Alkes SN</span>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded">
                <span class="text-[10px] text-slate-500 block uppercase font-semibold">Barang Sesuai (Klop)</span>
                <span class="text-sm font-bold font-mono text-slate-900 mt-0.5 block">105 Item (94.6%)</span>
                <span class="text-[10px] text-slate-600">Disaring otomatis saat verifikasi</span>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded">
                <span class="text-[10px] text-slate-500 block uppercase font-semibold">Kasus Anomali Selisih</span>
                <span class="text-sm font-bold font-mono text-rose-700 mt-0.5 block">6 Skenario Uji</span>
                <span class="text-[10px] text-slate-500">Rusak, Expired, Hilang, Surplus</span>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded">
                <span class="text-[10px] text-slate-500 block uppercase font-semibold">Pengendalian Akses</span>
                <span class="text-sm font-bold font-mono text-emerald-700 mt-0.5 block">3 Peran Terpisah</span>
                <span class="text-[10px] text-slate-500">Superadmin, Kasir, Admin</span>
            </div>
        </div>

        <!-- 2. REKAPITULASI ESTIMASI NILAI ACUAN KERUGIAN PERSEDIAAN -->
        <div class="border border-slate-200 rounded p-4 bg-slate-50/60 space-y-2.5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Estimasi Nilai Acuan Kerugian Persediaan
                </h2>
                <span class="text-[10px] font-mono text-slate-600 font-semibold">Rumus: (Jumlah Rusak + Jumlah Expired + Jumlah Hilang) &times; Harga Beli (Satuan Terkecil)</span>
            </div>
            <p class="text-[11px] text-slate-600 leading-relaxed">
                Perhitungan estimasi nilai acuan kerugian menggunakan <strong>Harga Beli</strong> per satuan barang untuk memberikan gambaran nilai persediaan yang terdampak kerusakan fisik, kedaluwarsa, maupun kehilangan di rak. Selisih lebih (surplus) dicatat sebesar Rp 0 agar tidak mengurangi nilai kerugian defisit.
            </p>
        </div>

        <!-- 3. TABEL MATRIKS PENGUJIAN BLACK-BOX -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between border-b border-slate-200 pb-1.5">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Matriks Hasil Pengujian Fungsional (10 Kasus Uji Black-Box)
                </h2>
                <span class="text-[10px] font-mono text-emerald-800 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                    Status: 10 / 10 Lulus
                </span>
            </div>

            <div class="overflow-x-auto border border-slate-300 rounded text-xs">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-100 border-b border-slate-300 text-slate-800 font-bold text-[10px] uppercase">
                        <tr>
                            <th class="py-2 px-2 w-10 text-center border-r border-slate-200">Kode</th>
                            <th class="py-2 px-2.5 border-r border-slate-200">Fitur / Skenario Uji</th>
                            <th class="py-2 px-2.5 border-r border-slate-200">Prosedur &amp; Data Masukan</th>
                            <th class="py-2 px-2.5 border-r border-slate-200">Hasil yang Diharapkan</th>
                            <th class="py-2 px-2.5 border-r border-slate-200">Hasil Aktual Sistem</th>
                            <th class="py-2 px-2 text-center w-16">Kesimpulan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-[10.5px] text-slate-700">
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U01</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Inisiasi Sesi &amp; Pembekuan Transaksi (Freeze Lock)</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Superadmin menekan tombol Mulai Sesi Stok Opname Baru</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Sesi aktif; akses POS kasir dan checkout e-commerce dibekukan (HTTP 423 Locked)</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Transaksi dibekukan dengan status 423 dan seluruh 111 entitas masuk ke daftar hitung</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U02</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Pencacahan Fisik Buta (Blind Count Kasir)</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Kasir login saat sesi opname sedang aktif berjalan</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Kasir dialihkan ke halaman Hitung Fisik tanpa melihat angka stok sistem</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Tampilan hanya memuat kolom Kondisi Baik, Rusak, dan Expired tanpa stok sistem</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U03</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Penguncian Otomatis Batch Kedaluwarsa</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Pemeriksaan baris Sanmol Sirup batch SNM-24K061 (lewat tanggal ED)</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Kolom Kondisi Baik terkunci (0) dan kuantitas dialokasikan ke kolom Expired</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Kolom Baik bertanda Terkunci (0) Exp dan kuantitas masuk ke Kondisi Expired</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U04</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Isi Cepat Angka Uji Coba (Client-Side) &amp; Simpan</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Kasir menekan tombol Isi Cepat Angka Uji Coba lalu menekan Simpan Hasil Hitung</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Tombol Isi Cepat mengisi angka di form tanpa menyimpan otomatis hingga tombol Simpan diklik</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Input terisi di sisi klien dan tersimpan ke database saat Simpan Hasil Hitung ditekan</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U05</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Pemisahan Tugas Admin pada Tahap Penghitungan</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Admin membuka halaman Stok Opname pada Tahap 1</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Admin tidak dapat menginput angka fisik dan hanya memantau progres kasir</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Form input fisik tidak ditampilkan bagi Admin; tombol Verifikasi aktif setelah hitung selesai</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U06</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Filter Anomali Otomatis pada Tahap Verifikasi</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Admin menekan tombol Verifikasi Selisih untuk masuk ke Tahap 2</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">105 barang klop disembunyikan otomatis; hanya 6 baris anomali yang ditampilkan</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Tabel verifikasi Admin langsung menampilkan 6 baris berselisih tanpa tab barang klop</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U07</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Klasifikasi Alkes (BMHP) vs Alkes (SN)</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Pemeriksaan label komoditas Alat Cek Asam Urat vs Tensimeter Digital Omron</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Alkes habis pakai ber-batch dilabeli Alkes (BMHP); unit ber-serial dilabeli Alkes (SN)</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Label komoditas dan kolom pelacakan Batch/SN tampil konsisten di seluruh tahapan</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U08</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Pop-Up Mutasi Radio Button &amp; Pelacakan SN Alkes</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Admin mengeksekusi mutasi barang rusak, kedaluwarsa, hilang, dan memilih SN Alkes</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Pop-up menampilkan opsi Radio Button kategori mutasi dan daftar ceklis SN Alkes</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Stok batch, stok toko, status unit_serial, dan Kartu Stok terperbarui secara akurat</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U09</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Koreksi Selisih Lebih (Surplus) Persediaan</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Admin memverifikasi surplus +2 Botol Betadine Solution 15ml</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Tersedia opsi Kelebihan Kirim Supplier / Koreksi Salah Catat dengan beban rugi Rp 0</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Stok bertambah +2 Botol di batch terkait dan tercatat sebagai mutasi masuk di Kartu Stok</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-2 text-center font-mono font-bold border-r border-slate-200">U10</td>
                            <td class="py-2 px-2.5 font-semibold text-slate-900 border-r border-slate-200">Penutupan Sesi &amp; Cetak Berita Acara</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Superadmin menekan Selesaikan Sesi dan membuka Cetak Berita Acara</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Kunci transaksi dibuka kembali; Berita Acara menampilkan semua barang dengan sorotan merah pada anomali</td>
                            <td class="py-2 px-2.5 border-r border-slate-200">Sistem kembali normal dan Berita Acara menampilkan 111 item dengan kotak merah pada baris selisih</td>
                            <td class="py-2 px-2 text-center font-bold text-emerald-700">SESUAI</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. DOKUMENTASI TANGKAPAN LAYAR PENGUJIAN LANGKAH DEMI LANGKAH -->
        <div class="space-y-5 pt-2">
            <div class="border-b border-slate-200 pb-1.5">
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                    Rekaman Tangkapan Layar Pengujian Sistem (Langkah 1 s/d Langkah 6)
                </h2>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Bukti visual hasil eksekusi pengujian fungsional pada sistem Stok Opname ZenCare Medical.
                </p>
            </div>

            <?php
            $steps = [
                1 => [
                    'title' => 'Langkah 1: Inisiasi Sesi Stok Opname & Pembekuan Transaksi (Superadmin)',
                    'actor' => 'Superadmin',
                    'desc'  => 'Superadmin memulai sesi stok opname baru. Sistem mengaktifkan pembekuan transaksi terpusat (HTTP 423 Locked) pada POS Kasir dan toko daring serta mengambil snapshot 111 entitas persediaan.',
                    'img'   => '../gambar/testing/langkah_1_mulai_sesi_freeze.png'
                ],
                2 => [
                    'title' => 'Langkah 2: Pencacahan Fisik Tiga Kondisi & Penguncian Batch Expired (Kasir)',
                    'actor' => 'Kasir',
                    'desc'  => 'Kasir terkunci pada halaman Hitung Fisik (Blind Count) dengan fitur pencarian cepat barcode/SKU, 3 kolom kondisi (Baik, Rusak, Expired), penguncian otomatis kolom Baik pada batch kedaluwarsa, serta tombol Isi Cepat Angka Uji Coba (Client-Side) dan Simpan Hasil Hitung.',
                    'img'   => '../gambar/testing/langkah_2_kasir_blind_count.png'
                ],
                3 => [
                    'title' => 'Langkah 3: Pemantauan Progres Penghitungan Fisik (Admin)',
                    'actor' => 'Admin',
                    'desc'  => 'Selama tahap penghitungan berlangsung, Admin hanya dapat memantau progres pengisian kasir tanpa dapat menginput angka fisik. Setelah seluruh 111 item selesai dihitung, tombol Verifikasi Selisih menjadi aktif.',
                    'img'   => '../gambar/testing/langkah_3_admin_monitoring.png'
                ],
                4 => [
                    'title' => 'Langkah 4: Penyaringan Anomali Otomatis & Estimasi Nilai Acuan Kerugian (Admin / Superadmin)',
                    'actor' => 'Admin / Superadmin',
                    'desc'  => 'Pada tahap Verifikasi, sistem menyembunyikan 105 barang yang sudah klop secara otomatis bagi Admin dan hanya menampilkan 6 baris anomali beserta panel Estimasi Nilai Acuan Kerugian Persediaan berdasarkan Harga Beli.',
                    'img'   => '../gambar/testing/langkah_4_verifikasi_anomali.png'
                ],
                5 => [
                    'title' => 'Langkah 5: Pop-Up Konfirmasi Mutasi Selisih Berbasis Radio Button',
                    'actor' => 'Admin / Superadmin',
                    'desc'  => 'Saat tombol verifikasi pada baris anomali diklik, sistem menampilkan pop-up konfirmasi dengan pilihan kategori mutasi berbentuk Radio Button (atau pemilihan nomor seri pada Alkes SN) sebelum mutasi dicatat ke Kartu Stok.',
                    'img'   => '../gambar/testing/langkah_5_popup_mutasi.png'
                ],
                6 => [
                    'title' => 'Langkah 6: Penyelesaian Sesi & Dokumen Cetak Berita Acara (Highlight Baris Selisih)',
                    'actor' => 'Superadmin',
                    'desc'  => 'Setelah seluruh anomali selesai dimutasi, Superadmin menutup sesi untuk membuka kembali kunci transaksi POS. Dokumen Cetak Berita Acara menampilkan seluruh 111 barang dengan bingkai garis merah tegas pada barang yang mengalami selisih.',
                    'img'   => '../gambar/testing/langkah_6_selesai_berita_acara.png'
                ],
            ];

            foreach ($steps as $num => $st):
            ?>
            <div class="border border-slate-200 rounded p-4 space-y-2.5 bg-white break-inside-avoid">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($st['title']) ?></h3>
                    <span class="text-[10px] font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 shrink-0">
                        Aktor: <?= htmlspecialchars($st['actor']) ?>
                    </span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    <?= htmlspecialchars($st['desc']) ?>
                </p>

                <div class="screenshot-box">
                    <div class="text-[10px] font-mono font-bold text-slate-600 uppercase tracking-wider mb-2">
                        Tangkapan Layar Langkah <?= $num ?>
                    </div>
                    <?php if ($modeTampilan === 'placeholder'): ?>
                        <div class="screenshot-placeholder-empty">
                            <img src="" alt="Tangkapan Layar Langkah <?= $num ?>" class="hidden">
                            <span>[ Placeholder Tangkapan Layar Langkah <?= $num ?> ]</span>
                        </div>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($st['img']) ?>?v=<?= time() ?>" 
                             alt="Tangkapan Layar Langkah <?= $num ?>" 
                             class="w-full h-auto rounded border border-slate-300 shadow-2xs mx-auto block"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="screenshot-placeholder-empty" style="display:none;">
                            <span>Tangkapan Layar Langkah <?= $num ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>

</div>

<?php
layoutFooter();
?>
