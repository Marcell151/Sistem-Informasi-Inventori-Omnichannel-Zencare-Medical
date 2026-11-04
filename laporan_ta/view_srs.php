<?php
// File: laporan_ta/view_srs.php
// Pratinjau Dokumen SRS Resmi ZenCare Medical (Bisa langsung dibuka di browser & Cetak/Save PDF via Ctrl+P)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen SRS - Sistem Informasi Inventori & POS ZenCare Medical</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Mermaid.js untuk render diagram alur & ERD secara visual dan tajam -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <script>
        mermaid.initialize({
            startOnLoad: true,
            theme: 'neutral',
            fontFamily: 'Inter, sans-serif'
        });
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .prose code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85em;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 1rem 0;
            font-size: 0.875rem;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #0f2d5a;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .mermaid {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            display: flex;
            justify-content: center;
        }
        @media print {
            body { background: #ffffff !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .page-break { page-break-before: always; }
            table, tr, td, th { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6 lg:px-8">

    <!-- Floating Action Toolbar (Layar Saja) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 sticky top-4 z-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 font-black text-sm">
                SRS
            </div>
            <div>
                <h2 class="font-extrabold text-[#0f2d5a] text-sm">Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS)</h2>
                <p class="text-xs text-slate-500">Format Visual Interaktif Lengkap dengan Diagram Alur, ERD, &amp; Kamus Data Basis Data</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <button onclick="window.print()" class="px-4 py-2 bg-[#1a75d2] hover:bg-[#1562b3] text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak / Simpan PDF (Ctrl+P)</span>
            </button>
            <a href="DOKUMENTASI_SRS_LENGKAP_ZENCARE_MEDICAL.doc" class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Download Word (.doc)</span>
            </a>
        </div>
    </div>

    <!-- Container Utama Dokumen -->
    <main class="print-container max-w-5xl mx-auto bg-white border border-slate-200 rounded-3xl p-8 sm:p-14 shadow-sm space-y-12">

        <!-- KOP DOKUMEN RESMI -->
        <header class="border-b-2 border-[#0f2d5a] pb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare Medical" class="h-16 w-auto object-contain" onerror="this.src='../gambar/Logo.png'">
                <div>
                    <h1 class="text-2xl font-black text-[#0f2d5a] tracking-tight leading-none">ZENCARE MEDICAL</h1>
                    <span class="text-xs font-bold text-[#1a75d2] uppercase tracking-widest block mt-1">Sistem Informasi Inventori &amp; POS Omnichannel</span>
                    <p class="text-[11px] text-slate-500 mt-0.5">Distributor Resmi Farmasi &amp; Alat Kesehatan Medis Terstandarisasi</p>
                </div>
            </div>
            <div class="text-left sm:text-right text-xs text-slate-600 leading-snug">
                <span class="font-mono font-bold text-[#0f2d5a] block text-sm">DOKUMEN SRS v3.0 (FINAL)</span>
                <span>Jl. Muharto No. 55D, Jodipan, Kota Malang</span><br>
                <span>Kontak: 0895-3434-36025 &bull; Email: zencare.id@gmail.com</span>
            </div>
        </header>

        <!-- JUDUL BESAR DOKUMEN -->
        <div class="text-center py-4 bg-[#eef6fb] border border-[#cbe4f5] rounded-2xl">
            <h2 class="text-xl sm:text-2xl font-black text-[#0f2d5a] uppercase tracking-wide">
                SPESIFIKASI KEBUTUHAN PERANGKAT LUNAK (SRS)
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 font-medium mt-1">
                Perancangan Sistem Inventori Farmasi, UOM Bertingkat, FEFO, Serial Number Alkes, Omnichannel POS, &amp; Stok Opname
            </p>
        </div>

        <!-- BAB 1: PENDAHULUAN & RUANG LINGKUP -->
        <section class="space-y-4">
            <h3 class="text-lg font-black text-[#0f2d5a] uppercase border-b-2 border-slate-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-md bg-[#0f2d5a] text-white flex items-center justify-center text-xs">1</span>
                Pendahuluan &amp; Ruang Lingkup Sistem
            </h3>
            <p class="text-sm text-slate-700 leading-relaxed text-justify">
                <strong>ZenCare Medical</strong> adalah sistem informasi terintegrasi (*omnichannel*) yang dirancang khusus untuk memfasilitasi kegiatan distribusi dan operasional ritel obat-obatan kemasan besar serta alat kesehatan berstandar klinis. Sistem ini menjembatani transaksi luring melalui **Kasir POS (Point-of-Sale)** di gerai fisik dan transaksi daring melalui platform **E-Commerce**, yang keseluruhannya bersumber langsung pada stok fisik terpusat di **Cabang Muharto**.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <h4 class="font-bold text-xs uppercase text-[#0f2d5a] mb-1">Karakteristik Komoditas Obat:</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Dikelola berdasarkan <strong>Nomor Batch Pabrik</strong>, tanggal kedaluwarsa fisik, kaidah ketat <strong>FEFO (First Expired, First Out)</strong>, serta konversi satuan bertingkat (Satuan Besar/Karton/Dus dipecah menjadi Satuan Kecil/Strip/Botol/Pcs) yang menghasilkan sub-batch resmi.
                    </p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <h4 class="font-bold text-xs uppercase text-[#0f2d5a] mb-1">Karakteristik Komoditas Alat Kesehatan:</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Dikelola berdasarkan <strong>Nomor Seri Fisik Unik (Serial Number / SN)</strong> per unit kemasan utuh pabrik. Setiap unit dilacak secara individual status keberadaannya (Tersedia, Terjual, Retur/Rusak, Hilang) beserta tanggal garansi resminya.
                    </p>
                </div>
            </div>
        </section>

        <!-- BAB 2: ATURAN BISNIS & ARSITEKTUR KOMODITAS (DIAGRAM VISUAL) -->
        <section class="space-y-4">
            <h3 class="text-lg font-black text-[#0f2d5a] uppercase border-b-2 border-slate-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-md bg-[#0f2d5a] text-white flex items-center justify-center text-xs">2</span>
                Struktur Komoditas Medis &amp; Aturan Bisnis Khusus
            </h3>
            
            <p class="text-xs text-slate-500 mb-2 font-semibold">Diagram 2.1: Pemisahan Logika Komoditas Obat vs Alat Kesehatan pada ZenCare Medical</p>
            
            <!-- MERMAID DIAGRAM 1: TAKSONOMI PRODUK -->
            <div class="mermaid">
graph TD
    A["Produk ZenCare Medical"] --> B["Kategori: OBAT"]
    A --> C["Kategori: ALAT KESEHATAN (ALKES)"]

    B --> B1["Pelacakan Lot / Batch Pabrik"]
    B --> B2["Kaidah Pengeluaran: FEFO (First Expired, First Out)"]
    B --> B3["UOM Ganda: Satuan Besar (Karton/Dus) & Kecil (Strip/Botol)"]
    B --> B4["Konversi: Pembongkaran Dus melahirkan Sub-Batch (.A, .B)"]

    C --> C1["Pelacakan Nomor Seri Unik (Serial Number / SN)"]
    C --> C2["Satu Barcode SN = Satu Unit Fisik Kemasan Utuh"]
    C --> C3["Pelacakan Masa Garansi Pabrik (Exp Date Garansi)"]
    C --> C4["Status Unit: Tersedia, Terjual, Retur/Rusak, Hilang"]

    style A fill:#0f2d5a,stroke:#0f2d5a,color:#ffffff,stroke-width:2px;
    style B fill:#eff6ff,stroke:#1a75d2,color:#0f2d5a,stroke-width:2px;
    style C fill:#ecfdf5,stroke:#059669,color:#064e3b,stroke-width:2px;
            </div>

            <div class="space-y-3 pt-2 text-sm text-slate-700 leading-relaxed">
                <h4 class="font-extrabold text-xs text-[#0f2d5a] uppercase">Rincian Aturan Bisnis Inti:</h4>
                <ul class="list-disc list-inside space-y-2 text-xs text-slate-700">
                    <li><strong>Hierarki 2-Tingkat Katalog:</strong> Data dipisahkan menjadi <code>produk_induk</code> (nama dagang umum &amp; deskripsi) dan <code>produk_variasi</code> (SKU varian kemasan, UOM, harga jual, dan rasio konversi).</li>
                    <li><strong>Audit Anomali FEFO:</strong> Apabila kasir POS mengeluarkan obat dari batch yang tanggal kedaluwarsanya lebih lama daripada batch terdekat yang masih memiliki stok, transaksi tetap diizinkan namun sistem secara otomatis mencatat deviasi tersebut ke tabel <code>log_anomali_fefo</code> untuk diaudit oleh SuperAdmin.</li>
                    <li><strong>Reservasi Stok Otomatis (Cooldown 2 Jam):</strong> Saat pelanggan melakukan checkout online, status pesanan menjadi <code>Menunggu Pembayaran</code> dan stok sistem langsung dikunci (*reserved*). Sistem memberikan batas waktu pembayaran 2 jam. Jika melewati batas tanpa pembayaran, pesanan dibatalkan otomatis dan stok dikembalikan.</li>
                    <li><strong>Freezing Session Saat Stok Opname:</strong> Seluruh modul transaksi kasir POS, checkout daring, penerimaan logistik, dan mutasi manual dibekukan sementara (*system lockout*) saat sesi opname berstatus <code>Aktif</code> guna mencegah <em>race condition</em>.</li>
                </ul>
            </div>
        </section>

        <!-- BAB 3: BASIS DATA LENGKAP & ERD -->
        <section class="space-y-4">
            <h3 class="text-lg font-black text-[#0f2d5a] uppercase border-b-2 border-slate-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-md bg-[#0f2d5a] text-white flex items-center justify-center text-xs">3</span>
                Desain Basis Data Relasional (ERD &amp; Kamus Data)
            </h3>

            <p class="text-xs text-slate-500 mb-2 font-semibold">Diagram 3.1: Entity Relationship Diagram (ERD) Sistem Informasi ZenCare Medical</p>

            <!-- MERMAID DIAGRAM 2: ERD -->
            <div class="mermaid">
erDiagram
    users ||--o{ penjualan : "melakukan transaksi"
    users ||--o{ sesi_opname : "membuka/menutup sesi"
    users ||--o{ penerimaan : "menerima logistik"
    users ||--o{ histori_konversi : "membongkar dus"
    supplier ||--o{ penerimaan : "memasok barang"

    produk_induk ||--|{ produk_variasi : "memiliki varian"
    produk_variasi ||--o{ stok_batch : "memiliki lot batch"
    produk_variasi ||--o{ unit_serial : "memiliki serial number"
    produk_variasi ||--|| stok_toko : "akumulasi stok toko"
    produk_variasi ||--o{ kartu_stok : "buku besar mutasi"

    penerimaan ||--|{ penerimaan_detail : "rincian barang masuk"
    produk_variasi ||--o{ penerimaan_detail : "diterima sebagai"

    penjualan ||--|{ detail_penjualan : "item terjual"
    produk_variasi ||--o{ detail_penjualan : "dipilih dalam"
    penjualan ||--o{ log_anomali_fefo : "memicu deviasi"

    sesi_opname ||--|{ detail_opname_batch : "rincian audit stok"
    produk_variasi ||--o{ detail_opname_batch : "dihitung dalam"
    detail_opname_batch ||--o{ detail_opname_sn : "checklist unit SN"
    detail_opname_batch ||--o{ riwayat_hitung_opname : "rekam koreksi hitung"
            </div>

            <!-- KAMUS DATA LENGKAP -->
            <div class="pt-4 space-y-6">
                <h4 class="font-extrabold text-sm text-[#0f2d5a] uppercase tracking-wide">Kamus Data Struktur Tabel Basis Data:</h4>

                <!-- Tabel Users -->
                <div>
                    <h5 class="font-bold text-xs text-slate-800 font-mono mb-1">1. Tabel `users`</h5>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr><th>Kolom</th><th>Tipe Data</th><th>Null</th><th>Key</th><th>Default</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code>id</code></td><td>int(11)</td><td>NO</td><td>PRI</td><td>auto_increment</td><td>Primary key identitas pengguna</td></tr>
                                <tr><td><code>username</code></td><td>varchar(50)</td><td>NO</td><td>UNI</td><td>NULL</td><td>Username unik untuk login</td></tr>
                                <tr><td><code>password</code></td><td>varchar(255)</td><td>NO</td><td>-</td><td>NULL</td><td>Password hash terenkripsi (Bcrypt)</td></tr>
                                <tr><td><code>nama_lengkap</code></td><td>varchar(100)</td><td>NO</td><td>-</td><td>NULL</td><td>Nama lengkap staf atau pelanggan</td></tr>
                                <tr><td><code>role</code></td><td>enum('superadmin','admin','kasir','pelanggan')</td><td>NO</td><td>-</td><td>NULL</td><td>Hak akses hak istimewa pengguna</td></tr>
                                <tr><td><code>email</code></td><td>varchar(150)</td><td>YES</td><td>-</td><td>NULL</td><td>Alamat email aktif</td></tr>
                                <tr><td><code>telepon</code></td><td>varchar(20)</td><td>YES</td><td>-</td><td>NULL</td><td>Nomor WhatsApp / telepon</td></tr>
                                <tr><td><code>alamat</code></td><td>text</td><td>YES</td><td>-</td><td>NULL</td><td>Alamat domisili pengiriman</td></tr>
                                <tr><td><code>kota_id</code></td><td>int(11)</td><td>YES</td><td>-</td><td>NULL</td><td>Kode kota tujuan RajaOngkir</td></tr>
                                <tr><td><code>is_active</code></td><td>tinyint(1)</td><td>YES</td><td>-</td><td>1</td><td>Status akun aktif (1=Ya, 0=Tidak)</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel Produk Variasi -->
                <div>
                    <h5 class="font-bold text-xs text-slate-800 font-mono mb-1">2. Tabel `produk_variasi`</h5>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr><th>Kolom</th><th>Tipe Data</th><th>Null</th><th>Key</th><th>Default</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code>id</code></td><td>int(11)</td><td>NO</td><td>PRI</td><td>auto_increment</td><td>Primary key variasi produk</td></tr>
                                <tr><td><code>id_produk_induk</code></td><td>int(11)</td><td>YES</td><td>MUL</td><td>NULL</td><td>FK ke produk_induk.id</td></tr>
                                <tr><td><code>sku_variasi</code></td><td>varchar(50)</td><td>NO</td><td>UNI</td><td>NULL</td><td>SKU kode barcode unik varian</td></tr>
                                <tr><td><code>nama_variasi</code></td><td>varchar(100)</td><td>NO</td><td>-</td><td>NULL</td><td>Nama varian kemasan (misal: Tablet 500mg)</td></tr>
                                <tr><td><code>satuan_kecil</code></td><td>varchar(50)</td><td>NO</td><td>-</td><td>'Pcs'</td><td>Satuan eceran (Strip, Botol, Pcs)</td></tr>
                                <tr><td><code>satuan_besar</code></td><td>varchar(50)</td><td>NO</td><td>-</td><td>'Box'</td><td>Satuan grosir (Box, Karton, Dus)</td></tr>
                                <tr><td><code>rasio_konversi</code></td><td>smallint(6)</td><td>NO</td><td>-</td><td>1</td><td>Pengali UOM (1 Satuan Besar = n Satuan Kecil)</td></tr>
                                <tr><td><code>harga_jual_kecil</code></td><td>decimal(12,2)</td><td>NO</td><td>-</td><td>0.00</td><td>Harga jual satuan kecil (Kasir POS)</td></tr>
                                <tr><td><code>harga_jual_besar</code></td><td>decimal(12,2)</td><td>NO</td><td>-</td><td>0.00</td><td>Harga jual satuan besar (E-Commerce)</td></tr>
                                <tr><td><code>stok_minimum_kecil</code></td><td>int(11)</td><td>YES</td><td>-</td><td>0</td><td>Batas ROP satuan kecil</td></tr>
                                <tr><td><code>stok_minimum_besar</code></td><td>int(11)</td><td>YES</td><td>-</td><td>0</td><td>Batas ROP satuan besar</td></tr>
                                <tr><td><code>berat</code></td><td>int(11)</td><td>NO</td><td>-</td><td>100</td><td>Berat gram untuk kalkulasi ongkir kurir</td></tr>
                                <tr><td><code>tampil_di_online</code></td><td>tinyint(1)</td><td>YES</td><td>-</td><td>1</td><td>Status tampil di katalog e-commerce</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel Stok Batch -->
                <div>
                    <h5 class="font-bold text-xs text-slate-800 font-mono mb-1">3. Tabel `stok_batch`</h5>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr><th>Kolom</th><th>Tipe Data</th><th>Null</th><th>Key</th><th>Default</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code>id</code></td><td>int(11)</td><td>NO</td><td>PRI</td><td>auto_increment</td><td>Primary key lot batch</td></tr>
                                <tr><td><code>id_variasi</code></td><td>int(11)</td><td>NO</td><td>MUL</td><td>NULL</td><td>FK ke produk_variasi.id</td></tr>
                                <tr><td><code>no_batch</code></td><td>varchar(100)</td><td>NO</td><td>-</td><td>NULL</td><td>Nomor lot batch pabrik atau sub-batch (.A)</td></tr>
                                <tr><td><code>tgl_exp</code></td><td>date</td><td>NO</td><td>-</td><td>NULL</td><td>Tanggal kedaluwarsa fisik (FEFO Sorting)</td></tr>
                                <tr><td><code>stok_sisa</code></td><td>int(11)</td><td>NO</td><td>-</td><td>0</td><td>Sisa fisik batch dalam ekuivalen satuan kecil</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel Unit Serial -->
                <div>
                    <h5 class="font-bold text-xs text-slate-800 font-mono mb-1">4. Tabel `unit_serial`</h5>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr><th>Kolom</th><th>Tipe Data</th><th>Null</th><th>Key</th><th>Default</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code>id</code></td><td>int(11)</td><td>NO</td><td>PRI</td><td>auto_increment</td><td>Primary key unit SN</td></tr>
                                <tr><td><code>id_variasi</code></td><td>int(11)</td><td>NO</td><td>MUL</td><td>NULL</td><td>FK ke produk_variasi.id</td></tr>
                                <tr><td><code>serial_number</code></td><td>varchar(150)</td><td>NO</td><td>UNI</td><td>NULL</td><td>Nomor seri pabrik unik unit fisik</td></tr>
                                <tr><td><code>status</code></td><td>enum('Tersedia','Terjual','Retur/Rusak','Hilang')</td><td>NO</td><td>-</td><td>'Tersedia'</td><td>Status fisik keberadaan unit</td></tr>
                                <tr><td><code>id_penjualan</code></td><td>int(11)</td><td>YES</td><td>-</td><td>NULL</td><td>FK ke transaksi penjualan jika telah laku</td></tr>
                                <tr><td><code>tgl_habis_garansi</code></td><td>date</td><td>YES</td><td>-</td><td>NULL</td><td>Batas akhir garansi resmi pabrikan</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel Kartu Stok -->
                <div>
                    <h5 class="font-bold text-xs text-slate-800 font-mono mb-1">5. Tabel `kartu_stok`</h5>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr><th>Kolom</th><th>Tipe Data</th><th>Null</th><th>Key</th><th>Default</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code>id</code></td><td>int(11)</td><td>NO</td><td>PRI</td><td>auto_increment</td><td>Primary key log mutasi</td></tr>
                                <tr><td><code>id_variasi</code></td><td>int(11)</td><td>NO</td><td>MUL</td><td>NULL</td><td>FK ke produk_variasi.id</td></tr>
                                <tr><td><code>satuan_tipe</code></td><td>enum('besar','kecil')</td><td>NO</td><td>-</td><td>'kecil'</td><td>Buku kartu stok yang dipengaruhi</td></tr>
                                <tr><td><code>jenis_mutasi</code></td><td>enum('Masuk','Keluar','Penyesuaian')</td><td>NO</td><td>-</td><td>NULL</td><td>Arah pergerakan mutasi fisik</td></tr>
                                <tr><td><code>kanal</code></td><td>enum('POS','E-Commerce','Manual','Penerimaan','PO')</td><td>NO</td><td>-</td><td>'Manual'</td><td>Kanal omnichannel penyebab mutasi</td></tr>
                                <tr><td><code>alasan_mutasi</code></td><td>varchar(255)</td><td>NO</td><td>-</td><td>-</td><td>Alasan audit mutasi barang</td></tr>
                                <tr><td><code>qty</code></td><td>int(11)</td><td>NO</td><td>-</td><td>NULL</td><td>Kuantitas barang yang berpindah</td></tr>
                                <tr><td><code>sisa_stok</code></td><td>int(11)</td><td>NO</td><td>-</td><td>NULL</td><td>Saldo akhir fisik setelah mutasi dicatat</td></tr>
                                <tr><td><code>no_ref_dokumen</code></td><td>varchar(100)</td><td>YES</td><td>-</td><td>NULL</td><td>Nomor Invoice / Faktur / PO terkait</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- BAB 4: SEQUENCE ALUR TRANSAKSI DETAIL -->
        <section class="space-y-4 page-break">
            <h3 class="text-lg font-black text-[#0f2d5a] uppercase border-b-2 border-slate-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-md bg-[#0f2d5a] text-white flex items-center justify-center text-xs">4</span>
                Diagram Alur Transaksi &amp; Pemenuhan Fisik (Sequence Diagrams)
            </h3>

            <!-- SEQUENCE 1: KASIR POS -->
            <div class="pt-2">
                <h4 class="font-extrabold text-xs text-[#0f2d5a] uppercase mb-1">Diagram 4.1: Alur Transaksi Kasir POS (Offline) &amp; Validasi FEFO / SN</h4>
                <div class="mermaid">
sequenceDiagram
    autonumber
    actor Kasir as Kasir Toko
    participant Sys as Sistem ZenCare POS
    participant DB as Basis Data (MySQL)

    Kasir->>Sys: Scan Barcode SKU / Cari Nama Produk
    alt Produk adalah OBAT
        Sys->>DB: Query stok_batch ORDER BY tgl_exp ASC
        Sys->>Kasir: Rekomendasi Ambil Batch Terdekat (FEFO)
        alt Kasir Memilih Batch Lain (Lebih Baru)
            Sys->>DB: INSERT INTO log_anomali_fefo (status='terlewat')
        end
    else Produk adalah ALKES
        Sys->>Kasir: Minta Scan Barcode Serial Number (SN) Unit
        Kasir->>Sys: Scan Barcode SN Fisik
        Sys->>DB: Validasi status unit_serial = 'Tersedia'
    end

    Kasir->>Sys: Klik Tombol Bayar (Tunai / QRIS)
    Sys->>DB: BEGIN TRANSACTION
    Sys->>DB: INSERT INTO penjualan & detail_penjualan (status='Selesai')
    Sys->>DB: UPDATE stok_batch (-qty) ATAU UPDATE unit_serial (status='Terjual')
    Sys->>DB: UPDATE stok_toko (-qty)
    Sys->>DB: INSERT INTO kartu_stok (jenis='Keluar', kanal='POS')
    Sys->>DB: COMMIT TRANSACTION
    Sys-->>Kasir: Cetak Struk Kasir Thermal & Selesai
                </div>
            </div>

            <!-- SEQUENCE 2: E-COMMERCE & RESERVASI STOK -->
            <div class="pt-6">
                <h4 class="font-extrabold text-xs text-[#0f2d5a] uppercase mb-1">Diagram 4.2: Alur Pemesanan E-Commerce, Reservasi Stok 2 Jam, &amp; Pengiriman</h4>
                <div class="mermaid">
sequenceDiagram
    autonumber
    actor Pelanggan as Pelanggan Online
    participant Web as Toko E-Commerce
    participant Midtrans as Payment Gateway
    participant Sys as Sistem Admin Logistik
    participant DB as Basis Data (MySQL)

    Pelanggan->>Web: Checkout Keranjang (Pilih Kurir / Pick-Up)
    Web->>DB: BEGIN TRANSACTION
    Web->>DB: INSERT penjualan (status='Menunggu Pembayaran')
    Web->>DB: Kunci Alokasi Batch FEFO / SN (Stok Reserved)
    Web->>DB: COMMIT TRANSACTION
    Web->>Pelanggan: Tampilkan Midtrans Snap & Timer Cooldown 2 Jam

    alt Melewati 2 Jam Tanpa Pembayaran
        Sys->>DB: UPDATE penjualan status='Dibatalkan'
        Sys->>DB: Pulihkan Alokasi Batch / SN ke Stok Bebas
    else Pelanggan Berhasil Membayar (Settlement)
        Midtrans->>Web: Webhook HTTP Notification
        Web->>DB: UPDATE penjualan status='Diproses', paid_at=NOW()
        Sys->>Sys: Notifikasi Pesanan Baru di Layar Admin
        Sys->>Sys: Cetak Faktur Kertas A4 & Label Stiker Thermal 100x150mm
        Sys->>DB: Input Resi Kurir -> status='Dikirim' (atau 'Siap Diambil')
        Pelanggan->>Web: Lacak Resi & Konfirmasi Penerimaan Paket -> status='Selesai'
    end
                </div>
            </div>
        </section>

        <!-- BAB 5: ROLE-BASED ACCESS CONTROL -->
        <section class="space-y-4">
            <h3 class="text-lg font-black text-[#0f2d5a] uppercase border-b-2 border-slate-200 pb-2 flex items-center gap-2">
                <span class="w-6 h-6 rounded-md bg-[#0f2d5a] text-white flex items-center justify-center text-xs">5</span>
                Matriks Hak Akses Pengguna (Role-Based Access Control)
            </h3>
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Modul / Fitur Sistem</th>
                            <th class="text-center">SuperAdmin (Pemilik)</th>
                            <th class="text-center">Admin (Operasional)</th>
                            <th class="text-center">Kasir (Front Office)</th>
                            <th class="text-center">Pelanggan (Customer)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Dashboard Manajerial &amp; Grafik Penjualan</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Dashboard Operasional Harian</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Kasir POS (Penjualan Luring)</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Manajemen Pesanan E-Commerce</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-500">Cetak</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Penerimaan Logistik (Inbound)</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Konversi Satuan UOM (Bongkar Dus)</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Buka / Tutup Sesi Stok Opname (System Lock)</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Pencacahan Fisik Stok Opname (Counting)</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Kartu Stok (Satuan Besar &amp; Satuan Kecil)</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Laporan Keuangan &amp; Laba Rugi</td><td class="text-center font-bold text-emerald-600">&#10004;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td></tr>
                        <tr><td>Belanja Online &amp; Lacak Resi E-Commerce</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center text-slate-300">&#10008;</td><td class="text-center font-bold text-emerald-600">&#10004;</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- FOOTER & LEMBAR PENGESAHAN DOKUMEN -->
        <footer class="pt-10 border-t-2 border-slate-900 page-break">
            <div class="grid grid-cols-2 gap-8 text-center text-xs text-slate-700">
                <div>
                    <span class="block text-slate-500 mb-1">Disusun &amp; Diperiksa Oleh:</span>
                    <strong class="text-sm text-[#0f2d5a] block mb-16">Pengelola Sistem &amp; Logistik Farmasi</strong>
                    <span class="inline-block border-b border-slate-900 font-bold pb-0.5 min-w-[200px]">Marcell (Pengembang Sistem)</span>
                    <span class="block text-[11px] text-slate-500 mt-1">Staf Pengembang Sistem Informasi TA</span>
                </div>
                <div>
                    <span class="block text-slate-500 mb-1">Disetujui &amp; Disahkan Oleh:</span>
                    <strong class="text-sm text-[#0f2d5a] block mb-16">Pemilik Usaha (SuperAdmin)</strong>
                    <span class="inline-block border-b border-slate-900 font-bold pb-0.5 min-w-[200px]">SuperAdmin (Pemilik ZenCare)</span>
                    <span class="block text-[11px] text-slate-500 mt-1">Pimpinan Usaha ZenCare Medical</span>
                </div>
            </div>
            <div class="mt-8 text-center text-[10px] text-slate-400">
                Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS) ZenCare Medical &bull; Dicetak Pada: <?= date('d/m/Y H:i') ?> WIB
            </div>
        </footer>

    </main>

</body>
</html>

