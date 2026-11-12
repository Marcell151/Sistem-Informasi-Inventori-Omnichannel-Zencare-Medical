import re
from bs4 import BeautifulSoup

# Test with a small HTML fragment
html_sample = """
<p class="text-xs font-serif text-slate-700 text-justify indent-8 leading-relaxed">
    Halaman autentikasi utama satu pintu bagi seluruh staf internal toko kesehatan ZenCare Medical yang mencakup peran Superadmin, Admin, dan Kasir. Antarmuka menerapkan formulir masukan nama pengguna (username) dan kata sandi (password), identitas visual resmi ZenCare Medical, serta indikator verifikasi kredensial. Pada sisi backend, sistem memvalidasi kredensial terhadap tabel users menggunakan pencocokan hash terenkripsi. Berdasarkan nilai atribut peran (role), sistem secara otomatis mengarahkan alur kerja ke antarmuka yang tepat: Kasir diarahkan langsung menuju Terminal Kasir guna mempercepat transaksi penjualan luring di gerai fisik, Admin diarahkan ke dashboard operasional toko dan pengelolaan logistik, sedangkan Superadmin diarahkan ke dashboard eksekutif dan konfigurasi manajerial. Gerbang ini terproteksi ketat dari akses luar, di mana akun Pelanggan toko online dicegah masuk melalui formulir staf ini.
</p>
"""

# Let's see how BeautifulSoup handles NavigableString replacement
soup = BeautifulSoup(html_sample, 'html.parser')
print('Parsed successfully!')

