import os
import re

files_to_check = {
    'stok_opname': 'inventori/stok_opname.php',
    'konversi_uom': 'inventori/konversi_uom.php',
    'histori_konversi': 'inventori/histori_konversi.php',
    'anomali_fefo': 'laporan/anomali_fefo.php',
    'pengaturan_web': 'admin/pengaturan_web.php',
    'profil_cust': 'ecommerce/profil.php',
    'detail_produk': 'ecommerce/produk.php',
    'cart': 'ecommerce/cart.php'
}

for name, rel_path in files_to_check.items():
    if os.path.exists(rel_path):
        with open(rel_path, 'r', encoding='utf-8', errors='ignore') as f:
            c = f.read()
        # Find headings or card titles
        h_tags = re.findall(r'<h[1-4][^>]*>(.*?)</h[1-4]>', c)
        clean_h = [re.sub(r'<[^>]+>', '', h).strip() for h in h_tags if h.strip()]
        # Find buttons
        btns = re.findall(r'<button[^>]*>(.*?)</button>', c)
        clean_btns = [re.sub(r'<[^>]+>', '', b).strip() for b in btns if b.strip()][:6]
        print(f"=== {name} ({rel_path}) ===")
        print("  Headings:", clean_h[:5])
        print("  Buttons :", clean_btns)
    else:
        print(f"=== {name} NOT FOUND ===")

