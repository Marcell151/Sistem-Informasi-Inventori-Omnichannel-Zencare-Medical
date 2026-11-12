from verify_reassembly import piece_50figs
import re

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
depth = 0
for m in tag_re.finditer(piece_50figs):
    tag = m.group(0)
    if tag.startswith('</'):
        depth -= 1
    else:
        depth += 1

print(f"Net div depth change inside piece_50figs: {depth}")

