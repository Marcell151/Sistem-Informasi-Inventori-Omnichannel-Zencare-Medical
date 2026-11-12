import os
import shutil

img_dir = r"lain\File PPT\UI_UX_Asli"
brain_dir = r"C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784"

import sys
sys.path.append('scratch')
from build_katalog_50 import items

print(f"Total items: {len(items)}")

# Let's inspect the opname images and dashboard images
targets = [
    '02_Dashboard_Admin.png',
    '13_Dashboard_Superadmin.png',
    '07_Direktori_Supplier.png',
    '10_Kartu_Stok.png',
    '18_Manajemen_Pengguna.png',
    '11_Stok_Opname_Riwayat.png',
    '30_Stok_Opname_Mulai_Sesi.png',
    '31_Stok_Opname_Blind_Count_Kasir.png',
    '32_Stok_Opname_Verifikasi_Admin.png',
    '44_Modal_Mutasi_Opname.png',
    '33_Cetak_Berita_Acara_Opname.png'
]

for t in targets:
    src = os.path.join(img_dir, t)
    dst = os.path.join(brain_dir, f"check_{t}")
    shutil.copy(src, dst)
    print(f"Copied {t} ({os.path.getsize(src)} bytes)")


