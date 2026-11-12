with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('add_ortho(pts, r=8')
print(text[pos:pos+4000])

