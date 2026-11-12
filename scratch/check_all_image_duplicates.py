import os
import hashlib
from collections import defaultdict

img_dir = r'c:\xampp\htdocs\inventory_zencare\lain\File PPT\UI_UX_Asli'
from build_katalog_50 import items

hashes = {}
sizes = {}
duplicates = defaultdict(list)

for item in items:
    fname = item['filename']
    fpath = os.path.join(img_dir, fname)
    if not os.path.exists(fpath):
        print(f"MISSING: {fname}")
        continue
    h = hashlib.md5(open(fpath, 'rb').read()).hexdigest()
    s = os.path.getsize(fpath)
    hashes[fname] = h
    sizes[fname] = s
    duplicates[h].append(fname)

print("=== CHECKING FOR DUPLICATE IMAGES ===")
found_dup = False
for h, flist in duplicates.items():
    if len(flist) > 1:
        found_dup = True
        print(f"DUPLICATE DETECTED ({len(flist)} files share MD5 {h}):")
        for f in flist:
            print(f"  - {f} (size: {sizes[f]} bytes)")

if not found_dup:
    print("Zero byte-identical duplicates found among the 50 items.")

# Now let's check files with nearly identical sizes (within 1%)
print("\n=== CHECKING NEARLY IDENTICAL SIZES (<1% difference) ===")
sorted_by_size = sorted(sizes.items(), key=lambda x: x[1])
for i in range(len(sorted_by_size) - 1):
    f1, s1 = sorted_by_size[i]
    f2, s2 = sorted_by_size[i+1]
    diff = abs(s1 - s2)
    pct = diff / max(s1, s2) * 100
    if pct < 1.0:
        print(f"Similar size ({pct:.2f}% diff): {f1} ({s1}B) vs {f2} ({s2}B)")

