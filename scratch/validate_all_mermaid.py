import re

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    c = f.read()

divs = re.findall(r'<div class="mermaid[^"]*">([\s\S]*?)</div>', c)

for idx in [4, 5, 6, 7]:
    print(f"\n====================== MERMAID BLOCK {idx+1} ======================")
    print(divs[idx].strip())
