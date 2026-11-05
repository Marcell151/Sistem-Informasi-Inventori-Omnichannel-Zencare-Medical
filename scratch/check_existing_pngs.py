import os
from PIL import Image

wf_dir = r'lain\File PPT\Wireframe'
files = [f for f in os.listdir(wf_dir) if f.endswith('.png')]
print(f"Total existing PNG wireframes in {wf_dir}: {len(files)}")
for f in sorted(files):
    p = os.path.join(wf_dir, f)
    im = Image.open(p)
    print(f"  {f:35} | Size: {im.size} | Mode: {im.mode}")

