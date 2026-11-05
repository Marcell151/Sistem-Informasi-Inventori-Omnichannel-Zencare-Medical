import re

with open('lain/wireframe_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

nav_items = re.findall(r'<div class="nav-item[^"]*"[^>]*onclick="showPage\(\'([^\']+)\'\)[^>]*>(.*?)</div>', text, re.DOTALL)
print(f"Total nav items: {len(nav_items)}")
for page_id, inner in nav_items:
    clean_inner = re.sub(r'<[^>]+>', ' ', inner).strip()
    clean_inner = ' '.join(clean_inner.split())
    print(f"  {page_id:25} -> {clean_inner}")

pages = re.findall(r'<div id="page-([^"]+)"', text)
print(f"\nTotal page divs: {len(pages)}")
print(pages)

