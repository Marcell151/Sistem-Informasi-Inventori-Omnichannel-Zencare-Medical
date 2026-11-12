import os
import subprocess
import tempfile
import shutil
import re

php_exe = r'c:\xampp\php\php.exe'
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
cache_dir = os.path.abspath(r'scratch\cache')

# 1. Capture 36_Form_Mutasi_Stok with POPULATED FORM
print("Step 1: Rendering Form Mutasi Stok with populated data...")
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/proses_mutasi.php', '36_Form_Mutasi_Stok.html', ''])
fpath36 = os.path.join(cache_dir, '36_Form_Mutasi_Stok.html')
with open(fpath36, 'r', encoding='utf-8') as f:
    html36 = f.read()

# Inject values into select and inputs
# 1. Select Amoxicillin in select_mutasi_produk
html36 = html36.replace(
    '<option value="1">Amoxicillin 500mg — Tablet 500mg</option>',
    '<option value="1" selected>Amoxicillin 500mg — Tablet 500mg</option>'
)
# 2. Select Pengurangan in jenis_mutasi
html36 = html36.replace(
    '<option value="Pengurangan">Kurangi Stok (-)</option>',
    '<option value="Pengurangan" selected>Kurangi Stok (-)</option>'
)
# 3. Reveal dynamic_identitas and populate dynamic_input_container
populated_identitas = """
<div id="dynamic_identitas" class="p-3 bg-amber-50/60 border border-amber-200 rounded-xl space-y-2">
    <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-amber-900" id="dynamic_label">IDENTITAS BATCH (OBAT)</span>
        <span class="text-[10px] text-amber-700 font-medium">Stok Terkini: 4 Box / 40 Strip</span>
    </div>
    <div id="dynamic_input_container">
        <label class="block text-xs font-semibold text-zcTxt mb-1">Pilih Batch yang Dikurangi *</label>
        <select name="no_batch" class="w-full text-sm border border-zcBrd rounded-lg px-3 py-2 bg-white">
            <option value="AMX-2026-01" selected>Batch: AMX-2026-01 (Sisa: 4 Box | Exp: 15/12/2027)</option>
        </select>
    </div>
</div>
"""
# Replace the original hidden dynamic_identitas
html36 = re.sub(
    r'<div id="dynamic_identitas" class="hidden[^>]*>.*?</div>\s*</div>',
    populated_identitas,
    html36,
    flags=re.DOTALL
)

# 4. Fill Qty input
html36 = html36.replace('id="qty_input" value=""', 'id="qty_input" value="2"')
html36 = html36.replace('id="qty_input"', 'id="qty_input" value="2"')

# 5. Select Alasan "Barang Rusak"
html36 = html36.replace(
    '<option value="Barang Rusak">Barang Rusak / Kadaluarsa</option>',
    '<option value="Barang Rusak" selected>Barang Rusak / Kemasan Cacat</option>'
)

# 6. Fill Catatan
html36 = html36.replace(
    '<textarea name="catatan"',
    '<textarea name="catatan">Penarikan fisik dari rak etalase depan karena kemasan strip penyok saat penataan.'
)

with open(fpath36, 'w', encoding='utf-8') as f:
    f.write(html36)

# Screenshot 36
temp_dir = tempfile.mkdtemp()
out_png36 = os.path.join(out_dir, '36_Form_Mutasi_Stok.png')
cmd36 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png36}',
    '--window-size=1440,900', 'http://localhost/inventory_zencare/scratch/cache/36_Form_Mutasi_Stok.html'
]
subprocess.run(cmd36)
shutil.rmtree(temp_dir, ignore_errors=True)
print(f"Captured 36_Form_Mutasi_Stok.png: {os.path.getsize(out_png36)} bytes")


# 2. Capture 11_Stok_Opname_Riwayat (Landing page when session is Selesai)
print("\nStep 2: Capturing Stok Opname Riwayat (Landing Page)...")
# Ensure no active session
subprocess.run(['c:/xampp/mysql/bin/mysql.exe', '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', '11_Stok_Opname_Riwayat.html', ''])
temp_dir = tempfile.mkdtemp()
out_png11 = os.path.join(out_dir, '11_Stok_Opname_Riwayat.png')
cmd11 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png11}',
    '--window-size=1440,900', 'http://localhost/inventory_zencare/scratch/cache/11_Stok_Opname_Riwayat.html'
]
subprocess.run(cmd11)
shutil.rmtree(temp_dir, ignore_errors=True)
print(f"Captured 11_Stok_Opname_Riwayat.png: {os.path.getsize(out_png11)} bytes")


# 3. Capture 44_Modal_Mutasi_Opname (Modal Dialog Eksekusi Mutasi Selisih Per Item)
print("\nStep 3: Capturing Modal Mutasi Opname...")
# Set session 40 to Aktif Verifikasi
subprocess.run(['c:/xampp/mysql/bin/mysql.exe', '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Aktif', tahap = 'Verifikasi' WHERE id = 40;"])
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', '44_Modal_Mutasi_Opname.html', ''])

fpath44 = os.path.join(cache_dir, '44_Modal_Mutasi_Opname.html')
with open(fpath44, 'r', encoding='utf-8') as f:
    html44 = f.read()

# Make modal_mutasi visible and inject sample discrepancy item details (Amoxicillin 500mg, selisih -2 Strip)
modal_override = """
<style>
#modal_mutasi { display: flex !important; }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('mutasi_modal_title').innerText = 'Verifikasi Mutasi Selisih Stok — Amoxicillin 500mg';
    document.getElementById('mutasi_nama_produk').innerText = 'Amoxicillin 500mg — Tablet 500mg';
    document.getElementById('mutasi_batch').innerText = 'AMX-2026-01.A (Exp: 15/12/2027)';
    document.getElementById('mutasi_satuan').innerText = 'Strip';
    document.getElementById('mutasi_sistem').innerText = '8';
    document.getElementById('mutasi_fisik').innerText = '6 (Baik: 6, Rusak: 0, Exp: 0)';
    document.getElementById('mutasi_selisih').innerText = '-2 Strip (Defisit)';
    document.getElementById('mutasi_selisih').className = 'font-bold text-rose-600';
    document.getElementById('mutasi_beban_alasan').innerHTML = `
        <option value="Barang Hilang" selected>Barang Hilang / Selisih Kurang (-2 Strip)</option>
        <option value="Barang Rusak">Barang Rusak / Kemasan Cacat</option>
        <option value="Barang Expired">Barang Kedaluwarsa</option>
    `;
    document.getElementById('mutasi_nominal_info').innerText = 'Rp 8.000 (2 Strip × Rp 4.000)';
    document.getElementById('mutasi_nominal_box').classList.remove('hidden');
    document.getElementById('mutasi_catatan').value = 'Selisih kurang 2 strip pada saat pencacahan fisik rak etalase depan. Dialokasikan sebagai beban barang hilang.';
});
</script>
"""
html44 = html44.replace('</head>', modal_override + '</head>')
with open(fpath44, 'w', encoding='utf-8') as f:
    f.write(html44)

temp_dir = tempfile.mkdtemp()
out_png44 = os.path.join(out_dir, '44_Modal_Mutasi_Opname.png')
cmd44 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png44}',
    '--window-size=1280,850', 'http://localhost/inventory_zencare/scratch/cache/44_Modal_Mutasi_Opname.html'
]
subprocess.run(cmd44)
shutil.rmtree(temp_dir, ignore_errors=True)
print(f"Captured 44_Modal_Mutasi_Opname.png: {os.path.getsize(out_png44)} bytes")

# Reset session 40 back to Selesai
subprocess.run(['c:/xampp/mysql/bin/mysql.exe', '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
print("\nAll missing and adjusted UI captured successfully!")

