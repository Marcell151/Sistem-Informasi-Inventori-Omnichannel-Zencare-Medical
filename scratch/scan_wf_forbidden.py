with open('scratch/latest_user_wf.txt', 'r', encoding='utf-8') as f:
    text = f.read()

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print('Forbidden words found in wireframe prompt:', found)

lines = text.split('\n')
for i, line in enumerate(lines):
    for w in forbidden:
        if w in line.lower():
            print(f"Line {i+1} [{w}]:")
            print("   ", line.strip())

