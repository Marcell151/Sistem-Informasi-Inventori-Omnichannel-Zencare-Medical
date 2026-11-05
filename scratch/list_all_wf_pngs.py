import os

wf_dir = r'lain\File PPT\Wireframe'
pngs = sorted([f for f in os.listdir(wf_dir) if f.endswith('.png')])
print(f"Total PNG wireframes: {len(pngs)}")
for i, f in enumerate(pngs, 1):
    print(f"{i:2}. {f}")

