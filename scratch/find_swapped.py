with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i, l in enumerate(lines, 1):
    if 'swappedTag' in l or 'swapped' in l or 'is_anomaly' in l:
        if i > 1800 and i < 2200:
            safe_l = l.encode('ascii', errors='replace').decode('ascii')
            print(f"{i}: {safe_l.strip()}")

