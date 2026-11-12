with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    html = f.read()

import re

blocks = re.findall(r'<div class="mermaid">(.*?)</div>', html, re.DOTALL)

for i in [4, 5, 6, 7]: # sitemaps & use case
    print(f"\n==================== BLOCK {i+1} ====================")
    print(blocks[i])

