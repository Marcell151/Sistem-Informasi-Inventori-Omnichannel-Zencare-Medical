with open('Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for line_idx in [210, 321, 404]:
    print(f"--- Line {line_idx} ---")
    print(lines[line_idx-1])

