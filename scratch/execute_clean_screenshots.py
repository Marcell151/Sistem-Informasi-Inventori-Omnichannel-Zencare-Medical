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

print("=== STEP 1: CAPTURING 10_Kartu_Stok.png ===")
html10_name = "10_Kartu_Stok.html"
html10_path = os.path.join(cache_dir, html10_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'laporan/kartu_stok.php', html10_name, 'id_variasi=1'], capture_output=True)

temp_dir = tempfile.mkdtemp()
out_png10 = os.path.join(out_dir, '10_Kartu_Stok.png')
cmd10 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png10}',
    '--window-size=1440,900', f'http://localhost/inventory_zencare/scratch/cache/{html10_name}'
]
subprocess.run(cmd10)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png10, os.path.join(brain_dir, 'check_final_10.png'))
print(f"Captured 10_Kartu_Stok.png ({os.path.getsize(out_png10)} bytes)")


print("\n=== STEP 2: CAPTURING 11_Stok_Opname_Riwayat.png ===")
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
html11_name = "11_Stok_Opname_Riwayat.html"
html11_path = os.path.join(cache_dir, html11_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', html11_name, ''], capture_output=True)

with open(html11_path, 'r', encoding='utf-8') as f:
    c11 = f.read()

# Exact replacement for demo buttons container in 11
pos_btn_start = c11.find('<div class="flex items-center gap-2.5 flex-wrap shrink-0">')
pos_ket = c11.find('<!-- Keterangan Penilaian Kerugian Persediaan -->')
if pos_btn_start != -1 and pos_ket != -1:
    replacement = '<div class="flex items-center gap-2 shrink-0"><span class="px-3.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold border border-emerald-200">Sistem Berjalan Normal</span></div>\n                </div>\n            </div>\n\n            '
    c11 = c11[:pos_btn_start] + replacement + c11[pos_ket:]

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
shutil.copy(out_png11, os.path.join(brain_dir, 'check_final_11.png'))
print(f"Captured 11_Stok_Opname_Riwayat.png ({os.path.getsize(out_png11)} bytes)")


print("\n=== STEP 3: CAPTURING 32_Stok_Opname_Verifikasi_Admin.png ===")
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Aktif', tahap = 'Verifikasi' WHERE id = 40;"])
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE detail_opname_batch SET status_mutasi = 'Belum Dimutasi' WHERE id_sesi = 40 AND selisih != 0;"])

html32_name = "32_Stok_Opname_Verifikasi_Admin.html"
html32_path = os.path.join(cache_dir, html32_name)
subprocess.run([php_exe, 'scratch/render_page.php', 'admin', 'inventori/stok_opname.php', html32_name, ''], capture_output=True)

with open(html32_path, 'r', encoding='utf-8') as f:
    c32 = f.read()

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
shutil.copy(out_png32, os.path.join(brain_dir, 'check_final_32.png'))
print(f"Captured 32_Stok_Opname_Verifikasi_Admin.png ({os.path.getsize(out_png32)} bytes)")


print("\n=== STEP 4: CAPTURING 44_Modal_Mutasi_Opname.png ===")
html44_name = "44_Modal_Mutasi_Opname.html"
html44_path = os.path.join(cache_dir, html44_name)

c44 = c32
modal_script = """
<style>
#modal_mutasi {
    display: flex !important;
    background-color: rgba(15, 23, 42, 0.6) !important;
    align-items: center !important;
    justify-content: center !important;
}
#modal_mutasi > div {
    max-height: 96vh !important;
    width: 32rem !important;
}
</style>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const m = document.getElementById('modal_mutasi');
    if (m) {
        m.classList.remove('hidden');
        m.style.display = 'flex';
        
        // Populate modal data
        const komoditas = document.getElementById('mutasi_komoditas_badge');
        if (komoditas) {
            komoditas.innerText = 'OBAT';
            komoditas.className = 'px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-[10px] font-bold';
        }
        
        const nama = document.getElementById('mutasi_nama_produk');
        if (nama) nama.innerText = 'Amoxicillin 500mg — Tablet 500mg';
        
        const selisih = document.getElementById('mutasi_selisih_badge');
        if (selisih) {
            selisih.innerText = '-2 Strip (Defisit)';
            selisih.className = 'font-extrabold text-sm text-rose-600';
        }
        
        const satuanLabel = document.getElementById('mutasi_batch_satuan_label');
        if (satuanLabel) satuanLabel.innerText = 'Satuan Kecil (Strip)';
        
        const batchNo = document.getElementById('mutasi_batch_display_no');
        if (batchNo) batchNo.innerText = 'AMX-2026-01.A';
        
        const batchExp = document.getElementById('mutasi_batch_display_exp');
        if (batchExp) batchExp.innerText = '15/12/2027';
        
        // Select radio Barang Hilang
        const radios = document.querySelectorAll('input[type="radio"][name="alasan_mutasi_tunggal"]');
        radios.forEach(r => {
            if (r.value === 'Barang Hilang') {
                r.checked = true;
                const p = r.closest('label');
                if (p) {
                    p.className = 'flex items-center gap-2.5 p-3 rounded-xl border border-blue-500 bg-blue-50/70 cursor-pointer shadow-xs';
                }
            }
        });
        
        const defisitBox = document.getElementById('box_kuantitas_defisit');
        if (defisitBox) defisitBox.innerText = '- 2 Strip';
        
        const ket = document.getElementById('keterangan_mutasi');
        if (ket) ket.value = 'Selisih kurang 2 strip pada saat pencacahan fisik rak etalase depan. Dialokasikan sebagai beban barang hilang.';
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
shutil.copy(out_png44, os.path.join(brain_dir, 'check_final_44.png'))
print(f"Captured clean 44_Modal_Mutasi_Opname.png ({os.path.getsize(out_png44)} bytes)")

# Reset session 40 to Selesai
subprocess.run([mysql_exe, '-u', 'root', 'db_inventory', '-e', "UPDATE sesi_opname SET status = 'Selesai' WHERE id = 40;"])
print("\nExecution complete!")

