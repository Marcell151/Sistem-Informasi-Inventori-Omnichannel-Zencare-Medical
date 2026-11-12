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

print("=== 1. CAPTURING 10_Kartu_Stok.png (Populated Inventory Ledger) ===")
# Render kartu stok with id_variasi=1 (Amoxicillin 500mg)
html10_name = "10_Kartu_Stok.html"
html10_path = os.path.join(cache_dir, html10_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'laporan/kartu_stok.php', html10_name, 'id_variasi=1'], capture_output=True)

# Take screenshot of populated kartu stok
temp_dir = tempfile.mkdtemp()
out_png10 = os.path.join(out_dir, '10_Kartu_Stok.png')
cmd10 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png10}',
    '--window-size=1440,900', f'http://localhost/inventory_zencare/scratch/cache/{html10_name}'
]
subprocess.run(cmd10)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png10, os.path.join(brain_dir, 'test_10_Kartu_Stok.png'))
print(f"Captured 10_Kartu_Stok.png ({os.path.getsize(out_png10)} bytes)")


print("\n=== 2. CAPTURING 11_Stok_Opname_Riwayat.png (Clean Landing Page) ===")
# Ensure session 40 is Selesai
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
html11_name = "11_Stok_Opname_Riwayat.html"
html11_path = os.path.join(cache_dir, html11_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', html11_name, ''], capture_output=True)

with open(html11_path, 'r', encoding='utf-8') as f:
    html11 = f.read()

# Hide demo buttons on landing page view so title has full width
clean_landing_css = """
<style>
/* Hide demo buttons for clean professional screenshot */
form[action*="demo"], button:has-text("Utilitas Demo"), a[href*="pengujian"],
div:has(> form[method="POST"] button:contains("Demo")),
div.flex.items-center.gap-2\\.5.flex-wrap.shrink-0 {
    display: none !important;
}
/* Ensure clean flex layout */
.flex.flex-col.lg\\:flex-row.items-start.lg\\:items-center.justify-between {
    display: flex !important;
    width: 100% !important;
}
</style>
"""
# Replace the demo buttons container directly in html
html11 = re.sub(r'<div class="flex items-center gap-2\.5 flex-wrap shrink-0">[\s\S]*?</div>\s*</div>\s*</div>\s*<!-- ARSIP', '<div class="flex items-center gap-2 shrink-0"><span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">Sistem Berjalan Normal</span></div></div></div><!-- ARSIP', html11)
with open(html11_path, 'w', encoding='utf-8') as f:
    f.write(html11)

temp_dir = tempfile.mkdtemp()
out_png11 = os.path.join(out_dir, '11_Stok_Opname_Riwayat.png')
cmd11 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png11}',
    '--window-size=1440,900', f'http://localhost/inventory_zencare/scratch/cache/{html11_name}'
]
subprocess.run(cmd11)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png11, os.path.join(brain_dir, 'test_11_Stok_Opname_Riwayat.png'))
print(f"Captured 11_Stok_Opname_Riwayat.png ({os.path.getsize(out_png11)} bytes)")


print("\n=== 3. CAPTURING 32_Stok_Opname_Verifikasi_Admin.png (Active Verification with 6 Discrepancies) ===")
# Set session 40 to Aktif Verifikasi and reset 6 discrepancies to 'Belum Dimutasi'
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Aktif', tahap = 'Verifikasi' WHERE id = 40;"])
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE detail_opname_batch SET status_mutasi = 'Belum Dimutasi' WHERE id_sesi = 40 AND selisih != 0;"])

html32_name = "32_Stok_Opname_Verifikasi_Admin.html"
html32_path = os.path.join(cache_dir, html32_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', html32_name, ''], capture_output=True)

with open(html32_path, 'r', encoding='utf-8') as f:
    html32 = f.read()

# Hide demo buttons in verification header
html32 = re.sub(r'<div class="flex items-center gap-2 flex-wrap pt-2 border-t border-slate-100">[\s\S]*?</div>', '', html32)

with open(html32_path, 'w', encoding='utf-8') as f:
    f.write(html32)

temp_dir = tempfile.mkdtemp()
out_png32 = os.path.join(out_dir, '32_Stok_Opname_Verifikasi_Admin.png')
cmd32 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png32}',
    '--window-size=1440,900', f'http://localhost/inventory_zencare/scratch/cache/{html32_name}'
]
subprocess.run(cmd32)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png32, os.path.join(brain_dir, 'test_32_Stok_Opname_Verifikasi_Admin.png'))
print(f"Captured 32_Stok_Opname_Verifikasi_Admin.png ({os.path.getsize(out_png32)} bytes)")


print("\n=== 4. CAPTURING 44_Modal_Mutasi_Opname.png (Modal Dialog Eksekusi Mutasi Selisih) ===")
html44_name = "44_Modal_Mutasi_Opname.html"
html44_path = os.path.join(cache_dir, html44_name)
# Use html32 as base because it is already rendered in active verification stage!
with open(html32_path, 'r', encoding='utf-8') as f:
    html44 = f.read()

# Make modal_mutasi open and populated
modal_code = """
<style>
#modal_mutasi {
    display: flex !important;
    background-color: rgba(15, 23, 42, 0.55) !important;
}
</style>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const m = document.getElementById('modal_mutasi');
    if (m) {
        m.classList.remove('hidden');
        m.style.display = 'flex';
        
        // Populate modal with Amoxicillin discrepancy details
        const title = document.getElementById('mutasi_modal_title');
        if (title) title.innerText = 'Verifikasi Mutasi Selisih Stok — Amoxicillin 500mg';
        
        const nama = document.getElementById('mutasi_nama_produk');
        if (nama) nama.innerText = 'Amoxicillin 500mg — Tablet 500mg';
        
        const batch = document.getElementById('mutasi_batch');
        if (batch) batch.innerText = 'AMX-2026-01.A (Exp: 15/12/2027)';
        
        const satuan = document.getElementById('mutasi_satuan');
        if (satuan) satuan.innerText = 'Strip';
        
        const sistem = document.getElementById('mutasi_sistem');
        if (sistem) sistem.innerText = '8';
        
        const fisik = document.getElementById('mutasi_fisik');
        if (fisik) fisik.innerText = '6 (Baik: 6, Rusak: 0, Exp: 0)';
        
        const selisih = document.getElementById('mutasi_selisih');
        if (selisih) {
            selisih.innerText = '-2 Strip (Defisit)';
            selisih.className = 'font-bold text-rose-600';
        }
        
        const alasan = document.getElementById('mutasi_beban_alasan');
        if (alasan) {
            alasan.innerHTML = `
                <option value="Barang Hilang" selected>Barang Hilang / Selisih Kurang (-2 Strip)</option>
                <option value="Barang Rusak">Barang Rusak / Cacat Fisik</option>
                <option value="Barang Expired">Barang Kedaluwarsa</option>
            `;
        }
        
        const nomBox = document.getElementById('mutasi_nominal_box');
        if (nomBox) nomBox.classList.remove('hidden');
        
        const nomInfo = document.getElementById('mutasi_nominal_info');
        if (nomInfo) nomInfo.innerText = 'Rp 8.000 (2 Strip × Rp 4.000)';
        
        const catatan = document.getElementById('mutasi_catatan');
        if (catatan) catatan.value = 'Selisih kurang 2 strip pada saat pencacahan fisik rak etalase depan. Dialokasikan sebagai beban barang hilang.';
    }
});
</script>
"""
html44 = html44.replace('</head>', modal_code + '</head>')
with open(html44_path, 'w', encoding='utf-8') as f:
    f.write(html44)

temp_dir = tempfile.mkdtemp()
out_png44 = os.path.join(out_dir, '44_Modal_Mutasi_Opname.png')
cmd44 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png44}',
    '--window-size=1280,850', f'http://localhost/inventory_zencare/scratch/cache/{html44_name}'
]
subprocess.run(cmd44)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png44, os.path.join(brain_dir, 'test_44_Modal_Mutasi_Opname.png'))
print(f"Captured 44_Modal_Mutasi_Opname.png ({os.path.getsize(out_png44)} bytes)")

# Reset session 40 back to Selesai
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
print("\nDone fixing critical screens!")

