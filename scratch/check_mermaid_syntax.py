import re

with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    c = f.read()

blocks = re.findall(r'<div class=[\'"]mermaid[\'"]>(.*?)</div>', c, re.DOTALL)
print(f"Total mermaid blocks in BAB_III: {len(blocks)}")
for i, b in enumerate(blocks):
    print(f"\n--- Block {i+1} ---")
    lines = [l.strip() for l in b.strip().splitlines() if l.strip()]
    print("Header:", lines[0] if lines else "Empty")
    for line in lines:
        if line.count('"') % 2 != 0:
            print("  [!] Unbalanced quote:", line)
        if line.count('[') != line.count(']'):
            print("  [!] Unbalanced bracket:", line)
        if line.count('{') != line.count('}'):
            print("  [!] Unbalanced brace:", line)
        if line.count('(') != line.count(')'):
            print("  [!] Unbalanced paren:", line)
