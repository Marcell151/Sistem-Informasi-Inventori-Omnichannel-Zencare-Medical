# -*- coding: utf-8 -*-
"""
Full Generator for Activity Diagram SVGs (Gambar 3.10 to Gambar 3.17)
"""

import os

base_dir = r'c:\xampp\htdocs\inventory_zencare\lain'

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

# 1. Gambar 3.10: Autentikasi
def get_svg_310():
    from gen_svg_a import generate_svg_a
    return generate_svg_a()

# 2. Gambar 3.11: Pesanan Daring (E-Commerce)
def get_svg_311():
    return f'''<svg viewBox="0 0 960 1160" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="940" height="1140" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="940" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="480" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Pesanan Daring (E-Commerce Checkout &amp; Midtrans)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="290" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="155" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan</text>

  <rect x="300" y="45" width="370" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="485" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem E-Commerce</text>

  <rect x="670" y="45" width="280" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="810" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Payment Gateway (Midtrans)</text>

  <line x1="300" y1="45" x2="300" y2="1150" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="670" y1="45" x2="670" y2="1150" stroke="#0f172a" stroke-width="1.5"/>

  <!-- PELANGGAN -->
  <circle cx="155" cy="105" r="10" fill="#0f172a"/>
  <line x1="155" y1="115" x2="155" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="140" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="165" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Checkout dari Keranjang</text>
  <path d="M 255 161 L 380 161" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- SISTEM -->
  <rect x="380" y="140" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="157" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menampilkan Ringkasan Belanja</text>
  <text x="485" y="171" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">&amp; Opsi Pengambilan</text>
  <path d="M 485 182 L 485 220 L 255 220" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- PELANGGAN -->
  <rect x="55" y="200" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="218" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Memilih Metode</text>
  <text x="155" y="232" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Pengambilan Pesanan</text>
  <line x1="155" y1="242" x2="155" y2="270" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="155,270 195,290 155,310 115,290" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="155" y="294" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Metode?</text>

  <!-- Kurir Ekspedisi -->
  <text x="110" y="325" font-size="9" font-weight="bold" fill="#0f172a">[Kurir]</text>
  <path d="M 115 290 L 80 290 L 80 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="25" y="340" width="115" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="82" y="357" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pilih Alamat &amp;</text>
  <text x="82" y="370" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Layanan Kurir</text>
  <path d="M 140 361 L 330 361" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pick-up in Store -->
  <text x="205" y="305" font-size="9" font-weight="bold" fill="#0f172a">[Pick-up]</text>
  <path d="M 195 290 L 230 290 L 230 340 L 510 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- SISTEM -->
  <rect x="330" y="340" width="160" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="410" y="357" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Hitung Tarif Ongkir</text>
  <text x="410" y="370" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">(API RajaOngkir)</text>

  <rect x="510" y="325" width="140" height="35" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="580" y="346" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Ongkos Kirim Rp 0</text>

  <!-- Merge Diamond -->
  <polygon points="485,395 500,405 485,415 470,405" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 410 382 L 410 405 L 470 405" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 580 360 L 580 405 L 500 405" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="485" y1="415" x2="485" y2="440" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="380" y="440" width="210" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="458" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tampilkan Total Tagihan &amp;</text>
  <text x="485" y="472" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Terbitkan Midtrans Snap Token</text>
  <path d="M 380 462 L 255 462" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- PELANGGAN -->
  <rect x="55" y="440" width="200" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="458" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Pilih Metode Pembayaran &amp;</text>
  <text x="155" y="472" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Transfer Dana (Batas 2 Jam)</text>
  <path d="M 155 485 L 155 525 L 720 525" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- MIDTRANS -->
  <rect x="720" y="505" width="180" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="810" y="524" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Memvalidasi Status</text>
  <text x="810" y="538" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Pembayaran Pelanggan</text>
  <line x1="810" y1="547" x2="810" y2="575" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="810,575 850,595 810,615 770,595" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="810" y="598" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Hasil?</text>

  <!-- Gagal -->
  <text x="750" y="630" font-size="9" font-weight="bold" fill="#dc2626">[Gagal/Expired]</text>
  <path d="M 770 595 L 720 595 L 720 645 L 590 645" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>
  
  <rect x="400" y="630" width="190" height="40" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="495" y="648" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">Update Status "Dibatalkan"</text>
  <text x="495" y="660" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">&amp; Kirim Notifikasi Gagal</text>
  <path d="M 400 650 L 255 650" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="65" y="635" width="180" height="35" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="155" y="656" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Menerima Notifikasi Gagal</text>
  <line x1="155" y1="670" x2="155" y2="700" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="155" cy="712" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="155" cy="712" r="7" fill="#0f172a"/>

  <!-- Sukses -->
  <text x="825" y="640" font-size="9.5" font-weight="bold" fill="#16a34a">[Settlement/Sukses]</text>
  <path d="M 810 615 L 810 740 L 590 740" fill="none" stroke="#16a34a" stroke-width="1.5" marker-end="url(#arrow-green)"/>

  <!-- SISTEM: WEBHOOK & RESERVASI -->
  <rect x="380" y="720" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="737" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Webhook Midtrans Diterima:</text>
  <text x="485" y="751" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Evaluasi Kategori Komoditas</text>
  <line x1="485" y1="762" x2="485" y2="790" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="485,790 525,810 485,830 445,810" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="485" y="814" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Kategori?</text>

  <!-- Obat: FEFO -->
  <text x="390" y="825" font-size="9" font-weight="bold" fill="#0f172a">[Obat]</text>
  <path d="M 445 810 L 390 810 L 390 855" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="320" y="855" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="390" y="872" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Alokasikan Stok Batch</text>
  <text x="390" y="885" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Rekomendasi FEFO</text>

  <!-- Alkes: SN -->
  <text x="560" y="825" font-size="9" font-weight="bold" fill="#0f172a">[Alat Kesehatan]</text>
  <path d="M 525 810 L 580 810 L 580 855" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="510" y="855" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="580" y="872" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Alokasikan Unit dengan</text>
  <text x="580" y="885" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Serial Number (SN)</text>

  <!-- Merge -->
  <polygon points="485,920 500,930 485,940 470,930" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 390 897 L 390 930 L 470 930" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 580 897 L 580 930 L 500 930" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="485" y1="940" x2="485" y2="965" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="380" y="965" width="210" height="40" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="983" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Kunci Persediaan Fisik:</text>
  <text x="485" y="996" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tandai Status Stok "Reserved"</text>
  <line x1="485" y1="1005" x2="485" y2="1030" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="380" y="1030" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="1047" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Perbarui Status "Diproses" &amp;</text>
  <text x="485" y="1061" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Terbitkan Faktur Digital (Invoice)</text>
  <path d="M 380 1051 L 255 1051" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- PELANGGAN -->
  <rect x="55" y="1030" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="1048" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerima Bukti Faktur &amp;</text>
  <text x="155" y="1061" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Memantau Status Pesanan di Akun</text>

  <line x1="155" y1="1072" x2="155" y2="1105" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="155" cy="1118" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="155" cy="1118" r="8" fill="#0f172a"/>
</svg>'''

print("Diagram B generator ready.")
