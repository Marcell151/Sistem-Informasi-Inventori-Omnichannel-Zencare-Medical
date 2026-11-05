with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i in range(2950, min(len(lines), 3150)):
    # Clean non-ascii for safe terminal output
    safe_line = lines[i].encode('ascii', errors='replace').decode('ascii')
    print(f"{i+1}: {safe_line}", end='')

