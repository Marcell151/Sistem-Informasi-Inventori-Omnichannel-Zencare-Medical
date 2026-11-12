with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
idx_start = text.find('id="full_bab_iv_content"')

pos = idx_start
tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)

for i in range(10):
    m = tag_re.search(text, pos)
    if not m: break
    print(f"{i}: {m.group(0)} at {m.start()}..{m.end()}")
    pos = m.end()

