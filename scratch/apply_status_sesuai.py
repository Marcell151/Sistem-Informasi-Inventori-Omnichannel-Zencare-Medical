import re
import os
import subprocess
import tempfile
import shutil

files_to_update = [
    r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html',
    r'lain\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html'
]

for fpath in files_to_update:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    # 1. Replace table headers: Kategori -> Status
    # In BAB_IV and Combined:
    text = re.sub(r'<th class="border border-slate-400 px-2 py-1\.5 w-24">Kategori</th>', '<th class="border border-slate-400 px-2 py-1.5 w-24">Status</th>', text)
    # In INSTRUMEN:
    text = re.sub(r'<th class="w-24">Kategori</th>', '<th class="w-24">Status</th>', text)

    # 2. Replace table cells: Sangat Layak -> Sesuai
    text = text.replace('font-semibold text-emerald-800">Sangat Layak</td>', 'font-semibold text-emerald-800">Sesuai</td>')
    text = text.replace('font-bold text-emerald-800">Sangat Layak</td>', 'font-bold text-emerald-800">Sesuai</td>')
    text = text.replace('font-black text-emerald-800 uppercase">Sangat Layak</td>', 'font-black text-emerald-800 uppercase">Sesuai</td>')

    # 3. Replace badges and text
    text = text.replace('Kategori: Sangat Layak', 'Status: Sesuai Kebutuhan (93.75%)')
    text = text.replace('93,75% (Sangat Layak)', '93,75% (Sesuai Kebutuhan)')
    text = text.replace('93.75% (Sangat Layak)', '93.75% (Sesuai Kebutuhan)')

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Updated {fpath}")

# Re-capture screenshots 51 and 52
chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
brain_dir = r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784'

url_uat = 'http://localhost/inventory_zencare/laporan_ta/INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html'

print("\nCapturing 52_Hasil_Evaluasi_UAT_Terisi.png...")
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

print("All status labels updated to Sesuai and screenshots refreshed!")

