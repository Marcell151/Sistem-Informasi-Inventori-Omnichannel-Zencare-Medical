with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, l in enumerate(lines):
    if 'eksekusi_mutasi_baris' in l:
        print(f"{i+1}: {l.strip()}")

