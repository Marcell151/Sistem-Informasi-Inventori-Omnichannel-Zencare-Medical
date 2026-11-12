with open('config/layout.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = re.findall(r'<a[^>]*href=[^>]*>.*?</a>', code, re.DOTALL)
for m in matches:
    if any(k in m.lower() for k in ['dokumen', 'testing', 'panduan', 'bimbingan']):
        print('Found in layout.php:', m.strip())
