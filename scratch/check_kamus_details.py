with open('scratch/user_step_kamus.txt', 'r', encoding='utf-8') as f:
    text = f.read()

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']

lines = text.split('\n')
for i, line in enumerate(lines):
    for w in forbidden:
        if w in line.lower():
            print(f"Line {i+1} [{w}]:")
            print("   ", line.strip())
