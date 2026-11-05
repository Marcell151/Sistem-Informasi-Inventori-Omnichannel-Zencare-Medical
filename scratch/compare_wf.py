import os
import re

wf_dir = r'lain\File PPT\Wireframe'
png_files = [f.replace('.png', '') for f in os.listdir(wf_dir) if f.endswith('.png')]

with open('lain/wireframe_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

html_titles = re.findall(r'title:\s*[\'"]([^\'"]+)[\'"]', text)

print("PNG files (22):")
for p in sorted(png_files):
    print(" -", p)

print("\nHTML titles (18):")
for h in html_titles:
    print(" -", h)

print("\nIn PNG but not in HTML:")
for p in sorted(png_files):
    matched = any(p.lower() in h.lower() or h.lower() in p.lower() for h in html_titles)
    if not matched:
        print(" * Missing in HTML:", p)

