import os
import re

for fpath in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(fpath, 'r', encoding='utf-8', errors='ignore') as f:
        c = f.read()
    imgs = re.findall(r'<img[^>]+src=[\'"]([^\'"]+)[\'"]', c)
    base_dir = os.path.dirname(os.path.abspath(fpath))
    missing = []
    for src in imgs:
        full_p = os.path.normpath(os.path.join(base_dir, src))
        if not os.path.exists(full_p):
            missing.append(src)
    print(f"{fpath}: {len(imgs)} images, {len(missing)} missing")
    if missing:
        print("  Missing:", missing[:5])

