import re
import os

with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

img_srcs = re.findall(r'<img\s+[^>]*src="([^"]+)"', text)
print(f"Total images referenced in BAB_III_DAN_IV: {len(img_srcs)}")

missing = []
for src in img_srcs:
    # Resolve relative to laporan_ta/
    target_path = os.path.normpath(os.path.join('laporan_ta', src))
    if not os.path.exists(target_path):
        missing.append((src, target_path))

if missing:
    print("MISSING OR BROKEN IMAGES:", missing)
else:
    print(f"ALL {len(img_srcs)} IMAGES EXIST AND ARE ACCESSIBLE ON DISK!")

