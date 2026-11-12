with open('scratch/wf_stok_opname.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

lines = text.splitlines()
print(f"Total lines in wf_stok_opname.html: {len(lines)}")
print("CSS section (lines 1 to 120):")
for l in lines[:60]:
    print(l)

