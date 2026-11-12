import os
import subprocess
import tempfile
import re

php_exe = r'c:\xampp\php\php.exe'
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
cache_dir = os.path.abspath(r'scratch\cache')

# 1. Capture 08_Penerimaan_Barang (Active populated input form)
print("Rendering 08_Penerimaan_Barang (Populated Form)...")
html_name = "08_Penerimaan_Barang.html"
html_path = os.path.join(cache_dir, html_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/tambah_stok.php', html_name, ''], capture_output=True)

with open(html_path, 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Populate Supplier
html = html.replace('<option value="">-- Pilih Supplier --</option>', '<option value="1" selected>PT. Kimia Farma Trading &amp; Distribution</option>')
# Populate No Faktur & Tanggal
html = html.replace('name="no_faktur" required placeholder="Contoh: INV-2026/X/001"', 'name="no_faktur" value="FAK-KF-2026-1188" required')
html = html.replace('name="tgl_terima" required', 'name="tgl_terima" value="2026-11-12" required')

# Pre-fill row 1 in incoming goods table
# Replace first empty row with populated row
row_1 = """
<tr class="item-row border-b border-zcBrd hover:bg-slate-50/50 bg-white">
    <td class="p-2">
        <select name="items[0][id_variasi]" required class="w-full text-xs border border-zcBrd rounded-lg p-2 bg-slate-50 font-bold text-slate-800">
            <option value="1" selected>Amoxicillin 500mg &mdash; Box (1 Box = 10 Strip)</option>
        </select>
    </td>
    <td class="p-1 text-center">
        <input type="number" name="items[0][qty_pesan]" value="50" min="1" class="w-16 text-xs text-center border border-zcBrd rounded-lg p-1.5 font-bold text-slate-700">
    </td>
    <td class="p-1 text-center">
        <input type="number" name="items[0][qty_terima]" value="50" min="1" class="w-16 text-xs text-center border border-emerald-300 rounded-lg p-1.5 bg-emerald-50 text-emerald-800 font-bold">
    </td>
    <td class="p-1 text-center font-bold text-slate-600 text-xs">Box</td>
    <td class="p-1">
        <input type="text" name="items[0][no_batch]" value="AMX-2026-02" class="w-full text-xs font-mono uppercase border border-zcBrd rounded-lg p-1.5 font-bold text-slate-800">
    </td>
    <td class="p-1">
        <input type="date" name="items[0][tgl_exp]" value="2028-12-15" class="w-full text-xs border border-zcBrd rounded-lg p-1.5 font-medium text-slate-700">
    </td>
    <td class="p-1">
        <input type="number" name="items[0][harga_beli]" value="40000" class="w-full text-xs text-right border border-zcBrd rounded-lg p-1.5 font-mono font-bold text-slate-800">
    </td>
    <td class="p-2 text-right font-mono font-bold text-xs text-slate-900 bg-slate-50/70">
        Rp 2.000.000
    </td>
</tr>
<tr class="item-row border-b border-zcBrd hover:bg-slate-50/50 bg-white">
    <td class="p-2">
        <select name="items[1][id_variasi]" required class="w-full text-xs border border-zcBrd rounded-lg p-2 bg-slate-50 font-bold text-slate-800">
            <option value="2" selected>Betadine Solution 15ml &mdash; Botol (1 Botol = 15ml)</option>
        </select>
    </td>
    <td class="p-1 text-center">
        <input type="number" name="items[1][qty_pesan]" value="30" min="1" class="w-16 text-xs text-center border border-zcBrd rounded-lg p-1.5 font-bold text-slate-700">
    </td>
    <td class="p-1 text-center">
        <input type="number" name="items[1][qty_terima]" value="30" min="1" class="w-16 text-xs text-center border border-emerald-300 rounded-lg p-1.5 bg-emerald-50 text-emerald-800 font-bold">
    </td>
    <td class="p-1 text-center font-bold text-slate-600 text-xs">Botol</td>
    <td class="p-1">
        <input type="text" name="items[1][no_batch]" value="BET-2026-03" class="w-full text-xs font-mono uppercase border border-zcBrd rounded-lg p-1.5 font-bold text-slate-800">
    </td>
    <td class="p-1">
        <input type="date" name="items[1][tgl_exp]" value="2028-11-30" class="w-full text-xs border border-zcBrd rounded-lg p-1.5 font-medium text-slate-700">
    </td>
    <td class="p-1">
        <input type="number" name="items[1][harga_beli]" value="12000" class="w-full text-xs text-right border border-zcBrd rounded-lg p-1.5 font-mono font-bold text-slate-800">
    </td>
    <td class="p-2 text-right font-mono font-bold text-xs text-slate-900 bg-slate-50/70">
        Rp 360.000
    </td>
</tr>
"""
html = re.sub(r'<tbody id="items_body">.*?</tbody>', f'<tbody id="items_body">{row_1}</tbody>', html, flags=re.DOTALL)

# Update grand total display
html = re.sub(r'id="grand_total_display"[^>]*>.*?</span>', 'id="grand_total_display">Rp 2.360.000</span>', html)

with open(html_path, 'w', encoding='utf-8') as f:
    f.write(html)

temp_dir = tempfile.mkdtemp()
png_penerimaan = os.path.join(out_dir, "08_Penerimaan_Barang.png")
chrome_cmd = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={png_penerimaan}',
    '--window-size=1440,950', f"http://localhost/inventory_zencare/scratch/cache/{html_name}"
]
subprocess.run(chrome_cmd, capture_output=True)
print(f"Captured 08_Penerimaan_Barang.png (Populated Form): {os.path.getsize(png_penerimaan)} bytes")

# 2. Capture 46_Cetak_Label_Stiker_Batch (Sticker Barcode Grid)
print("\nRendering 46_Cetak_Label_Stiker_Batch...")
html_stiker = "46_Cetak_Label_Stiker_Batch.html"
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/cetak_batch_stiker.php', html_stiker, 'id_penerimaan=1&mode=a4'], capture_output=True)
png_stiker = os.path.join(out_dir, "46_Cetak_Label_Stiker_Batch.png")
temp_dir2 = tempfile.mkdtemp()
chrome_cmd2 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir2}', f'--screenshot={png_stiker}',
    '--window-size=1200,950', f"http://localhost/inventory_zencare/scratch/cache/{html_stiker}"
]
subprocess.run(chrome_cmd2, capture_output=True)
print(f"Captured 46_Cetak_Label_Stiker_Batch.png: {os.path.getsize(png_stiker)} bytes")

