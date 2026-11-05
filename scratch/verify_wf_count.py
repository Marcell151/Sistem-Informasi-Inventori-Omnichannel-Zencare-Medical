with open(r'lain\wireframe_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'data-id="([^"]+)"', text)
print('Total pages in wireframe_zencare.html:', len(set(matches)))
print('List of IDs:')
for m in sorted(list(set(matches))):
    print(' ', m)

