import os
import sys
from PIL import Image, ImageChops

sys.path.append('scratch')
from build_katalog_50 import items

img_dir = r"lain\File PPT\UI_UX_Asli"

imgs = []
for it in items:
    fn = it['filename']
    fp = os.path.join(img_dir, fn)
    im = Image.open(fp).convert('RGB').resize((256, 256))
    imgs.append((it['fig'], fn, im))

print(f"Loaded {len(imgs)} images.")

very_close = []
for i in range(len(imgs)):
    for j in range(i + 1, len(imgs)):
        fig1, fn1, im1 = imgs[i]
        fig2, fn2, im2 = imgs[j]
        diff = ImageChops.difference(im1, im2)
        stat = sum(diff.convert('L').getdata()) / (256 * 256)
        if stat < 4.0:
            very_close.append((fig1, fn1, fig2, fn2, stat))

print(f"\nPairs with diff < 4.0: {len(very_close)}")
for v in very_close:
    print(f"  {v[0]} ({v[1]}) vs {v[2]} ({v[3]}) -> diff = {v[4]:.3f}")


