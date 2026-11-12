with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('svg_parts.append(\'  <g id="connections"')
if pos == -1:
    pos = text.find('build_rounded_path')
print(text[pos+1000:pos+3500])

