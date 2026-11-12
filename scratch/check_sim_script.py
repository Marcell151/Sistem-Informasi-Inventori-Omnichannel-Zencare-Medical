with open('scratch/run_full_opname_simulation.php', 'r', encoding='utf-8') as f:
    text = f.read()

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print("run_full_opname_simulation.php forbidden found:", found)

