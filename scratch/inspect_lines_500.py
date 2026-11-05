with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

for i in range(505, 605):
    safe_l = lines[i].encode('ascii', errors='replace').decode('ascii')
    print(f"{i+1}: {safe_l}", end='')

