with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('# Table Cards (Rendered AFTER lines')
print(text[pos:pos+2500])

