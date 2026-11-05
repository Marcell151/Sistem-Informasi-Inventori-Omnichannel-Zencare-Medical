with open(r'scratch\clean_mp.html', 'rb') as f:
    b = f.read()

if b.startswith(b'\xff\xfe'):
    s = b.decode('utf-16')
else:
    s = b.decode('utf-8', errors='ignore')

# Find <!DOCTYPE
idx = s.find('<!DOCTYPE')
if idx != -1:
    s = s[idx:]

# Ensure base tag
if '<base' not in s and '<head>' in s:
    s = s.replace('<head>', '<head><base href="http://localhost/inventory_zencare/">', 1)

with open(r'scratch\clean_mp_utf8.html', 'w', encoding='utf-8') as f:
    f.write(s)

print('Cleaned HTML written, size:', len(s))

