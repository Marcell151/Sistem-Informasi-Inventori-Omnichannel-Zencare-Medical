import os
import re
from build_subbab_3_6 import generate_subbab_3_6_html

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    html = f.read()

sub_3_6_html = generate_subbab_3_6_html()

# Check if Subbab 3.6 already exists
idx_3_6 = html.find('id="sec_3_6_wrapper"')
if idx_3_6 != -1:
    print("Found existing Subbab 3.6, replacing...")
    # Find start of wrapper
    idx_start = html.rfind('<!-- 3.6 Wireframe -->', 0, idx_3_6)
    if idx_start == -1:
        idx_start = idx_3_6
    # Find end of Subbab 3.6 before closing paper
    idx_end = html.find('</div> <!-- Closes full_bab3_content paper -->', idx_3_6)
    new_html = html[:idx_start] + sub_3_6_html.strip() + '\n\n    ' + html[idx_end:]
else:
    print("Subbab 3.6 not found, inserting before closing paper...")
    idx_close_paper = html.find('</div> <!-- Closes full_bab3_content paper -->')
    if idx_close_paper == -1:
        print("Error: closing paper div not found!")
        exit(1)
    new_html = html[:idx_close_paper] + sub_3_6_html.strip() + '\n\n    ' + html[idx_close_paper:]

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_html)

print("Successfully injected Subbab 3.6 into BAB_III_ZENCARE_MEDICAL.html!")

