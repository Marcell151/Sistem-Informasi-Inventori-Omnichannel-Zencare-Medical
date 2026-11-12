import os
from PIL import Image
import itertools

img_dir = r"lain\File PPT\UI_UX_Asli"
sys_path = 'scratch'
import sys
sys.path.append(sys_path)
from build_katalog_50 import items

print(f"Total items in catalog: {len(items)}")

# 1. Check all image files exist and get basic stats
imgs = {}
for it in items:
    fname = it['filename']
    fpath = os.path.join(img_dir, fname)
    if not os.path.exists(fpath):
        print(f"MISSING: {fname}")
        continue
    img = Image.open(fpath)
    imgs[it['fig']] = {
        'filename': fname,
        'title': it['title'],
        'img': img,
        'size': img.size,
        'bytes': os.path.getsize(fpath)
    }

# 2. Check visual difference / identical pixels across ALL pairs!
print("\nChecking pixel similarity across all pairs...")
suspicious_pairs = []

# Resize to small thumbnail for fast comparison
thumbs = {}
for fig, data in imgs.items():
    thumbs[fig] = data['img'].convert('L').resize((64, 64))

for (fig1, d1), (fig2, d2) in itertools.combinations(imgs.items(), 2):
    # Compute mean absolute difference between thumbnails
    t1 = list(thumbs[fig1].getdata())
    t2 = list(thumbs[fig2].getdata())
    diff = sum(abs(p1 - p2) for p1, p2 in zip(t1, t2)) / (64 * 64)
    
    # If difference is very small (less than 15 out of 255), they are virtually identical images!
    if diff < 15:
        suspicious_pairs.append((fig1, d1['filename'], fig2, d2['filename'], diff))

print(f"\nFound {len(suspicious_pairs)} highly similar image pairs:")
for fig1, fn1, fig2, fn2, diff in suspicious_pairs:
    print(f"  {fig1} ({fn1}) vs {fig2} ({fn2}) -> diff score: {diff:.2f}")


