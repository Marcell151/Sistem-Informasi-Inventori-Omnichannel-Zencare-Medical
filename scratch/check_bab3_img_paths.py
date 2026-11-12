import re

with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    c = f.read()

imgs = re.findall(r'<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*alt=[\'"]([^\'"]*)[\'"]', c)
for src, alt in imgs:
    print(f"src: {src} | alt: {alt}")

