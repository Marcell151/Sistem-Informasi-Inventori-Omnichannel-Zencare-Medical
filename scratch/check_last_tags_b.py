from verify_reassembly import piece_50figs
import re

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
matches = list(tag_re.finditer(piece_50figs))
print(f"Total div tags in piece_50figs: {len(matches)}")
for m in matches[-15:]:
    print(m.group(0), piece_50figs[max(0, m.start()-20):m.end()+40].replace('\n', ' '))

