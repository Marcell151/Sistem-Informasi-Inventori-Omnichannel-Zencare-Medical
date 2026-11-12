from verify_reassembly import piece_top, piece_50figs
import re

content = piece_top + "\n" + piece_50figs

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
depth = 0
unmatched_closes = 0

for m in tag_re.finditer(content):
    tag = m.group(0)
    if tag.startswith('</'):
        depth -= 1
        if depth < 0:
            unmatched_closes += 1
            print(f"Unmatched closing div at pos {m.start()}: {content[max(0, m.start()-50):m.end()+20]}")
            depth = 0
    else:
        depth += 1

print(f"Final depth after piece_top + piece_50figs: {depth}, unmatched closes: {unmatched_closes}")

