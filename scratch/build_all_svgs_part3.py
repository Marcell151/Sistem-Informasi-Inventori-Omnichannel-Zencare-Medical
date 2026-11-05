# -*- coding: utf-8 -*-
"""
Part 3: SVGs 3.14 (Penerimaan), 3.15 (Mutasi), 3.16 (Laporan), 3.17 (Stok Opname)
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
      <feDropShadow dx="1" dy="2" stdDeviation="1.5" flood-opacity="0.08"/>
    </filter>
  </defs>'''

def get_svg_314():
    """Gambar 3.14: Kelola Penerimaan Barang & Validasi Logistik"""
    return f'''<svg viewBox="0 0 900 1100" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="880" height="1080" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="880" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="450" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Kelola Penerimaan Barang &amp; Validasi Identitas Medis (Batch/SN)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="430" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="225" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="440" y="45" width="450" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="665" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <line x1="440" y1="45" x2="440" y2="1090" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Admin Init -->
  <circle cx="225" cy="105" r="10" fill="#0f172a"/>
  <line x1="225" y1="115" x2="225" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="140" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="158" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Penerimaan</text>
  <text x="225" y="172" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Barang dari Supplier</text>
  <line x1="225" y1="182" x2="225" y2="210" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="210" width="220" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="228" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Data Dokumen: No Faktur,</text>
  <text x="225" y="241" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tgl Terima, Supplier, Qty &amp; Harga</text>
  <line x1="225" y1="258" x2="225" y2="285" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="225,285 270,305 225,325 180,305" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="225" y="309" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Kategori?</text>

  <!-- Obat -->
  <text x="135" y="300" font-size="9" font-weight="bold" fill="#0f172a">[Obat]</text>
  <path d="M 180 305 L 115 305 L 115 350" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="30" y="350" width="170" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="115" y="368" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Input Nomor Batch &amp;</text>
  <text x="115" y="381" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Tanggal Kedaluwarsa (ED)</text>
  <path d="M 115 392 L 115 425 L 210 425" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Alkes -->
  <text x="285" y="300" font-size="9" font-weight="bold" fill="#0f172a">[Alkes]</text>
  <path d="M 270 305 L 335 305 L 335 350" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="250" y="350" width="170" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="335" y="368" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Input Serial Number</text>
  <text x="335" y="381" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">(SN) Fisik Unik</text>
  <path d="M 335 392 L 335 425 L 240 425" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Form -->
  <polygon points="225,415 240,425 225,435 210,425" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="225" y1="435" x2="225" y2="465" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="465" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="489" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan Tombol "Simpan"</text>
  <path d="M 335 485 L 530 485" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Validasi Medis -->
  <rect x="530" y="465" width="270" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="483" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Validasi Identitas Medis:</text>
  <text x="665" y="496" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Cek Kelengkapan Batch/SN &amp; ED</text>
  <line x1="665" y1="505" x2="665" y2="535" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="665,535 710,555 665,575 620,555" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="665" y="559" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Lolos Valid?</text>

  <!-- Tidak Valid -->
  <text x="720" y="550" font-size="9" font-weight="bold" fill="#dc2626">[Tidak/ED Lampau]</text>
  <path d="M 710 555 L 775 555 L 775 600" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  <rect x="690" y="600" width="170" height="45" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="775" y="618" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Tolak Simpan &amp; Tampilkan</text>
  <text x="775" y="631" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Pesan Kesalahan Medis</text>
  <path d="M 775 645 L 775 680 L 460 680 L 460 234 L 335 234" fill="none" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4" marker-end="url(#arrow-red)"/>

  <!-- Valid -->
  <text x="675" y="595" font-size="9.5" font-weight="bold" fill="#16a34a">[Lolos]</text>
  <line x1="665" y1="575" x2="665" y2="615" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="615" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="633" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Merekam Identitas Batch / SN</text>
  <text x="665" y="646" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">ke Basis Data Persediaan</text>
  <line x1="665" y1="657" x2="665" y2="690" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="690" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="708" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menambahkan Kuantitas</text>
  <text x="665" y="721" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">ke Saldo Stok Utama Gudang</text>
  <line x1="665" y1="732" x2="665" y2="765" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="765" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="783" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Mencatat Mutasi Masuk Logistik</text>
  <text x="665" y="796" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">ke Buku Besar Kartu Stok</text>
  <line x1="665" y1="807" x2="665" y2="840" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="840" width="270" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="858" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerbitkan Instruksi Cetak</text>
  <text x="665" y="871" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Stiker Barcode / Batch Logistik</text>
  <path d="M 530 862 L 335 862" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Admin Cetak Label & Tempel -->
  <rect x="115" y="840" width="220" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="858" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Cetak Stiker Label &amp; Tempelkan</text>
  <text x="225" y="871" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">ke Fisik Dus Kemasan Sebelum ke Rak</text>

  <line x1="225" y1="885" x2="225" y2="925" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="225" cy="940" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="225" cy="940" r="8" fill="#0f172a"/>
</svg>'''

def get_svg_315():
    """Gambar 3.15: Mutasi Stok Manual"""
    return f'''<svg viewBox="0 0 900 1080" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="880" height="1060" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="880" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="450" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Mutasi Stok Manual (Penyesuaian Fisik, Rusak, ED, Retur &amp; Kartu Stok)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="430" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="225" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="440" y="45" width="450" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="665" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <line x1="440" y1="45" x2="440" y2="1070" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Admin Init -->
  <circle cx="225" cy="105" r="10" fill="#0f172a"/>
  <line x1="225" y1="115" x2="225" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="140" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="158" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Mutasi Stok</text>
  <text x="225" y="172" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">&amp; Cari Produk Target</text>
  <line x1="225" y1="182" x2="225" y2="210" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="210" width="220" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="228" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Pilih Jenis (Tambah/Kurang) &amp;</text>
  <text x="225" y="241" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Alasan: Rusak, ED, Selisih, Retur</text>
  <line x1="225" y1="258" x2="225" y2="285" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="225,285 270,305 225,325 180,305" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="225" y="309" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Kategori?</text>

  <!-- Obat -->
  <text x="135" y="300" font-size="9" font-weight="bold" fill="#0f172a">[Obat]</text>
  <path d="M 180 305 L 115 305 L 115 350" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="30" y="350" width="170" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="115" y="368" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Pilih Nomor Batch</text>
  <text x="115" y="381" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Obat yang Terdampak</text>
  <path d="M 115 392 L 115 425 L 210 425" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Alkes -->
  <text x="285" y="300" font-size="9" font-weight="bold" fill="#0f172a">[Alkes]</text>
  <path d="M 270 305 L 335 305 L 335 350" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="250" y="350" width="170" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="335" y="368" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Pilih Serial Number (SN)</text>
  <text x="335" y="381" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Unit yang Terdampak</text>
  <path d="M 335 392 L 335 425 L 240 425" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge -->
  <polygon points="225,415 240,425 225,435 210,425" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="225" y1="435" x2="225" y2="465" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="465" width="220" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="483" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Kuantitas, No Referensi,</text>
  <text x="225" y="496" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">&amp; Catatan Medis Penyesuaian</text>
  <line x1="225" y1="510" x2="225" y2="540" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="540" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="564" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan "Proses Mutasi"</text>
  <path d="M 335 560 L 530 560" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Validasi Kecukupan Stok -->
  <rect x="530" y="540" width="270" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="558" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Validasi Kecukupan Saldo Stok Fisik</text>
  <text x="665" y="571" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">(Khusus Mutasi Pengurangan)</text>
  <line x1="665" y1="580" x2="665" y2="610" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="665,610 710,630 665,650 620,630" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="665" y="634" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Stok Cukup?</text>

  <!-- Tidak Cukup -->
  <text x="720" y="625" font-size="9" font-weight="bold" fill="#dc2626">[Tidak/Minus]</text>
  <path d="M 710 630 L 775 630 L 775 675" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  <rect x="690" y="675" width="170" height="45" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="775" y="693" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Tolak Mutasi &amp; Tampilkan</text>
  <text x="775" y="706" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Peringatan Stok Kurang</text>
  <path d="M 775 720 L 775 750 L 460 750 L 460 487 L 335 487" fill="none" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4" marker-end="url(#arrow-red)"/>

  <!-- Cukup / Penambahan -->
  <text x="675" y="670" font-size="9.5" font-weight="bold" fill="#16a34a">[Cukup/Tambah]</text>
  <line x1="665" y1="650" x2="665" y2="690" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="690" width="270" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="708" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Perbarui Kuantitas Fisik Database</text>
  <text x="665" y="721" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Sesuai Batch/SN Terpilih</text>
  <line x1="665" y1="735" x2="665" y2="765" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="765" width="270" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="783" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Mencatat Mutasi Penyesuaian ke</text>
  <text x="665" y="796" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Kartu Stok Beserta Alasan Medis</text>
  <line x1="665" y1="810" x2="665" y2="840" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="840" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="858" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menampilkan Notifikasi Berhasil</text>
  <text x="665" y="871" text-anchor="middle" font-size="9.5" fill="#0f172a">Penyesuaian Fisik Selesai Dicatat</text>

  <line x1="665" y1="882" x2="665" y2="925" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="665" cy="940" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="665" cy="940" r="8" fill="#0f172a"/>
</svg>'''

def get_svg_316():
    """Gambar 3.16: Output Laporan Manajerial & Logistik"""
    return f'''<svg viewBox="0 0 900 1100" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="880" height="1080" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="880" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="450" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Output (Laporan Kuantitas Manajerial, Logistik Medis &amp; RBAC Gatekeeper)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="430" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="225" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Superadmin (Pemilik)</text>

  <rect x="440" y="45" width="450" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="665" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <line x1="440" y1="45" x2="440" y2="1090" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Superadmin Init -->
  <circle cx="225" cy="105" r="10" fill="#0f172a"/>
  <line x1="225" y1="115" x2="225" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="140" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="158" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Laporan</text>
  <text x="225" y="172" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Kuantitas Logistik</text>
  <path d="M 335 161 L 530 161" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem RBAC Gatekeeper -->
  <rect x="530" y="140" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="158" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Validasi Otoritas Sesi Pengguna</text>
  <text x="665" y="172" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">(RBAC Gatekeeper Sesi)</text>
  <line x1="665" y1="182" x2="665" y2="215" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="665,215 710,235 665,255 620,235" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="665" y="239" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Superadmin?</text>

  <!-- Bukan Superadmin -->
  <text x="720" y="230" font-size="9" font-weight="bold" fill="#dc2626">[Bukan Superadmin]</text>
  <path d="M 710 235 L 775 235 L 775 280" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  <rect x="690" y="280" width="170" height="42" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="775" y="298" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Blokir Akses &amp; Alihkan</text>
  <text x="775" y="311" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">ke Dashboard Utama</text>
  <line x1="775" y1="322" x2="775" y2="350" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="775" cy="362" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="775" cy="362" r="7" fill="#0f172a"/>

  <!-- Superadmin Lolos -->
  <text x="675" y="275" font-size="9.5" font-weight="bold" fill="#16a34a">[Superadmin]</text>
  <line x1="665" y1="255" x2="665" y2="300" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="530" y="300" width="270" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="318" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Buka Akses 4 Modul Laporan Kuantitas:</text>
  <text x="665" y="331" text-anchor="middle" font-size="8.5" fill="#0f172a">Penjualan, Persediaan, Logistik Medis &amp; Mutasi</text>
  <path d="M 530 322 L 335 322" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin Pilih Jenis & Filter -->
  <rect x="115" y="300" width="220" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="318" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Pilih Jenis Laporan Kuantitas &amp;</text>
  <text x="225" y="331" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tentukan Parameter Filter</text>
  <line x1="225" y1="345" x2="225" y2="380" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="115" y="380" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="404" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan Tombol "Filter Data"</text>
  <path d="M 335 401 L 530 401" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Query Database -->
  <rect x="530" y="380" width="270" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="398" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Eksekusi Kueri Agregasi Basis Data &amp;</text>
  <text x="665" y="411" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Sajikan Visualisasi Data Tabular</text>
  <path d="M 530 415 L 335 415 L 335 460" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin Review Data -->
  <rect x="115" y="460" width="220" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="225" y="478" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Meninjau &amp; Menganalisis Data</text>
  <text x="225" y="491" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Rekapitulasi Kuantitas Fisik</text>
  <line x1="225" y1="505" x2="225" y2="540" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="225,540 270,560 225,580 180,560" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="225" y="564" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Cetak Arsip?</text>

  <!-- Tidak Cetak -->
  <text x="135" y="555" font-size="9" font-weight="bold" fill="#0f172a">[Tidak/Cukup Layar]</text>
  <path d="M 180 560 L 100 560 L 100 620" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="25" y="620" width="150" height="40" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="100" y="644" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Selesai Meninjau</text>
  <line x1="100" y1="660" x2="100" y2="700" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="100" cy="712" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="100" cy="712" r="7" fill="#0f172a"/>

  <!-- Ya Cetak -->
  <text x="280" y="555" font-size="9" font-weight="bold" fill="#16a34a">[Ya / Cetak PDF]</text>
  <path d="M 270 560 L 335 560 L 335 620" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="245" y="620" width="180" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="335" y="638" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Tekan Tombol Cetak /</text>
  <text x="335" y="651" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Ekspor Laporan PDF</text>
  <path d="M 425 641 L 530 641" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Terbitkan PDF -->
  <rect x="530" y="620" width="270" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="665" y="638" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Format Dokumen Rekapitulasi Resmi &amp;</text>
  <text x="665" y="651" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Generate Berkas Laporan PDF</text>
  <path d="M 530 655 L 335 655 L 335 700" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin Simpan Arsip -->
  <rect x="245" y="700" width="180" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="335" y="718" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Menyimpan &amp; Mengarsipkan</text>
  <text x="335" y="731" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Dokumen Fisik Manajerial</text>

  <line x1="335" y1="745" x2="335" y2="785" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="335" cy="800" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="335" cy="800" r="8" fill="#0f172a"/>
</svg>'''

def get_svg_317():
    """Gambar 3.17: Kelola Stok Opname & Audit Fisik"""
    return f'''<svg viewBox="0 0 960 1180" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="940" height="1160" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="940" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="480" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Kelola Stok Opname (System Freeze Lock, Blind Count &amp; Berita Acara)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="290" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="155" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Superadmin (Pemilik)</text>

  <rect x="300" y="45" width="310" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="455" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="610" y="45" width="340" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="780" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <line x1="300" y1="45" x2="300" y2="1170" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="610" y1="45" x2="610" y2="1170" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Superadmin Init -->
  <circle cx="155" cy="105" r="10" fill="#0f172a"/>
  <line x1="155" y1="115" x2="155" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="140" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="158" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Stok Opname &amp;</text>
  <text x="155" y="172" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Tentukan Jadwal Sesi Audit</text>
  <line x1="155" y1="182" x2="155" y2="210" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="210" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="228" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Tekan Tombol</text>
  <text x="155" y="242" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">"Buka Sesi Stok Opname"</text>
  <path d="M 255 231 L 640 231" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Freeze Lock -->
  <rect x="640" y="210" width="280" height="48" rx="8" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="780" y="228" text-anchor="middle" font-size="10" font-weight="bold" fill="#dc2626">Inisiasi Sesi Opname Aktif &amp;</text>
  <text x="780" y="241" text-anchor="middle" font-size="10" font-weight="bold" fill="#dc2626">Trigger "System Freeze Lock"</text>
  <text x="780" y="254" text-anchor="middle" font-size="8.5" fill="#dc2626">(Kunci Kasir POS &amp; Checkout Daring)</text>
  <path d="M 640 245 L 560 245 L 560 295 L 550 295" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Admin Cetak Blind Count Sheet -->
  <rect x="350" y="280" width="210" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="455" y="298" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Cetak Lembar Hitung Buta</text>
  <text x="455" y="311" text-anchor="middle" font-size="8.5" fill="#0f172a">(Blind Count Sheet tanpa Saldo Buku)</text>
  <line x1="455" y1="325" x2="455" y2="355" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Admin Hitung Fisik -->
  <rect x="350" y="355" width="210" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="455" y="373" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Lakukan Pencacahan Fisik di Rak</text>
  <text x="455" y="386" text-anchor="middle" font-size="8.5" fill="#0f172a">Etalase Toko &amp; Rak Gudang</text>
  <line x1="455" y1="400" x2="455" y2="430" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Admin Input Hasil Hitung -->
  <rect x="350" y="430" width="210" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="455" y="448" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Hasil Cacahan Fisik ke</text>
  <text x="455" y="461" text-anchor="middle" font-size="8.5" fill="#0f172a">Formulir Lembar Kerja Sistem</text>
  <line x1="455" y1="475" x2="455" y2="505" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="350" y="505" width="210" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="455" y="529" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan "Simpan Data Hitung Fisik"</text>
  <path d="M 560 525 L 640 525" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Hitung Varian Selisih -->
  <rect x="640" y="505" width="280" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="780" y="523" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Kalkulasi Otomatis Deviasi Selisih:</text>
  <text x="780" y="536" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Fisik vs Saldo Buku Sistem</text>
  <text x="780" y="549" text-anchor="middle" font-size="8.5" fill="#0f172a">(Status: Sesuai / Selisih Kurang / Lebih)</text>
  <path d="M 640 540 L 255 540 L 255 595" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin Evaluasi & Catatan -->
  <rect x="55" y="595" width="200" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="613" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Evaluasi Matriks Varian Selisih</text>
  <text x="155" y="626" text-anchor="middle" font-size="9" fill="#0f172a">&amp; Input Catatan Justifikasi Audit</text>
  <line x1="155" y1="643" x2="155" y2="675" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="675" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="693" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tekan "Otorisasi Rekonsiliasi</text>
  <text x="155" y="706" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">&amp; Tutup Sesi Opname"</text>
  <path d="M 255 696 L 640 696" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem Rekonsiliasi, Update & Unfreeze -->
  <rect x="640" y="675" width="280" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="780" y="693" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Penyesuaian Saldo Persediaan Otomatis</text>
  <text x="780" y="706" text-anchor="middle" font-size="9.5" fill="#0f172a">Database Mengikuti Hasil Fisik Riil</text>
  <line x1="780" y1="720" x2="780" y2="750" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="640" y="750" width="280" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="780" y="768" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Membukukan Jurnal Selisih</text>
  <text x="780" y="781" text-anchor="middle" font-size="9.5" fill="#0f172a">ke Buku Besar Riwayat Kartu Stok</text>
  <line x1="780" y1="792" x2="780" y2="825" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="640" y="825" width="280" height="45" rx="8" fill="#f0fdf4" stroke="#16a34a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="780" y="843" text-anchor="middle" font-size="10" font-weight="bold" fill="#16a34a">Lepas Status "System Freeze Lock"</text>
  <text x="780" y="856" text-anchor="middle" font-size="8.5" fill="#16a34a">(POS Kasir &amp; E-Commerce Kembali Aktif)</text>
  <line x1="780" y1="870" x2="780" y2="900" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="640" y="900" width="280" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="780" y="918" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerbitkan Dokumen Resmi</text>
  <text x="780" y="931" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Berita Acara Stok Opname (PDF)</text>
  <path d="M 640 924 L 380 924 L 380 970" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin & Admin Tanda Tangan & Arsip -->
  <rect x="250" y="970" width="260" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="380" y="988" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Superadmin &amp; Admin Menandatangani,</text>
  <text x="380" y="1001" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Mengunduh &amp; Mengarsipkan Berita Acara</text>

  <line x1="380" y1="1018" x2="380" y2="1060" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="380" cy="1075" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="380" cy="1075" r="8" fill="#0f172a"/>
</svg>'''

print("Diagrams 3.14 to 3.17 generators ready.")

