import re

with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')
first_part = text[:pos_paper_close]

imgs_first = re.findall(r'<img[^>]+src=[\'"]([^\'"]+)[\'"]', first_part)
print(f"Total img tags in first part: {len(imgs_first)}")
for i, img in enumerate(imgs_first[:15]):
    print(f"  {i+1}: {img}")

# Check figures in first part
fig_captions = re.findall(r'Gambar 4\.\d+[^<]*', first_part)
print(f"\nFigure captions in first part: {len(fig_captions)}")
for c in fig_captions:
    print(" ", c.strip())

