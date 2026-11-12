with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
idx_start = text.find('id="full_bab_iv_content"')

pos = text.find('>', idx_start) + 1
depth = 1 # inside full_bab_iv_content
tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)

while pos < len(text):
    m = tag_re.search(text, pos)
    if not m:
        print("Reached end of document without closing!")
        break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            print(f"full_bab_iv_content REALLY CLOSED at index {pos}!")
            print("Before:\n", text[pos-200:pos])
            print("After:\n", text[pos:pos+200])
            break
        elif depth < 0:
            print(f"Depth went NEGATIVE ({depth}) at index {pos}!")
            break
    else:
        depth += 1

print(f"Loop finished. Pos={pos}, Total len={len(text)}, Depth={depth}")

