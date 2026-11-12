with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    html = f.read()

import re

# Find all <div class="mermaid"> ... </div>
blocks = re.findall(r'<div class="mermaid">(.*?)</div>', html, re.DOTALL)
print(f"Total mermaid blocks: {len(blocks)}")

for i, b in enumerate(blocks):
    print(f"\n--- Block {i+1} ---")
    lines = [line.strip() for line in b.strip().split('\n') if line.strip()]
    print(lines[0] if lines else 'empty')
    # check for obvious syntax errors like unquoted parentheses or special chars
    for line in lines:
        if '(' in line and not line.startswith('style'):
            # check if inside quotes
            # e.g. K2["Terminal Kasir (POS)"]
            pass

