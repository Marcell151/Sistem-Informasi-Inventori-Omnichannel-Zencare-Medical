with open('Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    lines = f.readlines()

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']

for i, line in enumerate(lines):
    for w in forbidden:
        if w in line.lower():
            print(f"Line {i+1} [{w}]: {line.strip()[:100]}")
