import re
import os
import subprocess
import tempfile
import shutil

# 1. Prepare data for the 26 scenarios
from build_business_uat import uat_modules

# 2. Build rows for INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html
uat_table_rows_html = []
for m in uat_modules:
    # Section Header
    header_tr = f"""                    <tr class="bg-slate-100 font-bold">
                        <td colspan="6" class="text-left font-sans-ui uppercase text-[10px] tracking-wider py-1.5 text-slate-800">
                            {m['roman']}: {m['title']}
                        </td>
                    </tr>"""
    uat_table_rows_html.append(header_tr)
    
    # Scenario rows
    for s in m['scenarios']:
        row_tr = f"""                    <tr>
                        <td class="text-center font-mono font-bold">{s['code']}</td>
                        <td>{s['req']}</td>
                        <td>{s['crit']}</td>
                        <td class="text-center status-col font-bold text-emerald-700">{s['status']}</td>
                        <td class="text-center penguji-col">{s['role']}</td>
                        <td class="text-center text-xs text-slate-600">Sesuai Kebutuhan</td>
                    </tr>"""
        uat_table_rows_html.append(row_tr)

all_uat_instrument_rows = '\n'.join(uat_table_rows_html)

# Update INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html
for uat_file in [r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', r'lain\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html']:
    with open(uat_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Update table headers: Kode | Kebutuhan Pengguna / Alur Bisnis | Kriteria Penerimaan Sistem | Status Hasil Uji | Penguji | Keterangan
    new_thead = """                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                    <tr>
                        <th class="w-14">Kode</th>
                        <th class="w-64">Kebutuhan Pengguna / Alur Bisnis</th>
                        <th>Kriteria Penerimaan Sistem</th>
                        <th class="w-24">Status</th>
                        <th class="w-28">Penguji</th>
                        <th class="w-32">Keterangan</th>
                    </tr>
                </thead>"""
    
    # Replace thead
    content = re.sub(r'<thead class="bg-slate-100 text-slate-900 font-bold text-center">.*?</thead>', new_thead, content, flags=re.DOTALL)
    
    # Replace tbody
    idx_tb_start = content.find('<tbody>')
    idx_tb_end = content.find('</tbody>', idx_tb_start)
    content = content[:idx_tb_start+7] + "\n" + all_uat_instrument_rows + "\n                " + content[idx_tb_end:]
    
    # Update text: 36 skenario -> 26 butir skenario kebutuhan bisnis
    content = content.replace('36 skenario uji lapangan', '26 butir skenario kebutuhan operasional')
    content = content.replace('36 skenario operasional', '26 butir skenario kebutuhan operasional')
    content = content.replace('36 (tiga puluh enam) skenario', '26 (dua puluh enam) butir skenario kebutuhan operasional')
    content = content.replace('36 skenario', '26 butir skenario')
    
    with open(uat_file, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Updated {uat_file}")

# 3. Re-capture screenshots 51 and 52
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
brain_dir = r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784'

url_uat = 'http://localhost/inventory_zencare/laporan_ta/INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html'

print("Capturing 52_Hasil_Evaluasi_UAT_Terisi.png...")
temp_dir = tempfile.mkdtemp()
out_filled = os.path.join(out_dir, '52_Hasil_Evaluasi_UAT_Terisi.png')
cmd_filled = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_filled}',
    '--window-size=1200,1100', url_uat
]
subprocess.run(cmd_filled)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_filled, os.path.join(brain_dir, 'test_uat_filled.png'))
print(f"Captured 52 ({os.path.getsize(out_filled)} bytes)")

print("Capturing 51_Blangko_Instrumen_UAT.png...")
cache_dir = os.path.abspath(r'scratch\cache')
blank_html_path = os.path.join(cache_dir, 'uat_blank_view.html')

with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text_blank = f.read()

text_blank = text_blank.replace('<body class="bg-slate-100 min-h-screen py-8 px-4">', '<body class="bg-slate-100 min-h-screen py-8 px-4" onload="switchTab(\'kosong\')">')
with open(blank_html_path, 'w', encoding='utf-8') as f:
    f.write(text_blank)

temp_dir = tempfile.mkdtemp()
out_blank = os.path.join(out_dir, '51_Blangko_Instrumen_UAT.png')
cmd_blank = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_blank}',
    '--window-size=1200,1100', 'http://localhost/inventory_zencare/scratch/cache/uat_blank_view.html'
]
subprocess.run(cmd_blank)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_blank, os.path.join(brain_dir, 'test_uat_blank.png'))
print(f"Captured 51 ({os.path.getsize(out_blank)} bytes)")

print("Done updating UAT HTML and screenshots!")

