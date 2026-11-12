import re

with open('scratch/check_usecase_draft.py', 'r', encoding='utf-8') as f:
    c = f.read()

pos_start = c.find('text = """') + len('text = """')
pos_end = c.find('"""\n\nimport re')
text = c[pos_start:pos_end]

for w in ['audit', 'farmasi', 'apotek', 'akuntansi', 'hpp', 'dosen', 'sidang', 'sempurna']:
    matches = re.findall(rf'\b\w*{w}\w*\b', text, re.I)
    if matches:
        print(f"{w}: {matches}")

