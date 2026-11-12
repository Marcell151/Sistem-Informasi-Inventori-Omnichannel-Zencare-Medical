import re

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    content = f.read()

blocks = re.findall(r'<div class="mermaid">([\s\S]*?)</div>', content)
print(f"Total mermaid blocks in BAB_III_ZENCARE_MEDICAL.html: {len(blocks)}")

for i, b in enumerate(blocks):
    lines = [l.strip() for l in b.strip().split('\n') if l.strip()]
    header = lines[0] if lines else 'EMPTY'
    print(f"\nBlock {i+1}: lines={len(lines)}, header='{header}'")
    for line_num, l in enumerate(lines):
        quotes = l.count('"')
        if quotes % 2 != 0:
            print(f"  [!] Block {i+1} Line {line_num+1} ODD QUOTES: {l}")
        if l.count('[') != l.count(']'):
            print(f"  [!] Block {i+1} Line {line_num+1} MISMATCHED []: {l}")
        if l.count('(') != l.count(')'):
            print(f"  [!] Block {i+1} Line {line_num+1} MISMATCHED (): {l}")
