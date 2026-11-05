# -*- coding: utf-8 -*-
"""
Builder for Diagram 3.10 (Autentikasi) and Diagram 3.11 (Pesanan Daring)
Strictly orthogonal, valid XML, zero farmasi, exact draw.io aesthetic.
"""

from final_builder_common import DEFS

def get_diagram_310():
    return f'''<svg viewBox="0 0 980 1140" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1120" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Autentikasi (Login &amp; Pengalihan Hak Akses RBAC)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="290" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="155" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Kasir / Admin / Superadmin</text>

  <rect x="300" y="45" width="330" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="465" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan</text>

  <rect x="630" y="45" width="340" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="800" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem (Basis Data &amp; RBAC)</text>

  <!-- Swimlane Dividers -->
  <line x1="300" y1="45" x2="300" y2="1130" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="630" y1="45" x2="630" y2="1130" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= LANE 1: KASIR / ADMIN / SUPERADMIN ================= -->
  <circle cx="155" cy="105" r="10" fill="#0f172a"/>
  <line x1="155" y1="115" x2="155" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="135" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="160" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Mengakses halaman Login</text>
  <line x1="155" y1="177" x2="155" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="205" width="200" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="224" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Memasukkan Username</text>
  <text x="155" y="238" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">dan Password</text>
  <line x1="155" y1="249" x2="155" y2="280" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="280" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="305" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Menekan tombol Login</text>

  <!-- Straight 90-degree orthogonal line to System: along x=155 down to y=535, then right to x=695 -->
  <path d="M 155 322 L 155 535 L 695 535" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= LANE 2: PELANGGAN ================= -->
  <circle cx="465" cy="105" r="10" fill="#0f172a"/>
  <line x1="465" y1="115" x2="465" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="365" y="135" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="465" y="160" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Mengakses halaman E-Commerce</text>
  <line x1="465" y1="177" x2="465" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="365" y="205" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="465" y="230" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Memilih menu Masuk/Daftar</text>
  <line x1="465" y1="247" x2="465" y2="275" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Apakah Punya Akun? -->
  <polygon points="465,275 510,295 465,315 420,295" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="465" y="299" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Punya Akun?</text>

  <!-- [Ya] Left branch (Orthogonal: left to x=375, then down to y=340) -->
  <text x="385" y="288" font-size="10" font-weight="bold" fill="#0f172a">[Ya]</text>
  <path d="M 420 295 L 375 295 L 375 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="320" y="340" width="110" height="44" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="375" y="358" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Username</text>
  <text x="375" y="372" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">&amp; Password</text>
  <line x1="375" y1="384" x2="375" y2="410" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="320" y="410" width="110" height="38" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="375" y="429" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tekan Tombol</text>
  <text x="375" y="441" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Login</text>

  <!-- Line from Login Pelanggan to Pelanggan Merge Diamond (Orthogonal) -->
  <path d="M 375 448 L 375 490 L 440 490" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- [Tidak] Right branch (Orthogonal: right to x=555, then down to y=340) -->
  <text x="520" y="288" font-size="10" font-weight="bold" fill="#0f172a">[Tidak]</text>
  <path d="M 510 295 L 555 295 L 555 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="480" y="340" width="145" height="44" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="552" y="357" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Input Nama, User, Pass,</text>
  <text x="552" y="371" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">No WA, Email, Alamat</text>
  <line x1="552" y1="384" x2="552" y2="410" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="495" y="410" width="115" height="36" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="552" y="432" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Buat Akun</text>

  <!-- From Buat Akun horizontally into System: Menyimpan Data Baru (Orthogonal) -->
  <path d="M 610 428 L 695 428" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- System: Menyimpan Data Profil Baru -->
  <rect x="695" y="407" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="795" y="425" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menyimpan Data</text>
  <text x="795" y="439" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Profil Baru ke Database</text>

  <!-- Loop back from Menyimpan Data Baru to Input Username Pelanggan (Orthogonal) -->
  <path d="M 795 449 L 795 470 L 440 470 L 440 362 L 430 362" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Merge Diamond for Pelanggan Login -->
  <polygon points="450,480 465,490 450,500 435,490" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Pelanggan Merge down to y=520, then right to x=695 (Orthogonal) -->
  <path d="M 450 500 L 450 520 L 695 520" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= LANE 3: SISTEM (BASIS DATA & RBAC) ================= -->
  <rect x="695" y="505" width="200" height="50" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="795" y="526" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Memverifikasi kredensial</text>
  <text x="795" y="542" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">ke Basis Data (Hash)</text>
  <line x1="795" y1="555" x2="795" y2="585" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Apakah Data Valid? -->
  <polygon points="795,585 840,605 795,625 750,605" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="795" y="609" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Data Valid?</text>

  <!-- Branch [Tidak] Orthogonal: right to x=910, up to y=490 into error box, then loop back -->
  <text x="850" y="600" font-size="9.5" font-weight="bold" fill="#dc2626">[Tidak]</text>
  <path d="M 840 605 L 910 605 L 910 515" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="835" y="475" width="130" height="40" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="900" y="492" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Pesan Kesalahan:</text>
  <text x="900" y="504" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Kredensial Tidak Valid</text>

  <!-- Error loop line up along x=910 to y=225, then left across to inputs (Orthogonal) -->
  <path d="M 910 475 L 910 225 L 255 225" fill="none" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4" marker-end="url(#arrow-red)"/>

  <!-- Branch [Ya] (Orthogonal straight down) -->
  <text x="805" y="645" font-size="9.5" font-weight="bold" fill="#16a34a">[Ya]</text>
  <line x1="795" y1="625" x2="795" y2="660" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box: Mengecek Role Pengguna -->
  <rect x="695" y="660" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="795" y="679" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Mengecek Role Pengguna</text>
  <text x="795" y="693" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">(Otorisasi Akses RBAC)</text>
  <line x1="795" y1="702" x2="795" y2="730" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Pengecekan Role (4 Cabang Murni Orthogonal) -->
  <polygon points="795,730 850,750 795,770 740,750" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="795" y="754" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Role Akses</text>

  <!-- 1. Superadmin (Left, then down: orthogonal) -->
  <path d="M 740 750 L 710 750 L 710 790" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="670" y="770" font-size="8.5" font-weight="bold" fill="#0f172a">[Superadmin]</text>
  <rect x="645" y="790" width="130" height="54" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="710" y="808" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Menu Manajerial:</text>
  <text x="710" y="820" text-anchor="middle" font-size="8" fill="#0f172a">User, Laporan, Pengaturan</text>
  <text x="710" y="832" text-anchor="middle" font-size="8" fill="#0f172a">&amp; Seluruh Modul Sistem</text>

  <!-- 2. Admin (Right, then down: orthogonal) -->
  <path d="M 850 750 L 880 750 L 880 790" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="885" y="770" font-size="8.5" font-weight="bold" fill="#0f172a">[Admin]</text>
  <rect x="815" y="790" width="130" height="54" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="880" y="808" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Modul Operasional:</text>
  <text x="880" y="820" text-anchor="middle" font-size="8" fill="#0f172a">Dashboard, Master Data,</text>
  <text x="880" y="832" text-anchor="middle" font-size="8" fill="#0f172a">Logistik &amp; Stok Opname</text>

  <!-- 3. Kasir (Straight down from bottom-left corner: orthogonal) -->
  <path d="M 765 760 L 710 760 L 710 875" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="670" y="865" font-size="8.5" font-weight="bold" fill="#0f172a">[Kasir]</text>
  <rect x="645" y="875" width="130" height="48" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="710" y="893" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Mengarahkan ke</text>
  <text x="710" y="906" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Terminal Kasir (POS)</text>

  <!-- 4. Pelanggan (Down from bottom-right corner: orthogonal) -->
  <path d="M 825 760 L 880 760 L 880 875" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="885" y="865" font-size="8.5" font-weight="bold" fill="#0f172a">[Pelanggan]</text>
  <rect x="815" y="875" width="130" height="48" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="880" y="893" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Antarmuka E-Commerce,</text>
  <text x="880" y="906" text-anchor="middle" font-size="8" fill="#0f172a">Katalog, Keranjang &amp; Akun</text>

  <!-- Merge Diamond for Roles (Orthogonal lines from all 4 boxes) -->
  <polygon points="795,950 815,965 795,980 775,965" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>

  <!-- From Superadmin to Merge -->
  <path d="M 710 844 L 710 965 L 775 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Admin to Merge -->
  <path d="M 880 844 L 880 965 L 815 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Kasir to Merge -->
  <path d="M 710 923 L 710 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Pelanggan to Merge -->
  <path d="M 880 923 L 880 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Final Node (Straight orthogonal down) -->
  <line x1="795" y1="980" x2="795" y2="1020" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="795" cy="1035" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="795" cy="1035" r="8" fill="#0f172a"/>
</svg>'''

def get_diagram_311():
    return f'''<svg viewBox="0 0 980 1340" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{DEFS}
  <!-- Outer Border & Header -->
  <rect x="10" y="10" width="960" height="1320" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="960" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="490" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Pesanan Daring (E-Commerce Checkout, Midtrans &amp; Alokasi FEFO/SN)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="290" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="155" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan</text>

  <rect x="300" y="45" width="370" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="485" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem E-Commerce</text>

  <rect x="670" y="45" width="300" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="820" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Payment Gateway (Midtrans)</text>

  <!-- Dividers -->
  <line x1="300" y1="45" x2="300" y2="1330" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="670" y1="45" x2="670" y2="1330" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= PELANGGAN: INITIAL CHECKOUT ================= -->
  <circle cx="155" cy="100" r="10" fill="#0f172a"/>
  <line x1="155" y1="110" x2="155" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="55" y="135" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="160" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Checkout dari Keranjang</text>
  <!-- To System (Orthogonal) -->
  <path d="M 255 156 L 380 156" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- SISTEM: DISPLAY SUMMARY -->
  <rect x="380" y="135" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="152" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menampilkan Ringkasan Belanja</text>
  <text x="485" y="166" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">&amp; Opsi Pengambilan</text>
  <!-- To Pelanggan (Orthogonal) -->
  <path d="M 485 177 L 485 215 L 255 215" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- PELANGGAN: PILIH METODE -->
  <rect x="55" y="195" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="213" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Memilih Metode</text>
  <text x="155" y="227" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Pengambilan Pesanan</text>
  <line x1="155" y1="237" x2="155" y2="265" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Kurir vs Pick-up -->
  <polygon points="155,265 195,285 155,305 115,285" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="155" y="289" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Metode?</text>

  <!-- Kurir Ekspedisi (Orthogonal: left to x=80, down to y=330) -->
  <text x="110" y="320" font-size="9" font-weight="bold" fill="#0f172a">[Kurir]</text>
  <path d="M 115 285 L 80 285 L 80 330" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="25" y="330" width="115" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="82" y="347" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pilih Alamat &amp;</text>
  <text x="82" y="360" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Layanan Kurir</text>
  <path d="M 140 351 L 330 351" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pick-up in Store (Orthogonal: right to x=230, down to y=330, right to x=510) -->
  <text x="205" y="300" font-size="9" font-weight="bold" fill="#0f172a">[Pick-up]</text>
  <path d="M 195 285 L 230 285 L 230 330 L 510 330" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- SISTEM: ONGKIR CALCULATION -->
  <rect x="330" y="330" width="160" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="410" y="347" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Hitung Tarif Ongkir Riil</text>
  <text x="410" y="360" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">(API RajaOngkir)</text>

  <rect x="510" y="315" width="140" height="35" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="580" y="336" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Ongkos Kirim Rp 0</text>

  <!-- Merge Diamond for Shipping Method (Orthogonal) -->
  <polygon points="485,385 500,395 485,405 470,395" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 410 372 L 410 395 L 470 395" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 580 350 L 580 395 L 500 395" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="485" y1="405" x2="485" y2="430" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- SISTEM: TOTAL TAGIHAN -->
  <rect x="380" y="430" width="210" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="448" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tampilkan Total Tagihan Akhir</text>
  <text x="485" y="462" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">&amp; Opsi Waktu Pembayaran</text>
  <path d="M 380 452 L 255 452" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- PELANGGAN: DECISION WAKTU BAYAR (BAYAR NANTI VS BAYAR SEKARANG) -->
  <rect x="55" y="430" width="200" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="448" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Evaluasi Pilihan Waktu</text>
  <text x="155" y="462" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Pembayaran Pesanan</text>
  <line x1="155" y1="475" x2="155" y2="505" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="155,505 195,525 155,545 115,525" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="155" y="529" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Waktu Bayar?</text>

  <!-- Branch: Bayar Nanti (Orthogonal: left to x=70, down to y=575) -->
  <text x="75" y="518" font-size="8.5" font-weight="bold" fill="#0f172a">[Bayar Nanti]</text>
  <path d="M 115 525 L 70 525 L 70 575" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="20" y="575" width="105" height="42" rx="6" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="72" y="592" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Simpan Request</text>
  <text x="72" y="605" text-anchor="middle" font-size="8" fill="#0f172a">(Timer 2 Jam)</text>
  <line x1="72" y1="617" x2="72" y2="645" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="72" cy="657" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="72" cy="657" r="7" fill="#0f172a"/>

  <!-- Branch: Bayar Sekarang (Orthogonal: right to x=220, down to y=575) -->
  <text x="185" y="518" font-size="8.5" font-weight="bold" fill="#16a34a">[Bayar Sekarang]</text>
  <path d="M 195 525 L 220 525 L 220 575" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="145" y="575" width="145" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="217" y="592" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pilih Metode Pembayaran</text>
  <text x="217" y="605" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">&amp; Transfer Dana</text>

  <!-- From Bayar Sekarang to Midtrans (Orthogonal: right across to x=720) -->
  <path d="M 290 596 L 720 596" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= MIDTRANS PAYMENT GATEWAY ================= -->
  <rect x="720" y="575" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="820" y="593" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Memvalidasi Status</text>
  <text x="820" y="607" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Pembayaran Pelanggan</text>
  <line x1="820" y1="617" x2="820" y2="650" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Validasi Midtrans -->
  <polygon points="820,650 860,670 820,690 780,670" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="820" y="674" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Status?</text>

  <!-- Gagal / Expired (Orthogonal: left to x=730, down to y=720, left to x=590) -->
  <text x="745" y="705" font-size="9" font-weight="bold" fill="#dc2626">[Gagal/Expired]</text>
  <path d="M 780 670 L 730 670 L 730 720 L 590 720" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="400" y="700" width="190" height="42" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="495" y="718" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">Update Status "Dibatalkan"</text>
  <text x="495" y="731" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">&amp; Kirim Notifikasi Gagal</text>
  <path d="M 400 721 L 255 721" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="75" y="705" width="180" height="35" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="165" y="726" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Menerima Notifikasi Gagal</text>
  <line x1="165" y1="740" x2="165" y2="765" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="165" cy="777" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="165" cy="777" r="7" fill="#0f172a"/>

  <!-- Sukses / Settlement (Orthogonal: down along x=820 to y=815, left to x=590) -->
  <text x="830" y="710" font-size="9.5" font-weight="bold" fill="#16a34a">[Settlement/Sukses]</text>
  <path d="M 820 690 L 820 815 L 590 815" fill="none" stroke="#16a34a" stroke-width="1.5" marker-end="url(#arrow-green)"/>

  <!-- ================= SISTEM: WEBHOOK & ALOKASI STOK ================= -->
  <rect x="380" y="795" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="812" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Webhook Midtrans Diterima:</text>
  <text x="485" y="826" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Evaluasi Kategori Komoditas</text>
  <line x1="485" y1="837" x2="485" y2="865" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Kategori Produk? -->
  <polygon points="485,865 525,885 485,905 445,885" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="485" y="889" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Kategori?</text>

  <!-- Obat: FEFO (Orthogonal: left to x=390, down to y=930) -->
  <text x="390" y="900" font-size="9" font-weight="bold" fill="#0f172a">[Obat]</text>
  <path d="M 445 885 L 390 885 L 390 930" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="320" y="930" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="390" y="947" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Alokasikan Stok Batch</text>
  <text x="390" y="960" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Rekomendasi FEFO</text>

  <!-- Alkes: SN (Orthogonal: right to x=580, down to y=930) -->
  <text x="560" y="900" font-size="9" font-weight="bold" fill="#0f172a">[Alat Kesehatan]</text>
  <path d="M 525 885 L 580 885 L 580 930" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="510" y="930" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="580" y="947" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Alokasikan Unit dengan</text>
  <text x="580" y="960" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Serial Number (SN)</text>

  <!-- Merge Kategori (Orthogonal) -->
  <polygon points="485,995 500,1005 485,1015 470,1005" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 390 972 L 390 1005 L 470 1005" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 580 972 L 580 1005 L 500 1005" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="485" y1="1015" x2="485" y2="1040" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- KUNCI STOK RESERVED -->
  <rect x="380" y="1040" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="1058" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Kunci Persediaan Fisik:</text>
  <text x="485" y="1072" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tandai Status Stok "Reserved"</text>
  <line x1="485" y1="1082" x2="485" y2="1110" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- UPDATE DIPROSES & FAKTUR -->
  <rect x="380" y="1110" width="210" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="1128" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Perbarui Status "Diproses" &amp;</text>
  <text x="485" y="1142" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Terbitkan Faktur Digital (Invoice)</text>
  <!-- To Pelanggan (Orthogonal) -->
  <path d="M 380 1132 L 255 1132" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- PELANGGAN: TERIMA BUKTI & PANTAU -->
  <rect x="55" y="1110" width="200" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="155" y="1128" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menerima Bukti Faktur Digital &amp;</text>
  <text x="155" y="1142" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Memantau Status Pesanan di Akun</text>

  <!-- Final Node -->
  <line x1="155" y1="1154" x2="155" y2="1195" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="155" cy="1208" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="155" cy="1208" r="8" fill="#0f172a"/>
</svg>'''

if __name__ == '__main__':
    print("Part 1 functions compiled successfully.")

