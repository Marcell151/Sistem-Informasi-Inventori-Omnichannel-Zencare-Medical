with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'class=[\'"][^\'"]*\bpaper\b[^\'"]*[\'"]', text)]
print("Paper occurrences at positions:", matches)
for pos in matches:
    print(text[max(0, pos-50):pos+100])
    print("="*40)

