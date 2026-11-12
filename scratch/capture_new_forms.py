import os
import subprocess
import time
import tempfile
import re

php_exe = r'c:\xampp\php\php.exe'
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
cache_dir = os.path.abspath(r'scratch\cache')

os.makedirs(out_dir, exist_ok=True)
os.makedirs(cache_dir, exist_ok=True)

new_items = [
    # 1. Form Input Produk Variasi Multi-UOM
    {
        'key': '45_Form_Input_Produk_Variasi',
        'role': 'admin',
        'target_rel': 'admin/master_produk.php',
        'query': '',
        'modal_id': 'modal_tambah_variasi',
        'width': 1280,
        'height': 900,
        'populate': 'variasi'
    },
    # 2. Form Input Penerimaan Logistik Barang Masuk
    {
        'key': '46_Form_Penerimaan_Barang',
        'role': 'admin',
        'target_rel': 'inventori/tambah_stok.php',
        'query': '',
        'modal_id': None,
        'width': 1440,
        'height': 950,
        'populate': 'penerimaan'
    },
    # 3. Form Tambah Supplier Baru
    {
        'key': '47_Form_Tambah_Supplier',
        'role': 'admin',
        'target_rel': 'admin/master_supplier.php',
        'query': '',
        'modal_id': 'modal_tambah',
        'width': 1280,
        'height': 850,
        'populate': 'supplier'
    },
    # 4. Form Tambah Pengguna Internal RBAC
    {
        'key': '48_Form_Tambah_User',
        'role': 'superadmin',
        'target_rel': 'admin/master_user.php',
        'query': '',
        'modal_id': 'modal_tambah_user',
        'width': 1280,
        'height': 850,
        'populate': 'user'
    }
]

for item in new_items:
    key = item['key']
    html_name = f"{key}.html"
    png_name = f"{key}.png"
    png_path = os.path.join(out_dir, png_name)
    html_path = os.path.join(cache_dir, html_name)

    print(f"Rendering {key}...")
    render_cmd = [php_exe, 'scratch/render_page.php', item['role'], item['target_rel'], html_name, item['query']]
    subprocess.run(render_cmd, capture_output=True, text=True)

    if not os.path.exists(html_path):
        print(f"[ERR] Failed to render {html_name}")
        continue

    with open(html_path, 'r', encoding='utf-8', errors='ignore') as f:
        html = f.read()

    # Pre-populate fields and show modal
    if item['populate'] == 'variasi':
        # Show modal
        html = html.replace('id="modal_tambah_variasi" class="hidden', 'id="modal_tambah_variasi" class="flex')
        html = html.replace('<span id="var_parent_name" class="text-zcNavy"></span>', '<span id="var_parent_name" class="text-zcNavy">Amoxicillin 500mg</span>')
        html = html.replace('name="sku_variasi" required placeholder="OBT-2026-0004-100ML"', 'name="sku_variasi" value="OBT-AMX-500MG-BOX" required')
        html = html.replace('name="nama_variasi" required placeholder="100ml / Merah / etc"', 'name="nama_variasi" value="Strip Isi 10" required')
        html = html.replace('name="satuan_kecil" required placeholder="Pcs/Strip"', 'name="satuan_kecil" value="Strip" required')
        html = html.replace('name="satuan_besar" required placeholder="Box/Karton"', 'name="satuan_besar" value="Box" required')
        html = html.replace('name="rasio_konversi" required placeholder="12"', 'name="rasio_konversi" value="10" required')
        html = html.replace('name="harga_beli" required placeholder="10000"', 'name="harga_beli" value="40000" required')
        html = html.replace('name="harga_jual_besar" required placeholder="120000"', 'name="harga_jual_besar" value="48000" required')
        html = html.replace('name="harga_jual_kecil" required placeholder="12000"', 'name="harga_jual_kecil" value="5000" required')
        html = html.replace('name="stok_minimum_kecil" placeholder="10"', 'name="stok_minimum_kecil" value="20"')
        html = html.replace('name="stok_minimum_besar" placeholder="2"', 'name="stok_minimum_besar" value="2"')
    
    elif item['populate'] == 'supplier':
        html = html.replace('id="modal_tambah" class="hidden', 'id="modal_tambah" class="flex')
        html = html.replace('name="nama" required placeholder="PT. Alkes Medika"', 'name="nama" value="PT. Medika Distribusi Nusantara" required')
        html = html.replace('name="kontak" placeholder="Bpk. Budi Santoso"', 'name="kontak" value="Bpk. Hendra Wijaya"')
        html = html.replace('name="telepon" placeholder="08123456789"', 'name="telepon" value="081234567890"')
        html = html.replace('name="email" placeholder="sales@alkesmedika.com"', 'name="email" value="sales@medikadistribusi.co.id"')
        html = html.replace('<textarea name="alamat"', '<textarea name="alamat">Kawasan Industri Rungkut Blok B-12, Surabaya</textarea><textarea style="display:none"')

    elif item['populate'] == 'user':
        html = html.replace('id="modal_tambah_user" class="hidden', 'id="modal_tambah_user" class="flex')
        html = html.replace('name="username" required placeholder="********"', 'name="username" value="kasir_pagi" required')
        html = html.replace('name="nama_lengkap" required placeholder="John Doe"', 'name="nama_lengkap" value="Dewi Lestari, A.Md.Farm"', 1)
        html = html.replace('name="email" placeholder="user@zencare.com"', 'name="email" value="dewi.kasir@zencare.co.id"')
        html = html.replace('name="password" required placeholder="********"', 'name="password" value="********" required')
        html = html.replace('<option value="kasir">Kasir</option>', '<option value="kasir" selected>Kasir (Terminal POS Luring)</option>')

    with open(html_path, 'w', encoding='utf-8') as f:
        f.write(html)

    # Capture with chrome
    temp_dir = tempfile.mkdtemp()
    cached_url = f"http://localhost/inventory_zencare/scratch/cache/{html_name}"
    chrome_cmd = [
        chrome_exe,
        '--headless=new',
        '--disable-gpu',
        '--no-sandbox',
        f'--user-data-dir={temp_dir}',
        f'--screenshot={png_path}',
        f'--window-size={item["width"]},{item["height"]}',
        cached_url
    ]

    subprocess.run(chrome_cmd, capture_output=True)
    if os.path.exists(png_path):
        size_kb = os.path.getsize(png_path) / 1024
        print(f"[OK] Captured {png_name} ({size_kb:.1f} KB)")
    else:
        print(f"[ERR] Failed to capture {png_name}")

