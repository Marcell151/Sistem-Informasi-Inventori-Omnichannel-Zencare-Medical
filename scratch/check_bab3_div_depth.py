with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

p_start = text.find('class="paper')
if p_start == -1:
    p_start = text.find("class='paper'")
if p_start == -1:
    # search regex
    m = re.search(r'class=[\'"][^\'"]*\bpaper\b[^\'"]*[\'"]', text)
    p_start = m.start()

div_open_end = text.find('>', p_start) + 1
pos = div_open_end
depth = 1

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
unclosed = []

while pos < len(text):
    m = tag_re.search(text, pos)
    if not m:
        break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            print(f"Paper closed at pos {pos}!")
            break
    else:
        depth += 1

print(f"End of file reached with depth = {depth}")

