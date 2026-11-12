import re

with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')
second_part = text[pos_paper_close:]

imgs_second = re.findall(r'<img[^>]+src=[\'"]([^\'"]+)[\'"]', second_part)
print(f"Total img tags in second part: {len(imgs_second)}")

fig_captions_second = re.findall(r'Gambar 4\.\d+[^<]*', second_part)
print(f"Figure captions in second part: {len(fig_captions_second)}")
for c in fig_captions_second[:25]:
    print(" ", c.strip())

