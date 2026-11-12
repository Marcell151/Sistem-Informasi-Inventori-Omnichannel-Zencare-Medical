with open(r'scratch\capture_missing_and_adjusted_ui.py', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i in range(80, 155):
    if i < len(lines):
        print(f"{i+1}: {lines[i]}", end='')

