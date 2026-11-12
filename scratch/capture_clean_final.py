import os
import subprocess
import tempfile
import shutil
import re

php_exe = r'c:\xampp\php\php.exe'
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
mysql_exe = r'c:\xampp\mysql\bin\mysql.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
cache_dir = os.path.abspath(r'scratch\cache')
brain_dir = r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784'

# 1. Clean up 11_Stok_Opname_Riwayat.html
print("Processing 11_Stok_Opname_Riwayat...")
# Ensure session 40 is Selesai
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
html11_name = "11_Stok_Opname_Riwayat.html"
html11_path = os.path.join(cache_dir, html11_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', html11_name, ''], capture_output=True)

with open(html11_path, 'r', encoding='utf-8') as f:
    c11 = f.read()

# Replace the demo buttons container in 11
pos_btn_start = c11.find('<div class="flex items-center gap-2.5 flex-wrap shrink-0">')
pos_arsip = c11.find('<!-- ARSIP RIWAYAT SESI STOK OPNAME -->')
if pos_btn_start != -1 and pos_arsip != -1:
    sub = c11[pos_btn_start:pos_arsip]
    # find the last </div> before pos_arsip
    last_div = sub.rfind('</div>')
    replacement = '<div class="flex items-center gap-2 shrink-0"><span class="px-3.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">Sistem Berjalan Normal</span></div>\n                </div>\n            </div>\n\n            '
    c11 = c11[:pos_btn_start] + replacement + c11[pos_arsip:]

with open(html11_path, 'w', encoding='utf-8') as f:
    f.write(c11)

temp_dir = tempfile.mkdtemp()
out_png11 = os.path.join(out_dir, '11_Stok_Opname_Riwayat.png')
cmd11 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png11}',
    '--window-size=1440,900', f'http://localhost/inventory_zencare/scratch/cache/{html11_name}'
]
subprocess.run(cmd11)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png11, os.path.join(brain_dir, 'final_11.png'))
print(f"Captured clean 11_Stok_Opname_Riwayat.png: {os.path.getsize(out_png11)} bytes")


# 2. Clean up 32_Stok_Opname_Verifikasi_Admin.html
print("Processing 32_Stok_Opname_Verifikasi_Admin...")
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Aktif', tahap = 'Verifikasi' WHERE id = 40;"])
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE detail_opname_batch SET status_mutasi = 'Belum Dimutasi' WHERE id_sesi = 40 AND selisih != 0;"])

html32_name = "32_Stok_Opname_Verifikasi_Admin.html"
html32_path = os.path.join(cache_dir, html32_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', html32_name, ''], capture_output=True)

with open(html32_path, 'r', encoding='utf-8') as f:
    c32 = f.read()

# Replace demo buttons block in 32
# Pattern: from '<div class="flex items-center gap-2 flex-wrap">' containing demo buttons
demo_pattern = r'<div class="flex items-center gap-2 flex-wrap">[\s\S]*?<!-- Tombol \[Utilitas Demo\] Jalankan Uji Coba Testing -->[\s\S]*?Dokumentasi Pengujian[\s\S]*?</a>\s*</div>'
c32 = re.sub(demo_pattern, '<div class="flex items-center gap-2"><span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-semibold border border-amber-200">Tahap Verifikasi &amp; Rekonsiliasi</span></div>', c32)

with open(html32_path, 'w', encoding='utf-8') as f:
    f.write(c32)

temp_dir = tempfile.mkdtemp()
out_png32 = os.path.join(out_dir, '32_Stok_Opname_Verifikasi_Admin.png')
cmd32 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png32}',
    '--window-size=1440,900', f'http://localhost/inventory_zencare/scratch/cache/{html32_name}'
]
subprocess.run(cmd32)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png32, os.path.join(brain_dir, 'final_32.png'))
print(f"Captured clean 32_Stok_Opname_Verifikasi_Admin.png: {os.path.getsize(out_png32)} bytes")


# 3. Clean up 44_Modal_Mutasi_Opname.html
print("Processing 44_Modal_Mutasi_Opname...")
html44_name = "44_Modal_Mutasi_Opname.html"
html44_path = os.path.join(cache_dir, html44_name)

# Use c32 as base
c44 = c32

# In c44, inject script to display and populate the modal with complete details
modal_script = """
<style>
#modal_mutasi {
    display: flex !important;
    background-color: rgba(15, 23, 42, 0.6) !important;
    align-items: center !important;
    justify-content: center !important;
}
#modal_mutasi > div {
    max-height: 94vh !important;
}
</style>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const m = document.getElementById('modal_mutasi');
    if (m) {
        m.classList.remove('hidden');
        m.style.display = 'flex';
        
        // Fill data
        const title = document.getElementById('mutasi_modal_title');
        if (title) title.innerText = 'Verifikasi Mutasi Selisih Stok — Amoxicillin 500mg';
        
        const nama = document.getElementById('mutasi_nama_produk');
        if (nama) nama.innerText = 'Amoxicillin 500mg — Tablet 500mg';
        
        // Find radio for Barang Hilang and check it
        const radios = document.querySelectorAll('input[type="radio"]');
        radios.forEach(r => {
            if (r.value === 'Barang Hilang') {
                r.checked = true;
                // Highlight parent container
                const p = r.closest('label');
                if (p) {
                    p.classList.add('border-primary-500', 'bg-blue-50/50');
                }
            }
        });
        
        // Batch details
        const batchEl = document.getElementById('mutasi_batch');
        if (batchEl) batchEl.innerText = 'AMX-2026-01.A';
        
        const expEl = document.getElementById('mutasi_exp');
        if (expEl) expEl.innerText = '15/12/2027';
        
        const catatan = document.getElementById('mutasi_catatan');
        if (catatan) catatan.value = 'Selisih kurang 2 strip pada saat pencacahan fisik rak etalase depan. Dialokasikan sebagai beban barang hilang.';
    }
});
</script>
"""
c44 = c44.replace('</head>', modal_script + '</head>')
with open(html44_path, 'w', encoding='utf-8') as f:
    f.write(c44)

temp_dir = tempfile.mkdtemp()
out_png44 = os.path.join(out_dir, '44_Modal_Mutasi_Opname.png')
cmd44 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png44}',
    '--window-size=1280,950', f'http://localhost/inventory_zencare/scratch/cache/{html44_name}'
]
subprocess.run(cmd44)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png44, os.path.join(brain_dir, 'final_44.png'))
print(f"Captured clean 44_Modal_Mutasi_Opname.png: {os.path.getsize(out_png44)} bytes")

# Reset session 40 to Selesai
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
print("\nAll captured successfully!")

