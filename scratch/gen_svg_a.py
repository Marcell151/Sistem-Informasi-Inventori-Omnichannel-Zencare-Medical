# -*- coding: utf-8 -*-
"""
Generator for Activity Diagram A: Autentikasi (Login)
"""

def generate_svg_a():
    svg = '''<svg viewBox="0 0 960 1120" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="font-family: Arial, sans-serif;">
  <defs>
    <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 0 1 L 10 5 L 0 9 z" fill="#0f172a" />
    </marker>
    <filter id="shadow" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="1" dy="2" stdDeviation="2" flood-opacity="0.08"/>
    </filter>
  </defs>

  <!-- Main Container & Header -->
  <rect x="10" y="10" width="940" height="1100" fill="#ffffff" stroke="#0f172a" stroke-width="2"/>
  
  <!-- Title Bar -->
  <rect x="10" y="10" width="940" height="35" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
  <text x="480" y="33" text-anchor="middle" font-size="14" font-weight="bold" fill="#0f172a">Autentikasi (Login)</text>

  <!-- Swimlane Headers -->
  <rect x="10" y="45" width="280" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="150" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Kasir / Admin / Superadmin</text>

  <rect x="290" y="45" width="300" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="440" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Pelanggan</text>

  <rect x="590" y="45" width="360" height="32" fill="#f1f5f9" stroke="#0f172a" stroke-width="1"/>
  <text x="770" y="66" text-anchor="middle" font-size="12" font-weight="bold" fill="#0f172a">Sistem</text>

  <!-- Vertical Dividing Lines -->
  <line x1="290" y1="45" x2="290" y2="1110" stroke="#0f172a" stroke-width="1.5"/>
  <line x1="590" y1="45" x2="590" y2="1110" stroke="#0f172a" stroke-width="1.5"/>

  <!-- ================= LANE 1: KASIR / ADMIN / SUPERADMIN ================= -->
  <!-- Initial Node -->
  <circle cx="150" cy="105" r="10" fill="#0f172a"/>
  <line x1="150" y1="115" x2="150" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box 1 -->
  <rect x="50" y="140" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="165" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Mengakses halaman Login</text>
  <line x1="150" y1="182" x2="150" y2="215" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box 2 -->
  <rect x="50" y="215" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="234" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Memasukkan Username</text>
  <text x="150" y="248" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">dan Password</text>
  <line x1="150" y1="257" x2="150" y2="290" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box 3 -->
  <rect x="50" y="290" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="150" y="315" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Menekan tombol Login</text>
  <!-- Connector from Box 3 to System -->
  <path d="M 150 332 L 150 565 L 670 565" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= LANE 2: PELANGGAN ================= -->
  <!-- Initial Node -->
  <circle cx="430" cy="105" r="10" fill="#0f172a"/>
  <line x1="430" y1="115" x2="430" y2="140" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box 1 -->
  <rect x="330" y="140" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="430" y="165" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Mengakses halaman E-Commerce</text>
  <line x1="430" y1="182" x2="430" y2="215" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box 2 -->
  <rect x="330" y="215" width="200" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="430" y="240" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a">Memilih menu Masuk/Daftar</text>
  <line x1="430" y1="257" x2="430" y2="285" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Apakah Punya Akun? -->
  <polygon points="430,285 470,305 430,325 390,305" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="495" y="300" font-size="10" font-weight="bold" fill="#0f172a">Apakah Punya</text>
  <text x="495" y="312" font-size="10" font-weight="bold" fill="#0f172a">Akun?</text>

  <!-- Branch [Ya] (Left of lane 2) -->
  <text x="365" y="320" font-size="10" font-weight="bold" fill="#0f172a">[Ya]</text>
  <path d="M 390 305 L 350 305 L 350 350" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  
  <rect x="300" y="350" width="105" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="352" y="369" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Input Username</text>
  <text x="352" y="383" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">&amp; Password</text>
  <line x1="352" y1="395" x2="352" y2="425" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="300" y="425" width="105" height="40" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="352" y="444" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Menekan tombol</text>
  <text x="352" y="456" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Login</text>

  <!-- Branch [Tidak] (Right of lane 2) -->
  <text x="475" y="335" font-size="10" font-weight="bold" fill="#0f172a">[Tidak]</text>
  <path d="M 470 305 L 505 305 L 505 350" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="430" y="350" width="150" height="45" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="505" y="367" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">Input Nama, User, Pass,</text>
  <text x="505" y="381" text-anchor="middle" font-size="9" font-weight="bold" fill="#0f172a">No WA, Email, Alamat</text>
  <line x1="505" y1="395" x2="505" y2="425" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <rect x="450" y="425" width="110" height="35" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="505" y="446" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Buat Akun</text>

  <!-- From Buat Akun to System (Menyimpan Data Baru) -->
  <path d="M 560 442 L 670 442" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- From System (Menyimpan Data Baru) loop back to Input Login Pelanggan -->
  <path d="M 750 465 L 750 495 L 352 495 L 352 475" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Merge Diamond for Pelanggan Login -->
  <polygon points="410,500 425,510 410,520 395,510" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 352 465 L 352 510 L 395 510" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 425 510 L 670 575" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- ================= LANE 3: SISTEM ================= -->
  <!-- Box: Menyimpan Data Profil Baru -->
  <rect x="670" y="420" width="180" height="45" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="760" y="440" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Menyimpan Data</text>
  <text x="760" y="454" text-anchor="middle" font-size="10" font-weight="bold" fill="#0f172a">Profil Baru</text>

  <!-- Box: Memverifikasi Kredensial -->
  <rect x="670" y="550" width="180" height="48" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="760" y="570" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Memverifikasi kredensial</text>
  <text x="760" y="585" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">ke Database</text>
  <line x1="760" y1="598" x2="760" y2="625" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Apakah Data Valid? -->
  <polygon points="760,625 805,645 760,665 715,645" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="815" y="642" font-size="10" font-weight="bold" fill="#0f172a">Apakah Data</text>
  <text x="815" y="654" font-size="10" font-weight="bold" fill="#0f172a">Valid?</text>

  <!-- Branch [Tidak] -->
  <text x="810" y="625" font-size="10" font-weight="bold" fill="#dc2626">[Tidak]</text>
  <path d="M 805 645 L 860 645 L 860 495" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <rect x="790" y="470" width="145" height="42" rx="6" fill="#fef2f2" stroke="#dc2626" stroke-width="1.5"/>
  <text x="862" y="488" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">Menampilkan Pesan</text>
  <text x="862" y="501" text-anchor="middle" font-size="9" font-weight="bold" fill="#dc2626">Username/Password Salah</text>
  <!-- Loop back from error to login inputs -->
  <path d="M 862 470 L 862 235 L 255 235" fill="none" stroke="#dc2626" stroke-width="1.2" stroke-dasharray="4" marker-end="url(#arrow)"/>

  <!-- Branch [Ya] -->
  <text x="768" y="680" font-size="10" font-weight="bold" fill="#16a34a">[Ya]</text>
  <line x1="760" y1="665" x2="760" y2="695" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Box: Mengecek Role -->
  <rect x="670" y="695" width="180" height="42" rx="8" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="760" y="714" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">Mengecek Role</text>
  <text x="760" y="728" text-anchor="middle" font-size="10.5" font-weight="bold" fill="#0f172a">(Otorisasi Akses)</text>
  <line x1="760" y1="737" x2="760" y2="765" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>

  <!-- Decision: Pengecekan Role / Hak Akses (4 Cabang!) -->
  <polygon points="760,765 815,790 760,815 705,790" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <text x="760" y="794" text-anchor="middle" font-size="9.5" font-weight="bold" fill="#0f172a">Role Akses</text>

  <!-- 4 Roles Connectors -->
  <!-- 1. Superadmin (Left top) -->
  <path d="M 705 790 L 610 790 L 610 835" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="615" y="810" font-size="9.5" font-weight="bold" fill="#0f172a">[Superadmin]</text>
  <rect x="595" y="835" width="160" height="55" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="675" y="852" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Menampilkan Seluruh Modul</text>
  <text x="675" y="865" text-anchor="middle" font-size="8.5" fill="#0f172a">Admin + Kelola User,</text>
  <text x="675" y="878" text-anchor="middle" font-size="8.5" fill="#0f172a">Laporan &amp; Pengaturan Sistem</text>

  <!-- 2. Admin (Right top) -->
  <path d="M 815 790 L 910 790 L 910 835" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="850" y="810" font-size="9.5" font-weight="bold" fill="#0f172a">[Admin]</text>
  <rect x="770" y="835" width="165" height="55" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="852" y="852" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Menampilkan Dashboard,</text>
  <text x="852" y="865" text-anchor="middle" font-size="8.5" fill="#0f172a">Master Data, Pesanan Online,</text>
  <text x="852" y="878" text-anchor="middle" font-size="8.5" fill="#0f172a">Logistik &amp; Stok Opname</text>

  <!-- 3. Kasir (Left bottom) -->
  <path d="M 730 805 L 675 805 L 675 915" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="635" y="905" font-size="9.5" font-weight="bold" fill="#0f172a">[Kasir]</text>
  <rect x="595" y="915" width="160" height="48" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="675" y="933" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Mengarahkan Langsung ke</text>
  <text x="675" y="946" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Terminal Transaksi Kasir (POS)</text>

  <!-- 4. Pelanggan (Right bottom) -->
  <path d="M 790 805 L 852 805 L 852 915" fill="none" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <text x="860" y="905" font-size="9.5" font-weight="bold" fill="#0f172a">[Pelanggan]</text>
  <rect x="770" y="915" width="165" height="48" rx="6" fill="#ffffff" stroke="#0f172a" stroke-width="1.5" filter="url(#shadow)"/>
  <text x="852" y="933" text-anchor="middle" font-size="8.5" font-weight="bold" fill="#0f172a">Menampilkan E-Commerce,</text>
  <text x="852" y="946" text-anchor="middle" font-size="8.5" fill="#0f172a">Katalog, Keranjang &amp; Checkout</text>

  <!-- Merge Diamond -->
  <polygon points="760,990 780,1005 760,1020 740,1005" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <!-- Connectors into Merge -->
  <path d="M 675 890 L 675 1005 L 740 1005" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 852 890 L 852 1005 L 780 1005" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 675 963 L 675 1005" fill="none" stroke="#0f172a" stroke-width="1.5"/>
  <path d="M 852 963 L 852 1005" fill="none" stroke="#0f172a" stroke-width="1.5"/>

  <!-- Final Node -->
  <line x1="760" y1="1020" x2="760" y2="1055" stroke="#0f172a" stroke-width="1.5" marker-end="url(#arrow)"/>
  <circle cx="760" cy="1070" r="14" fill="#ffffff" stroke="#0f172a" stroke-width="1.5"/>
  <circle cx="760" cy="1070" r="8" fill="#0f172a"/>
</svg>'''
    return svg

print("Testing generate_svg_a()... Length:", len(generate_svg_a()))

