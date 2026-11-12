with open('lain/wireframe_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
matches = re.findall(r'<script>(.*?)</script>', text, re.DOTALL)
print(f"Total script tags: {len(matches)}")
for idx, s in enumerate(matches):
    print(f"Script {idx}: length {len(s)}")
    print(s[:300])

