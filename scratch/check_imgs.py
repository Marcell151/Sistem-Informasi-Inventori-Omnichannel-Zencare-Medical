with open(r'scratch\clean_mp_utf8.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
onerrors = re.findall(r'onerror\s*=\s*["\']([^"\']+)["\']', text)
print('Total onerror attributes:', len(onerrors))
for o in set(onerrors):
    print(o)

imgs = re.findall(r'<img[^>]+src\s*=\s*["\']([^"\']+)["\']', text)
print('Total img tags:', len(imgs))
for img in imgs[:10]:
    print('Img src:', img)

