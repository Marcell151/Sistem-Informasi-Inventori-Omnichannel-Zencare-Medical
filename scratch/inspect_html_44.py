with open('scratch/cache/44_Modal_Mutasi_Opname.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
print("Length:", len(text))
# Check if modal_mutasi is in HTML
pos = text.find('id="modal_mutasi"')
if pos != -1:
    print("Found modal_mutasi at:", pos)
    print(text[pos:pos+500])
else:
    print("modal_mutasi NOT FOUND!")

# Check what the main heading is
headings = re.findall(r'<h[1-3][^>]*>([\s\S]*?)</h[1-3]>', text)
print("Headings:", [re.sub(r'<[^>]+>', '', h).strip() for h in headings[:10]])

