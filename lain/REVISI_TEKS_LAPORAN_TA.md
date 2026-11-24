# Naskah Revisi Laporan Tugas Akhir (Siap Copy-Paste ke Word)
**Judul:** Rancang Bangun Sistem Informasi Inventory Omnichannel Alat Kesehatan dan Obat (Studi Kasus ZenCare Medical)  
**Mahasiswa:** Marcell Chandra Kenchana (NIM: 322310015)  
**Dasar Revisi:** Seminar Hasil TA — 23 November 2026 (Bu Meme Susilowati, S.Kom., MMSI. & Dr. Soetam Rizky Wicaksono, S.Kom., MM.)

---

## 1. BAB I — Subbab 1.3 Batasan Masalah (Revisi Poin 4)

> **Lokasi di Word:** Ganti paragraf **poin ke-4** pada `1.3 Batasan Masalah` (paragraf mengenai *pre-order* dan *refund otomatis*) dengan teks berikut:

4. Sistem tidak menyediakan fitur pemesanan tunggu (*pre-order*), saldo dompet digital (*deposit wallet*), maupun pengembalian dana (*refund*) secara otomatis. Pengajuan pembatalan pesanan daring yang telah dibayar (status *Diproses*) hanya diperbolehkan dalam batas waktu maksimal **1×24 jam** sejak pembayaran terverifikasi dan berlaku untuk satu transaksi utuh (bukan pembatalan parsial per item). Saat mengajukan pembatalan, pelanggan wajib mencantumkan nama bank, nomor rekening, dan nama pemilik rekening tujuan pengembalian dana manual oleh Admin, disertai pernyataan persetujuan bahwa kesalahan pengisian nomor rekening atau nama bank yang mengakibatkan salah transfer sepenuhnya menjadi tanggung jawab pelanggan. Selain itu, proses konversi satuan (Multi-UOM) dirancang bersifat satu arah (*non-reverse*) dari kemasan besar (karton/boks) ke satuan eceran tanpa modul pengemasan ulang (*repacking*). Modul retur pada sistem ini hanya memfasilitasi retur pembelian dari toko kepada pihak *Supplier* saat penerimaan barang, sedangkan retur penjualan dari pelanggan diselesaikan di luar sistem e-commerce dan dicatat manual melalui modul Mutasi Stok.

---

## 2. BAB III — Subbab 3.1 Metode Penelitian (Tambahan Wajib Bu Meme: Gambar Alur Penelitian)

> **Lokasi di Word:** Sisipkan tepat di bawah paragraf kedua Subbab `3.1 Metode Penelitian` (sebelum subjudul `Perencanaan Kebutuhan (Requirements Planning)`), beserta gambar **[Gambar_3.1_Alur_Penelitian.png](revisi_semhas_gambar/Gambar_3.1_Alur_Penelitian.png)** (atau versi vektor **[Gambar_3.1_Alur_Penelitian.svg](Gambar_3.1_Alur_Penelitian.svg)**).  
> *(Catatan: Setelah menambahkan gambar ini sebagai `Gambar 3.1 Alur Penelitian`, lakukan `Ctrl+A` lalu tekan `F9` di Microsoft Word agar seluruh nomor Caption Gambar di Bab III otomatis terurut rapi.)*

**Teks Pengantar Alur Penelitian (Siap Tempel):**

Alur pelaksanaan penelitian disusun secara sistematis mulai dari identifikasi permasalahan operasional hingga tahap pengujian kelayakan sistem bersama pengguna. Tahapan penelitian menggabungkan teknik pengumpulan data empiris di lapangan (wawancara dan observasi langsung pada ZenCare Medical) dengan empat fase pengembangan perangkat lunak metode *Rapid Application Development* (RAD) yang bersifat iteratif. Secara visual, alur penelitian yang dilaksanakan disajikan pada **Gambar 3.1**.

**[SISIPKAN GAMBAR: `lain/revisi_semhas_gambar/Gambar_3.1_Alur_Penelitian.png`]**  
*Gambar 3.1 Alur Penelitian Sistem Informasi Inventory Omnichannel ZenCare Medical*

Berdasarkan alur pada Gambar 3.1, tahapan penelitian meliputi:
1. **Perencanaan Kebutuhan (*Requirements Planning*):** Mengidentifikasi kendala operasional di lapangan, melakukan studi literatur, mengumpulkan data melalui **wawancara dan observasi** langsung di gerai ZenCare Medical, menganalisis proses bisnis berjalan, serta merumuskan kebutuhan fungsional sistem.
2. **Perancangan Sistem (*User Design*):** Memodelkan proses bisnis usulan, merancang struktur sistem dan pemodelan UML, menyusun perancangan basis data (model data dan kamus data), serta merancang antarmuka pengguna (*UI/UX*) secara iteratif bersama pengguna.
3. **Pembangunan Sistem (*Construction*):** Mengimplementasikan basis data, melakukan pengkodean aplikasi berbasis web secara modular, mengintegrasikan antar modul dan layanan API pendukung, serta melaksanakan pengujian fungsional internal (verifikasi dan perbaikan kode) sesuai evaluasi desain.
4. **Pengujian dan Peralihan (*Cutover*):** Melaksanakan pengujian penerimaan pengguna (*User Acceptance Testing* / UAT) secara langsung bersama pihak pengguna sistem, menganalisis hasil serta evaluasi pengujian (jika belum layak kembali ke siklus iteratif untuk perbaikan), menarik kesimpulan dan saran, serta menyusun dokumentasi Laporan Akhir.

---

## 3. BAB III — Pembaruan Diagram & Kamus Data (File Sudah Diperbarui)

1. **Activity Diagram Kelola Pesanan Daring & Pembatalan (`Gambar 3.13`):**
   - Ganti gambar di Word dengan **[Gambar_3.13_Activity_Kelola_Pesanan_Daring.png](revisi_semhas_gambar/Gambar_3.13_Activity_Kelola_Pesanan_Daring.png)** (versi vektor: [Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg](Gambar_3.13_Activity_Kelola_Pesanan_Daring.svg)).
   - Salin teks narasi terbarunya dari tab **D (Kelola Pesanan Daring)** pada [semua_activity_diagram_zencare.html](semua_activity_diagram_zencare.html).
2. **Logical Database (`Gambar 3.18`):**
   - Definisi DBML pada [logical_database_zencare.html](logical_database_zencare.html) dan [zencare_dbml_dbdiagram.txt](zencare_dbml_dbdiagram.txt) sudah diperbarui dengan 5 kolom baru pada tabel `penjualan` (`refund_bank`, `refund_rekening`, `refund_atas_nama`, `status_refund`, `waktu_refund`).
   - Buka [logical_database_zencare.html](logical_database_zencare.html) di browser atau tempel isi [zencare_dbml_dbdiagram.txt](zencare_dbml_dbdiagram.txt) ke `dbdiagram.io` jika ingin mengekspor ulang gambar ERD terbaru.
3. **Kamus Data (`Tabel 3.11 Tabel penjualan`):**
   - Buka [kamus_data_zencare.html](kamus_data_zencare.html) bagian **`3.5.11 penjualan`** (sekarang lengkap **32 kolom**, termasuk 6 kolom pembatalan dan 5 kolom refund baru) lalu salin ke tabel `penjualan` di Word.

---

## 4. BAB IV — Pembaruan Narasi & Screenshot Implementasi

### A. Penyesuaian Kalimat Konversi Satu Arah (Diselipkan di Paragraf Gambar 4.13 — Tidak Buat Paragraf Baru)
> **Lokasi di Word:** Cukup selipkan 1 kalimat singkat ini di akhir paragraf penjelasan **Gambar 4.13 (Histori Konversi Satuan)** (rincian pengembangan *reverse/repacking* diletakkan di Bab V Saran):

Mekanisme konversi satuan ini dirancang bersifat satu arah (*non-reverse*), yaitu hanya mendukung pembongkaran dari kemasan besar utuh ke satuan eceran guna menjaga keutuhan segel kemasan serta konsistensi nomor *batch* dan tanggal kedaluwarsa.

### B. Penyesuaian Penjelasan Gambar yang Sudah Ada (Selipkan di Paragraf Lama — Tanpa Paragraf Baru)
- **Gambar 4.2 (Dashboard Admin):** Ganti gambar dengan **[Gambar_4.2_Dashboard_Admin_Pengingat.png](revisi_semhas_gambar/Gambar_4.2_Dashboard_Admin_Pengingat.png)**, lalu tambahkan 1 kalimat di akhir paragraf lama:
  > Selain itu, pada bagian teratas *Dashboard* ditampilkan banner pengingat pesanan berstatus Diproses beserta kolom batas kirim **1×24 jam** sejak pembayaran terverifikasi agar pesanan daring yang telah dibayar tidak mengendap atau terlewat dari jadwal pengemasan.
- **Gambar 4.5 (Pengelolaan Transaksi Omnichannel):** Ganti gambar dengan **[Gambar_4.5_Kelola_Pesanan_Pengingat_Refund.png](revisi_semhas_gambar/Gambar_4.5_Kelola_Pesanan_Pengingat_Refund.png)**, lalu tambahkan kalimat berikut di akhir paragraf lama:
  > Halaman ini turut dilengkapi banner pengingat operasional beserta *tab* filter cepat "Perlu Diproses" dan indikator batas waktu kirim 1×24 jam (tanda merah untuk pesanan yang melewati 24 jam). Apabila pelanggan mengajukan pembatalan dalam rentang 1×24 jam, panel peninjauan Admin menampilkan data lengkap Nama Bank, Nomor Rekening, Atas Nama Pemilik Rekening, dan nominal pengembalian dana (*refund*). Setelah pembatalan disetujui dan stok dipulihkan, status pengembalian dana tercatat sebagai Menunggu Transfer hingga Admin menekan tombol "Tandai Refund Sudah Ditransfer" untuk menyelesaikan pencatatan *refund*.
- **Gambar 4.46 (Profil Pelanggan):** Ganti gambar dengan **[Gambar_4.46a_Profil_Pelanggan_Kunci_24Jam_Exp.png](revisi_semhas_gambar/Gambar_4.46a_Profil_Pelanggan_Kunci_24Jam_Exp.png)**, lalu sesuaikan paragraf lamanya menjadi:
  > Pada halaman Profil dan Pemantauan Pesanan Pelanggan (Gambar 4.46), konsumen dapat memperbarui biodata, nomor telepon, dan alamat pengiriman utama serta memantau daftar transaksi daringnya lengkap dengan informasi tanggal kedaluwarsa (*Exp*) obat yang dialokasikan tanpa menampilkan kode *barcode* internal. Untuk pesanan berstatus Diproses, sistem menerapkan batas waktu pengajuan pembatalan maksimal **1×24 jam** sejak pembayaran terverifikasi; apabila telah melewati batas tersebut, tombol pembatalan otomatis terkunci (*Batal Terkunci >24 Jam*) baik pada antarmuka maupun validasi server.
- **Gambar 4.47 (Beranda E-Commerce):** Ganti gambar dengan **[Gambar_4.47_Beranda_Ecommerce_Exp.png](revisi_semhas_gambar/Gambar_4.47_Beranda_Ecommerce_Exp.png)**, lalu tambahkan 1 kalimat di akhir paragraf lama:
  > Khusus untuk produk kategori Obat yang tersedia, setiap kartu katalog turut menampilkan informasi singkat tanggal kedaluwarsa (*Exp*) berdasarkan *batch FEFO* aktif tanpa menampilkan kode *barcode*.
- **Gambar 4.48 (Detail Produk):** Ganti gambar dengan **[Gambar_4.48_Detail_Produk_Exp.png](revisi_semhas_gambar/Gambar_4.48_Detail_Produk_Exp.png)**, lalu tambahkan 1 kalimat di akhir paragraf lama:
  > Khusus produk kategori Obat, sistem menampilkan satu bidang informasi **Tanggal Kedaluwarsa** yang diambil otomatis dari *batch* kemasan utuh terdekat sesuai prinsip *FEFO* (tanpa menampilkan kode *barcode* demi keamanan logistik internal) guna meyakinkan pembeli daring atas kelayakan masa simpan obat; adapun pada terminal kasir fisik (*POS*) tampilan tersebut tidak diperlukan karena pembeli dapat memeriksa langsung pada kemasan fisik di gerai.

### C. Tambahan Gambar Baru + Paragraf Penjelasan Baru (`Gambar 4.46b`)
> **Sisipkan gambar baru** tepat di bawah penjelasan Gambar 4.46: **[Gambar_4.46b_Modal_Form_Refund_Disclaimer.png](revisi_semhas_gambar/Gambar_4.46b_Modal_Form_Refund_Disclaimer.png)**  
> *Gambar 4.46b Formulir Pengajuan Pembatalan Pesanan, Input Rekening Refund, dan Disclaimer Tanggung Jawab Pelanggan*

Apabila pelanggan mengajukan pembatalan pesanan yang masih berada dalam rentang batas waktu 1×24 jam, sistem menampilkan jendela Formulir Pengajuan Pembatalan Pesanan dan Rekening *Refund* (Gambar 4.46b). Pada formulir ini, pelanggan wajib mengisi alasan pembatalan serta data tujuan pengembalian dana manual berupa Nama Bank, Nomor Rekening, dan Nama Pemilik Rekening, disertai centang pernyataan persetujuan (*disclaimer*) bahwa karena sistem tidak menyediakan *deposit wallet*, kesalahan pengisian nomor rekening atau nama bank yang mengakibatkan salah transfer sepenuhnya menjadi tanggung jawab pelanggan.

---

## 5. BAB V — Subbab 5.2 Saran (Tambahan Wajib Pak Soetam & Masukan Bu Meme)

> **Lokasi di Word:** Ganti atau lengkapi paragraf `5.2 Saran` dengan versi lengkap berikut (mempertahankan saran sebelumnya + menambahkan **konversi balik / *reverse*** dan **pembatalan parsial per item**):

Untuk pengembangan sistem pada masa mendatang, terdapat beberapa hal yang disarankan bagi peneliti maupun pengembang selanjutnya:
1. **Integrasi API Produksi (*Live Production*):** Meningkatkan integrasi layanan gerbang pembayaran digital (*payment gateway*) Midtrans dan layanan kurir ekspedisi RajaOngkir dari lingkungan pengujian (*sandbox*) ke tahap operasional langsung (*live production*) agar pembayaran daring, pelacakan resi otomatis, dan rekonsiliasi transaksi berjalan secara penuh.
2. **Pengembangan Mekanisme Konversi Balik (*Reverse* / *Repacking*):** Mengembangkan modul pengemasan ulang (*repacking*) atau konversi balik (*reverse*) dari satuan eceran ke kemasan besar yang dilengkapi standar verifikasi kesesuaian nomor *batch*, tanggal kedaluwarsa, serta pencatatan kelayakan penyegelan ulang kemasan agar tetap menjaga mutu produk medis.
3. **Fitur Pembatalan Parsial per Item:** Mengembangkan mekanisme pembatalan pesanan secara parsial per item (bukan hanya per nota transaksi utuh) yang terintegrasi dengan perhitungan ulang otomatis berat paket, penyesuaian ongkos kirim ekspedisi, serta mekanisme pengembalian dana parsial yang terotomatisasi.
4. **Fitur Loyalitas & Rekomendasi Produk:** Melengkapi portal toko daring dengan fitur keanggotaan pelanggan (*membership*) serta fitur rekomendasi produk (*product recommendation*) untuk meningkatkan retensi pelanggan dan mendorong pertumbuhan penjualan secara berkelanjutan.
