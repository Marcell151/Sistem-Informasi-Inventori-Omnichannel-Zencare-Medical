with open('Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    text = f.read()

import re

# Check occurrences
words = ['farmasi', 'apotek', 'audit']
for w in words:
    matches = re.finditer(re.escape(w), text, re.IGNORECASE)
    for m in matches:
        start = max(0, m.start() - 40)
        end = min(len(text), m.end() + 40)
        print(f"[{w}] at {m.start()}: ...{text[start:end]}...")

