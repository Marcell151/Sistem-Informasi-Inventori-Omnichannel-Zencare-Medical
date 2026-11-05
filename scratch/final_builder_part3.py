# -*- coding: utf-8 -*-
"""
Builder for Diagrams 3.14 to 3.17
Strictly orthogonal, valid XML, zero farmasi, exact draw.io aesthetic.
Updated Diagram 3.16 to include all 6 Report Modules (including Laporan Audit Anomali FEFO).
"""

from final_builder_common import DEFS

def get_diagram_314():
    return f'''<svg viewBox="0 0 980 1200" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1180" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Kelola Penerimaan Barang &amp; Validasi Identitas Medis (Batch/SN)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="410" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="215" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="420" y="45" width="550" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="695" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <!-- Divider -->
  <line x1="420" y1="45" x2="420" y2="1190" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= ADMIN: INPUT PENGADAAN ================= -->
  <circle cx="215" cy="100" r="10" fill="#0f172a"/>
  <line x1="215" y1="110" x2="215" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="135" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="153" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Penerimaan</text>
  <text x="215" y="167" text-anchor="middle" font-size="10.5" fill="#0f172a">Barang dari Supplier</text>
  <line x1="215" y1="177" x2="215" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="205" width="220" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="223" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Input Data Dokumen: No Faktur,</text>
  <text x="215" y="237" text-anchor="middle" font-size="10" fill="#0f172a">Tgl Terima, Supplier, Qty &amp; Harga</text>
  <line x1="215" y1="249" x2="215" y2="275" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Kategori? -->
  <polygon points="215,275 255,295 215,315 175,295" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="215" y="299" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Kategori?</text>

  <!-- Obat Branch (Orthogonal: left to x=135, down to y=345) -->
  <text x="145" y="288" font-size="9" font-weight="bold" fill="#0f172a">[Obat]</text>
  <path d="M 175 295 L 135 295 L 135 345" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="65" y="345" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="135" y="363" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Input Nomor Batch &amp;</text>
  <text x="135" y="376" text-anchor="middle" font-size="8.5" fill="#0f172a">Tanggal Kedaluwarsa (ED)</text>
  <!-- To Merge (Orthogonal) -->
  <path d="M 135 387 L 135 420 L 200 420" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Alkes Branch (Orthogonal: right to x=295, down to y=345) -->
  <text x="260" y="288" font-size="9" font-weight="bold" fill="#0f172a">[Alkes]</text>
  <path d="M 255 295 L 295 295 L 295 345" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="225" y="345" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="295" y="363" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Input Serial Number</text>
  <text x="295" y="376" text-anchor="middle" font-size="8.5" fill="#0f172a">(SN) Fisik Unik</text>
  <!-- To Merge (Orthogonal) -->
  <path d="M 295 387 L 295 420 L 230 420" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Kategori -->
  <polygon points="215,410 230,420 215,430 200,420" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="215" y1="430" x2="215" y2="455" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Admin presses save -->
  <rect x="105" y="455" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="479" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Tekan Tombol "Simpan"</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 325 475 L 560 475" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= SISTEM: VALIDASI MUTU & RECORDING ================= -->
  <rect x="560" y="455" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="472" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Validasi Identitas Medis:</text>
  <text x="695" y="485" text-anchor="middle" font-size="9" fill="#0f172a">Cek Kelengkapan Batch/SN &amp; ED &gt;= 180 Hari</text>
  <line x1="695" y1="497" x2="695" y2="525" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Lolos Validasi Mutu? -->
  <polygon points="695,525 745,545 695,565 645,545" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="695" y="549" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Lolos Valid?</text>

  <!-- [Tidak / ED Lampau] (Orthogonal: right to x=850, up to y=430) -->
  <text x="750" y="538" font-size="8.5" font-weight="bold" fill="#dc2626">[Tidak/ED Lampau]</text>
  <path d="M 745 545 L 850 545 L 850 430" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  <rect x="770" y="390" width="160" height="40" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="850" y="407" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Tolak Simpan &amp; Tampilkan</text>
  <text x="850" y="420" text-anchor="middle" font-size="8.5" fill="#dc2626">Pesan Kesalahan Medis</text>
  <!-- Loop back to Admin input (Orthogonal) -->
  <path d="M 850 390 L 850 225 L 325 225" fill="none" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4" marker-end="url(#arrow-red)"/>

  <!-- [Lolos] (Orthogonal straight down) -->
  <text x="705" y="585" font-size="9" font-weight="bold" fill="#16a34a">[Lolos]</text>
  <line x1="695" y1="565" x2="695" y2="600" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="600" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="618" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Merekam Identitas Batch / SN</text>
  <text x="695" y="632" text-anchor="middle" font-size="9" fill="#0f172a">ke Basis Data Persediaan</text>
  <line x1="695" y1="642" x2="695" y2="670" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="670" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="688" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menambahkan Kuantitas</text>
  <text x="695" y="702" text-anchor="middle" font-size="9" fill="#0f172a">ke Saldo Stok Utama Gudang</text>
  <line x1="695" y1="712" x2="695" y2="740" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="740" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="758" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Mencatat Mutasi Masuk Logistik</text>
  <text x="695" y="772" text-anchor="middle" font-size="9" fill="#0f172a">ke Buku Besar Kartu Stok</text>
  <line x1="695" y1="782" x2="695" y2="810" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="810" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="828" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerbitkan Instruksi Cetak</text>
  <text x="695" y="842" text-anchor="middle" font-size="9" fill="#0f172a">Stiker Barcode / Batch Logistik</text>
  <!-- To Admin (Orthogonal: left to x=325) -->
  <path d="M 560 831 L 325 831" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Admin prints & sticks label -->
  <rect x="105" y="810" width="220" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="828" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Cetak Stiker Label &amp; Tempelkan</text>
  <text x="215" y="842" text-anchor="middle" font-size="9" fill="#0f172a">ke Fisik Dus Kemasan Sebelum ke Rak</text>

  <!-- Final Node -->
  <line x1="215" y1="854" x2="215" y2="890" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="215" cy="903" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="215" cy="903" r="8" fill="#0f172a"/>
</svg>'''

def get_diagram_315():
    return f'''<svg viewBox="0 0 980 1160" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1140" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Mutasi Stok Manual (Penyesuaian Fisik, Rusak, ED, Retur &amp; Kartu Stok)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="410" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="215" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="420" y="45" width="550" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="695" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <!-- Divider -->
  <line x1="420" y1="45" x2="420" y2="1150" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= ADMIN: FORM MUTASI ================= -->
  <circle cx="215" cy="100" r="10" fill="#0f172a"/>
  <line x1="215" y1="110" x2="215" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="135" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="153" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Mutasi Stok</text>
  <text x="215" y="167" text-anchor="middle" font-size="10.5" fill="#0f172a">&amp; Cari Produk Target</text>
  <line x1="215" y1="177" x2="215" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="205" width="220" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="223" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Pilih Jenis (Tambah/Kurang) &amp;</text>
  <text x="215" y="237" text-anchor="middle" font-size="10" fill="#0f172a">Alasan: Rusak, ED, Selisih, Retur</text>
  <line x1="215" y1="249" x2="215" y2="275" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Kategori Produk? -->
  <polygon points="215,275 255,295 215,315 175,295" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="215" y="299" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Kategori?</text>

  <!-- Obat Branch (Orthogonal: left to x=135, down to y=345) -->
  <text x="145" y="288" font-size="9" font-weight="bold" fill="#0f172a">[Obat]</text>
  <path d="M 175 295 L 135 295 L 135 345" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="65" y="345" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="135" y="363" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pilih Nomor Batch</text>
  <text x="135" y="376" text-anchor="middle" font-size="8.5" fill="#0f172a">Obat yang Terdampak</text>
  <!-- To Merge (Orthogonal) -->
  <path d="M 135 387 L 135 420 L 200 420" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Alkes Branch (Orthogonal: right to x=295, down to y=345) -->
  <text x="260" y="288" font-size="9" font-weight="bold" fill="#0f172a">[Alkes]</text>
  <path d="M 255 295 L 295 295 L 295 345" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="225" y="345" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="295" y="363" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pilih Serial Number (SN)</text>
  <text x="295" y="376" text-anchor="middle" font-size="8.5" fill="#0f172a">Unit yang Terdampak</text>
  <!-- To Merge (Orthogonal) -->
  <path d="M 295 387 L 295 420 L 230 420" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Kategori -->
  <polygon points="215,410 230,420 215,430 200,420" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="215" y1="430" x2="215" y2="455" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Input details & submit -->
  <rect x="105" y="455" width="220" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="473" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Kuantitas, No Referensi,</text>
  <text x="215" y="487" text-anchor="middle" font-size="9" fill="#0f172a">&amp; Catatan Medis Penyesuaian</text>
  <line x1="215" y1="499" x2="215" y2="525" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="525" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="549" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Tekan "Proses Mutasi"</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 325 545 L 560 545" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= SISTEM: VALIDASI STOK & REKAM ================= -->
  <rect x="560" y="525" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="542" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Validasi Kecukupan Saldo Stok Fisik</text>
  <text x="695" y="555" text-anchor="middle" font-size="8.5" fill="#0f172a">(Khusus Mutasi Pengurangan)</text>
  <line x1="695" y1="567" x2="695" y2="595" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Stok Cukup? -->
  <polygon points="695,595 745,615 695,635 645,615" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="695" y="619" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Stok Cukup?</text>

  <!-- [Tidak / Minus] (Orthogonal: right to x=850, up to y=500) -->
  <text x="750" y="608" font-size="8.5" font-weight="bold" fill="#dc2626">[Tidak/Minus]</text>
  <path d="M 745 615 L 850 615 L 850 500" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  <rect x="770" y="460" width="160" height="40" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="850" y="477" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Tolak Mutasi &amp; Tampilkan</text>
  <text x="850" y="490" text-anchor="middle" font-size="8.5" fill="#dc2626">Peringatan Stok Kurang</text>
  <!-- Loop back to Admin input (Orthogonal) -->
  <path d="M 850 460 L 850 475 L 325 475" fill="none" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4" marker-end="url(#arrow-red)"/>

  <!-- [Cukup / Tambah] (Orthogonal straight down) -->
  <text x="705" y="655" font-size="9" font-weight="bold" fill="#16a34a">[Cukup/Tambah]</text>
  <line x1="695" y1="635" x2="695" y2="670" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="670" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="688" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Perbarui Kuantitas Fisik Database</text>
  <text x="695" y="702" text-anchor="middle" font-size="9" fill="#0f172a">Sesuai Batch/SN Terpilih</text>
  <line x1="695" y1="712" x2="695" y2="740" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="740" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="758" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Mencatat Mutasi Penyesuaian ke</text>
  <text x="695" y="772" text-anchor="middle" font-size="9" fill="#0f172a">Kartu Stok Beserta Alasan Medis</text>
  <line x1="695" y1="782" x2="695" y2="810" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="560" y="810" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="828" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menampilkan Notifikasi Berhasil:</text>
  <text x="695" y="842" text-anchor="middle" font-size="9" fill="#0f172a">Penyesuaian Fisik Selesai Dicatat</text>

  <!-- Final Node -->
  <line x1="695" y1="852" x2="695" y2="890" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="695" cy="903" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="695" cy="903" r="8" fill="#0f172a"/>
</svg>'''

def get_diagram_316():
    return f'''<svg viewBox="0 0 980 1260" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1240" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Output (Laporan Manajerial, Audit Logistik &amp; RBAC Gatekeeper)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="410" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="215" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Superadmin (Pemilik)</text>

  <rect x="420" y="45" width="550" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="695" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <!-- Divider -->
  <line x1="420" y1="45" x2="420" y2="1250" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= INITIAL STEP: ACCESS REPORT ================= -->
  <circle cx="215" cy="100" r="10" fill="#0f172a"/>
  <line x1="215" y1="110" x2="215" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="135" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="153" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Membuka Menu Laporan</text>
  <text x="215" y="167" text-anchor="middle" font-size="10.5" fill="#0f172a">Kuantitas &amp; Audit Logistik</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 325 156 L 560 156" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem: RBAC Gatekeeper -->
  <rect x="560" y="135" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="153" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Validasi Otoritas Sesi Pengguna</text>
  <text x="695" y="167" text-anchor="middle" font-size="9" fill="#0f172a">(RBAC Gatekeeper Sesi)</text>
  <line x1="695" y1="177" x2="695" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Superadmin? -->
  <polygon points="695,205 750,225 695,245 640,225" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="695" y="229" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Superadmin?</text>

  <!-- [Bukan Superadmin] (Orthogonal: right to x=860, down to y=280) -->
  <text x="760" y="218" font-size="8.5" font-weight="bold" fill="#dc2626">[Bukan Superadmin]</text>
  <path d="M 750 225 L 860 225 L 860 280" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  <rect x="785" y="280" width="150" height="40" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="860" y="297" text-anchor="middle" font-size="8" font-weight="bold" fill="#dc2626">Blokir Akses &amp; Alihkan</text>
  <text x="860" y="309" text-anchor="middle" font-size="8" fill="#dc2626">ke Dashboard Utama</text>
  <line x1="860" y1="320" x2="860" y2="355" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="860" cy="367" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="860" cy="367" r="7" fill="#0f172a"/>

  <!-- [Superadmin] (Orthogonal straight down) -->
  <text x="705" y="265" font-size="9" font-weight="bold" fill="#16a34a">[Superadmin]</text>
  <line x1="695" y1="245" x2="695" y2="280" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- THE 6 REPORTS BOX -->
  <rect x="525" y="280" width="340" height="110" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="299" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Buka Akses 6 Modul Pelaporan Manajerial:</text>
  <text x="695" y="313" text-anchor="middle" font-size="8" fill="#0f172a">1. Lap. Penjualan Omnichannel (POS vs Daring)</text>
  <text x="695" y="325" text-anchor="middle" font-size="8" fill="#0f172a">2. Lap. Persediaan Stok (Kuantitas &amp; Batas Min)</text>
  <text x="695" y="337" text-anchor="middle" font-size="8" fill="#0f172a">3. Lap. Logistik Medis (Batch, ED &amp; Jejak SN)</text>
  <text x="695" y="349" text-anchor="middle" font-size="8" fill="#0f172a">4. Lap. Penerimaan Barang dari Supplier</text>
  <text x="695" y="361" text-anchor="middle" font-size="8" fill="#0f172a">5. Lap. Mutasi &amp; Konversi UOM (Bongkar Box)</text>
  <text x="695" y="375" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">6. Lap. Audit Anomali FEFO Kasir</text>
  <!-- To Superadmin (Orthogonal: left from x=525 to x=325) -->
  <path d="M 525 335 L 325 335" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin selects report & filters -->
  <rect x="105" y="310" width="220" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="328" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Pilih Kategori dari 6 Laporan &amp;</text>
  <text x="215" y="342" text-anchor="middle" font-size="9" fill="#0f172a">Tentukan Parameter Filter</text>
  <line x1="215" y1="358" x2="215" y2="390" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="105" y="390" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="414" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Tekan Tombol "Filter Data"</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 325 410 L 560 410" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem queries & renders data -->
  <rect x="560" y="390" width="270" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="408" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Eksekusi Kueri Agregasi Basis Data &amp;</text>
  <text x="695" y="421" text-anchor="middle" font-size="9" fill="#0f172a">Sajikan Visualisasi Data Tabular</text>
  <!-- Back to Superadmin (Orthogonal: left to x=325) -->
  <path d="M 695 434 L 695 475 L 325 475" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin reviews data -->
  <rect x="105" y="455" width="220" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="215" y="473" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Meninjau &amp; Menganalisis Data</text>
  <text x="215" y="486" text-anchor="middle" font-size="9" fill="#0f172a">Rekapitulasi Kuantitas Fisik</text>
  <line x1="215" y1="497" x2="215" y2="525" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Cetak Arsip? -->
  <polygon points="215,525 260,545 215,565 170,545" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="215" y="549" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Cetak Arsip?</text>

  <!-- [Tidak / Cukup Layar] (Orthogonal: left to x=100, down to y=600) -->
  <text x="85" y="538" font-size="8.5" font-weight="bold" fill="#0f172a">[Tidak/Cukup Layar]</text>
  <path d="M 170 545 L 100 545 L 100 600" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="35" y="600" width="130" height="38" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="100" y="623" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Selesai Meninjau</text>
  <line x1="100" y1="638" x2="100" y2="665" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="100" cy="677" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="100" cy="677" r="7" fill="#0f172a"/>

  <!-- [Ya / Cetak PDF] (Orthogonal: right to x=280, down to y=600) -->
  <text x="250" y="538" font-size="8.5" font-weight="bold" fill="#16a34a">[Ya / Cetak PDF]</text>
  <path d="M 260 545 L 280 545 L 280 600" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="205" y="600" width="150" height="40" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="280" y="617" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Tekan Tombol Cetak /</text>
  <text x="280" y="630" text-anchor="middle" font-size="8.5" fill="#0f172a">Ekspor Laporan PDF</text>
  <!-- To Sistem (Orthogonal: right to x=560) -->
  <path d="M 355 620 L 560 620" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem generates PDF -->
  <rect x="560" y="600" width="270" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="695" y="617" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Format Dokumen Rekapitulasi Resmi &amp;</text>
  <text x="695" y="630" text-anchor="middle" font-size="9" fill="#0f172a">Generate Berkas Laporan PDF</text>
  <!-- Back to Superadmin (Orthogonal: left to x=280) -->
  <path d="M 695 642 L 695 685 L 280 685 L 280 710" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin archives PDF -->
  <rect x="205" y="710" width="150" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="280" y="728" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Menyimpan &amp; Mengarsipkan</text>
  <text x="280" y="741" text-anchor="middle" font-size="8.5" fill="#0f172a">Dokumen Fisik Manajerial</text>

  <!-- Final Node -->
  <line x1="280" y1="752" x2="280" y2="785" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="280" cy="797" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="280" cy="797" r="7" fill="#0f172a"/>
</svg>'''

def get_diagram_317():
    return f'''<svg viewBox="0 0 980 1340" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1320" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Kelola Stok Opname (System Freeze Lock, Blind Count &amp; Berita Acara)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="280" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="150" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Superadmin (Pemilik)</text>

  <rect x="290" y="45" width="320" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="450" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Admin (Staff Logistik)</text>

  <rect x="610" y="45" width="360" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="790" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem Back-Office</text>

  <!-- Dividers -->
  <line x1="290" y1="45" x2="290" y2="1330" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="610" y1="45" x2="610" y2="1330" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= SUPERADMIN: INIT OPNAME ================= -->
  <circle cx="150" cy="100" r="10" fill="#0f172a"/>
  <line x1="150" y1="110" x2="150" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="45" y="135" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="153" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Membuka Menu Stok Opname &amp;</text>
  <text x="150" y="167" text-anchor="middle" font-size="9.5" fill="#0f172a">Tentukan Jadwal Sesi Audit</text>
  <line x1="150" y1="177" x2="150" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="45" y="205" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="223" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan Tombol</text>
  <text x="150" y="237" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">"Buka Sesi Stok Opname"</text>
  <!-- To Sistem (Orthogonal: right to x=660) -->
  <path d="M 255 226 L 660 226" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem initiates Freeze Lock & prints Blind Count -->
  <rect x="660" y="205" width="260" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="790" y="223" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Inisiasi Sesi Opname Aktif &amp;</text>
  <text x="790" y="236" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">Trigger "System Freeze Lock"</text>
  <text x="790" y="248" text-anchor="middle" font-size="8.5" fill="#dc2626">(Kunci Kasir POS &amp; Checkout Daring)</text>
  <line x1="790" y1="251" x2="790" y2="280" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="660" y="280" width="260" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="790" y="298" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Cetak Lembar Hitung Buta</text>
  <text x="790" y="311" text-anchor="middle" font-size="8.5" fill="#0f172a">(Blind Count Sheet tanpa Saldo Buku)</text>
  <!-- To Admin (Orthogonal: left to x=450) -->
  <path d="M 660 302 L 560 302" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Admin counts physically -->
  <rect x="340" y="280" width="220" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="298" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Lakukan Pencacahan Fisik di Rak</text>
  <text x="450" y="311" text-anchor="middle" font-size="9" fill="#0f172a">Etalase Toko &amp; Rak Gudang</text>
  <line x1="450" y1="324" x2="450" y2="350" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="340" y="350" width="220" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="368" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Hasil Cacahan Fisik ke</text>
  <text x="450" y="381" text-anchor="middle" font-size="9" fill="#0f172a">Formulir Lembar Kerja Sistem</text>
  <line x1="450" y1="394" x2="450" y2="420" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="340" y="420" width="220" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="444" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Tekan "Simpan Data Hitung Fisik"</text>
  <!-- To Sistem (Orthogonal: right to x=660) -->
  <path d="M 560 440 L 660 440" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem calculates variance -->
  <rect x="660" y="420" width="260" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="790" y="437" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Kalkulasi Otomatis Deviasi Selisih:</text>
  <text x="790" y="450" text-anchor="middle" font-size="8.5" fill="#0f172a">Fisik vs Saldo Buku Sistem</text>
  <text x="790" y="462" text-anchor="middle" font-size="8" fill="#0f172a">(Status: Sesuai / Selisih Kurang / Lebih)</text>
  <!-- To Superadmin (Orthogonal: left to x=255) -->
  <path d="M 790 466 L 790 500 L 150 500 L 150 520" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin evaluates variance -->
  <rect x="45" y="520" width="210" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="538" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Evaluasi Matriks Varian Selisih</text>
  <text x="150" y="551" text-anchor="middle" font-size="8.5" fill="#0f172a">&amp; Input Catatan Justifikasi Audit</text>
  <line x1="150" y1="566" x2="150" y2="595" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Perlu Cek Ulang? -->
  <polygon points="150,595 195,615 150,635 105,615" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="150" y="619" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Cek Ulang?</text>

  <!-- [Perlu Cek Ulang] (Orthogonal: right to x=310, up to y=372, right to x=340) -->
  <text x="180" y="605" font-size="8.5" font-weight="bold" fill="#dc2626">[Cek Ulang]</text>
  <path d="M 195 615 L 310 615 L 310 372 L 340 372" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <!-- [Tidak / Disetujui] (Orthogonal straight down) -->
  <text x="160" y="655" font-size="8.5" font-weight="bold" fill="#16a34a">[Disetujui]</text>
  <line x1="150" y1="635" x2="150" y2="675" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="45" y="675" width="210" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="693" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tekan "Otorisasi Rekonsiliasi</text>
  <text x="150" y="707" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">&amp; Tutup Sesi Opname"</text>
  <!-- To Sistem (Orthogonal: right to x=660) -->
  <path d="M 255 698 L 660 698" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Sistem executes reconciliation & unfreezes -->
  <rect x="660" y="675" width="260" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="790" y="693" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Penyesuaian Saldo Persediaan Otomatis</text>
  <text x="790" y="706" text-anchor="middle" font-size="8.5" fill="#0f172a">Database Mengikuti Hasil Fisik Riil</text>
  <line x1="790" y1="721" x2="790" y2="750" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="660" y="750" width="260" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="790" y="768" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Membukukan Jurnal Selisih</text>
  <text x="790" y="781" text-anchor="middle" font-size="8.5" fill="#0f172a">ke Buku Besar Riwayat Kartu Stok</text>
  <line x1="790" y1="794" x2="790" y2="820" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="660" y="820" width="260" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="790" y="838" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#16a34a">Lepas Status "System Freeze Lock"</text>
  <text x="790" y="851" text-anchor="middle" font-size="8.5" fill="#16a34a">(POS Kasir &amp; E-Commerce Kembali Aktif)</text>
  <line x1="790" y1="866" x2="790" y2="895" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="660" y="895" width="260" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="790" y="913" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerbitkan Dokumen Resmi</text>
  <text x="790" y="926" text-anchor="middle" font-size="8.5" fill="#0f172a">Berita Acara Stok Opname (PDF)</text>
  <!-- To Superadmin & Admin (Orthogonal: left to x=450) -->
  <path d="M 660 917 L 450 917 L 450 960" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Superadmin & Admin sign & archive -->
  <rect x="250" y="960" width="400" height="46" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="450" y="978" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Superadmin &amp; Admin Menandatangani,</text>
  <text x="450" y="991" text-anchor="middle" font-size="9" fill="#0f172a">Mengunduh &amp; Mengarsipkan Dokumen Berita Acara (PDF)</text>

  <!-- Final Node -->
  <line x1="450" y1="1006" x2="450" y2="1040" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="450" cy="1053" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="450" cy="1053" r="8" fill="#0f172a"/>
</svg>'''

if __name__ == '__main__':
    print("Part 3 functions compiled successfully.")
