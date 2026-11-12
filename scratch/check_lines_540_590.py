with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

lines = text.splitlines()
for i in range(540, min(590, len(lines))):
    print(f"L{i+1}: {lines[i]}")

