with open('scratch/update_kamus_static_tables.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

lines = text.splitlines()
print(f"Total lines: {len(lines)}")
for l in lines[15:70]:
    print(l)

