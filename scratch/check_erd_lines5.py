with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('GUTTER 3-4')
if pos != -1:
    print(text[pos:pos+3500])
else:
    pos = text.find('opname')
    print(text[pos:pos+2500])

