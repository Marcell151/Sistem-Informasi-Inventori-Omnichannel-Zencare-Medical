with open('lain/wireframe_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i in range(560, 585):
    print(f"L{i+1}: {lines[i]}", end='')

