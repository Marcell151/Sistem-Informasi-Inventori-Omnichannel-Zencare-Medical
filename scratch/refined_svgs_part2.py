# -*- coding: utf-8 -*-
"""
Part 2: Refined Diagram 3.12 (Kasir POS) and 3.13 (Kelola Pesanan Daring)
Guaranteed valid XML (no &ge;) and 100% strictly orthogonal lines.
"""

defs_str = '''  <defs>
    <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#0f172a" />
    </marker>
    <marker id="arrow-red" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#dc2626" />
    </marker>
    <marker id="arrow-green" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#16a34a" />
    </marker>
    <filter id="shadow" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="1" dy="2" stdDeviation="1.5" flood-opacity="0.06"/>
    </filter>
  </defs>'''

def get_refined_312():
    return f'''<svg viewBox="0 0 960 1240" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="940" height="1220" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="940" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="480" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Pesanan Luring (Transaksi Kasir / POS &amp; FEFO)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="250" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="135" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan Toko</text>

  <rect x="260" y="45" width="360" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="440" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Kasir</text>

  <rect x="620" y="45" width="330" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="785" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem POS</text>

  <line x1="260" y1="45" x2="260" y2="1230" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="620" y1="45" x2="620" y2="1230" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Kasir Init -->
  <circle cx="440" cy="105" r="10" fill="#0f172a"/>
  <line x1="440" y1="115" x2="440" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="340" y="140" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="440" y="158" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Terminal Kasir</text>
  <text x="440" y="172" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">(pos/pos.php)</text>

  <!-- Pelanggan Membawa Barang -->
  <rect x="35" y="140" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="135" y="158" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membawa Barang Belanjaan</text>
  <text x="135" y="172" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">ke Meja Kasir</text>
  <path d="M 235 161 L 340 161" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <line x1="440" y1="182" x2="440" y2="210" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir Scan -->
  <rect x="340" y="210" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="440" y="228" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Pindai Barcode Kemasan</text>
  <text x="440" y="242" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">atau Input SKU Produk</text>
  <path d="M 540 231 L 670 231" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Respon Scan -->
  <rect x="670" y="208" width="230" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="785" y="226" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tampilkan Detail Produk,</text>
  <text x="785" y="239" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Dual-UOM (Strip vs Box), &amp;</text>
  <text x="785" y="252" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Rekomendasi FEFO / Serial Number</text>
  <!-- Orthogonal connection from Sistem to Kasir -->
  <path d="M 785 256 L 785 295 L 540 295" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir Pilih UOM & Fisik -->
  <rect x="340" y="275" width="200" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="440" y="293" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Pilih Satuan Jual (Opsi Bongkar</text>
  <text x="440" y="306" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Box Jika Rak Habis) &amp;</text>
  <text x="440" y="319" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Ambil Fisik Kemasan</text>
  <line x1="440" y1="323" x2="440" y2="350" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision Kesesuaian Batch Fisik vs Rekomendasi FEFO -->
  <polygon points="440,350 490,370 440,390 390,370" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="440" y="373" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Sesuai FEFO?</text>

  <!-- Tidak Sesuai: Catat Log Anomali FEFO (Strictly Orthogonal) -->
  <text x="325" y="365" font-size="9" font-weight="bold" fill="#d97706">[Fisik Berbeda]</text>
  <path d="M 390 370 L 320 370 L 320 415" fill="none" stroke="#d97706" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="270" y="415" width="130" height="45" rx="6" fill="#fffbeb" stroke="#d97706" stroke-width="1.5"/>
  <text x="335" y="433" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#d97706">Pilih Batch Fisik</text>
  <text x="335" y="446" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#d97706">yang Terambil</text>
  <path d="M 400 437 L 670 437" fill="none" stroke="#d97706" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="670" y="415" width="230" height="45" rx="6" fill="#fffbeb" stroke="#d97706" stroke-width="1.5"/>
  <text x="785" y="433" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#d97706">Rekam Log Anomali FEFO</text>
  <text x="785" y="446" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#d97706">Otomatis (Tanpa Setop Transaksi)</text>
  <!-- Orthogonal line from Log Anomali to Merge -->
  <path d="M 785 460 L 785 490 L 455 490" fill="none" stroke="#d97706" stroke-width="1.5"/>

  <!-- Sesuai FEFO (Straight orthogonal down) -->
  <text x="480" y="405" font-size="9" font-weight="bold" fill="#16a34a">[Sesuai]</text>
  <path d="M 440 390 L 440 480" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Batch -->
  <polygon points="440,480 455,490 440,500 425,490" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="440" y1="500" x2="440" y2="525" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Ada Barang Lain? -->
  <polygon points="440,525 480,545 440,565 400,545" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="440" y="548" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Barang Lain?</text>

  <!-- Loop line: strictly orthogonal -->
  <text x="485" y="540" font-size="9" font-weight="bold" fill="#0f172a">[Ya]</text>
  <path d="M 480 545 L 560 545 L 560 231 L 540 231" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <text x="445" y="580" font-size="9" font-weight="bold" fill="#0f172a">[Tidak]</text>
  <line x1="440" y1="565" x2="440" y2="600" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="340" y="600" width="200" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="440" y="624" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan "Proses Pembayaran"</text>
  <path d="M 540 620 L 670 620" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="670" y="600" width="230" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="785" y="618" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Hitung Total Belanja &amp;</text>
  <text x="785" y="631" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tampilkan Ringkasan Tagihan</text>
  <!-- Orthogonal line from Sistem to Pelanggan -->
  <path d="M 670 635 L 235 635" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pelanggan Bayar -->
  <rect x="35" y="665" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="135" y="683" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menyerahkan Pembayaran</text>
  <text x="135" y="697" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">(Uang Tunai atau QRIS)</text>
  <path d="M 235 686 L 340 686" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir Terima Bayar -->
  <rect x="340" y="665" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="440" y="683" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menerima Pembayaran &amp;</text>
  <text x="440" y="697" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Pilih Metode di Kasir</text>
  <line x1="440" y1="707" x2="440" y2="735" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Metode Pembayaran -->
  <polygon points="440,735 480,755 440,775 400,755" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="440" y="758" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Metode?</text>

  <!-- Tunai (Orthogonal) -->
  <text x="350" y="750" font-size="9" font-weight="bold" fill="#0f172a">[Tunai]</text>
  <path d="M 400 755 L 345 755 L 345 795" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="280" y="795" width="130" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="345" y="813" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Input Nominal</text>
  <text x="345" y="826" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Uang Diterima</text>
  <path d="M 410 816 L 680 816" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- FIXED ENTITY: Validasi Uang &gt;= Total -->
  <rect x="680" y="795" width="210" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="785" y="813" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Validasi Uang &gt;= Total &amp;</text>
  <text x="785" y="826" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Hitung Kembalian Otomatis</text>
  <path d="M 785 837 L 785 865 L 455 865" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Non Tunai (QRIS) (Orthogonal) -->
  <text x="495" y="750" font-size="9" font-weight="bold" fill="#0f172a">[QRIS]</text>
  <path d="M 480 755 L 535 755 L 535 795" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="470" y="795" width="130" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="535" y="813" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Verifikasi Transaksi</text>
  <text x="535" y="826" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Digital Sukses</text>
  <path d="M 535 837 L 535 865 L 455 865" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Bayar -->
  <polygon points="440,855 455,865 440,875 425,865" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="440" y1="875" x2="440" y2="905" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir Selesaikan -->
  <rect x="340" y="905" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="440" y="924" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan "Selesaikan Transaksi"</text>
  <text x="440" y="937" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">&amp; Konfirmasi Struk</text>
  <path d="M 540 926 L 670 926" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Update Stok & Cetak Struk (Orthogonal vertical stack) -->
  <rect x="670" y="905" width="230" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="785" y="923" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Potong Stok Fisik Database</text>
  <text x="785" y="936" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Sesuai Batch/SN Terpilih</text>
  <line x1="785" y1="947" x2="785" y2="975" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="670" y="975" width="230" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="785" y="993" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Catat Mutasi Keluar Penjualan</text>
  <text x="785" y="1006" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">ke Riwayat Kartu Stok</text>
  <line x1="785" y1="1017" x2="785" y2="1045" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="670" y="1045" width="230" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="785" y="1063" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Rekam ke Riwayat Kasir Hari Ini &amp;</text>
  <text x="785" y="1076" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Cetak Struk Belanja Thermal</text>
  <!-- Orthogonal line from Sistem to Kasir -->
  <path d="M 670 1067 L 540 1067" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir Serahkan Struk -->
  <rect x="340" y="1045" width="200" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="440" y="1063" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menyerahkan Struk Belanja &amp;</text>
  <text x="440" y="1076" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Fisik Barang ke Pelanggan</text>
  <!-- Orthogonal line from Kasir to Pelanggan -->
  <path d="M 340 1067 L 235 1067" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pelanggan Terima -->
  <rect x="35" y="1045" width="200" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="135" y="1063" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerima Struk &amp;</text>
  <text x="135" y="1076" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Barang Belanjaan</text>

  <line x1="135" y1="1090" x2="135" y2="1140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="135" cy="1155" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="135" cy="1155" r="8" fill="#0f172a"/>
</svg>'''

def get_refined_313():
    return f'''<svg viewBox="0 0 900 1140" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="880" height="1120" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="880" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="450" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Kelola Pesanan Daring &amp; Pembatalan (Picking List &amp; Release Reserved)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="430" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="225" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="440" y="45" width="450" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="665" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <line x1="440" y1="45" x2="440" y2="1130" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Admin Init -->
  <circle cx="225" cy="105" r="10" fill="#0f172a"/>
  <line x1="225" y1="115" x2="225" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="140" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="158" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Transaksi &amp;</text>
  <text x="225" y="172" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Pilih Tab "E-Commerce"</text>
  <path d="M 335 161 L 530 161" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Tampilkan Pesanan Masuk -->
  <rect x="530" y="140" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="158" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menampilkan Antrean Pesanan "Diproses"</text>
  <text x="665" y="172" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">&amp; Panduan Identitas Logistik (Batch/SN)</text>
  <path d="M 665 182 L 665 215 L 335 215" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Admin Cetak Picking List & Ambil Fisik -->
  <rect x="115" y="195" width="220" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="213" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Cetak Picking List &amp; Siapkan</text>
  <text x="225" y="227" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Fisik Barang dari Rak Berpanduan FEFO</text>
  <line x1="225" y1="240" x2="225" y2="270" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Tindakan Evaluasi Pesanan -->
  <polygon points="225,270 270,290 225,310 180,290" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="225" y="294" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Tindakan?</text>

  <!-- CABANG 1: BATALKAN PESANAN (Strictly Orthogonal) -->
  <text x="110" y="285" font-size="9" font-weight="bold" fill="#dc2626">[Batalkan Pesanan]</text>
  <path d="M 180 290 L 100 290 L 100 340" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="25" y="340" width="150" height="45" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="100" y="358" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">Pilih Opsi "Batalkan"</text>
  <text x="100" y="371" text-anchor="middle" font-size="8" fill="#dc2626">(Pick-up Kedaluwarsa/Kendala)</text>
  <path d="M 175 362 L 530 362" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="530" y="340" width="270" height="45" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="665" y="358" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#dc2626">Lepas Kuncian Stok "Reserved"</text>
  <text x="665" y="372" text-anchor="middle" font-size="9.5" fill="#dc2626">Kembali Menjadi Stok Aktif Toko</text>
  <line x1="665" y1="385" x2="665" y2="415" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="530" y="415" width="270" height="42" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="665" y="433" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#dc2626">Perbarui Status Jadi "Dibatalkan"</text>
  <text x="665" y="446" text-anchor="middle" font-size="8.5" fill="#dc2626">&amp; Kirim Notifikasi ke Pembeli</text>
  <path d="M 665 457 L 665 950" fill="none" stroke="#dc2626" stroke-width="1.5"/>

  <!-- CABANG 2: LANJUTKAN PEMENUHAN (Orthogonal) -->
  <text x="240" y="335" font-size="9" font-weight="bold" fill="#16a34a">[Lanjut Pemenuhan]</text>
  <line x1="225" y1="310" x2="225" y2="490" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="225,490 270,510 225,530 180,510" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="225" y="514" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Metode?</text>

  <!-- SUB-CABANG A: KURIR EKSPEDISI (Orthogonal) -->
  <text x="135" y="505" font-size="9" font-weight="bold" fill="#0f172a">[Kurir]</text>
  <path d="M 180 510 L 115 510 L 115 560" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="30" y="560" width="170" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="115" y="578" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Kemas Paket, Input Resi &amp;</text>
  <text x="115" y="591" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Cetak Label Pengiriman</text>
  <line x1="115" y1="605" x2="115" y2="635" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="30" y="635" width="170" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="115" y="653" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Perbarui Status Jadi</text>
  <text x="115" y="666" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">"Dikirim"</text>
  <path d="M 200 656 L 530 656" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="635" width="270" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="653" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Konversi Stok "Reserved" Menjadi</text>
  <text x="665" y="666" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Mutasi Keluar Penjualan di Kartu Stok</text>
  <path d="M 665 680 L 665 950" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- SUB-CABANG B: PICK-UP IN STORE (Orthogonal) -->
  <text x="290" y="505" font-size="9" font-weight="bold" fill="#0f172a">[Pick-up]</text>
  <path d="M 270 510 L 335 510 L 335 560" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="250" y="560" width="170" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="335" y="578" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Siapkan di Rak Pick-up &amp;</text>
  <text x="335" y="591" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Ubah Status "Siap Diambil"</text>
  <line x1="335" y1="605" x2="335" y2="710" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pelanggan Datang Ambil -->
  <rect x="250" y="710" width="170" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="335" y="728" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pelanggan Tiba &amp; Tunjukkan</text>
  <text x="335" y="741" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Kode Unik Pengambilan</text>
  <line x1="335" y1="755" x2="335" y2="785" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="250" y="785" width="170" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="335" y="803" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Validasi Kode, Serahkan Barang,</text>
  <text x="335" y="816" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">&amp; Ubah Status Jadi "Selesai"</text>
  <path d="M 420 807 L 530 807" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="785" width="270" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="803" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Konversi Stok "Reserved" Menjadi</text>
  <text x="665" y="816" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Mutasi Keluar Penjualan di Kartu Stok</text>
  <line x1="665" y1="830" x2="665" y2="950" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Akhir Semua Cabang (Orthogonal) -->
  <polygon points="665,950 680,960 665,970 650,960" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="665" y1="970" x2="665" y2="995" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="995" width="270" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="1013" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menampilkan Notifikasi Sukses</text>
  <text x="665" y="1027" text-anchor="middle" font-size="9.5" fill="#0f172a">Pembaruan Siklus Pesanan Berhasil</text>

  <line x1="665" y1="1040" x2="665" y2="1075" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="665" cy="1090" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="665" cy="1090" r="8" fill="#0f172a"/>
</svg>'''

print("Refined 3.12 and 3.13 ready.")

