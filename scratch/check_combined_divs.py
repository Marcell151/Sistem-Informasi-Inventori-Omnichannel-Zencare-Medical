with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
idx_start = text.find('BAB IV')
idx_42 = text.find('id="subbab_42"')

pos = text.find('class="paper', 0, idx_start) # find paper container
print("Paper start in combined:", pos)

pos = text.find('>', pos) + 1
depth = 1
tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)

closed_at = None
while pos < idx_42:
    m = tag_re.search(text, pos)
    if not m: break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            closed_at = pos
            print(f"Paper closed BEFORE subbab_42 at index {pos}!")
            print("Surrounding:\n", text[pos-100:pos+100])
            break
    else:
        depth += 1

print(f"At subbab_42, depth in combined is: {depth}")

