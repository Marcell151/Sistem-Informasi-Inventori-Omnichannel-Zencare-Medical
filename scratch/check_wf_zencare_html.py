with open('lain/wireframe_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

print("lain/wireframe_zencare.html length:", len(text))
print("First 500 chars:")
print(text[:500])

import re
titles = re.findall(r'<div[^>]*class="[^"]*wf-title[^"]*"[^>]*>(.*?)</div>', text)
print(f"\nwf-title in wireframe_zencare.html: {len(titles)}")
for t in titles[:10]:
    print("  -", t)

# Check opname in wireframe_zencare.html
for term in ['stok opname', 'audit', 'terminal']:
    m = re.findall(term, text, re.IGNORECASE)
    print(f"Term '{term}': {len(m)}")

