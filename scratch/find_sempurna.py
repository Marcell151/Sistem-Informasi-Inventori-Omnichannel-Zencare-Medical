with open("laporan_ta/BAB_II_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

m = re.search(r'Sempurna', text, re.IGNORECASE)
if m:
    print("Found at:", m.start())
    print(text[max(0, m.start()-100):m.end()+100])

