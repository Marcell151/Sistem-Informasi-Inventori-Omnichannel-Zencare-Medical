# -*- coding: utf-8 -*-
"""
Builder for Diagram 3.12 (Kasir POS) and Diagram 3.13 (Kelola Pesanan Daring)
Strictly orthogonal, valid XML, zero farmasi, exact draw.io aesthetic.
"""

from final_builder_common import DEFS

def get_diagram_312():
    return f'''<svg viewBox="0 0 980 1360" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1340" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Pesanan Luring (Transaksi Kasir / POS &amp; FEFO)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="260" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="140" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan Toko</text>

  <rect x="270" y="45" width="360" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="450" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Kasir</text>

  <rect x="630" y="45" width="340" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="800" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem POS</text>

  <!-- Dividers -->
  <line x1="270" y1="45" x2="270" y2="1350" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="630" y1="45" x2="630" y2="1350" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= INITIAL STEP ================= -->
  <!-- Kasir opens POS -->
  <circle cx="450" cy="100" r="10" fill="#0f172a"/>
  <line x1="450" y1="110" x2="450" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="350" y="135" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="153" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Terminal Kasir</text>
  <text x="450" y="167" text-anchor="middle" font-size="10.5" fill="#0f172a">(pos/pos.php)</text>

  <!-- Pelanggan brings items -->
  <circle cx="140" cy="100" r="10" fill="#0f172a"/>
  <line x1="140" y1="110" x2="140" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="40" y="135" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="140" y="153" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membawa Barang Belanjaan</text>
  <text x="140" y="167" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">ke Meja Kasir</text>
  <!-- To Kasir (Orthogonal) -->
  <path d="M 240 156 L 350 156" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir Scans Barcode / SKU -->
  <line x1="450" y1="177" x2="450" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="350" y="205" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="223" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Pindai Barcode Kemasan</text>
  <text x="450" y="237" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">atau Input SKU Produk</text>
  <!-- To Sistem (Orthogonal) -->
  <path d="M 550 226 L 690 226" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem displays details & FEFO recommendation -->
  <rect x="690" y="205" width="220" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="800" y="222" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tampilkan Detail Produk,</text>
  <text x="800" y="235" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Dual-UOM (Strip vs Box), &amp;</text>
  <text x="800" y="248" text-anchor="middle" font-size="9" fill="#0f172a">Rekomendasi FEFO / Serial Number</text>
  <!-- Back to Kasir (Orthogonal) -->
  <path d="M 800 249 L 800 285 L 550 285" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir picks item -->
  <rect x="350" y="265" width="200" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="282" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Pilih Satuan Jual &amp;</text>
  <text x="450" y="296" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Ambil Fisik Kemasan</text>
  <line x1="450" y1="309" x2="450" y2="335" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Sesuai FEFO? -->
  <polygon points="450,335 490,355 450,375 410,355" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="450" y="359" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Sesuai FEFO?</text>

  <!-- Fisik Berbeda / Bypass (Orthogonal: right to x=540, down to y=400) -->
  <text x="500" y="348" font-size="8.5" font-weight="bold" fill="#dc2626">[Fisik Beda]</text>
  <path d="M 490 355 L 540 355 L 540 400" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  <rect x="470" y="400" width="140" height="40" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="540" y="417" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pilih Batch Fisik</text>
  <text x="540" y="430" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">yang Terambil</text>
  <!-- To Sistem Log Anomali -->
  <path d="M 610 420 L 690 420" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="690" y="400" width="220" height="40" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="800" y="417" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Rekam Log Anomali FEFO</text>
  <text x="800" y="430" text-anchor="middle" font-size="8.5" fill="#0f172a">Otomatis (Tanpa Setop Transaksi)</text>
  <!-- From Log back to Kasir Merge (Orthogonal) -->
  <path d="M 800 440 L 800 470 L 465 470" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Sesuai FEFO (Straight down) -->
  <text x="415" y="395" font-size="8.5" font-weight="bold" fill="#16a34a">[Sesuai]</text>
  <line x1="450" y1="375" x2="450" y2="460" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Merge FEFO -->
  <polygon points="450,460 465,470 450,480 435,470" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="450" y1="480" x2="450" y2="505" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Ada Barang Lain? -->
  <polygon points="450,505 490,525 450,545 410,525" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="450" y="529" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Barang Lain?</text>

  <!-- [Ya] Loopback to Pindai Barcode (Orthogonal: left along y=525 to x=320, up to y=226, right to x=350) -->
  <text x="375" y="518" font-size="8.5" font-weight="bold" fill="#0f172a">[Ya]</text>
  <path d="M 410 525 L 320 525 L 320 226 L 350 226" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- [Tidak] Proceed to Payment -->
  <text x="460" y="560" font-size="8.5" font-weight="bold" fill="#0f172a">[Tidak]</text>
  <line x1="450" y1="545" x2="450" y2="575" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="350" y="575" width="200" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="599" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Tekan "Proses Pembayaran"</text>
  <!-- To Sistem (Orthogonal) -->
  <path d="M 550 595 L 690 595" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem displays total bill -->
  <rect x="690" y="575" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="800" y="592" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Hitung Total Belanja &amp;</text>
  <text x="800" y="605" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tampilkan Ringkasan Tagihan</text>
  <!-- To Pelanggan (Orthogonal) -->
  <path d="M 800 615 L 800 645 L 140 645 L 140 660" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pelanggan hands over payment -->
  <rect x="40" y="660" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="140" y="678" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menyerahkan Pembayaran</text>
  <text x="140" y="692" text-anchor="middle" font-size="9.5" fill="#0f172a">(Uang Tunai atau QRIS)</text>
  <!-- To Kasir (Orthogonal) -->
  <path d="M 240 681 L 350 681" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir receives payment & selects method -->
  <rect x="350" y="660" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="678" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menerima Pembayaran &amp;</text>
  <text x="450" y="692" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Pilih Metode di Terminal</text>
  <line x1="450" y1="702" x2="450" y2="730" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Metode Pembayaran? -->
  <polygon points="450,730 490,750 450,770 410,750" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="450" y="754" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Metode?</text>

  <!-- Tunai Branch (Orthogonal: left to x=375, down to y=800) -->
  <text x="365" y="742" font-size="9" font-weight="bold" fill="#0f172a">[Tunai]</text>
  <path d="M 410 750 L 375 750 L 375 800" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="315" y="800" width="120" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="375" y="818" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Input Nominal</text>
  <text x="375" y="832" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Uang Diterima</text>
  <!-- To Sistem (Orthogonal) -->
  <path d="M 435 821 L 670 821" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem: Validate Cash >= Total -->
  <rect x="670" y="800" width="160" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="750" y="817" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Validasi Nominal Uang</text>
  <text x="750" y="830" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">terhadap Total Tagihan</text>
  <line x1="750" y1="842" x2="750" y2="865" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Uang Cukup? -->
  <polygon points="750,865 790,885 750,905 710,885" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="750" y="889" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Uang Cukup?</text>

  <!-- [Tidak] Loop back to input nominal (Orthogonal) -->
  <text x="795" y="880" font-size="8" font-weight="bold" fill="#dc2626">[Tidak]</text>
  <path d="M 790 885 L 850 885 L 850 780 L 375 780 L 375 800" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <!-- [Ya] Hitung kembalian (Orthogonal straight down) -->
  <text x="755" y="920" font-size="8" font-weight="bold" fill="#16a34a">[Ya]</text>
  <line x1="750" y1="905" x2="750" y2="935" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="670" y="935" width="160" height="38" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="750" y="952" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Hitung Nilai Uang</text>
  <text x="750" y="964" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Kembalian Otomatis</text>
  <!-- To Merge (Orthogonal) -->
  <path d="M 750 973 L 750 1000 L 465 1000" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- QRIS Branch (Orthogonal: right to x=525, down to y=885) -->
  <text x="500" y="742" font-size="9" font-weight="bold" fill="#0f172a">[QRIS]</text>
  <path d="M 490 750 L 525 750 L 525 885" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="460" y="885" width="130" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="525" y="903" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Verifikasi Transaksi</text>
  <text x="525" y="916" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Digital Sukses</text>
  <!-- To Merge (Orthogonal) -->
  <path d="M 525 927 L 525 1000 L 465 1000" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Payment Method -->
  <polygon points="450,990 465,1000 450,1010 435,1000" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="450" y1="1010" x2="450" y2="1035" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir clicks Selesaikan Transaksi -->
  <rect x="350" y="1035" width="200" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="1053" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan "Selesaikan Transaksi"</text>
  <text x="450" y="1066" text-anchor="middle" font-size="9" fill="#0f172a">&amp; Konfirmasi Pembayaran</text>
  <!-- To Sistem (Orthogonal) -->
  <path d="M 550 1055 L 690 1055" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem updates stock & stock card -->
  <rect x="690" y="1035" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="800" y="1052" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Potong Stok Fisik Database</text>
  <text x="800" y="1065" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Sesuai Batch/SN Terpilih</text>
  <line x1="800" y1="1075" x2="800" y2="1100" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="690" y="1100" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="800" y="1117" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Catat Mutasi Keluar Penjualan</text>
  <text x="800" y="1130" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">ke Riwayat Kartu Stok</text>
  <line x1="800" y1="1140" x2="800" y2="1165" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem prints receipt -->
  <rect x="690" y="1165" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="800" y="1182" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Rekam Riwayat Kasir Hari Ini &amp;</text>
  <text x="800" y="1196" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Cetak Struk Belanja Thermal</text>
  <!-- To Kasir (Orthogonal) -->
  <path d="M 690 1186 L 550 1186" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Kasir hands over receipt & goods -->
  <rect x="350" y="1165" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="1182" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menyerahkan Struk Belanja &amp;</text>
  <text x="450" y="1196" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Fisik Barang ke Pelanggan</text>
  <!-- To Pelanggan (Orthogonal) -->
  <path d="M 350 1186 L 240 1186" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pelanggan receives items -->
  <rect x="40" y="1165" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="140" y="1182" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerima Struk &amp;</text>
  <text x="140" y="1196" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Barang Belanjaan</text>

  <!-- Final Node -->
  <line x1="140" y1="1207" x2="140" y2="1245" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="140" cy="1258" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="140" cy="1258" r="8" fill="#0f172a"/>
</svg>'''

def get_diagram_313():
    return f'''<svg viewBox="0 0 980 1280" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1260" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Kelola Pesanan Daring &amp; Pembatalan (Picking List &amp; Release Reserved)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="410" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="215" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="420" y="45" width="550" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="695" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <!-- Divider -->
  <line x1="420" y1="45" x2="420" y2="1270" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= INITIAL STEP ================= -->
  <circle cx="215" cy="100" r="10" fill="#0f172a"/>
  <line x1="215" y1="110" x2="215" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="135" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="153" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Transaksi &amp;</text>
  <text x="215" y="167" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Pilih Tab "E-Commerce"</text>
  <!-- To Sistem (Orthogonal) -->
  <path d="M 325 156 L 560 156" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem displays queue & picking instruction -->
  <rect x="560" y="135" width="270" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="153" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menampilkan Antrean Pesanan "Diproses"</text>
  <text x="695" y="167" text-anchor="middle" font-size="9.5" fill="#0f172a">&amp; Panduan Identitas Logistik (Batch/SN)</text>
  <!-- Back to Admin (Orthogonal) -->
  <path d="M 695 179 L 695 210 L 325 210" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Perlu Siapkan Fisik? -->
  <rect x="105" y="190" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="214" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Evaluasi Status Persiapan Fisik</text>
  <line x1="215" y1="230" x2="215" y2="255" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="215,255 265,275 215,295 165,275" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="215" y="279" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pesanan Baru?</text>

  <!-- [Ya] Print Picking List & Prepare Goods (Orthogonal: left to x=115, down to y=325) -->
  <text x="130" y="268" font-size="8.5" font-weight="bold" fill="#0f172a">[Ya]</text>
  <path d="M 165 275 L 115 275 L 115 325" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="35" y="325" width="160" height="46" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="115" y="343" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Cetak Picking List &amp; Siapkan</text>
  <text x="115" y="356" text-anchor="middle" font-size="8.5" fill="#0f172a">Fisik Barang dari Rak</text>
  <!-- To Merge (Orthogonal) -->
  <path d="M 115 371 L 115 405 L 200 405" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- [Tidak] Bypass (Orthogonal: right to x=285, down to y=405) -->
  <text x="270" y="268" font-size="8.5" font-weight="bold" fill="#0f172a">[Tidak]</text>
  <path d="M 265 275 L 285 275 L 285 405 L 230 405" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Picking Decision -->
  <polygon points="215,395 230,405 215,415 200,405" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="215" y1="415" x2="215" y2="445" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Tindakan Operasional? -->
  <polygon points="215,445 275,470 215,495 155,470" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="215" y="474" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Tindakan?</text>

  <!-- ================= BRANCH 1: BATALKAN PESANAN ================= -->
  <text x="105" y="462" font-size="8.5" font-weight="bold" fill="#dc2626">[Batalkan Pesanan]</text>
  <!-- Orthogonal: left along y=470 to x=80, down to y=525 -->
  <path d="M 155 470 L 80 470 L 80 525" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="20" y="525" width="125" height="46" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="82" y="543" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Pilih Opsi "Batalkan"</text>
  <text x="82" y="556" text-anchor="middle" font-size="8" fill="#dc2626">(Pick-up Kedaluwarsa)</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 145 548 L 560 548" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <!-- Sistem: Release Reserved Stock -->
  <rect x="560" y="525" width="270" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="543" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Lepas Kuncian Stok "Reserved"</text>
  <text x="695" y="557" text-anchor="middle" font-size="9" fill="#0f172a">Kembali Menjadi Stok Aktif Toko (Restore)</text>
  <line x1="695" y1="571" x2="695" y2="600" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="600" width="270" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="618" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Perbarui Status Jadi "Dibatalkan"</text>
  <text x="695" y="632" text-anchor="middle" font-size="9" fill="#0f172a">&amp; Kirim Notifikasi Pembatalan ke Pembeli</text>

  <!-- Final Node for Cancel Branch -->
  <line x1="695" y1="644" x2="695" y2="675" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="695" cy="687" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="695" cy="687" r="7" fill="#0f172a"/>

  <!-- ================= BRANCH 2: LANJUT PEMENUHAN ================= -->
  <text x="270" y="485" font-size="8.5" font-weight="bold" fill="#16a34a">[Lanjut Pemenuhan]</text>
  <line x1="215" y1="495" x2="215" y2="535" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Metode Pengambilan? -->
  <polygon points="215,535 265,555 215,575 165,555" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="215" y="559" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Metode?</text>

  <!-- 1. Kurir Ekspedisi (Orthogonal: left to x=150, down to y=620) -->
  <text x="145" y="590" font-size="9" font-weight="bold" fill="#0f172a">[Kurir]</text>
  <path d="M 165 555 L 150 555 L 150 620" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="70" y="620" width="160" height="46" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="638" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Kemas Paket, Input Resi &amp;</text>
  <text x="150" y="651" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Cetak Label Pengiriman</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 230 643 L 560 643" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="620" width="270" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="638" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Perbarui Status Jadi "Dikirim"</text>
  <text x="695" y="651" text-anchor="middle" font-size="9" fill="#0f172a">&amp; Rekam No Resi Pengiriman ke Database</text>
  <line x1="695" y1="666" x2="695" y2="700" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="700" width="270" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="718" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Konversi Stok "Reserved" Menjadi</text>
  <text x="695" y="731" text-anchor="middle" font-size="9" fill="#0f172a">Mutasi Keluar Penjualan di Kartu Stok</text>
  <!-- To Final Merge (Orthogonal) -->
  <path d="M 695 744 L 695 1060 L 695 1075" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- 2. Pick-up in Store (Orthogonal: right to x=280, down to y=760) -->
  <text x="260" y="590" font-size="9" font-weight="bold" fill="#0f172a">[Pick-up]</text>
  <path d="M 265 555 L 280 555 L 280 760" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="200" y="760" width="160" height="44" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="280" y="778" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Siapkan di Rak Pick-up &amp;</text>
  <text x="280" y="791" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Ubah Status "Siap Diambil"</text>
  <line x1="280" y1="804" x2="280" y2="835" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="200" y="835" width="160" height="44" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="280" y="853" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pelanggan Tiba &amp; Tunjukkan</text>
  <text x="280" y="866" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Kode Unik Pengambilan</text>
  <line x1="280" y1="879" x2="280" y2="910" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="200" y="910" width="160" height="46" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="280" y="928" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Validasi Kode, Serahkan Barang,</text>
  <text x="280" y="941" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">&amp; Ubah Status Jadi "Selesai"</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 360 933 L 560 933" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="910" width="270" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="928" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Konversi Stok "Reserved" Menjadi</text>
  <text x="695" y="941" text-anchor="middle" font-size="9" fill="#0f172a">Mutasi Keluar Penjualan di Kartu Stok</text>
  <!-- To Final Merge (Orthogonal) -->
  <path d="M 695 956 L 695 1075" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Final Merge Diamond -->
  <polygon points="695,1075 710,1085 695,1095 680,1085" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="695" y1="1095" x2="695" y2="1125" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem displays success notification -->
  <rect x="560" y="1125" width="270" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="1143" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menampilkan Notifikasi Sukses:</text>
  <text x="695" y="1157" text-anchor="middle" font-size="9" fill="#0f172a">Pembaruan Siklus Pesanan Selesai</text>

  <!-- Final Node -->
  <line x1="695" y1="1169" x2="695" y2="1205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="695" cy="1218" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="695" cy="1218" r="8" fill="#0f172a"/>
</svg>'''

if __name__ == '__main__':
    print("Part 2 functions compiled successfully.")

