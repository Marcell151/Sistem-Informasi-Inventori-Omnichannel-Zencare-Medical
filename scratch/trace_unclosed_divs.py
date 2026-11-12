with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

p_start = re.search(r'class=[\'"][^\'"]*\bpaper\b[^\'"]*[\'"]', text).start()
div_open_end = text.find('>', p_start) + 1
pos = div_open_end

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
stack = [('root_paper', p_start)]

while pos < len(text):
    m = tag_re.search(text, pos)
    if not m:
        break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        if stack:
            popped = stack.pop()
        else:
            print(f"Extra closing tag at {pos}: {tag}")
    else:
        # extract id or class
        m_id = re.search(r'id=[\'"]([^\'"]+)[\'"]', tag)
        m_cls = re.search(r'class=[\'"]([^\'"]+)[\'"]', tag)
        desc = (m_id.group(1) if m_id else '') + ' ' + (m_cls.group(1)[:30] if m_cls else '')
        stack.append((desc.strip(), m.start()))

print(f"Remaining open divs in stack: {len(stack)}")
for desc, start in stack:
    print(f"  Unclosed: {desc} at pos {start}: {text[start:start+80].replace(chr(10), ' ')}")

