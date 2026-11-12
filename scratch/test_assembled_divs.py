from verify_reassembly import piece_top, piece_50figs, piece_uat_tables, footer
import re

assembled = piece_top + "\n\n" + piece_50figs + "\n\n" + piece_uat_tables + "\n\n" + footer

p_start = assembled.find('class="paper')
div_open_end = assembled.find('>', p_start) + 1
pos = div_open_end
depth = 1

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
bottom_pos = assembled.find('<!-- Bottom Notification')

while pos < len(assembled):
    m = tag_re.search(assembled, pos)
    if not m:
        break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            print(f"Paper closed at pos {pos}!")
            dist = bottom_pos - pos
            print(f"Distance to bottom notification: {dist} chars.")
            leak = assembled[pos:bottom_pos].strip()
            print(f"Leaked text length: {len(leak)}")
            break
    else:
        depth += 1

print(f"Final depth at EOF: {depth}")

