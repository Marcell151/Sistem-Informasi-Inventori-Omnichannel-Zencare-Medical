with open(r'c:\xampp\htdocs\inventory_zencare\database\Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    text = f.read()

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print("database/Demo_Bersih.sql forbidden found:", found)

