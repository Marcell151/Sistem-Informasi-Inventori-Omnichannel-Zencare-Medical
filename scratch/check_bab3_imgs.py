with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

imgs = re.findall(r'<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\']', text)
print(f"Total img tags in Bab III: {len(imgs)}")
for i, (src, alt) in enumerate(imgs[:30]):
    print(f"{i+1:2}: {src} | {alt}")

