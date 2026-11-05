with open('pos/pos.php', 'rb') as f:
    content = f.read()

idx = content.find(b'function toggleBatchTerlewatPanel()')
text = content[idx:idx+1000].decode('utf-8', errors='replace')
print(text.encode('ascii', errors='backslashreplace').decode('ascii'))
