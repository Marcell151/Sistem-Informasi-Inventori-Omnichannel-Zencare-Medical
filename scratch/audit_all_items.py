import os
import sys
from PIL import Image

sys.path.append('scratch')
from build_katalog_50 import items

img_dir = r"lain\File PPT\UI_UX_Asli"

print("Auditing all 50 images in catalog:")
for it in items:
    fname = it['filename']
    fpath = os.path.join(img_dir, fname)
    if not os.path.exists(fpath):
        print(f"ERROR: {it['fig']} ({fname}) does not exist!")
        continue
    img = Image.open(fpath)
    sz = os.path.getsize(fpath)
    print(f"{it['fig']}: {fname} | size={img.size} | bytes={sz:,} | title: {it['title']}")


