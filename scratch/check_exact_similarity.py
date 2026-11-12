import os
from PIL import Image, ImageChops
import itertools
import sys
sys.path.append('scratch')
from build_katalog_50 import items

img_dir = r"lain\File PPT\UI_UX_Asli"

imgs = {}
for it in items:
    fname = it['filename']
    fpath = os.path.join(img_dir, fname)
    img = Image.open(fpath)
    imgs[it['fig']] = {
        'filename': fname,
        'title': it['title'],
        'img': img
    }

print("Checking for extremely close pairs (diff < 4.0)...")
for (fig1, d1), (fig2, d2) in itertools.combinations(imgs.items(), 2):
    # Resize both to same 256x256
    im1 = d1['img'].convert('RGB').resize((256, 256))
    im2 = d2['img'].convert('RGB').resize((256, 256))
    diff = ImageChops.difference(im1, im2)
    # calculate average pixel difference
    stat = sum(diff.convert('L').getdata()) / (256 * 256)
    if stat < 5.0:
        print(f"VERY SIMILAR: {fig1} ({d1['filename']}) vs {fig2} ({d2['filename']}) -> diff = {stat:.3f}")
    elif stat < 10.0:
        print(f"MODERATELY SIMILAR: {fig1} ({d1['filename']}) vs {fig2} ({d2['filename']}) -> diff = {stat:.3f}")


