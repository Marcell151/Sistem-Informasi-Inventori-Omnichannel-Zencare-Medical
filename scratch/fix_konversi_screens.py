import os
import subprocess
import tempfile
import re

php_exe = r'c:\xampp\php\php.exe'
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
cache_dir = os.path.abspath(r'scratch\cache')

# 1. Render and capture 35_Form_Konversi_UOM (Populated Form)
print("Rendering 35_Form_Konversi_UOM...")
html_form_name = "35_Form_Konversi_UOM.html"
html_form_path = os.path.join(cache_dir, html_form_name)
render_cmd = [php_exe, 'scratch/render_page.php', 'admin', 'inventori/konversi_uom.php', html_form_name, '']
subprocess.run(render_cmd, capture_output=True, text=True)

with open(html_form_path, 'r', encoding='utf-8', errors='ignore') as f:
    html = f.read()

# Pre-populate product selection, batch, qty, and show result
# Select Amoxicillin 500mg
html = re.sub(
    r'(<option value="[^"]*"[^>]*>.*?Amoxicillin 500mg.*?</option>)',
    r'\1',
    html, flags=re.DOTALL
)
# Make batch container visible and populate
batch_html = """
<div id="batch_container" class="block">
    <label class="block text-sm font-semibold text-zcTxt mb-1.5">Pilih Batch Asal (Satuan Besar) *</label>
    <select name="no_batch_asal" id="no_batch_asal" required class="select2 w-full text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-white font-medium">
        <option value="AMX-2026-01" selected>AMX-2026-01 (Exp: 15/12/2027) &mdash; Sisa Fisik: 4 Box</option>
    </select>
</div>
"""
html = re.sub(r'<div id="batch_container" class="hidden">.*?</div>', batch_html, html, flags=re.DOTALL)

# Make qty container visible and populated
qty_html = """
<div id="qty_container" class="block">
    <label class="block text-sm font-semibold text-zcTxt mb-1.5">Jumlah Satuan Besar yang Akan Dipecah *</label>
    <div class="flex items-center gap-3">
        <input type="number" name="qty_box" id="qty_box" required min="1" value="1" class="w-32 text-sm border border-zcBrd rounded-xl px-3.5 py-2.5 focus:outline-none focus:border-zc bg-white font-bold text-slate-800">
        <span id="satuan_besar_lbl" class="text-sm font-bold text-zcTxt bg-slate-100 px-3 py-2 rounded-lg border border-slate-200">Box</span>
        <span class="text-zcMut mx-1">
            <svg class="w-5 h-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </span>
        <input type="text" id="hasil_konversi" readonly value="10" class="w-32 text-sm border border-emerald-300 bg-emerald-50 text-emerald-700 font-bold rounded-xl px-3.5 py-2.5 text-center">
        <span id="satuan_kecil_lbl" class="text-sm font-bold text-emerald-700 bg-emerald-100/60 px-3 py-2 rounded-lg border border-emerald-200">Strip</span>
    </div>
    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2">
        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <b>Kalkulasi Otomatis Sistem Multi-UOM:</b> 1 Box Amoxicillin 500mg akan dideplesi dari saldo kemasan besar batch <code>AMX-2026-01</code>, kemudian secara atomik diterbitkan sub-batch eceran <code>AMX-2026-01.A</code> dengan saldo masuk <b>+10 Strip</b>.
        </div>
    </div>
</div>
"""
html = re.sub(r'<div id="qty_container" class="hidden">.*?</div>', qty_html, html, flags=re.DOTALL)

# Ensure select has chosen option visually
html = html.replace('<option value="">-- Pilih Obat --</option>', '<option value="1" selected>OBAT &mdash; Amoxicillin 500mg (1 Box = 10 Strip)</option>')

with open(html_form_path, 'w', encoding='utf-8') as f:
    f.write(html)

temp_dir = tempfile.mkdtemp()
png_form_path = os.path.join(out_dir, "35_Form_Konversi_UOM.png")
cached_form_url = f"http://localhost/inventory_zencare/scratch/cache/{html_form_name}"
chrome_cmd = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={png_form_path}',
    '--window-size=1440,900', cached_form_url
]
subprocess.run(chrome_cmd, capture_output=True)
print(f"Captured 35_Form_Konversi_UOM.png: {os.path.getsize(png_form_path)} bytes")

# 2. Render and capture 12_Konversi_Satuan from histori_konversi.php
print("\nRendering 12_Konversi_Satuan from histori_konversi.php...")
html_hist_name = "12_Konversi_Satuan.html"
html_hist_path = os.path.join(cache_dir, html_hist_name)
render_cmd2 = [php_exe, 'scratch/render_page.php', 'admin', 'inventori/histori_konversi.php', html_hist_name, '']
subprocess.run(render_cmd2, capture_output=True, text=True)

temp_dir2 = tempfile.mkdtemp()
png_hist_path = os.path.join(out_dir, "12_Konversi_Satuan.png")
cached_hist_url = f"http://localhost/inventory_zencare/scratch/cache/{html_hist_name}"
chrome_cmd2 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir2}', f'--screenshot={png_hist_path}',
    '--window-size=1440,900', cached_hist_url
]
subprocess.run(chrome_cmd2, capture_output=True)
print(f"Captured 12_Konversi_Satuan.png: {os.path.getsize(png_hist_path)} bytes")

