import os
import shutil

img_dir = r"lain\File PPT\UI_UX_Asli"
brain_dir = r"C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784"

checks2 = [
    '34_Form_Input_Master_Produk.png',
    '45_Form_Input_Produk_Variasi.png',
    '47_Form_Tambah_Supplier.png',
    '48_Form_Tambah_User.png'
]

for c in checks2:
    src = os.path.join(img_dir, c)
    dst = os.path.join(brain_dir, f"check_{c}")
    shutil.copy(src, dst)
    print(f"Copied {c}: {os.path.getsize(src)} bytes")

