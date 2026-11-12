with open(r'c:\xampp\htdocs\inventory_zencare\scratch\capture_all_operational_ui.py', 'r', encoding='utf-8') as f:
    lines = f.readlines()

print(f'Total lines: {len(lines)}')
for i, l in enumerate(lines[100:180], start=101):
    print(f'{i:3d}: {l}', end='')
