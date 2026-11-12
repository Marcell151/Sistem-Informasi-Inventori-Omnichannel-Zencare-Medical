import re

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t1 = f.read()

idx_start = t1.find('id="full_bab_iv_content"')
print("idx_start of full_bab_iv_content:", idx_start)

depth = 0
pos = idx_start
tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)

while True:
    m = tag_re.search(t1, pos)
    if not m:
        print("Reached end of document without closing!")
        break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            print(f"full_bab_iv_content CLOSED at index {pos}!")
            print("Context of closing:\n", t1[pos-150:pos+150])
            break
    else:
        depth += 1

print("Bottom notification index:", t1.find('<!-- Bottom Notification (No Print) -->'))
