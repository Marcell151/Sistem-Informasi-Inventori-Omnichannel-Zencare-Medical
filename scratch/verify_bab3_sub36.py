with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

print("=== 1. CHECK OCCURRENCES OF 'FARMASI' ===")
matches = re.findall(r'\bfarmasi\b', text, re.IGNORECASE)
print(f"Occurrences of 'farmasi' in BAB_III_ZENCARE_MEDICAL.html: {len(matches)}")

print("\n=== 2. CHECK WIREFRAME FIGURES (Gambar 3.19 - Gambar 3.43) ===")
fig_matches = re.findall(r'<b>(Gambar\s+3\.\d+)</b>\s*([^<]+)', text)
found_figs = [f[0] for f in fig_matches]
for num in range(19, 44):
    target = f"Gambar 3.{num}"
    if target in found_figs:
        print(f"  [OK] {target}")
    else:
        print(f"  [MISSING] {target}")

print("\n=== 3. CHECK PAPER CONTAINER HIERARCHY ===")
idx_paper_start = text.find('id="full_bab3_content"')
idx_3_4 = text.find('id="sec_3_4_wrapper"')
idx_3_5 = text.find('id="sec_3_5_wrapper"')
idx_3_6 = text.find('id="sec_3_6_wrapper"')
idx_paper_end = text.find('<!-- Closes full_bab3_content paper -->')
idx_bottom = text.find('<!-- Bottom Notification (No Print) -->')

print(f"Paper Start : {idx_paper_start}")
print(f"Subbab 3.4  : {idx_3_4}")
print(f"Subbab 3.5  : {idx_3_5}")
print(f"Subbab 3.6  : {idx_3_6}")
print(f"Paper End   : {idx_paper_end}")
print(f"Bottom Notif: {idx_bottom}")

if idx_paper_start < idx_3_4 < idx_3_5 < idx_3_6 < idx_paper_end < idx_bottom:
    print(">> PERFECT: Subbab 3.4, 3.5, and 3.6 are strictly inside the white A4 Paper container!")
else:
    print(">> WARNING: Container nesting issue detected!")

