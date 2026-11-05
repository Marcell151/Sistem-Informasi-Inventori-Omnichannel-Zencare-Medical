with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
matches = re.findall(r'function\s+([a-zA-Z0-9_]+)\s*\(', text)
print("JavaScript functions in pos.php:")
for fn in matches:
    if 'modal' in fn.lower() or 'batch' in fn.lower() or 'anomali' in fn.lower():
        print("  -", fn)

