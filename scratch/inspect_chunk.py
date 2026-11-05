with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

# Let's inspect character 37500 to 48000
chunk = text[37500:48000]
lines = chunk.split('\n')
for i, l in enumerate(lines):
    safe_l = l.encode('ascii', errors='replace').decode('ascii')
    print(f"{i+1}: {safe_l}")

