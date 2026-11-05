# -*- coding: utf-8 -*-
"""
Script to build lain/semua_activity_diagram_zencare.html
Interactive multi-tab viewer for all 8 activity diagrams
"""

viewer_path = r'c:\xampp\htdocs\inventory_zencare\lain\semua_activity_diagram_zencare.html'

html_content = '''<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan Activity Diagram Lengkap - ZenCare Medical (Bab 3.3.4)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Times+New+Roman&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            padding: 24px;
        }

        /* Top Header Control Bar */
        .header-bar {
            max-width: 1280px;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .header-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .header-title p {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 2px;
        }

        .header-nav {
            display: flex;
            gap: 10px;
        }

        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-outline {
            background-color: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-outline:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .main-container {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Tab Navigation Bar */
        .tabs-nav {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            background: #ffffff;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }

        .tab-btn {
            padding: 10px 16px;
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .tab-btn.active {
            background: #0f172a;
            color: #ffffff;
        }

        .badge-tab {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 6px;
            background: #e2e8f0;
            color: #1e293b;
        }

        .tab-btn.active .badge-tab {
            background: #334155;
            color: #f8fafc;
        }

        /* Tab Content Panel */
        .tab-panel {
            display: none;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }

        .tab-panel.active {
            display: grid;
        }

        @media (max-width: 1024px) {
            .tab-panel {
                grid-template-columns: 1fr;
            }
        }

        /* Diagram Viewport Card */
        .diagram-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        .diagram-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 14px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 16px;
        }

        .diagram-toolbar .title h2 {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }

        .diagram-toolbar .title p {
            font-size: 11.5px;
            color: #64748b;
        }

        .diagram-actions {
            display: flex;
            gap: 6px;
        }

        .btn-action {
            padding: 6px 10px;
            font-size: 11.5px;
            font-weight: 600;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-action:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .diagram-viewport {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            overflow: auto;
            max-height: 820px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 10px;
        }

        .diagram-viewport img, .diagram-viewport svg {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Info Card */
        .info-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 16px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-card h3 {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
        }

        .info-card p {
            text-align: justify;
            text-indent: 20px;
            color: #1e293b;
        }

        .info-card ol {
            padding-left: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: #334155;
        }

        .info-card ol li {
            text-align: justify;
        }

        .box-highlight {
            font-family: Arial, Helvetica, sans-serif;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            padding: 12px;
            border-radius: 6px;
            font-size: 11.5px;
            color: #166534;
            line-height: 1.5;
        }

        .box-highlight strong {
            color: #14532d;
            display: block;
            margin-bottom: 4px;
        }

        .btn-copy {
            font-family: Arial, Helvetica, sans-serif;
            background: #0f172a;
            color: #ffffff;
            border: none;
            padding: 8px 12px;
            font-size: 11.5px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            align-self: flex-start;
            transition: background 0.2s;
        }

        .btn-copy:hover {
            background: #1e293b;
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <div class="header-bar">
        <div class="header-title">
            <h1>Kumpulan Activity Diagram Lengkap - ZenCare Medical</h1>
            <p>Subbab 3.3.4 &bull; Memetakan 8 Alur Operasional UML (Diagram A s/d H) Berbasis Sistem Riil &amp; SRS</p>
        </div>
        <div class="header-nav">
            <a href="../laporan_ta/BAB_III_ZENCARE_MEDICAL.html#sec_3_3_4" class="btn-nav btn-primary">
                &larr; Kembali ke Bab 3.3.4
            </a>
            <a href="semua_usecase_zencare.html" class="btn-nav btn-outline">
                Lihat Use Case Diagram
            </a>
            <a href="semua_workflow_zencare.html" class="btn-nav btn-outline">
                Lihat Workflow Clipart
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">

        <!-- Tab Selector -->
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('tab_a', this)">
                <span class="badge-tab">A</span>
                Autentikasi (Login)
            </button>
            <button class="tab-btn" onclick="switchTab('tab_b', this)">
                <span class="badge-tab">B</span>
                Pesanan Daring (E-Commerce)
            </button>
            <button class="tab-btn" onclick="switchTab('tab_c', this)">
                <span class="badge-tab">C</span>
                Pesanan Luring (Kasir POS)
            </button>
            <button class="tab-btn" onclick="switchTab('tab_d', this)">
                <span class="badge-tab">D</span>
                Kelola Pesanan Daring
            </button>
            <button class="tab-btn" onclick="switchTab('tab_e', this)">
                <span class="badge-tab">E</span>
                Penerimaan Barang &amp; Batch
            </button>
            <button class="tab-btn" onclick="switchTab('tab_f', this)">
                <span class="badge-tab">F</span>
                Mutasi Stok Manual
            </button>
            <button class="tab-btn" onclick="switchTab('tab_g', this)">
                <span class="badge-tab">G</span>
                Laporan Manajerial (Output)
            </button>
            <button class="tab-btn" onclick="switchTab('tab_h', this)">
                <span class="badge-tab">H</span>
                Kelola Stok Opname (Freeze Lock)
            </button>
        </div>

        <!-- ================= TAB A: AUTENTIKASI ================= -->
        <div id="tab_a" class="tab-panel active">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.10 Activity Diagram Autentikasi (Login)</h2>
                        <p>Swimlane: Pengguna Internal (Kasir/Admin/Superadmin) &bull; Pelanggan &bull; Sistem</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.10_Activity_Autentikasi.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.10_Activity_Autentikasi.svg" alt="Activity Diagram Autentikasi">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.A)</h3>
                <p>
                    Diagram aktivitas Autentikasi memetakan mekanisme validasi identitas dan penyaluran hak akses pengguna berdasarkan prinsip <i>Role-Based Access Control</i> (RBAC). Alur masuk dibedakan ke dalam dua gerbang antarmuka: pengguna internal (Kasir, Admin Toko, dan Superadmin) mengakses melalui formulir login <i>back-office</i>, sedangkan Pelanggan mengakses melalui antarmuka web <i>e-commerce</i>.
                </p>
                <p>
                    Bagi pelanggan baru, sistem menyediakan alur registrasi mandiri untuk menyimpan profil pembeli sebelum melakukan login. Saat kredensial (nama pengguna dan kata sandi) dikirimkan, sistem melakukan verifikasi kecocokan hash password (<i>password_verify</i>) ke pangkalan data. Jika tidak valid, sistem memunculkan pesan peringatan dan meminta pengguna mengisi ulang. Sebaliknya, apabila data terverifikasi sah, sistem mengevaluasi peran pengguna dan mengarahkan ke antarmuka yang sesuai: Kasir dialihkan langsung ke terminal transaksi POS, Admin ke modul operasional logistik, Superadmin ke kendali manajerial penuh, dan Pelanggan ke halaman beranda belanja dan profil.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai implementasi pada file <code>auth/login.php</code> dan <code>auth/login_pelanggan.php</code>. Menyempurnakan draft draw.io terdahulu dengan memasukkan peran Kasir secara eksplisit sehingga konsisten 100% dengan Use Case 4 Aktor.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

        <!-- ================= TAB B: PESANAN DARING ================= -->
        <div id="tab_b" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.11 Activity Diagram Pesanan Daring (E-Commerce)</h2>
                        <p>Swimlane: Pelanggan &bull; Sistem E-Commerce &bull; Payment Gateway (Midtrans)</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.11_Activity_Pesanan_Daring.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.11_Activity_Pesanan_Daring.svg" alt="Activity Diagram Pesanan Daring">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.B)</h3>
                <p>
                    Diagram aktivitas Pesanan Daring memetakan interaksi transaksi belanja daring grosir antara Pelanggan, Sistem E-Commerce, dan gerbang pembayaran pihak ketiga (<i>Midtrans</i>). Alur diawali ketika pelanggan menekan tombol <i>checkout</i> dari keranjang belanja. Sistem memuat ringkasan produk dalam satuan kemasan besar (Box/Dus utuh bersegel resmi) dan meminta pelanggan memilih metode pemenuhan pesanan.
                </p>
                <p>
                    Jika pelanggan memilih pengiriman ekspedisi, sistem secara otomatis mengkalkulasi tarif ongkos kirim melalui integrasi API kurir (RajaOngkir). Sebaliknya, jika memilih Ambil di Toko (<i>Pick-up in Store</i>), biaya ongkos kirim ditetapkan Rp 0. Setelah total tagihan final ditampilkan, sistem menerbitkan token transaksi <i>Midtrans Snap</i> dan memberikan jendela pelunasan selama 2 jam.
                </p>
                <p>
                    Ketika pembayaran tervalidasi sukses (<i>settlement</i>), sistem di balik layar mengeksekusi algoritma alokasi logistik otomatis: untuk komoditas Obat dialokasikan berdasarkan batch terdekat kedaluwarsa (FEFO), sedangkan untuk Alat Kesehatan dialokasikan unit berseri unik (SN). Sistem seketika mengunci fisik barang dengan menandai status stok sebagai <i>Reserved</i> guna mencegah penjualan ganda (<i>overselling</i>) di meja kasir fisik, mengubah status pesanan menjadi "Diproses", dan menerbitkan faktur penjualan digital (<i>invoice</i>).
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai dengan alur pemrosesan <code>checkout.php</code>, <code>api/midtrans_notification.php</code>, dan penandaan kuantitas status <i>Reserved</i> di database inventori.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

        <!-- ================= TAB C: PESANAN LURING (KASIR POS) ================= -->
        <div id="tab_c" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.12 Activity Diagram Pesanan Luring (Transaksi Kasir POS)</h2>
                        <p>Swimlane: Pelanggan Toko &bull; Kasir &bull; Sistem POS</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.12_Activity_Pesanan_Kasir_POS.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.12_Activity_Pesanan_Kasir_POS.svg" alt="Activity Diagram Transaksi Kasir POS">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.C)</h3>
                <p>
                    Diagram aktivitas Transaksi Kasir (POS) memetakan alur penjualan langsung tatap muka (<i>walk-in customer</i>) di gerai fisik ZenCare Medical. Kasir membuka terminal POS tunggal, lalu memindai kode batang (<i>barcode</i>) kemasan atau menginput SKU produk. Sistem merespons dengan menampilkan informasi detail produk, fleksibilitas multi-satuan (<i>Dual-UOM</i>: Strip/Botol eceran vs Box grosir), kuantitas fisik tersedia, serta rekomendasi nomor batch obat terdekat sesuai algoritma FEFO (atau nomor seri alat kesehatan).
                </p>
                <p>
                    Kasir memeriksa fisik barang yang diambil di etalase. Apabila fisik yang terambil berbeda dari rekomendasi sistem (misalnya akibat penataan barang di rak oleh pembeli sebelumnya), kasir memilih nomor batch fisik riil tersebut, dan sistem secara otomatis membukukan deviasi tersebut ke dalam <b>Log Anomali FEFO</b> tanpa memblokir transaksi penjualan agar layanan tetap cepat. Jika stok eceran habis di rak, kasir dapat menggunakan fungsi instan <i>Bongkar Box</i> untuk memecah stok grosir menjadi eceran.
                </p>
                <p>
                    Setelah seluruh barang terekam, kasir memproses pembayaran tunai (dengan validasi nominal uang bayar dan kalkulasi uang kembalian) atau non-tunai (QRIS). Saat transaksi diselesaikan, sistem secara otomatis memotong stok fisik secara permanen sesuai identitas Batch/SN terpilih, mencatat histori mutasi keluar ke Kartu Stok, merekam transaksi ke Riwayat Kasir Hari Ini, serta mencetak struk belanja fisik berformat thermal untuk diserahkan kepada pelanggan.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai dengan logika <code>pos/pos.php</code>, pencatatan otomatis deviasi pada tabel <code>log_anomali_fefo</code>, dan integrasi modal riwayat kasir harian.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

        <!-- ================= TAB D: KELOLA PESANAN DARING ================= -->
        <div id="tab_d" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.13 Activity Diagram Kelola Pesanan Daring &amp; Pembatalan</h2>
                        <p>Swimlane: Admin (Staff Logistik) &bull; Sistem Back-Office</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg" alt="Activity Diagram Kelola Pesanan Daring">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.D)</h3>
                <p>
                    Diagram aktivitas ini memetakan tata kelola pemrosesan pesanan <i>e-commerce</i> oleh Admin Toko. Alur diawali ketika staf membuka menu Transaksi tab "E-Commerce" untuk melihat antrean pesanan berstatus "Diproses". Sistem menyajikan rincian barang beserta instruksi identitas logistik (Nomor Batch FEFO atau Serial Number) yang telah dipesan.
                </p>
                <p>
                    Staf mencetak lembar instruksi pengambilan barang (<i>Picking List</i>) dan mengambil fisik barang dari rak gudang sesuai identitas medis tersebut. Pada tahap evaluasi, terdapat dua percabangan utama:
                </p>
                <ol>
                    <li><b>Cabang Pembatalan (Batalkan Pesanan):</b> Apabila pesanan Ambil di Toko tidak kunjung diambil melampaui batas toleransi atau terjadi kendala darurat, Admin memilih opsi "Batalkan Pesanan". Sistem secara otomatis melepas kuncian persediaan status <i>Reserved</i> kembali menjadi stok aktif toko, memperbarui status menjadi "Dibatalkan", dan mengirimkan notifikasi.</li>
                    <li><b>Cabang Pemenuhan Standar:</b> Untuk pengiriman kurir, Admin mengemas paket, menginput nomor resi, mencetak label ekspedisi, dan mengubah status menjadi "Dikirim" (di mana sistem memotong stok <i>Reserved</i> menjadi mutasi keluar penjualan permanen pada Kartu Stok). Untuk metode <i>Pick-up in Store</i>, Admin menyiapkan paket di rak khusus dan mengubah status menjadi "Siap Diambil". Saat pembeli datang menunjukkan kode unik, Admin memverifikasi kode, menyerahkan barang, dan mengubah status menjadi "Selesai" (mencatat mutasi keluar pada Kartu Stok).</li>
                </ol>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai modul <code>admin/pesanan.php</code> dan <code>admin/cetak_label_pengiriman.php</code>, mengamankan stok melalui siklus pelepasan <i>Reserved</i> yang transparan.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

        <!-- ================= TAB E: PENERIMAAN BARANG ================= -->
        <div id="tab_e" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.14 Activity Diagram Penerimaan Barang &amp; Validasi Logistik</h2>
                        <p>Swimlane: Admin (Staff Logistik) &bull; Sistem Back-Office</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.14_Activity_Penerimaan_Barang.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.14_Activity_Penerimaan_Barang.svg" alt="Activity Diagram Penerimaan Barang">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.E)</h3>
                <p>
                    Diagram aktivitas ini memetakan mekanisme penerimaan pasokan barang baru dari pemasok rekanan ke dalam sistem inventori dengan standar keamanan regulasi medis yang ketat. Proses dimulai saat Admin membuka menu Penerimaan Barang dan menginput parameter dokumen dasar (Nomor Faktur/Surat Jalan, Tanggal Penerimaan, Nama Supplier, Kuantitas Diterima, dan Harga Beli).
                </p>
                <p>
                    Selanjutnya, sistem membedakan form input berdasarkan kategori komoditas: untuk kategori "Obat", staf diwajibkan menginput Nomor Batch dan Tanggal Kedaluwarsa (<i>Expiration Date</i>); sedangkan untuk "Alat Kesehatan", staf diwajibkan menginput Nomor Seri Unik (<i>Serial Number</i> / SN). Saat tombol Simpan ditekan, sistem bertindak sebagai <i>gatekeeper</i> yang memvalidasi format data dan memastikan tanggal kedaluwarsa tidak lampau. Jika data tidak lengkap atau tanggal tidak valid, sistem menolak penyimpanan dan menginstruksikan staf untuk memperbaiki form.
                </p>
                <p>
                    Sebaliknya, jika validasi lolos, sistem merekam data identitas ke tabel logistik, menambahkan saldo kuantitas ke stok utama gudang, mencatat mutasi masuk pada buku besar Kartu Stok, serta menerbitkan perintah cetak stiker label <i>barcode</i> logistik. Staf mencetak stiker tersebut dan menempelkannya pada fisik kemasan dus sebelum disimpan di rak persediaan.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai dengan formulir penerimaan barang pada <code>inventori/penerimaan.php</code> dan modul cetak label identitas logistik medis.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

        <!-- ================= TAB F: MUTASI STOK MANUAL ================= -->
        <div id="tab_f" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.15 Activity Diagram Mutasi Stok Manual</h2>
                        <p>Swimlane: Admin (Staff Logistik) &bull; Sistem Back-Office</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.15_Activity_Mutasi_Stok.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.15_Activity_Mutasi_Stok.svg" alt="Activity Diagram Mutasi Stok Manual">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.F)</h3>
                <p>
                    Diagram aktivitas ini memetakan alur penyesuaian kuantitas fisik persediaan secara manual yang dieksekusi oleh staf operasional untuk mengakomodasi pencatatan kasus khusus di luar alur transaksi penjualan reguler, seperti barang rusak di rak pajangan, obat kedaluwarsa di etalase, selisih fisik minor, atau klaim retur barang cacat ke pabrik.
                </p>
                <p>
                    Alur dimulai ketika Admin membuka menu Mutasi Stok, memilih produk target, serta menentukan Jenis Mutasi (Penambahan atau Pengurangan) beserta Alasan Penyesuaian medis yang relevan. Sistem mewajibkan pemilihan target identitas logistik spesifik: nomor batch bagi komoditas obat atau nomor serial number bagi alat kesehatan. Setelah staf menginput kuantitas, nomor dokumen referensi, dan catatan keterangan, tombol proses ditekan.
                </p>
                <p>
                    Sistem memvalidasi kecukupan saldo fisik dari batch/SN yang dipilih (khusus mutasi pengurangan). Apabila stok tidak mencukupi, sistem menampilkan peringatan guna mencegah terjadinya stok minus. Apabila validasi berhasil, sistem memperbarui saldo fisik permanen di basis data, membukukan mutasi penyesuaian ke dalam Kartu Stok secara otomatis beserta alasan medis yang dicantumkan, dan menampilkan konfirmasi keberhasilan pembaruan data.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai dengan implementasi modul <code>inventori/mutasi.php</code> yang mengunci kuantitas agar integritas kartu stok tetap terjaga tanpa deviasi liar.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

        <!-- ================= TAB G: OUTPUT LAPORAN MANAJERIAL ================= -->
        <div id="tab_g" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.16 Activity Diagram Output Laporan Manajerial &amp; Logistik</h2>
                        <p>Swimlane: Superadmin (Pemilik) &bull; Sistem Back-Office</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.16_Activity_Laporan_Manajerial.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.16_Activity_Laporan_Manajerial.svg" alt="Activity Diagram Output Laporan Manajerial">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.G)</h3>
                <p>
                    Diagram aktivitas ini memetakan alur penarikan rekapitulasi data hasil keluaran (<i>output</i>) manajerial yang hak aksesnya dibatasi secara eksklusif hanya untuk pengguna berwewenang <b>Superadmin (Pemilik)</b>. Alur diawali ketika pengguna mengakses menu Laporan. Sistem secara otomatis bertindak sebagai <i>RBAC Gatekeeper</i> untuk memvalidasi sesi otoritas; apabila pengguna terdeteksi bukan Superadmin, akses seketika diblokir dan dialihkan ke dashboard utama.
                </p>
                <p>
                    Setelah verifikasi berhasil, Superadmin memilih salah satu dari 4 modul laporan kuantitas fisik medis yang murni berfokus pada volume pergerakan fisik tanpa variabel finansial omzet:
                </p>
                <ol>
                    <li><b>Laporan Penjualan Omnichannel:</b> Membandingkan volume kuantitas fisik penjualan kasir POS vs transaksi daring e-commerce berdasarkan rentang tanggal.</li>
                    <li><b>Laporan Persediaan &amp; Kartu Stok:</b> Menampilkan rekapitulasi kuantitas fisik per produk dan pergerakan keluar-masuk barang secara komprehensif.</li>
                    <li><b>Laporan Logistik Medis &amp; Audit Deviasi FEFO:</b> Memantau daftar obat mendekati masa kedaluwarsa, rekapitulasi riwayat nomor seri alkes, serta audit log deviasi pengambilan anomali FEFO oleh kasir.</li>
                    <li><b>Laporan Riwayat Penerimaan, Mutasi, dan Konversi UOM:</b> Menampilkan histori pasokan barang dari pemasok dan penyesuaian stok manual.</li>
                </ol>
                <p>
                    Setelah parameter filter ditentukan dan diproses, sistem menyajikan data agregasi dalam format tabular. Superadmin dapat meninjau data di layar atau menekan tombol Cetak untuk menerbitkan dokumen resmi PDF bertandatangan sebagai arsip manajerial.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai dengan modul laporan manajerial <code>laporan/penjualan.php</code>, <code>laporan/kartu_stok.php</code>, dan <code>laporan/logistik.php</code> dengan filter khusus <code>role === 'superadmin'</code>.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

        <!-- ================= TAB H: KELOLA STOK OPNAME ================= -->
        <div id="tab_h" class="tab-panel">
            <div class="diagram-card">
                <div class="diagram-toolbar">
                    <div class="title">
                        <h2>Gambar 3.17 Activity Diagram Kelola Stok Opname (Audit Fisik &amp; Freeze Lock)</h2>
                        <p>Swimlane: Superadmin (Pemilik) &bull; Admin (Staff Logistik) &bull; Sistem Back-Office</p>
                    </div>
                    <div class="diagram-actions">
                        <a href="Gambar_3.17_Activity_Stok_Opname.svg" download class="btn-action">Unduh SVG</a>
                    </div>
                </div>
                <div class="diagram-viewport">
                    <img src="Gambar_3.17_Activity_Stok_Opname.svg" alt="Activity Diagram Kelola Stok Opname">
                </div>
            </div>
            <div class="info-card">
                <h3>Uraian Naratif Akademik (Subbab 3.3.4.H - DIAGRAM TAMBAHAN BARU)</h3>
                <p>
                    Diagram aktivitas ini memetakan siklus audit fisik berkala (<i>Stock Opname</i>) yang dirancang untuk menjamin kecocokan 100% antara saldo buku sistem dengan fisik barang riil di rak etalase dan gudang. Alur ini memiliki kebaruan tinggi (*novelty*) melalui mekanisme kolaborasi ketat antara Superadmin dan Admin Toko.
                </p>
                <p>
                    Proses dimulai saat Superadmin membuka menu Stok Opname, menetapkan jadwal sesi audit, dan menekan tombol <b>"Buka Sesi Stok Opname"</b>. Sistem seketika menginisiasi sesi aktif dan memicu mekanisme <b>System Freeze Lock</b>, yakni penguncian global yang menangguhkan seluruh transaksi kasir POS dan fitur pemesanan daring <i>e-commerce</i> guna mencegah terjadinya pergeseran data stok selama proses penghitungan berlangsung.
                </p>
                <p>
                    Admin Toko kemudian mengunduh dan mencetak Lembar Hitung Buta (<i>Blind Count Sheet</i>) yang sengaja tidak menampilkan angka saldo buku sistem guna memastikan objektivitas penghitungan. Staf melakukan penyisiran dan penghitungan fisik langsung di setiap rak pajangan dan gudang, menginput hasil cacahan ke lembar kerja sistem, lalu menyimpannya. Sistem mengkalkulasi secara otomatis deviasi selisih (<i>variance</i>: Sesuai, Selisih Kurang, atau Selisih Lebih).
                </p>
                <p>
                    Superadmin menganalisis tabel rekonsiliasi varian, mencantumkan catatan justifikasi audit, lalu menekan tombol <b>"Otorisasi Rekonsiliasi &amp; Tutup Sesi"</b>. Sistem mengeksekusi penyesuaian saldo persediaan secara otomatis di basis data mengikuti hasil cacah riil, membukukan jurnal selisih ke Kartu Stok, melepas kembali status <i>System Freeze Lock</i> (terminal POS dan web e-commerce kembali normal), serta menerbitkan dokumen resmi <b>Berita Acara Stok Opname</b> bertandatangan untuk diarsipkan.
                </p>
                <div class="box-highlight">
                    <strong>Penyelarasan Kode Sistem &amp; SRS:</strong>
                    Sesuai dengan arsitektur <code>inventori/stok_opname.php</code> (memeriksa <code>requireRole(['superadmin', 'admin'])</code>), penerapan <i>System Freeze Lock</i>, dan pencetakan Berita Acara bertandatangan resmi. Menjadi pelengkap sempurna dari Use Case 3.3.3 Opsi B.
                </div>
                <button class="btn-copy" onclick="copyCardText(this)">Salin Teks Narasi</button>
            </div>
        </div>

    </div>

    <script>
        function switchTab(tabId, btn) {
            // Hide all tab panels
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            // Show target panel
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }

        function copyCardText(btn) {
            const card = btn.closest('.info-card');
            const paragraphs = card.querySelectorAll('p, ol');
            let textToCopy = '';
            paragraphs.forEach(el => {
                textToCopy += el.innerText + '\\n\\n';
            });
            navigator.clipboard.writeText(textToCopy).then(() => {
                const orig = btn.innerText;
                btn.innerText = 'Teks Disalin!';
                btn.style.background = '#16a34a';
                setTimeout(() => {
                    btn.innerText = orig;
                    btn.style.background = '#0f172a';
                }, 2000);
            });
        }
    </script>
</body>

</html>
'''

with open(viewer_path, 'w', encoding='utf-8') as f:
    f.write(html_content)

print(f"Created {viewer_path} ({len(html_content)} bytes)")

