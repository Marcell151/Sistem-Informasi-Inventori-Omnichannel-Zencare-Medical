import os, re

for root, dirs, files in os.walk('.'):
    if '.git' in root or '.system_generated' in root:
        continue
    for f in files:
        if f.endswith(('.php', '.html', '.js')):
            p = os.path.join(root, f)
            with open(p, 'r', encoding='utf-8', errors='ignore') as fp:
                c = fp.read()
                if 'auth/logout' in c:
                    print(f"Found in {p}")

