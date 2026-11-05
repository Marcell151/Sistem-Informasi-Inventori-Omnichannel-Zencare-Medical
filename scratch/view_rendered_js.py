with open('scratch/rendered_js.txt', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

lines = text.split('\n')
for i, l in enumerate(lines[:60]):
    safe_l = l.encode('ascii', errors='replace').decode('ascii')
    print(f"{i+1}: {safe_l}")

