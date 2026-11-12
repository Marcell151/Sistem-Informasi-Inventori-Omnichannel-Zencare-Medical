import re

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

mermaid_blocks = re.findall(r'<div class=["\']mermaid["\']>([\s\S]*?)</div>', text)
print(f"Total mermaid blocks: {len(mermaid_blocks)}")
for i, b in enumerate(mermaid_blocks):
    first_line = b.strip().splitlines()[0] if b.strip() else "EMPTY"
    # Check if there are illegal characters or syntax errors
    print(f"Block #{i+1}: len={len(b.strip()):4} | {first_line}")

