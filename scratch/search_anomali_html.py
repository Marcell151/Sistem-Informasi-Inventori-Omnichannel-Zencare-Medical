with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i, l in enumerate(lines, 1):
    if i > 2500: break
    if 'anomali' in l.lower():
        safe_l = l.encode('ascii', errors='replace').decode('ascii')
        print(f"{i}: {safe_l.strip()}")

