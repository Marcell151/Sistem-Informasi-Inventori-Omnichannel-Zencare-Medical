# 📋 PANDUAN LENGKAP DEMONSTRASI & UJI SISTEM UNTUK BIMBINGAN / SIDANG DOSEN
**Sistem Informasi Inventori Omnichannel – ZenCare Medical**
*Disusun berdasarkan arahan bimbingan dosen tanggal 12 Oktober 2026*

---

## 🚀 Ringkasan 7 Poin Utama Penyesuaian

| No | Poin Masukan Dosen | Solusi Sistem yang Diimplementasikan | Lokasi Halaman |
|:---|:---|:---|:---|
| 1 | **Validasi Expired Date Minimal 60 Hari & Label Stiker** | Input tanggal kadaluwarsa dibatasi minimal `+60 hari` (2 bulan). Ada tombol cetak stiker barcode instan di penerimaan barang. Akses staf toko ke Lap. Logistik Medis dibuka (bebas 403 Forbidden). | `inventori/tambah_stok.php`, `laporan/logistik_medis.php` |
| 2 | **Kolom "Sisa Stok Kardus" & Filter Kartu Stok** | Kolom `sisa_dus_akhir` ditambahkan di `histori_konversi` (misal: "📦 4 Dus Tersisa"). Kartu stok dilengkapi filter khusus `Buka Kardus (Konversi UOM)` & ringkasan audit. | `inventori/histori_konversi.php`, `laporan/kartu_stok.php` |
| 3 | **Penanda Visual FEFO di Packing Pesanan Online** | Admin pesanan e-commerce kini menampilkan rekomendasi batch terdekat (`[Exp: DD/MM/YYYY / Sisa X Hari — FEFO Terdekat]`) pada detail order dan modal checklist packing. | `admin/pesanan.php` |
| 4 | **Sinkronisasi Stok Keranjang Real-time & Redirect Login** | Keranjang belanja otomatis memvalidasi stok kardus riil via `api/cek_stok_keranjang.php`. Jika kasir fisik membongkar kardus, quantity di keranjang online otomatis berkurang dengan notifikasi jelas. Login pelanggan dari checkout otomatis kembali ke checkout (`?redirect=checkout`). | `login_customer.php`, `ecommerce/footer.php`, `zencare_checkout.php` |
| 5 | **Midtrans Dynamic QRIS di POS Kasir** | Menggantikan dummy statis. Kasir memilih QRIS -> muncul pop-up QRIS Midtrans dengan nominal rupiah presisi tagihan. Setelah pelanggan scan & bayar, transaksi POS otomatis selesai & cetak nota. | `pos/pos.php`, `api/pos_snap_token.php` |
| 6 | **Barcode Scanner Kamera / Webcam di POS** | Tombol `Scan Kamera` di kasir mengaktifkan live webcam via `html5-qrcode`. Barcode / QR otomatis terbaca, berbunyi bip, dan masuk ke keranjang kasir tanpa perlu install aplikasi HP eksternal. | `pos/pos.php`, `assets/js/html5-qrcode.min.js` |
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
2. Di samping input barcode, klik tombol biru **"Scan Kamera"**.
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
