import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print('Total lines:', len(lines))

post_actions = []
for i, line in enumerate(lines):
    m = re.search(r"if\s*\(\$aksi\s*===?\s*['\"]([^'\"]+)['\"]", line)
    if m:
        post_actions.append((i+1, m.group(1)))

print('POST action handlers found:')
for line_no, act in post_actions:
    print(f'Line {line_no}: {act}')

