with open('lain/semua_workflow_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
headers = re.findall(r'<h[234][^>]*>(.*?)</h[234]>|<div class=["\'](?:section-title|main-title)["\'][^>]*>(.*?)</div>', text)
for h in headers:
    print([x for x in h if x])
