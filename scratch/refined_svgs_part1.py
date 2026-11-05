# -*- coding: utf-8 -*-
"""
Part 1: Diagram 3.10 (Autentikasi) and Diagram 3.11 (Pesanan Daring)
Strictly orthogonal, no slanted lines, valid XML, zero farmasi.
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

def get_refined_310():
    return f'''<svg viewBox="0 0 960 1140" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="940" height="1120" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="940" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="480" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Autentikasi (Login)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="280" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="150" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Kasir / Admin / Superadmin</text>

  <rect x="290" y="45" width="310" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="445" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan</text>

  <rect x="600" y="45" width="350" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="775" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem</text>

  <line x1="290" y1="45" x2="290" y2="1130" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="600" y1="45" x2="600" y2="1130" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= LANE 1: KASIR / ADMIN / SUPERADMIN ================= -->
  <circle cx="150" cy="105" r="10" fill="#0f172a"/>
  <line x1="150" y1="115" x2="150" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="50" y="135" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="160" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Mengakses halaman Login</text>
  <line x1="150" y1="177" x2="150" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="50" y="205" width="200" height="44" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="224" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Memasukkan Username</text>
  <text x="150" y="238" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">dan Password</text>
  <line x1="150" y1="249" x2="150" y2="280" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="50" y="280" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="305" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Menekan tombol Login</text>

  <!-- Straight 90-degree orthogonal line to System: down along x=150 to y=550, then right to x=660 -->
  <path d="M 150 322 L 150 550 L 660 550" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= LANE 2: PELANGGAN ================= -->
  <circle cx="445" cy="105" r="10" fill="#0f172a"/>
  <line x1="445" y1="115" x2="445" y2="135" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="345" y="135" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="445" y="160" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Mengakses halaman E-Commerce</text>
  <line x1="445" y1="177" x2="445" y2="205" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="345" y="205" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="445" y="230" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Memilih menu Masuk/Daftar</text>
  <line x1="445" y1="247" x2="445" y2="275" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Apakah Punya Akun? -->
  <polygon points="445,275 490,295 445,315 400,295" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="445" y="299" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Punya Akun?</text>

  <!-- [Ya] Left branch (Orthogonal: left along y=295 to x=355, then down to y=340) -->
  <text x="365" y="288" font-size="10" font-weight="bold" fill="#0f172a">[Ya]</text>
  <path d="M 400 295 L 355 295 L 355 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="300" y="340" width="110" height="44" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="355" y="358" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Username</text>
  <text x="355" y="372" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">&amp; Password</text>
  <line x1="355" y1="384" x2="355" y2="410" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="300" y="410" width="110" height="38" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="355" y="429" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Tekan Tombol</text>
  <text x="355" y="441" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Login</text>

  <!-- Line from Login Pelanggan to Pelanggan Merge Diamond (Orthogonal) -->
  <path d="M 355 448 L 355 490 L 415 490" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- [Tidak] Right branch (Orthogonal: right along y=295 to x=535, then down to y=340) -->
  <text x="500" y="288" font-size="10" font-weight="bold" fill="#0f172a">[Tidak]</text>
  <path d="M 490 295 L 535 295 L 535 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="460" y="340" width="135" height="44" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="527" y="357" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Input Nama, User, Pass,</text>
  <text x="527" y="371" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">No WA, Email, Alamat</text>
  <line x1="527" y1="384" x2="527" y2="410" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="470" y="410" width="115" height="36" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="527" y="432" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Buat Akun</text>

  <!-- From Buat Akun horizontally into System: Menyimpan Data Baru (Orthogonal) -->
  <path d="M 585 428 L 660 428" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- System: Menyimpan Data Baru -->
  <rect x="660" y="407" width="190" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="755" y="425" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menyimpan Data</text>
  <text x="755" y="439" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Profil Baru</text>

  <!-- Loop back from Menyimpan Data Baru to Input Username Pelanggan (Orthogonal) -->
  <path d="M 755 449 L 755 470 L 418 470 L 418 362 L 410 362" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Merge Diamond for Pelanggan Login -->
  <polygon points="430,480 445,490 430,500 415,490" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Pelanggan Merge down to y=525, then right to x=660 (Orthogonal) -->
  <path d="M 430 500 L 430 525 L 660 525" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= LANE 3: SISTEM ================= -->
  <!-- Box: Memverifikasi Kredensial -->
  <rect x="660" y="515" width="200" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="760" y="535" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Memverifikasi kredensial</text>
  <text x="760" y="549" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">ke Basis Data</text>
  <line x1="760" y1="563" x2="760" y2="590" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Apakah Data Valid? -->
  <polygon points="760,590 805,610 760,630 715,610" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="760" y="614" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Data Valid?</text>

  <!-- Branch [Tidak] Orthogonal: right to x=870, up to y=490 into error box, then loop back up and left -->
  <text x="815" y="605" font-size="9.5" font-weight="bold" fill="#dc2626">[Tidak]</text>
  <path d="M 805 610 L 870 610 L 870 515" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="800" y="475" width="140" height="40" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="870" y="492" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Pesan Kesalahan:</text>
  <text x="870" y="504" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#dc2626">Username/Password Salah</text>

  <!-- Error loop line up along x=870 to y=225, then horizontally left to x=250 into internal input & branch to customer (Orthogonal) -->
  <path d="M 870 475 L 870 225 L 250 225" fill="none" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4" marker-end="url(#arrow-red)"/>

  <!-- Branch [Ya] (Orthogonal straight down) -->
  <text x="770" y="648" font-size="9.5" font-weight="bold" fill="#16a34a">[Ya]</text>
  <line x1="760" y1="630" x2="760" y2="665" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box: Mengecek Role -->
  <rect x="660" y="665" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="760" y="684" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Mengecek Role</text>
  <text x="760" y="698" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">(Otorisasi Akses)</text>
  <line x1="760" y1="707" x2="760" y2="735" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Role Akses (4 Cabang Murni Orthogonal) -->
  <polygon points="760,735 815,755 760,775 705,755" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="760" y="759" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Role Akses</text>

  <!-- 1. Superadmin (Left, then down: orthogonal) -->
  <path d="M 705 755 L 675 755 L 675 795" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="635" y="775" font-size="8.5" font-weight="bold" fill="#0f172a">[Superadmin]</text>
  <rect x="610" y="795" width="130" height="52" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="675" y="812" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Menu Manajerial:</text>
  <text x="675" y="824" text-anchor="middle" font-size="8" fill="#0f172a">User, Laporan,</text>
  <text x="675" y="836" text-anchor="middle" font-size="8" fill="#0f172a">Pengaturan &amp; Admin</text>

  <!-- 2. Admin (Right, then down: orthogonal) -->
  <path d="M 815 755 L 845 755 L 845 795" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="850" y="775" font-size="8.5" font-weight="bold" fill="#0f172a">[Admin]</text>
  <rect x="780" y="795" width="130" height="52" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="845" y="812" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Modul Operasional:</text>
  <text x="845" y="824" text-anchor="middle" font-size="8" fill="#0f172a">Dashboard, Master Data,</text>
  <text x="845" y="836" text-anchor="middle" font-size="8" fill="#0f172a">Logistik &amp; Stok Opname</text>

  <!-- 3. Kasir (Straight down from bottom-left corner of diamond: orthogonal) -->
  <path d="M 730 765 L 675 765 L 675 875" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="635" y="865" font-size="8.5" font-weight="bold" fill="#0f172a">[Kasir]</text>
  <rect x="610" y="875" width="130" height="48" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="675" y="893" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Mengarahkan ke</text>
  <text x="675" y="906" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Terminal Kasir (POS)</text>

  <!-- 4. Pelanggan (Down from bottom-right corner: orthogonal) -->
  <path d="M 790 765 L 845 765 L 845 875" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="850" y="865" font-size="8.5" font-weight="bold" fill="#0f172a">[Pelanggan]</text>
  <rect x="780" y="875" width="130" height="48" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="845" y="893" text-anchor="middle" font-size="8" font-weight="bold" fill="#0f172a">Antarmuka E-Commerce,</text>
  <text x="845" y="906" text-anchor="middle" font-size="8" fill="#0f172a">Katalog, Keranjang &amp; Akun</text>

  <!-- Merge Diamond for Roles (Orthogonal lines from all 4 boxes) -->
  <polygon points="760,950 780,965 760,980 740,965" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>

  <!-- From Superadmin to Merge -->
  <path d="M 675 847 L 675 965 L 740 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Admin to Merge -->
  <path d="M 845 847 L 845 965 L 780 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Kasir to Merge -->
  <path d="M 675 923 L 675 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <!-- From Pelanggan to Merge -->
  <path d="M 845 923 L 845 965" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Final Node (Straight orthogonal down) -->
  <line x1="760" y1="980" x2="760" y2="1020" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="760" cy="1035" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="760" cy="1035" r="8" fill="#0f172a"/>
</svg>'''

def get_refined_311():
    return f'''<svg viewBox="0 0 960 1180" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif; background: #ffffff;">
{defs_str}
  <rect x="10" y="10" width="940" height="1160" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  <rect x="10" y="10" width="940" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="480" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Pesanan Daring (E-Commerce Checkout &amp; Midtrans)</text>

  <!-- Swimlanes -->
  <rect x="10" y="45" width="290" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="155" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan</text>

  <rect x="300" y="45" width="370" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="485" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem E-Commerce</text>

  <rect x="670" y="45" width="280" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="810" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Payment Gateway (Midtrans)</text>

  <line x1="300" y1="45" x2="300" y2="1170" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="670" y1="45" x2="670" y2="1170" stroke="#0f172a" stroke-width="1.5"/>

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

  <!-- Kurir Ekspedisi (Orthogonal: left to x=80, down to y=340) -->
  <text x="110" y="325" font-size="9" font-weight="bold" fill="#0f172a">[Kurir]</text>
  <path d="M 115 290 L 80 290 L 80 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="25" y="340" width="115" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="82" y="357" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Pilih Alamat &amp;</text>
  <text x="82" y="370" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Layanan Kurir</text>
  <path d="M 140 361 L 330 361" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Pick-up in Store (Orthogonal: right to x=230, down to y=340, right to x=510) -->
  <text x="205" y="305" font-size="9" font-weight="bold" fill="#0f172a">[Pick-up]</text>
  <path d="M 195 290 L 230 290 L 230 340 L 510 340" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- SISTEM -->
  <rect x="330" y="340" width="160" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="410" y="357" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Hitung Tarif Ongkir</text>
  <text x="410" y="370" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">(API RajaOngkir)</text>

  <rect x="510" y="325" width="140" height="35" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="580" y="346" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Ongkos Kirim Rp 0</text>

  <!-- Merge Diamond for Shipping Method (Orthogonal) -->
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

  <!-- Gagal (Orthogonal) -->
  <text x="735" y="630" font-size="9" font-weight="bold" fill="#dc2626">[Gagal/Expired]</text>
  <path d="M 770 595 L 720 595 L 720 645 L 590 645" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="400" y="625" width="190" height="42" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="495" y="643" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">Update Status "Dibatalkan"</text>
  <text x="495" y="656" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">&amp; Kirim Notifikasi Gagal</text>
  <path d="M 400 646 L 255 646" fill="none" stroke="#dc2626" stroke-width="1.5" marker-end="url(#arrow-red)"/>

  <rect x="65" y="630" width="180" height="35" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="155" y="651" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Menerima Notifikasi Gagal</text>
  <line x1="155" y1="665" x2="155" y2="695" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="155" cy="707" r="12" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="155" cy="707" r="7" fill="#0f172a"/>

  <!-- Sukses (Orthogonal: down to y=740, left to x=590) -->
  <text x="820" y="635" font-size="9.5" font-weight="bold" fill="#16a34a">[Settlement/Sukses]</text>
  <path d="M 810 615 L 810 740 L 590 740" fill="none" stroke="#16a34a" stroke-width="1.5" marker-end="url(#arrow-green)"/>

  <!-- SISTEM: WEBHOOK & RESERVASI -->
  <rect x="380" y="720" width="210" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="485" y="737" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Webhook Midtrans Diterima:</text>
  <text x="485" y="751" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Evaluasi Kategori Komoditas</text>
  <line x1="485" y1="762" x2="485" y2="790" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <polygon points="485,790 525,810 485,830 445,810" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="485" y="814" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Kategori?</text>

  <!-- Obat: FEFO (Orthogonal: left to x=390, down to y=855) -->
  <text x="390" y="825" font-size="9" font-weight="bold" fill="#0f172a">[Obat]</text>
  <path d="M 445 810 L 390 810 L 390 855" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="320" y="855" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="390" y="872" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Alokasikan Stok Batch</text>
  <text x="390" y="885" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Rekomendasi FEFO</text>

  <!-- Alkes: SN (Orthogonal: right to x=580, down to y=855) -->
  <text x="560" y="825" font-size="9" font-weight="bold" fill="#0f172a">[Alat Kesehatan]</text>
  <path d="M 525 810 L 580 810 L 580 855" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <rect x="510" y="855" width="140" height="42" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="580" y="872" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Alokasikan Unit dengan</text>
  <text x="580" y="885" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Serial Number (SN)</text>

  <!-- Merge (Orthogonal) -->
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

print("Refined 3.10 and 3.11 ready.")
