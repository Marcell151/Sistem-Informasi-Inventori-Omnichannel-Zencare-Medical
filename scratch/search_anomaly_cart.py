with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'is_anomaly|anomali', text, re.IGNORECASE)]
print(f"Total matches: {len(matches)}")
for pos in matches:
    snippet = text[max(0, pos-80):min(len(text), pos+120)].replace('\n', ' ')
    print(f"[{pos}]: {snippet}")

