with open('includes/sidebar.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re
matches = re.findall(r'<a[^>]*href=[^>]*>.*?</a>', code, re.DOTALL)
for m in matches:
    if any(k in m.lower() for k in ['dokumen', 'testing', 'panduan']):
        print('Found in sidebar:', m.strip())

