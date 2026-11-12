import re
import os

FORBIDDEN = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
emoji_pattern = re.compile(r'[\U00010000-\U0010ffff]', flags=re.UNICODE)

files = [
    r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html',
    r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html',
    r'C:\xampp\htdocs\inventory_zencare\laporan_ta\katalog_download_ui_ux_zencare.html',
    r'C:\xampp\htdocs\inventory_zencare\lain\katalog_download_ui_ux_zencare.html',
]

print("=== 1. VERIFY FORBIDDEN WORDS & EMOJIS ===")
for fpath in files:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()
    violations = []
    for f in FORBIDDEN:
        if f == 'Bab 4':
            m = re.findall(r'\bBab 4\b', text, re.IGNORECASE)
        elif f == 'HPP':
            m = re.findall(r'\bHPP\b', text)
        else:
            m = re.findall(re.escape(f), text, re.IGNORECASE)
        if m:
            violations.append((f, len(m)))
    
    emojis = emoji_pattern.findall(text)
    
    bname = os.path.basename(fpath)
    if violations:
        print(f"[{bname}] VIOLATIONS: {violations}")
    else:
        print(f"[{bname}] FORBIDDEN WORDS: 0 (100% CLEAN)")
    
    if emojis:
        print(f"[{bname}] EMOJIS: {len(emojis)} (FAIL)")
    else:
        print(f"[{bname}] EMOJIS: 0 (100% CLEAN)")

print("\n=== 2. VERIFY ALL 50 FIGURES IN BAB IV ===")
with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_iv = f.read()

figs_found = re.findall(r'Gambar 4\.(\d+)', text_iv)
fig_nums = sorted(list(set(int(x) for x in figs_found)))
print(f"Total unique figure numbers found: {len(fig_nums)}")
print(f"Min fig: Gambar 4.{min(fig_nums)}, Max fig: Gambar 4.{max(fig_nums)}")
expected = list(range(1, 51))
if fig_nums == expected:
    print("SUCCESS: Gambar 4.1 to Gambar 4.50 are 100% continuous and complete!")
else:
    missing = set(expected) - set(fig_nums)
    extra = set(fig_nums) - set(expected)
    print(f"Discrepancy: missing={missing}, extra={extra}")

print("\n=== 3. VERIFY ALL 50 PNG FILES ON DISK ===")
img_dir = r"C:\xampp\htdocs\inventory_zencare\lain\File PPT\UI_UX_Asli"
from build_katalog_50 import items
all_images_exist = True
for item in items:
    p = os.path.join(img_dir, item['filename'])
    if not os.path.exists(p):
        print(f"MISSING: {item['filename']}")
        all_images_exist = False

if all_images_exist:
    print(f"SUCCESS: All {len(items)} screenshot images exist on disk!")

