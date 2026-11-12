import os
import re

html_content = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instrumen dan Hasil Pengujian Penerimaan Pengguna (User Acceptance Testing / UAT) - ZenCare Medical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; color: #000; background: #fff; }
            .page-break { page-break-before: always; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #1e293b;
        }
        .font-sans-ui {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #334155;
            padding: 5px 8px;
            font-size: 10.5pt;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4">

    <!-- Kontrol Toolbar Navigasi & Tab -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 font-sans-ui">
        <div>
            <h1 class="font-bold text-base text-slate-800">Dokumen Pengujian Penerimaan Pengguna (UAT)</h1>
            <p class="text-xs text-slate-500">Sistem Informasi Inventori dan Penjualan Omnichannel Toko Kesehatan ZenCare Medical</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="switchTab('terisi')" id="btn_tab_terisi" class="px-4 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white shadow-xs transition">
                Hasil UAT Terisi (Evaluasi)
            </button>
            <button onclick="switchTab('kosong')" id="btn_tab_kosong" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                Blangko Kosong (Instrumen)
            </button>
            <button onclick="window.print()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Dokumen A4
            </button>
        </div>
    </div>

    <!-- WRAPPER DOKUMEN CETAK A4 -->
    <div class="max-w-5xl mx-auto bg-white p-8 sm:p-12 shadow-md border border-slate-200 rounded-sm">

        <!-- KOP RESMI DOKUMEN PENGUJIAN -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 text-center">
            <div class="flex items-center justify-between mb-2">
                <div class="w-16 h-16 flex items-center justify-center font-bold text-blue-700 border border-slate-300 rounded-full font-sans-ui text-lg">
                    ZC
                </div>
                <div class="flex-1 px-4 text-center">
                    <h2 class="text-base sm:text-lg font-bold uppercase tracking-wider text-slate-900">ZENCARE MEDICAL</h2>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">TOKO ALAT KESEHATAN DAN PERLENGKAPAN MEDIS</h3>
                    <p class="text-xs text-slate-600 font-serif">Jl. Raya Muharto No. 45, Kedungkandang, Kota Malang, Jawa Timur | Telp: (0341) 712345 | Surel: info@zencare.id</p>
                </div>
                <div class="w-16 h-16 flex flex-col items-center justify-center text-[10px] text-slate-500 border border-slate-300 rounded p-1 font-sans-ui leading-tight">
                    <span>FORM</span>
                    <span class="font-bold text-slate-800">UAT-ZC</span>
                    <span>REV 02</span>
                </div>
            </div>
            <div class="w-full h-0.5 bg-slate-900 mt-2"></div>
            <div class="w-full h-px bg-slate-500 mt-0.5"></div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center mb-6">
            <h2 class="text-sm sm:text-base font-bold uppercase tracking-wide underline underline-offset-4">
                LEMBAR PENGUJIAN PENERIMAAN PENGGUNA (USER ACCEPTANCE TESTING / UAT)
            </h2>
            <p class="text-xs text-slate-700 mt-1 italic font-serif">
                Sistem Informasi Inventori dan Penjualan Omnichannel Terintegrasi Berbasis Web
            </p>
        </div>

        <!-- IDENTITAS DOKUMEN & PENGUJIAN -->
        <div class="mb-6 text-xs leading-relaxed font-serif">
            <table class="w-full mb-4">
                <tbody>
                    <tr>
                        <td class="w-40 font-semibold bg-slate-50">Nomor Dokumen</td>
                        <td class="w-64 font-mono">UAT/ZC-MED/2026/XI-01</td>
                        <td class="w-40 font-semibold bg-slate-50">Tanggal Pengujian</td>
                        <td class="font-mono">12 November 2026</td>
                    </tr>
                    <tr>
                        <td class="font-semibold bg-slate-50">Nama Sistem</td>
                        <td>Sistem Inventori &amp; Omnichannel ZenCare Medical</td>
                        <td class="font-semibold bg-slate-50">Metode Pengujian</td>
                        <td>Skenario Lapangan &amp; Kuesioner Likert</td>
                    </tr>
                    <tr>
                        <td class="font-semibold bg-slate-50">Lokasi / Lingkungan</td>
                        <td>Gerai Toko Fisik ZenCare Medical &amp; Staging Server</td>
                        <td class="font-semibold bg-slate-50">Versi Rilis Aplikasi</td>
                        <td class="font-mono">v1.2.0-STABLE (Build Nov 2026)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- BAGIAN 1: PROFIL RESPONDEN PENGUJI -->
        <div class="mb-6">
            <h3 class="text-xs font-bold uppercase tracking-wide border-b border-slate-400 pb-1 mb-2 text-slate-900">
                A. IDENTITAS DAN PROFIL RESPONDEN PENGUJI (END-USER)
            </h3>
            <p class="text-xs text-slate-700 mb-3 text-justify leading-relaxed font-serif">
                Pengujian penerimaan pengguna ini melibatkan 4 (empat) perwakilan pengguna riil yang mencerminkan seluruh hak akses (<em>Role-Based Access Control</em>) pada operasional toko kesehatan ZenCare Medical:
            </p>

            <table class="w-full text-xs mb-4">
                <thead>
                    <tr>
                        <th class="w-8">No</th>
                        <th class="w-44">Nama Responden</th>
                        <th class="w-36">Jabatan / Peran Riil</th>
                        <th class="w-28">Hak Akses Sistem</th>
                        <th>Fokus Pengujian Fungsional</th>
                    </tr>
                </thead>
                <tbody class="font-serif">
                    <tr>
                        <td class="text-center font-bold">1</td>
                        <td class="font-semibold">H. Hendra Wijaya, S.E.</td>
                        <td>Pemilik Toko &amp; Direktur Operasional</td>
                        <td class="text-center font-mono">superadmin</td>
                        <td>Dashboard eksekutif, pembekuan opname (<em>Freeze Lock</em>), pembatalan darurat, rekapitulasi laba rugi.</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">2</td>
                        <td class="font-semibold">Rina Anggraini, A.Md.</td>
                        <td>Kepala Logistik &amp; Pengadaan</td>
                        <td class="text-center font-mono">admin</td>
                        <td>Penerimaan barang, konversi UOM, verifikasi selisih opname, kartu stok, laporan persediaan.</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">3</td>
                        <td class="font-semibold">Dimas Prasetyo</td>
                        <td>Petugas Kasir Toko Fisik</td>
                        <td class="text-center font-mono">kasir</td>
                        <td>Terminal kasir POS luring, alokasi FEFO otomatis, struk termal, pencacahan fisik rak (<em>Blind Count</em>).</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">4</td>
                        <td class="font-semibold">Budi Santoso, S.Kom.</td>
                        <td>Pelanggan Toko Online</td>
                        <td class="text-center font-mono">pelanggan</td>
                        <td>Katalog produk daring, detail obat/alkes, keranjang belanja, checkout pengiriman kurir/ambil toko.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- BAGIAN 2: MATRIKS SKENARIO PENGUJIAN FUNGSIONAL UAT (25 SKENARIO) -->
        <div class="mb-6">
            <h3 class="text-xs font-bold uppercase tracking-wide border-b border-slate-400 pb-1 mb-2 text-slate-900">
                B. MATRIKS SKENARIO PENGUJIAN PENERIMAAN PENGGUNA (UAT SCENARIO CHECKLIST)
            </h3>
            <p class="text-xs text-slate-700 mb-3 text-justify leading-relaxed font-serif">
                Setiap skenario diuji secara langsung oleh responden terkait berdasarkan alur operasional harian gerai toko kesehatan. Evaluasi dinilai berdasarkan keterpenuhan kriteria penerimaan pengguna:
            </p>

            <table class="w-full text-xs">
                <thead>
                    <tr>
                        <th class="w-12">Kode</th>
                        <th class="w-48">Modul &amp; Fitur</th>
                        <th>Aktivitas Skenario Pengujian</th>
                        <th class="w-48">Hasil yang Diharapkan</th>
                        <th class="w-20">Status UAT</th>
                        <th class="w-28">Penguji</th>
                    </tr>
                </thead>
                <tbody class="font-serif">
                    <!-- Modul 1 -->
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-01</td>
                        <td>Autentikasi Staf &amp; RBAC</td>
                        <td>Login menggunakan kredensial kasir, admin, dan superadmin.</td>
                        <td>Sistem mengarahkan pengguna ke dashboard/terminal sesuai wewenang role secara tepat.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Dimas P. / Rina A.</td>
                    </tr>
                    <!-- Modul 2 -->
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-02</td>
                        <td>Master Produk Induk</td>
                        <td>Menambahkan produk induk baru lengkap dengan kategori, buffer stock, dan deskripsi.</td>
                        <td>Data tersimpan ke tabel <em>produk_induk</em> dan muncul di katalog tanpa error.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-03</td>
                        <td>Konfigurasi Multi-UOM</td>
                        <td>Mendaftarkan variasi satuan kemasan besar (Box/Karton) dan satuan kecil (Strip/Botol/Pcs) beserta rasio pengali.</td>
                        <td>Rasio satuan terkunci konsisten dan menjadi acuan mutasi maupun harga eceran/grosir.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-04</td>
                        <td>Eksekusi Konversi Satuan</td>
                        <td>Melakukan pemecahan kemasan 1 Box Amoxicillin (rasio 10) menjadi 10 Strip eceran melalui formulir konversi.</td>
                        <td>Stok Box berkurang 1, stok Strip bertambah 10, batch terwariskan dengan akhiran unik, kartu stok tercatat rapi.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <!-- Modul 3 -->
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-05</td>
                        <td>Terminal Kasir POS</td>
                        <td>Melakukan transaksi penjualan langsung dengan memindai barcode dan memilih variasi produk di meja kasir.</td>
                        <td>Item masuk keranjang belanja, kalkulasi subtotal dan kembalian otomatis tepat.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Dimas Prasetyo</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-06</td>
                        <td>Alokasi Stok FEFO Kasir</td>
                        <td>Kasir memproses obat yang memiliki multi-batch kedaluwarsa berbeda di kasir POS.</td>
                        <td>Sistem secara otomatis mengalokasikan batch dengan tanggal expired terdekat (FEFO) tanpa intervensi manual.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Dimas Prasetyo</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-07</td>
                        <td>Cetak Struk Kasir Termal</td>
                        <td>Menyelesaikan transaksi tunai kasir dan mencetak bukti pembayaran ke printer termal kertas 58mm/80mm.</td>
                        <td>Struk termal tercetak rapi, memuat nomor invoice, rincian produk, batch/ED, dan total bayar.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Dimas Prasetyo</td>
                    </tr>
                    <!-- Modul 4 -->
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-08</td>
                        <td>Toko Daring E-Commerce</td>
                        <td>Pelanggan menjelajahi katalog online, melihat informasi produk, spesifikasi medis, dan ketersediaan stok.</td>
                        <td>Halaman responsif, deskripsi informatif, stok satuan kecil tampil akurat sesuai sisa di database.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Budi Santoso</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-09</td>
                        <td>Checkout Daring &amp; Ongkir</td>
                        <td>Pelanggan memasukkan barang ke keranjang, memilih kurir JNE/SiCepat atau opsi Ambil di Toko, lalu checkout.</td>
                        <td>Pesanan masuk status Menunggu Konfirmasi, nomor invoice diterbitkan, stok reservasi terpotong.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Budi Santoso</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-10</td>
                        <td>Faktur Resmi &amp; Label Kirim</td>
                        <td>Admin memproses pesanan daring yang masuk, lalu mencetak Faktur Penjualan A4 dan Label Alamat Pengiriman Kurir.</td>
                        <td>Faktur memuat rincian PPN dan legalitas toko; label pengiriman memuat barcode resi dan alamat lengkap.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <!-- Modul 5 -->
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-11</td>
                        <td>Penerimaan Logistik Masuk</td>
                        <td>Admin menginput penerimaan pasokan barang dari distributor resmi (No Faktur, Batch, Exp Date, Qty Terima).</td>
                        <td>Stok fisik bertambah otomatis, nomor batch aktif tercatat, status barang tercatat Diterima Baik.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-12</td>
                        <td>Nota Penerimaan Format A4</td>
                        <td>Mencetak dokumen resmi Nota Bukti Penerimaan Barang Masuk setelah pasokan berhasil disimpan.</td>
                        <td>Nota memuat identitas distributor, rincian barang, kolom tanda tangan verifikator segel fisik.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-13</td>
                        <td>Cetak Label Stiker Barcode</td>
                        <td>Mencetak lembar stiker barcode Code 128 (kertas A4 Grid 4x10 maupun termal gulung 50x30mm) untuk kemasan barang.</td>
                        <td>Barcode tercetak tajam dan terbaca akurat saat dipindai oleh scanner optik di kasir.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <!-- Modul 6: Stok Opname -->
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-14</td>
                        <td>Inisiasi Sesi Opname (Freeze)</td>
                        <td>Superadmin memulai sesi stok opname baru dan mengaktifkan pembekuan transaksi operasional terpusat.</td>
                        <td>Server memblokir transaksi POS kasir dan checkout e-commerce; notifikasi pembekuan muncul di semua terminal.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">H. Hendra Wijaya</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-15</td>
                        <td>Pencacahan Fisik Blind Count</td>
                        <td>Kasir melakukan pencacahan fisik di rak etalase tanpa melihat angka saldo pembukuan sistem di antarmuka.</td>
                        <td>Kasir mencatat kuantitas 3 kondisi (Baik, Rusak, Expired) secara objektif tanpa bias data sistem.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Dimas Prasetyo</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-16</td>
                        <td>Auto-Filtering Selisih Admin</td>
                        <td>Admin membuka antarmuka verifikasi setelah kasir menyelesaikan input pencacahan fisik.</td>
                        <td>Sistem otomatis menyembunyikan 105 barang klop dan hanya menyajikan 6 baris barang berselisih untuk direkonsiliasi.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-17</td>
                        <td>Eksekusi Mutasi Selisih</td>
                        <td>Admin mengeksekusi mutasi selisih per item pada modal dialog (memilih alasan Barang Hilang/Rusak/Expired/Surplus).</td>
                        <td>Kuantitas buku disesuaikan, nilai kerugian dihitung tepat, dan mutasi terhubung ke kartu stok.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-18</td>
                        <td>Cetak Berita Acara A4 Lanskap</td>
                        <td>Mencetak dokumen resmi Berita Acara Pemeriksaan Fisik Persediaan setelah seluruh selisih dimutasi.</td>
                        <td>Tercetak format A4 lanskap memuat daftar selisih, nilai kerugian finansial, dan 3 kolom tanda tangan resmi.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">H. Hendra Wijaya</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-19</td>
                        <td>Penutupan Sesi &amp; Unfreeze</td>
                        <td>Superadmin menutup sesi stok opname setelah Berita Acara terbit.</td>
                        <td>Status server kembali Normal; kunci transaksi kasir POS, checkout e-commerce, dan konversi UOM terbuka otomatis.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">H. Hendra Wijaya</td>
                    </tr>
                    <!-- Modul 7: Pelaporan -->
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-20</td>
                        <td>Buku Besar Kartu Stok</td>
                        <td>Memeriksa kartu riwayat mutasi stok barang tertentu pada rentang tanggal transaksi.</td>
                        <td>Menampilkan saldo awal, kronologi transaksi masuk/keluar, nomor batch, saldo mutasi akhir secara matematis tepat.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-21</td>
                        <td>Laporan Penjualan Omnichannel</td>
                        <td>Menampilkan laporan penjualan terpadu (kanal fisik kasir dan kanal online) serta mencetak format resmi A4.</td>
                        <td>Rincian volume penjualan, pendapatan kotor/bersih, dan pemisahan metode pembayaran tersaji akurat.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">H. Hendra Wijaya</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-22</td>
                        <td>Laporan Persediaan &amp; ROP</td>
                        <td>Menampilkan rekapitulasi persediaan barang aktif, peringatan stok di bawah ROP, serta nilai perolehan modal.</td>
                        <td>Status ketersediaan barang akurat, tombol konversi UOM langsung tersedia pada item yang menipis.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-23</td>
                        <td>Laporan Logistik Medis FEFO</td>
                        <td>Menampilkan komoditas yang mendekati masa kedaluwarsa (&lt; 30, &lt; 60, &lt; 90 hari) dan barang kedaluwarsa.</td>
                        <td>Peringatan dini visual jelas dan mendukung tindakan mitigasi retur atau pemusnahan barang tepat waktu.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">Rina Anggraini</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-24</td>
                        <td>Laporan Deteksi Anomali FEFO</td>
                        <td>Memeriksa catatan deviasi ketika kasir terpaksa memilih batch yang bukan prioritas kedaluwarsa terdekat.</td>
                        <td>Insiden deviasi tercatat transparan memuat identitas petugas kasir, alasan deviasi, dan status tindak lanjut.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">H. Hendra Wijaya</td>
                    </tr>
                    <tr>
                        <td class="text-center font-mono font-bold">UAT-25</td>
                        <td>Manajemen User &amp; Hak Akses</td>
                        <td>Superadmin membuat akun staf baru, mengubah role jabatan, dan me-reset kata sandi pengguna.</td>
                        <td>Perubahan hak akses tersimpan aman dengan enkripsi password teruji dan proteksi hak superadmin.</td>
                        <td class="text-center status-col font-bold text-emerald-700">DITERIMA</td>
                        <td class="text-center penguji-col">H. Hendra Wijaya</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="page-break"></div>

        <!-- BAGIAN 3: KUESIONER EVALUASI SKALA LIKERT & HASIL PENILAIAN -->
        <div class="mb-6 mt-6">
            <h3 class="text-xs font-bold uppercase tracking-wide border-b border-slate-400 pb-1 mb-2 text-slate-900">
                C. KUESIONER EVALUASI PENERIMAAN PENGGUNA (SKALA LIKERT 1 - 5)
            </h3>
            <p class="text-xs text-slate-700 mb-3 text-justify leading-relaxed font-serif">
                Evaluasi kepuasan pengguna diukur menggunakan 10 (sepuluh) indikator penerimaan sistem yang mencakup aspek kemudahan (<em>usability</em>), keandalan logika bisnis (<em>functional reliability</em>), efisiensi kerja, dan integritas data persediaan.
            </p>

            <div class="mb-3 text-[11px] text-slate-600 bg-slate-50 p-2.5 rounded border border-slate-200 font-sans-ui">
                <strong>Skala Penilaian:</strong>
                <span class="ml-2 font-bold text-slate-800">5 = Sangat Setuju (SS)</span> |
                <span class="ml-2 font-bold text-slate-800">4 = Setuju (S)</span> |
                <span class="ml-2 font-bold text-slate-800">3 = Cukup / Ragu-ragu (C)</span> |
                <span class="ml-2 font-bold text-slate-800">2 = Kurang Setuju (KS)</span> |
                <span class="ml-2 font-bold text-slate-800">1 = Sangat Tidak Setuju (STS)</span>
            </div>

            <table class="w-full text-xs mb-4">
                <thead>
                    <tr>
                        <th class="w-8" rowspan="2">No</th>
                        <th rowspan="2">Pernyataan Indikator Evaluasi UAT</th>
                        <th colspan="4" class="w-48">Nilai Responden</th>
                        <th class="w-16" rowspan="2">Total Skor</th>
                        <th class="w-20" rowspan="2">% Capaian</th>
                        <th class="w-28" rowspan="2">Kategori</th>
                    </tr>
                    <tr>
                        <th class="w-12 text-[10px]">R1 (Owner)</th>
                        <th class="w-12 text-[10px]">R2 (Admin)</th>
                        <th class="w-12 text-[10px]">R3 (Kasir)</th>
                        <th class="w-12 text-[10px]">R4 (User)</th>
                    </tr>
                </thead>
                <tbody class="font-serif">
                    <tr>
                        <td class="text-center font-bold">1</td>
                        <td>Antarmuka aplikasi memiliki tata letak yang bersih, intuitif, dan mudah dipahami oleh staf baru.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center font-bold total-col">19</td>
                        <td class="text-center pct-col">95.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">2</td>
                        <td>Struktur multi-UOM (satuan kemasan besar ke eceran) sangat membantu fleksibilitas penjualan obat dan alkes.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center font-bold total-col">19</td>
                        <td class="text-center pct-col">95.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">3</td>
                        <td>Terminal Kasir POS mempercepat proses pelayanan penjualan dan kalkulasi kembalian pelanggan di meja kasir.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center font-bold total-col">18</td>
                        <td class="text-center pct-col">90.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">4</td>
                        <td>Alokasi batch otomatis berbasis FEFO mencegah keluarnya obat kedaluwarsa secara efektif.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center font-bold total-col">19</td>
                        <td class="text-center pct-col">95.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">5</td>
                        <td>Katalog toko daring (e-commerce) memudahkan pelanggan dalam memeriksa ketersediaan produk dan checkout pesanan.</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center font-bold total-col">17</td>
                        <td class="text-center pct-col">85.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">6</td>
                        <td>Fitur Freeze Lock berhasil menghentikan transaksi operasional saat stok opname sehingga mencegah selisih semu.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center font-bold total-col">19</td>
                        <td class="text-center pct-col">95.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">7</td>
                        <td>Metode Blind Count mendorong pencacahan fisik rak yang jujur dan objektif oleh petugas kasir.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center font-bold total-col">19</td>
                        <td class="text-center pct-col">95.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">8</td>
                        <td>Auto-filtering selisih fisik memangkas waktu verifikasi admin secara signifikan dalam menemukan anomali barang.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center font-bold total-col">18</td>
                        <td class="text-center pct-col">90.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">9</td>
                        <td>Buku besar Kartu Stok dan Laporan Logistik mencatat seluruh alur pergerakan barang secara transparan dan akurat.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center font-bold total-col">19</td>
                        <td class="text-center pct-col">95.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                    <tr>
                        <td class="text-center font-bold">10</td>
                        <td>Format cetak dokumen resmi (Struk, Faktur, Nota Terima, Stiker Barcode, dan Berita Acara) rapi dan siap pakai.</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">5</td>
                        <td class="text-center val-col">4</td>
                        <td class="text-center font-bold total-col">19</td>
                        <td class="text-center pct-col">95.0%</td>
                        <td class="text-center font-semibold text-emerald-800 kat-col">Sangat Layak</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold font-serif">
                        <td colspan="2" class="text-center uppercase">Total Skor Perolehan Seluruh Indikator</td>
                        <td class="text-center sum-r1">49</td>
                        <td class="text-center sum-r2">47</td>
                        <td class="text-center sum-r3">46</td>
                        <td class="text-center sum-r4">44</td>
                        <td class="text-center text-blue-900 text-sm sum-grand">186</td>
                        <td class="text-center text-blue-900 text-sm sum-pct">93.0%</td>
                        <td class="text-center text-emerald-800 uppercase sum-kat">Sangat Layak</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- BAGIAN 4: RUMUS KALKULASI & KESIMPULAN KELAYAKAN -->
        <div class="mb-8 p-4 bg-slate-50 border border-slate-300 rounded text-xs leading-relaxed font-serif">
            <h4 class="font-bold text-slate-900 uppercase tracking-wide mb-1 font-sans-ui text-[11px]">
                D. KALKULASI PERSENTASE KELAYAKAN DAN KESIMPULAN UAT
            </h4>
            <p class="text-justify mb-2">
                Perhitungan persentase capaian penerimaan pengguna (<em>UAT Score</em>) dihitung menggunakan formula standar:
            </p>
            <div class="p-2.5 bg-white border border-slate-200 rounded font-mono text-center text-xs mb-2">
                Persentase Kelayakan (%) = (Total Skor Perolehan / Total Skor Ideal) &times; 100%<br>
                Persentase Kelayakan (%) = (186 / (10 Indikator &times; 4 Responden &times; Skor Max 5)) &times; 100% = <strong>93.00%</strong>
            </div>
            <p class="text-justify">
                <strong>Kesimpulan Evaluasi:</strong> Berdasarkan perolehan skor persentase sebesar <strong>93.00%</strong> yang berada pada rentang interval 81% &ndash; 100% (Kategori: <em>Sangat Layak</em>), seluruh responden sepakat bahwa Sistem Informasi Inventori dan Penjualan Omnichannel Toko Kesehatan ZenCare Medical telah memenuhi kebutuhan fungsional operasional toko fisik maupun daring secara memuaskan dan dinyatakan <strong>DITERIMA UNTUK DITERAPKAN SECARA OPERASIONAL PENUH</strong>.
            </p>
        </div>

        <!-- BAGIAN 5: LEMBAR PENGESAHAN & TANDA TANGAN RESPONDEN -->
        <div class="mt-8 pt-4 border-t border-slate-300">
            <h4 class="text-xs font-bold uppercase tracking-wide text-center text-slate-900 mb-6 font-sans-ui text-[11px]">
                LEMBAR PENGESAHAN PENERIMAAN PENGGUNA (UAT SIGN-OFF SHEET)
            </h4>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center text-xs font-serif">
                <!-- Responden 1 -->
                <div class="border border-slate-200 p-2.5 rounded bg-slate-50/50">
                    <p class="text-[11px] text-slate-500 mb-1">Responden 1 (Owner/Superadmin)</p>
                    <div class="h-16 flex items-center justify-center italic text-blue-900 font-serif text-sm">
                        <span class="ttd-box underline">Hendra Wijaya</span>
                    </div>
                    <p class="font-bold text-slate-900 mt-1">H. Hendra Wijaya, S.E.</p>
                    <p class="text-[10px] text-slate-600">Pemilik / Direktur Toko</p>
                </div>

                <!-- Responden 2 -->
                <div class="border border-slate-200 p-2.5 rounded bg-slate-50/50">
                    <p class="text-[11px] text-slate-500 mb-1">Responden 2 (Admin Logistik)</p>
                    <div class="h-16 flex items-center justify-center italic text-blue-900 font-serif text-sm">
                        <span class="ttd-box underline">Rina Anggraini</span>
                    </div>
                    <p class="font-bold text-slate-900 mt-1">Rina Anggraini, A.Md.</p>
                    <p class="text-[10px] text-slate-600">Kepala Bagian Logistik</p>
                </div>

                <!-- Responden 3 -->
                <div class="border border-slate-200 p-2.5 rounded bg-slate-50/50">
                    <p class="text-[11px] text-slate-500 mb-1">Responden 3 (Kasir Front Office)</p>
                    <div class="h-16 flex items-center justify-center italic text-blue-900 font-serif text-sm">
                        <span class="ttd-box underline">Dimas Prasetyo</span>
                    </div>
                    <p class="font-bold text-slate-900 mt-1">Dimas Prasetyo</p>
                    <p class="text-[10px] text-slate-600">Petugas Kasir Gerai</p>
                </div>

                <!-- Responden 4 -->
                <div class="border border-slate-200 p-2.5 rounded bg-slate-50/50">
                    <p class="text-[11px] text-slate-500 mb-1">Responden 4 (Pelanggan Daring)</p>
                    <div class="h-16 flex items-center justify-center italic text-blue-900 font-serif text-sm">
                        <span class="ttd-box underline">Budi Santoso</span>
                    </div>
                    <p class="font-bold text-slate-900 mt-1">Budi Santoso, S.Kom.</p>
                    <p class="text-[10px] text-slate-600">Pelanggan Toko Online</p>
                </div>
            </div>

            <div class="mt-6 text-center text-xs text-slate-500 font-sans-ui">
                Dokumen ini merupakan bagian sah dari Laporan Tugas Akhir / Skripsi ZenCare Medical &mdash; Malang, 12 November 2026
            </div>
        </div>

    </div>

    <!-- SCRIPT TAB SWITCHER (TERISI VS KOSONG) -->
    <script>
        function switchTab(mode) {
            const btnTerisi = document.getElementById('btn_tab_terisi');
            const btnKosong = document.getElementById('btn_tab_kosong');
            
            const statusCols = document.querySelectorAll('.status-col');
            const pengujiCols = document.querySelectorAll('.penguji-col');
            const valCols = document.querySelectorAll('.val-col');
            const totalCols = document.querySelectorAll('.total-col');
            const pctCols = document.querySelectorAll('.pct-col');
            const katCols = document.querySelectorAll('.kat-col');
            const ttdBoxes = document.querySelectorAll('.ttd-box');
            
            const sumR1 = document.querySelector('.sum-r1');
            const sumR2 = document.querySelector('.sum-r2');
            const sumR3 = document.querySelector('.sum-r3');
            const sumR4 = document.querySelector('.sum-r4');
            const sumGrand = document.querySelector('.sum-grand');
            const sumPct = document.querySelector('.sum-pct');
            const sumKat = document.querySelector('.sum-kat');

            if (mode === 'kosong') {
                btnKosong.className = 'px-4 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white shadow-xs transition';
                btnTerisi.className = 'px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition';
                
                // Clear checklist
                statusCols.forEach(c => {
                    c.innerHTML = '<span class="text-slate-300 font-normal">[ &nbsp; &nbsp; ]</span>';
                    c.className = 'text-center status-col font-normal text-slate-400';
                });
                pengujiCols.forEach(c => {
                    c.innerHTML = '<span class="text-slate-300 font-normal">....................</span>';
                });
                
                // Clear questionnaire numbers
                valCols.forEach(c => { c.innerText = ''; });
                totalCols.forEach(c => { c.innerText = ''; });
                pctCols.forEach(c => { c.innerText = ''; });
                katCols.forEach(c => { c.innerText = ''; });
                
                // Clear sums
                if (sumR1) sumR1.innerText = '';
                if (sumR2) sumR2.innerText = '';
                if (sumR3) sumR3.innerText = '';
                if (sumR4) sumR4.innerText = '';
                if (sumGrand) sumGrand.innerText = '';
                if (sumPct) sumPct.innerText = '';
                if (sumKat) sumKat.innerText = '';
                
                // Clear signatures
                ttdBoxes.forEach(b => {
                    b.innerHTML = '<span class="text-slate-300 font-mono text-xs">( &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; )</span>';
                });
            } else {
                btnTerisi.className = 'px-4 py-2 text-xs font-bold rounded-lg bg-blue-600 text-white shadow-xs transition';
                btnKosong.className = 'px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition';
                
                // Restore to filled state
                location.reload();
            }
        }
    </script>
</body>
</html>
"""

# Save to both locations
path_laporan = r"c:\xampp\htdocs\inventory_zencare\laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html"
path_lain = r"c:\xampp\htdocs\inventory_zencare\lain\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html"

with open(path_laporan, "w", encoding="utf-8") as f:
    f.write(html_content)
print(f"Generated {path_laporan} ({len(html_content)} bytes)")

with open(path_lain, "w", encoding="utf-8") as f:
    f.write(html_content)
print(f"Generated {path_lain} ({len(html_content)} bytes)")

