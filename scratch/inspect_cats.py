with open('scratch/build_katalog_50.py', 'r', encoding='utf-8') as f:
    text = f.read()

import re
print("Categories:", set(re.findall(r'"category":\s*"([^"]+)"', text)))
print("Folders:", set(re.findall(r'"folder":\s*"([^"]+)"', text)))

