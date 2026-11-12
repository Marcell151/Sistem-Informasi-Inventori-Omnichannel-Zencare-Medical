with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re
matches = [m.start() for m in re.finditer(r'<!-- Bottom Notification', text)]
print(f"Matches for '<!-- Bottom Notification': {matches}")
for idx in matches:
    print(f"At {idx}:")
    print(text[idx:idx+200])
    print("="*40)

