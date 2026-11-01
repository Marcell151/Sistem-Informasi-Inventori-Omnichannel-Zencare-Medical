# Panduan & Skenario Demo Sidang: Modul Stok Opname ZenCare Medical

Dokumen ini disusun sebagai panduan praktis langkah demi langkah untuk mendemonstrasikan keunggulan teknis dan kepatuhan audit modul **Stok Opname Fisik Terpusat (Blind Count & Audit-Proof)** kepada dosen pembimbing maupun penguji sidang.

---

## Ringkasan Fitur Unggulan untuk Ditonjolkan ke Dosen
1. **Server-Level Transaction Freeze (Pembekuan Operasional Nyata):**  
   Saat sesi opname aktif, kasir POS, checkout e-commerce, konversi UOM, dan penerimaan logistik otomatis terkunci di tingkat server untuk mencegah *race condition* (perubahan stok saat dihitung).
2. **Strict Blind Count (Hitung Buta):**  
   Kolom stok sistem dan selisih disembunyikan total sebelum petugas lapangan menginputkan angka fisik rak yang sesungguhnya.
3. **Dual-Commodity Tracking (Perbedaan Perlakuan Obat vs Alkes):**  
   - **Obat:** Audit berbasis kuantitas per *Batch & Tanggal Kedaluwarsa*.
   - **Alkes:** Audit berbasis identitas unik *Serial Number 1:1*. Jika ada unit rusak/hilang, Admin wajib memilih nomor barcode SN mana yang terdampak.
4. **Audit-Proof Reconciliation & Auto-Rollback:**  
   Sesi tidak bisa ditutup jika belum 100% dihitung dan dimutasi. Jika sesi dibatalkan darurat, seluruh mutasi otomatis di-rollback ke saldo semula.
5. **Dokumen Resmi Berita Acara (A4 Print-Ready):**  
   Format cetak standar industri dengan Kop Surat ZenCare, ringkasan 5 kotak statistik, dan kolom tanda tangan (TTD) ganda.

---

## Langkah-Langkah Simulasi Demo Langsung

### Langkah 1: Memulai Sesi & Pembekuan Operasional
1. Login sebagai **Superadmin** $\rightarrow$ Buka menu **Stok Opname** pada sidebar inventori.
2. Tunjukkan ke dosen status sistem dalam kondisi normal (belum ada sesi aktif).
3. Klik tombol biru **"Mulai Sesi Stok Opname Baru (Bekukan Transaksi)"**.
4. Masukkan catatan sesi, contoh:  
   `"Stok Opname Rutin Akhir Bulan - Verifikasi Fisik Rak Penjualan"`
5. Klik **"Ya, Mulai Sesi & Bekukan Transaksi"**.
6. **Poin Pembuktian ke Dosen:**  
   - Buka tab baru ke menu **Kasir (POS)** $\rightarrow$ Tunjukkan tombol *"Bayar"* dinonaktifkan dengan label *"Kasir Dibekukan (Stok Opname Aktif)"*.
   - Buka tab Toko Online (E-Commerce) saat checkout $\rightarrow$ Tunjukkan peringatan bahwa transaksi daring sementara ditutup demi sinkronisasi stok fisik.

---

### Langkah 2: Prinsip Blind Count (Hitung Buta)
1. Kembali ke halaman Stok Opname.
2. Tunjukkan ke dosen bahwa kolom **Stok Fisik Sistem** dan **Selisih** bertuliskan *"Terselubung / Gembok"*.
3. Jelaskan ke dosen:  
   > *"Pak/Bu Dosen, sistem menerapkan prinsip Blind Count sesuai standar audit. Petugas lapangan tidak diperlihatkan angka sistem terlebih dahulu agar mereka benar-benar menghitung fisik asli di rak tanpa manipulasi atau asumsi angka."*

---

### Langkah 3: Menginputkan Sampel Selisih (Demo Mutasi Obat & Alkes)
1. **Untuk Obat (Contoh: Baris 1 atau 2):**
   - Masukkan angka fisik lapangan yang berselisih (misal stok sistem 10, isi 9 pada kotak hitung fisik).
   - Klik tombol **"Simpan"**.
   - Seketika kolom sistem terbuka: muncul selisih `-1 Unit` dan tombol oranye **"Mutasi Selisih"**.
   - Klik **"Mutasi Selisih"** $\rightarrow$ Pilih alasan: `"Barang Rusak"` atau `"Barang Hilang"`.
   - Klik Simpan $\rightarrow$ Stok otomatis termutasi dan langsung tercatat di **Kartu Stok**.
2. **Untuk Alat Kesehatan (Pelacakan Serial Number 1:1):**
   - Klik tab filter **"Alat Kesehatan (Serial Number)"**.
   - Tunjukkan daftar unit aktif di rak: `"Unit di Rak: X Unit"` lengkap dengan chip nomor seri aktif.
   - Masukkan hitung fisik defisit 1 unit $\rightarrow$ Klik **Simpan**.
   - Klik **"Mutasi Selisih"** $\rightarrow$ Tunjukkan form khusus Alkes: Admin diwajibkan mencentang nomor SN persis yang rusak/hilang!

---

### Langkah 4: Menggunakan Tombol Pintasan "Isi Angka Fisik" & "Simpan Semua Hitungan"
1. Untuk sisa puluhan baris barang lainnya, Anda tidak perlu mengetik satu per satu secara manual.
2. Di bagian kanan atas banner sesi, klik tombol:  
   **`[⚡ Isi Angka Fisik (Demo)]`**.
3. Seketika kotak input pada seluruh baris barang langsung terisi angka fisik di layar:
   - Angka **BELUM tersimpan ke server**, sehingga Anda bisa menunjukkannya ke dosen terlebih dahulu.
   - Mayoritas diisi angka pas sesuai sistem, dan terdapat 2 sampel yang sengaja dibuat berselisih `-1` (1 obat satuan besar dan 1 alkes) untuk simulasi.
   - Anda bebas mengedit/mengubah angka di kotak input mana pun jika ingin mencoba angka lain.
4. Klik tombol:  
   **`[💾 Simpan Semua Hitungan Fisik]`**.
5. Seluruh angka fisik tersimpan serentak ke server:
   - Kolom stok sistem dan selisih terbuka seketika.
   - Terlihat status **Sesuai** dan **Perlu Mutasi** secara jelas dan rapi.
6. **Pilihan Rekonsiliasi Selisih ke Dosen:**
   - **Opsi A (Satu per satu):** Klik tombol *"Mutasi Selisih"* pada baris berselisih untuk memilih alasan mutasi spesifik (Barang Rusak, Kedaluwarsa, dll).
   - **Opsi B (Sekaligus / Cepat):** Klik tombol **`[⚡ Rekonsiliasi Semua Selisih]`** di kanan atas $\rightarrow$ Pilih alasan mutasi bersama $\rightarrow$ Klik *"Ya, Rekonsiliasi Seluruh Selisih"*. Seluruh selisih otomatis disesuaikan ke stok sistem dan kartu stok!
5. Statistik menunjukkan:
   - *Belum Dihitung: 0*
   - *Sudah Dihitung: 100%*
   - *Sudah Dimutasi: Lengkap*

---

### Langkah 5: Menyelesaikan Sesi & Membuka Kunci Sistem
1. Tunjukkan ke dosen bahwa tombol **"Selesaikan Sesi (Buka Kunci)"** kini berwarna hijau menyala dan siap diklik.
2. Klik **"Selesaikan Sesi (Buka Kunci)"** $\rightarrow$ Konfirmasi.
3. Banner pembekuan hilang dan sistem kembali normal.
4. Kasir POS dan checkout e-commerce otomatis terbuka kembali untuk melayani pembeli.

---

### Langkah 6: Mencetak Berita Acara Resmi Stok Opname
1. Gulir ke bawah pada tabel **"Riwayat Sesi Stok Opname Terdahulu"**.
2. Klik tombol **"Berita Acara"** pada sesi yang baru saja selesai (atau pada sesi historis `OPN-20261015-001`).
3. Tunjukkan hasil cetak pratinjau lembar A4:
   - Kop Resmi bersama Logo ZenCare dan alamat toko.
   - Ringkasan 5 kotak statistik hasil pemeriksaan fisik.
   - Kolom tanda tangan resmi: **Pengelola Stok Gudang** dan **Pemilik Toko**.
   - Tabel audit membedakan secara cerdas antara **Batch Induk (Satuan Besar / Dus Utuh)** dengan **Sub-Batch (Satuan Kecil / Eceran)**.

