with open('scratch/wf_stok_opname.html', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i in range(260, 420):
    if i < len(lines):
        print(lines[i], end='')

