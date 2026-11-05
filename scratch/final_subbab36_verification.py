import os
import re

files_to_check = [
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'lain/wireframe_zencare.html',
    'scratch/build_subbab_3_6.py'
]

print("=== 1. CHECK OCCURRENCES OF 'FARMASI' ===")
for fpath in files_to_check:
    with open(fpath, 'r', encoding='utf-8') as f:
        c = f.read()
    matches = re.findall(r'\bfarmasi\b', c, re.IGNORECASE)
    print(f"[{fpath}] Occurrences of 'farmasi': {len(matches)}")

print("\n=== 2. CHECK ALL WIREFRAME FIGURES IN BAB III ===")
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    bab3 = f.read()

all_figs_ok = True
for i in range(19, 44):
    fig_name = f"Gambar 3.{i}"
    if f"<b>{fig_name}</b>" in bab3:
        print(f"  [OK] {fig_name}")
    else:
        print(f"  [FAIL] {fig_name} NOT FOUND!")
        all_figs_ok = False

print("\n=== 3. CHECK WIREFRAME PNG ASSETS (25 FILES) ===")
wf_dir = r'lain\File PPT\Wireframe'
pngs = [f for f in os.listdir(wf_dir) if f.endswith('.png')]
print(f"Total PNGs in {wf_dir}: {len(pngs)}")

print("\n=== 4. CHECK PAPER CONTAINER INTEGRITY ===")
idx_paper_start = bab3.find('id="full_bab3_content"')
idx_3_4 = bab3.find('id="sec_3_4_wrapper"')
idx_3_5 = bab3.find('id="sec_3_5_wrapper"')
idx_3_6 = bab3.find('id="sec_3_6_wrapper"')
idx_paper_end = bab3.find('<!-- Closes full_bab3_content paper -->')
idx_bottom = bab3.find('<!-- Bottom Notification (No Print) -->')

print(f"Paper Start  : {idx_paper_start}")
print(f"Subbab 3.4   : {idx_3_4}")
print(f"Subbab 3.5   : {idx_3_5}")
print(f"Subbab 3.6   : {idx_3_6}")
print(f"Paper End    : {idx_paper_end}")
print(f"Bottom Notif : {idx_bottom}")

if idx_paper_start < idx_3_4 < idx_3_5 < idx_3_6 < idx_paper_end < idx_bottom:
    print(">> PERFECT: Subbab 3.4, 3.5, and 3.6 are 100% strictly nested inside full_bab3_content paper!")
else:
    print(">> FAIL: Container hierarchy issue!")

