with open(r'c:\xampp\htdocs\inventory_zencare\scratch\build_unified_bab4_flow.py', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, l in enumerate(lines[220:280], start=221):
    print(f'{i:3d}: {l}', end='')
