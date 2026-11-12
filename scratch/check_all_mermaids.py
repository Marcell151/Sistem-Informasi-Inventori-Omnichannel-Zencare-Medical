import re

files = [
    'laporan_ta/BAB_I_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_II_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_V_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/LAPORAN_TA_ZENCARE_MEDICAL_FULL.html'
]

print("=== CHECKING MERMAID BLOCKS IN ALL FILES ===")
for f in files:
    with open(f, 'r', encoding='utf-8') as fp:
        c = fp.read()
    m_blocks = re.findall(r'<div class=[\'"]mermaid[\'"]>(.*?)</div>', c, re.DOTALL)
    print(f"{f}: {len(m_blocks)} mermaid blocks")
    for i, b in enumerate(m_blocks):
        lines = [l.strip() for l in b.strip().splitlines() if l.strip()]
        for l in lines:
            if l.count('"') % 2 != 0:
                print(f"  [!] In block {i+1}, unbalanced quote: {l}")
            if l.count('[') != l.count(']'):
                print(f"  [!] In block {i+1}, unbalanced bracket: {l}")

print("All mermaid syntax verified!")
