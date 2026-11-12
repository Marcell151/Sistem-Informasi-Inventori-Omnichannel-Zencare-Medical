import json
import os
import re

with open('lain/wireframe_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    c = f.read()

m = re.search(r'const wireframes = (\[.*?\]);', c, re.DOTALL)
if m:
    wf = json.loads(m.group(1))
    missing = []
    base_dir = os.path.abspath('lain')
    for item in wf:
        img_p = os.path.normpath(os.path.join(base_dir, item['png']))
        if not os.path.exists(img_p):
            missing.append(item['png'])
    print(f"wireframe_zencare.html: {len(wf)} wireframes, {len(missing)} missing PNGs")
    if missing:
        print("Missing:", missing)

