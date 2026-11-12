import os
import re

print("=== VERIFIKASI KESELURUHAN PENYESUAIAN FINAL ===")

# 1. Check forbidden words across key files
forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
files_to_check = [
    r'inventori\stok_opname.php',
    r'inventori\cetak_berita_acara_opname.php',
    r'config\opname_helper.php',
    r'config\layout.php',
    r'Demo_Bersih.sql',
    r'C:\xampp\backup_data\Demo_Bersih.sql',
    r'laporan_ta\DOKUMENTASI_TESTING_STOK_OPNAME.html'
]

all_clean = True
for fpath in files_to_check:
    if os.path.exists(fpath):
        with open(fpath, 'r', encoding='utf-8', errors='ignore') as f:
            c = f.read()
        found = {w: c.lower().count(w) for w in forbidden if c.lower().count(w) > 0}
        if found:
            print(f"[FAIL] {fpath}: {found}")
            all_clean = False
        else:
            print(f"[OK] {fpath}: Bersih (0 kata terlarang)")
    else:
        print(f"[WARN] File not found: {fpath}")

# 2. Check data-stok-sistem in stok_opname.php
with open(r'inventori\stok_opname.php', 'r', encoding='utf-8') as f:
    opname_src = f.read()

if 'data-stok-sistem' in opname_src:
    print("[FAIL] data-stok-sistem masih ditemukan di stok_opname.php!")
    all_clean = False
else:
    print("[OK] data-stok-sistem: Telah dihapus seluruhnya dari DOM!")

# 3. Check A4 landscape in cetak_berita_acara_opname.php
with open(r'inventori\cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    ba_src = f.read()

if 'size: A4 landscape;' in ba_src:
    print("[OK] Berita Acara: Terverifikasi menggunakan size: A4 landscape.")
else:
    print("[FAIL] Berita Acara: Belum menggunakan A4 landscape!")
    all_clean = False

if 'tr.row-anomali td' in ba_src:
    print("[OK] Berita Acara: Border sel merah (tr.row-anomali td) terverifikasi aktif.")
else:
    print("[FAIL] Berita Acara: Border sel merah belum aktif!")
    all_clean = False

# 4. Check Backup Data directory
if os.path.exists(r"C:\xampp\backup_data\Demo_Bersih.sql"):
    print("[OK] Direktori aman luar web C:\\xampp\\backup_data\\Demo_Bersih.sql terverifikasi (Size: " + str(os.path.getsize(r"C:\xampp\backup_data\Demo_Bersih.sql")) + " bytes)")
else:
    print("[FAIL] C:\\xampp\\backup_data\\Demo_Bersih.sql tidak ditemukan!")
    all_clean = False

print("\nHASIL AKHIR AUDIT SISTEM:", "LULUS TERVERIFIKASI" if all_clean else "PERLU TINDAKAN")

