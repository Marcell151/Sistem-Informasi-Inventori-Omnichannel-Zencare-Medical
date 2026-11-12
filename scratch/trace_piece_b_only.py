from verify_reassembly import piece_50figs
import re

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
depth = 0

for idx, m in enumerate(tag_re.finditer(piece_50figs)):
    tag = m.group(0)
    if tag.startswith('</'):
        depth -= 1
    else:
        depth += 1
    if depth < 0:
        print(f"Tag {idx} went below zero ({depth}) at {m.start()}: {tag}")
        print(piece_50figs[max(0, m.start()-60):m.end()+40])
        break

print(f"Final depth inside piece_50figs alone: {depth}")

