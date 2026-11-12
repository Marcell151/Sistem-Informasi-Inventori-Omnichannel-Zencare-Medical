import os
import shutil

img_dir = r"lain\File PPT\UI_UX_Asli"
brain_dir = r"C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784"

checks = [
    '17_Laporan_Penerimaan_Barang.png',
    '17_Laporan_Anomali_FEFO.png',
    '18_Laporan_Mutasi_Konversi.png',
    '18_Manajemen_Pengguna.png',
    '14_Laporan_Penjualan.png',
    '15_Laporan_Persediaan.png',
    '16_Laporan_Logistik_Medis.png',
    '09_Mutasi_Stok.png',
    '36_Form_Mutasi_Stok.png',
    '06_Satuan_UOM.png',
    '35_Form_Konversi_UOM.png',
    '12_Konversi_Satuan.png'
]

for c in checks:
    src = os.path.join(img_dir, c)
    dst = os.path.join(brain_dir, f"check_{c}")
    shutil.copy(src, dst)
    print(f"Copied {c}: {os.path.getsize(src)} bytes")

