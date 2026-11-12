import os
import subprocess
import tempfile
import shutil

chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
brain_dir = r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784'

url_uat = 'http://localhost/inventory_zencare/laporan_ta/INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html'

print("Capturing UAT Filled...")
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
print(f"Captured 52_Hasil_Evaluasi_UAT_Terisi.png ({os.path.getsize(out_filled)} bytes)")

# For blank instrument, we can inject click to switch tab
print("\nCapturing UAT Blank...")
cache_dir = os.path.abspath(r'scratch\cache')
blank_html_path = os.path.join(cache_dir, 'uat_blank_view.html')

with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Add auto-switch to blank on load
text = text.replace('switchTab(\'terisi\')', 'switchTab(\'kosong\')')
text = text.replace('<body class="bg-slate-100 min-h-screen py-8 px-4">', '<body class="bg-slate-100 min-h-screen py-8 px-4" onload="switchTab(\'kosong\')">')

with open(blank_html_path, 'w', encoding='utf-8') as f:
    f.write(text)

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
print(f"Captured 51_Blangko_Instrumen_UAT.png ({os.path.getsize(out_blank)} bytes)")
print("Done!")

