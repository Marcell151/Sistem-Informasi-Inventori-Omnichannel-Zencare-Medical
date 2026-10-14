# 📋 PANDUAN LENGKAP DEMONSTRASI & UJI SISTEM UNTUK BIMBINGAN / SIDANG DOSEN
**Sistem Informasi Inventori Omnichannel – ZenCare Medical**
*Disusun berdasarkan arahan bimbingan dosen tanggal 12 Oktober 2026*

---

## 🚀 Ringkasan 7 Poin Utama Penyesuaian

| No | Poin Masukan Dosen | Solusi Sistem yang Diimplementasikan | Lokasi Halaman |
|:---|:---|:---|:---|
| 1 | **Validasi Expired Date Minimal 60 Hari & Label Stiker** | Input tanggal kadaluwarsa dibatasi minimal `+60 hari` (2 bulan). Ada tombol cetak stiker barcode instan di penerimaan barang. Akses staf toko ke Lap. Logistik Medis dibuka (bebas 403 Forbidden). | `inventori/tambah_stok.php`, `laporan/logistik_medis.php` |
| 2 | **Kolom "Sisa Stok Kardus" & Filter Kartu Stok** | Kolom `sisa_dus_akhir` ditambahkan di `histori_konversi` (misal: "📦 4 Dus Tersisa"). Kartu stok dilengkapi filter khusus `Buka Kardus (Konversi UOM)` & ringkasan audit. | `inventori/histori_konversi.php`, `laporan/kartu_stok.php` |
| 3 | **Penanda Visual FEFO di Packing Pesanan Online** | Admin pesanan e-commerce kini menampilkan rekomendasi batch terdekat (`[Exp: DD/MM/YYYY / Sisa X Hari — ⭐ FEFO Terdekat]`) pada detail order dan modal checklist packing. | `admin/pesanan.php` |
| 4 | **Sinkronisasi Stok Keranjang Real-time & Redirect Login** | Keranjang belanja otomatis memvalidasi stok kardus riil via `api/cek_stok_keranjang.php`. Jika kasir fisik membongkar kardus, quantity di keranjang online otomatis berkurang dengan notifikasi jelas. Login pelanggan dari checkout otomatis kembali ke checkout (`?redirect=checkout`). | `login_customer.php`, `ecommerce/footer.php`, `zencare_checkout.php` |
| 5 | **Midtrans Dynamic QRIS di POS Kasir** | Menggantikan dummy statis. Kasir memilih QRIS -> muncul pop-up QRIS Midtrans dengan nominal rupiah presisi tagihan. Setelah pelanggan scan & bayar, transaksi POS otomatis selesai & cetak nota. | `pos/pos.php`, `api/pos_snap_token.php` |
| 6 | **Barcode Scanner Kamera / Webcam di POS** | Tombol `📷 Scan Kamera` di kasir mengaktifkan live webcam via `html5-qrcode`. Barcode / QR otomatis terbaca, berbunyi bip, dan masuk ke keranjang kasir tanpa perlu install aplikasi HP eksternal. | `pos/pos.php`, `assets/js/html5-qrcode.min.js` |
| 7 | **Skenario Pengujian "Tragedi 5 Kardus"** | Disediakan modul setup 1-klik `scratch/setup_tragedi_5_kardus.php` untuk mendemonstrasikan kasus 5 kardus (4 dibeli online, 1 dibongkar kasir untuk eceran -> 0 kardus tersisa, stok konsisten 100%). | `scratch/setup_tragedi_5_kardus.php` |

---

## 🎬 Skenario Demonstrasi Live: "Tragedi 5 Kardus"

Skenario ini dirancang khusus untuk menjawab pertanyaan dosen saat bimbingan:
> *"Bagaimana sistem menangani jika ada 5 kardus di gudang, pembeli online ingin beli 4 kardus, tapi di saat bersamaan kasir fisik merobek 1 kardus untuk melayani pasien eceran?"*

### Langkah-langkah Demonstrasi:

### Langkah 1: Persiapan / Reset Awal
1. Buka browser ke: `http://localhost/inventory_zencare/scratch/setup_tragedi_5_kardus.php`
2. Klik tombol biru: **"🔄 Reset Stok ke 5 Kardus Utuh"**.
3. **Hasil:** Stok produk uji (`SIM-5BOX - Obat Uji Skenario Dosen`) ter-set tepat **5 Karton (50 Box eceran)**.
   - Batch A (FEFO 90 hari): 20 Box (2 Karton)
   - Batch B (FEFO 180 hari): 30 Box (3 Karton)
   - Eceran sobekan: 0.

### Langkah 2: Simulasi Pembeli Online
1. Buka tab baru ke Toko Online: `http://localhost/inventory_zencare/ecommerce/index.php`
2. Cari produk `Obat Uji Skenario Dosen`.
3. Masukkan **4 Karton** ke dalam keranjang belanja.
4. Buka keranjang belanja atau halaman checkout: `http://localhost/inventory_zencare/zencare_checkout.php`.
5. *(Jangan klik bayar dulu; biarkan 4 kardus berada di keranjang online).*

### Langkah 3: Simulasi Kasir Fisik (Merobek 1 Kardus untuk Eceran)
1. Buka tab baru ke Terminal POS: `http://localhost/inventory_zencare/pos/pos.php`
2. Pasien fisik datang ingin membeli **2 Box eceran**.
3. Di POS, pilih produk `Obat Uji Skenario Dosen` dengan satuan **Box (Eceran)**.
4. Karena stok eceran kosong, sistem POS otomatis memunculkan pop-up konfirmasi darurat:
   > *"Stok Eceran Kosong! Apakah Anda ingin membuka 1 Dus untuk dijual eceran sekarang?"*
5. Klik **"Ya, Robek 1 Dus"**.
6. Sistem memotong 1 kardus menjadi 10 box eceran (Batch `BATCH-DOSEN-A.1`), lalu menjual 2 box ke pasien.
7. Pilih metode bayar **Tunai** atau **QRIS Midtrans**, lalu klik **Selesaikan Transaksi**.

### Langkah 4: Pembuktian di Audit Histori Konversi & Kartu Stok
1. Buka menu Histori Konversi: `http://localhost/inventory_zencare/inventori/histori_konversi.php`
   - Terlihat catatan: `1 Karton dipecah menjadi 10 Box`.
   - Kolom **Sisa Dus Gudang** menunjukkan: `📦 4 Karton Tersisa`.
2. Buka menu Kartu Stok: `http://localhost/inventory_zencare/laporan/kartu_stok.php`
   - Pilih filter `Buka Kardus (Konversi UOM)`.
   - Terlihat audit mutasi perpindahan dari satuan Karton ke Box secara transparan.

### Langkah 5: Pembuktian di Sisi E-Commerce (Sinkronisasi Live)
1. Kembali ke tab Checkout E-Commerce pelanggan online tadi (`zencare_checkout.php`) atau refresh halaman:
2. Sistem secara otomatis menjalankan validasi real-time via `api/cek_stok_keranjang.php`:
   - Keranjang mendeteksi stok gudang yang tersisa sekarang adalah **4 Karton**.
   - Karena pelanggan meminta 4 karton dan sisa tepat 4 karton, pesanan tetap valid dan dapat langsung dibayar.
   - *(Jika pelanggan sebelumnya meminta 5 karton, keranjang otomatis menurunkan quantity menjadi 4 karton dengan pesan peringatan: "⚠️ Penyesuaian Stok Terkini: Stok fisik tersisa 4 Karton karena ada transaksi toko fisik.").

### Langkah 6: Selesaikan Pesanan Online & Pembuktian 0 Kardus
1. Pelanggan online menyelesaikan pembayaran (Midtrans Sandbox).
2. Buka menu Admin Pesanan: `http://localhost/inventory_zencare/admin/pesanan.php`
3. Staf gudang melihat pesanan 4 kardus tersebut.
4. Pada modal packing, sistem secara cerdas menampilkan **Rekomendasi FEFO**:
   - 1 Karton diambil dari sisa `BATCH-DOSEN-A` (FEFO terdekat).
   - 3 Karton diambil dari `BATCH-DOSEN-B`.
5. Cek stok akhir di gudang:
   - **Karton Utuh = 0 Karton** (1 dirobek kasir + 4 dibeli online = tepat 5 kardus!).
   - **Eceran Toko = 8 Box** (10 hasil robek - 2 terjual di kasir).
   - **Totalitas inventori 100% konsisten tanpa selisih 1 unit pun!**

---

## 📷 Cara Menguji Fitur Barcode Scanner Kamera di POS Kasir
1. Buka Terminal Kasir: `http://localhost/inventory_zencare/pos/pos.php`
2. Di samping input barcode, klik tombol biru **"📷 Scan Kamera"**.
3. Browser akan meminta izin kamera (klik *Allow*).
4. Kamera webcam laptop aktif dan menampilkan video live scanner.
5. Dekatkan barcode produk atau barcode kartu batch/SN ke depan kamera.
6. Bunyi **Bip Audio** akan terdengar, dan produk langsung otomatis masuk ke keranjang kasir.

---

## 💳 Cara Menguji Midtrans Dynamic QRIS di POS Kasir
1. Masukkan produk ke keranjang POS kasir.
2. Klik tombol **Proses Pembayaran**.
3. Pada dropdown *Metode Pembayaran*, pilih **QRIS / E-Wallet**.
4. Muncul panel *Dynamic QRIS Midtrans*.
5. Klik tombol **"⚡ Buka QRIS Midtrans (Pop-up Kasir)"**.
6. Pop-up Snap Midtrans langsung terbuka menampilkan kode QRIS dengan total nominal rupiah transaksi yang presisi.
7. Setelah disimulasikan sukses atau di-scan, sistem otomatis menyelesaikan transaksi dan membuka cetak struk nota.

---

## 🩺 Logika Inventori Alkes, Keterlacakan Serial Number (SN) & Batasan Ruang Lingkup Sistem (Pertahanan Sidang TA)

Bagian ini dirancang khusus untuk menjadi **naskah pertahanan akademis** Anda saat menghadapi pertanyaan dosen penguji (seperti Pak Soetam) mengenai perbedaan penanganan Alat Kesehatan (Alkes) vs Obat, serta **cara membatasi ruang lingkup (scope management)** agar penguji tidak melebar ke pembahasan modul retur atau garansi.

### 1. Komparasi Arsitektur: Jalur Obat (FEFO) vs Jalur Alkes (Serial Number)

| Parameter Inventori | Jalur Obat (Farmasi) | Jalur Alat Kesehatan (Alkes) |
| :--- | :--- | :--- |
| **Sifat Barang** | Kimiawi / Konsumsi / Habis Pakai | Perangkat Keras / Aset Elektronik Medis |
| **Identitas Utama** | **Nomor Batch (Lot Number)**: 1 nomor batch mewakili ribuan tablet/strip | **Serial Number (SN)**: 1 nomor seri unik mewakili tepat 1 unit fisik (1 to 1) |
| **Masa Kedaluwarsa** | **Ada (Wajib)**: Berkurang seiring waktu biologis/kimiawi | **Tidak Ada**: Tidak basi di gudang (tidak ada mutasi kedaluwarsa) |
| **Metode Pengeluaran** | **FEFO (First Expired, First Out)** | **FIFO / Scanned SN**: Bebas ambil unit mana saja di rak |
| **Konversi Satuan (UOM)** | **Multi-Level**: Kardus besar dipecah jadi strip/sub-batch (`BCH.1`) | **Rasio Flat 1:1**: Satuan Besar = Satuan Kecil = Unit / Pcs |
| **Audit Penjualan** | Tercatat via nama obat & nomor batch di invoice | Tercatat via **Serial Number unik** yang menempel di invoice |

---

### 2. Batasan Ruang Lingkup: Mengapa Tidak Dibuatkan Modul Khusus Retur/Garansi?

Jika dosen penguji bertanya:
> *"Kenapa sistem ini tidak ada menu khusus form klaim retur atau modul manajemen garansi alkes?"*

**Jawaban Sidang Anda (Kunci Sukses):**
> *"Sesuai dengan rumusan dan batasan masalah di **BAB I**, penelitian Tugas Akhir ini berfokus pada **Sistem Informasi Manajemen Inventori dan Sinkronisasi Stok Ritel Omnichannel (FEFO & UOM Conversion)**, bukan pada Reverse Logistics atau RMA (Return Merchandise Authorization).
> 
> Di lapangan, terminal **POS Kasir sengaja difokuskan 100% untuk kecepatan transaksi pelayanan kasir**, tanpa dibebani birokrasi pengecekan retur yang rumit. 
> Namun, sistem ZenCare tetap menjamin **Strict Traceability (Keterlacakan Tertutup)**: data keterlacakan setiap unit Alkes sudah terintegrasi secara rapi di dalam **Laporan Logistik Medis (Tab Jejak Serial Number)**. Admin maupun Superadmin dapat langsung melacak riwayat unit fisik, nomor invoice, dan tanggal penjualannya cukup dengan mengetik nomor seri di kolom pencarian."*

---

### 3. Mengapa Serial Number (SN) Dicatat Sejak Penerimaan? (Strict Traceability)

Jika dosen penguji bertanya:
> *"Kenapa SN Alkes dicatat satu per satu saat barang datang? Bukankah merusak segel kardus pabrik dan membebani staf gudang?"*

**Jawaban Sidang Anda:**
> *"Untuk barang bernilai tinggi (high-value asset) seperti Alkes, ZenCare menerapkan standar **Strict Traceability (Keterlacakan Ketat)** sesuai regulasi CDAKB (Cara Distribusi Alat Kesehatan yang Baik). Kardus pengiriman luar (shipping master carton) memang dibuka oleh staf gudang untuk keperluan Quality Control (QC fisik), tetapi **boks kemasan satuan alat di dalamnya tetap tersegel utuh dari pabrik**. Staf cukup memindai stiker barcode SN yang sudah tertera di luar boks satuan.
> Hal ini wajib dilakukan agar sistem memiliki **Closed-Loop Inventory (Sistem Inventori Tertutup)**: sistem mengetahui persis unit mana saja yang ada di dalam gedung. Jika ada barang hilang atau tertukar, sistem tahu nomor seri persisnya. Stiker barcode yang dicetak staf gudang menggunakan label netral **'Unit Original'**."*

---

### 4. Mengapa Alkes Tidak Mengenal Konversi UOM (Rasio 1:1)?

Jika dosen penguji bertanya:
> *"Kenapa obat ada fitur konversi kardus ke eceran, tapi Alkes tidak bisa dikonversi?"*

**Jawaban Sidang Anda:**
> *"Alat kesehatan tidak memiliki sifat 'eceran butiran' seperti obat, Pak. Kita tidak bisa merobek boks tensimeter untuk menjual selangnya saja atau layarnya saja. Satu tensimeter di dalam boks adalah satu kesatuan utuh dengan 1 Serial Number pabrik. Baik dibeli 1 unit oleh pasien di apotek maupun dibeli 10 unit sekaligus oleh klinik, satuannya tetap 'Unit'. Jika klinik membeli 10 unit, staf cukup men-scan 10 Serial Number unik dari 10 boks tersebut. Menjaga rasio 1:1 memastikan integritas database tetap 100% konsisten."*

---

### 5. Cara Penelusuran Serial Number di Sistem ZenCare

Jika dosen penguji meminta dibuktikan keterlacakannya secara live:
1. Masuk ke menu **Laporan ➡️ Lap. Logistik Medis** (`laporan/logistik_medis.php`).
2. Klik tab **"Jejak Serial Number"** (`?tab=sn`).
3. Pada form filter, ketik nomor seri di kolom **"Cari No. Seri (SN)..."** (misal: `OMR-HEM7120-A001`), lalu klik **Filter**.
4. Sistem langsung menampilkan rekam jejak unit tersebut:
   - **Status Unit**: *Tersedia / Terjual / Retur / Karantina*
   - **Transaksi Terkait**: No. Invoice, tipe transaksi (POS/E-Commerce), dan tanggal penjualan (`Terjual: DD/MM/YYYY`).
   - Terdapat info masa garansi toko standar (30 hari) secara ringkas tanpa mengganggu tampilan utama laporan.
5. Jika unit perlu diberi label barcode baru, klik tombol **Cetak** untuk membuka stiker thermal resmi bertuliskan **Unit Original**.

---

### 6. Skenario Alkes Rusak / Cacat Fisik Sejak Awal & Alur Kartu Stok (Audit Trail)

Jika dosen penguji bertanya:
> *"Bagaimana jika ada alat kesehatan yang cacat pabrik atau rusak di gudang sebelum sempat terjual? Bagaimana pencatatannya di kartu stok, dan apa yang terjadi jika kasir tidak sengaja men-scan alat yang rusak itu?"*

**Jawaban Sidang Anda:**
1. **Mengapa Tidak Otomatis Hilang dari Sistem?**
   - Obat kadaluarsa bisa dideteksi otomatis oleh sistem karena memiliki variabel tanggal kalender pasti (`tgl_exp < hari ini`).
   - Sedangkan **kerusakan fisik Alkes (layar pecah, mesin error, cacat pabrik)** tidak memiliki kalender waktu; kerusakan hanya dapat diketahui oleh manusia saat proses Quality Control (QC fisik). Oleh karena itu, sistem tidak boleh menghapus stok secara gaib tanpa ada penanggung jawabnya.

2. **Tahap 1: Pengeluaran Unit Rusak ke Karantina / Retur Supplier**
   - Staf gudang membuka menu **Logistik & Inventori ➡️ Mutasi Stok Manual** (`inventori/proses_mutasi.php`).
   - Pilih:
     - Jenis Mutasi: **Pengurangan (-)**
     - Alasan: **Barang Rusak / Cacat Pabrik** atau **Retur ke Supplier**
     - Serial Number: Masukkan nomor seri unit yang rusak (misal: `OMR-HEM7120-A005`).
   - **Efek di Kartu Stok (`laporan/kartu_stok.php`)**:
     - Tercatat mutasi: **Keluar (1 Unit)**.
     - Kolom *Serial Number*: Tercatat `OMR-HEM7120-A005`.
     - Sisa stok fisik etalase otomatis berkurang (misal dari 10 menjadi 9).
   - **Efek di Database (`unit_serial`)**:
     - Status unit `OMR-HEM7120-A005` otomatis terkunci menjadi **`Retur/Rusak`**.
     - Unit ini langsung **ter-hide (hilang)** dari katalog POS kasir dan e-commerce.

3. **Tahap 2: Penerimaan Unit Baru Pengganti dari Supplier**
   - Ketika pihak distributor/supplier mengganti dengan unit baru yang sehat (membawa Serial Number baru dari pabrik, misal: `OMR-HEM7120-A010`):
   - Staf menginput di menu **Mutasi Stok Manual** (atau Penerimaan Barang):
     - Jenis Mutasi: **Penambahan (+)**
     - Alasan: **Penggantian Unit dari Supplier (Baru)**
     - Serial Number: `OMR-HEM7120-A010`
   - **Efek di Kartu Stok**:
     - Tercatat mutasi: **Masuk (1 Unit)** dengan Serial Number baru `OMR-HEM7120-A010`.
     - Sisa stok fisik kembali seimbang dari 9 menjadi 10.
     - Riwayat audit tercatat 100% sempurna: ada jejak barang rusak keluar (-1) dan jejak barang pengganti masuk (+1).

4. **Proteksi Anti-Human Error di Terminal Kasir POS (Live Demo):**
   - Jika kardus fisik unit yang rusak masih tergeletak di meja dan kasir secara tidak sengaja men-scan barcode SN unit tersebut (`OMR-HEM7120-A005`) baik menggunakan scanner laser maupun kamera webcam:
   - **Sistem POS seketika MEMBLOKIR transaksi** dan memunculkan pop-up merah:
     - **Judul**: `⛔ ALAT KESEHATAN RUSAK / AFKIR!`
     - **Status**: `RUSAK / DIKARANTINA`
     - **Instruksi Kasir**: *"Unit ini telah dicatat sebagai Barang Rusak / Cacat dan dilarang keras untuk dijual ke pelanggan demi keamanan medis. Segera pisahkan unit ke area karantina."*

---

### 7. Skenario Klaim Garansi Pelanggan (Barang Sudah Terjual Lalu Rusak & Ditukar Unit Baru)

Jika dosen penguji bertanya:
> *"Bagaimana jika alkes sudah laku terjual ke pelanggan, lalu dalam masa garansi alatnya error dan pelanggan minta tukar unit baru? Bagaimana alur sistem dan kartu stoknya?"*

**Jawaban Sidang Anda:**
1. **Fase 1: Penjualan Awal (Misal Stok Awal: 10 Unit)**
   - Pelanggan membeli unit `SN-001`.
   - **Kartu Stok**: Keluar 1 unit (`SN-001`) $\to$ Sisa stok etalase = **9 unit**.
   - **Status SN-001**: `Terjual` (Masa garansi toko 30 hari aktif).

2. **Fase 2: Klaim Garansi & Tukar Unit Baru (Karantina SN Lama + Keluar SN Baru)**
   - Pelanggan membawa `SN-001` yang rusak.
   - Admin membuka **Laporan Logistik Medis (Tab Jejak SN)** (`laporan/logistik_medis.php?tab=sn`), cari `SN-001`, lalu klik tombol **`Karantina`**.
     - Status `SN-001` otomatis diubah dari `Terjual` menjadi **`Retur/Rusak (Karantina)`**.
     - Unit rusak ini **TIDAK dicatat masuk ke etalase** untuk mencegah salah jual ke pasien lain.
   - Toko mengambil unit baru yang sehat (`SN-002`) dari etalase untuk diserahkan ke pelanggan.
   - Admin mencatat di **Mutasi Stok Manual** (`inventori/proses_mutasi.php`):
     - Jenis: **Pengurangan (-)**, Qty: **1**, Alasan: **`Klaim Garansi Pelanggan`**, SN: `SN-002`.
     - Status `SN-002` otomatis tercatat sebagai unit pengganti (`Terjual`).
     - **Kartu Stok**: Keluar 1 unit (`SN-002`) $\to$ Sisa stok etalase menjadi **8 unit**.

3. **Fase 3: Pabrik / Supplier Mengganti Unit Cacat (Stok Kembali Seimbang)**
   - Distributor resmi memverifikasi `SN-001` dan mengirimkan unit baru gres (`SN-011`) ke toko.
   - Admin menginput di **Mutasi Stok Manual** (atau Penerimaan Barang):
     - Jenis: **Penambahan (+)**, Qty: **1**, Alasan: **`Penggantian Unit dari Supplier (Baru)`**, SN: `SN-011`.
     - Status `SN-011`: Masuk sebagai `Tersedia`.
     - **Kartu Stok**: Masuk 1 unit (`SN-011`) $\to$ Sisa stok etalase kembali menjadi **9 unit**!
   - **Hasil Akhir**: Saldo stok toko seimbang 100%, dan jejak ketiga nomor seri (`SN-001` rusak, `SN-002` pengganti ke pelanggan, dan `SN-011` dari pabrik) tercatat abadi di database.

| Tahapan Aktivitas | Pergerakan Kartu Stok Etalase | Sisa Stok Etalase | Status Serial Number Terkait | Posisi Fisik Barang |
| :--- | :---: | :---: | :--- | :--- |
| **Kondisi Awal** | - | 10 Unit | SN-001 s/d SN-010: `Tersedia` | Di Rak Etalase Toko |
| **1. Penjualan Awal** | **Keluar (-1)** | **9 Unit** | SN-001: `Terjual` | Dibawa Pelanggan |
| **2. Klaim Garansi (Tukar Unit Baru)** | **Keluar (-1)** | **8 Unit** | • SN-001: `Retur/Rusak`<br>• SN-002: `Terjual` (Pengganti) | • SN-001: Masuk Karantina Rusak<br>• SN-002: Dibawa Pelanggan |
| **3. Supplier Kirim Unit Baru** | **Masuk (+1)** | **9 Unit (Kembali Seimbang)** | SN-011: `Tersedia` (Unit Baru dari Pabrik) | Masuk Rak Etalase Toko |




