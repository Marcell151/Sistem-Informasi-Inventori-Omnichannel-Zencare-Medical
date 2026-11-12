with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
groups = re.findall(r'<g [^>]*id="([^"]+)"', text)
print("Group IDs in SVG:", groups)

# Find where paths are defined
paths = re.findall(r'(<path [^>]+>)', text)
print("Total paths in SVG:", len(paths))
for p in paths[:15]:
    print(p[:120])

