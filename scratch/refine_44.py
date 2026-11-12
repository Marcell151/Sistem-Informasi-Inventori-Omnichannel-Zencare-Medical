import os
import subprocess
import tempfile
import shutil

chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
out_dir = os.path.abspath(r'lain\File PPT\UI_UX_Asli')
cache_dir = os.path.abspath(r'scratch\cache')
brain_dir = r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784'

html44_path = os.path.join(cache_dir, '44_Modal_Mutasi_Opname.html')
with open(html44_path, 'r', encoding='utf-8') as f:
    c44 = f.read()

# Make form compact and fit without scrollbar so the buttons are clearly visible
c44 = c44.replace(
    '#modal_mutasi > div {\n    max-height: 96vh !important;\n    width: 32rem !important;\n}',
    """#modal_mutasi > div {
    max-height: none !important;
    width: 34rem !important;
    overflow: visible !important;
}
#modal_mutasi form {
    max-height: none !important;
    overflow: visible !important;
}"""
)

# Also check radio for Barang Hilang
check_radio = """
        const r2 = document.querySelector('input[type="radio"][value="Barang Hilang"]');
        if (r2) {
            r2.checked = true;
            const lbl = r2.closest('label');
            if (lbl) {
                lbl.style.borderColor = '#2563eb';
                lbl.style.backgroundColor = '#eff6ff';
            }
        }
"""
c44 = c44.replace("// Select radio Barang Hilang", check_radio)

with open(html44_path, 'w', encoding='utf-8') as f:
    f.write(c44)

temp_dir = tempfile.mkdtemp()
out_png44 = os.path.join(out_dir, '44_Modal_Mutasi_Opname.png')
cmd44 = [
    chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox',
    f'--user-data-dir={temp_dir}', f'--screenshot={out_png44}',
    '--window-size=1280,1050', f'http://localhost/inventory_zencare/scratch/cache/44_Modal_Mutasi_Opname.html'
]
subprocess.run(cmd44)
shutil.rmtree(temp_dir, ignore_errors=True)
shutil.copy(out_png44, os.path.join(brain_dir, 'check_final_44_full.png'))
print(f"Captured full 44_Modal_Mutasi_Opname.png ({os.path.getsize(out_png44)} bytes)")

