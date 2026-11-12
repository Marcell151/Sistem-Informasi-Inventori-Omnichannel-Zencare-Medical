with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('ports[t_name] = {}')
print(text[pos:pos+2500])

