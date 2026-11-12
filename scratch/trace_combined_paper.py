import re

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t2 = f.read()

idx_start = t2.find('id="full_bab_iv_content"')
if idx_start == -1:
    idx_start = t2.find('BAB IV')
print("idx around BAB IV in combined:", idx_start)

# Trace where paper container is in combined
idx_paper = t2.find('class="paper')
print("Paper in combined:", t2[idx_paper:idx_paper+150])

pos = idx_paper
depth = 0
tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)

while True:
    m = tag_re.search(t2, pos)
    if not m:
        print("Reached end of combined without closing paper!")
        break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            print(f"Paper in combined CLOSED at index {pos}!")
            print("Context:\n", t2[pos-150:pos+150])
            break
    else:
        depth += 1

print("Bottom in combined:", t2.find('<!-- Bottom Notification (No Print) -->'))

