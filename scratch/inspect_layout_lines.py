with open('config/layout.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i in range(324, 355):
    print(f"{i+1}: {lines[i]}", end='')

