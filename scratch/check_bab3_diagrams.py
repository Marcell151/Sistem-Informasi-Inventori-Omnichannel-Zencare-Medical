import re

with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    c = f.read()

figs = re.findall(r'<b>(Gambar 3\.\d+)</b>\s*([^<]+)', c)
for fig_num, fig_title in figs:
    # find where this figure is
    pos = c.find(fig_num)
    # search preceding 600 chars
    pre = c[max(0, pos-600):pos]
    tag = "UNKNOWN"
    if "<img" in pre:
        src = re.findall(r'<img[^>]+src=[\'"]([^\'"]+)', pre)
        tag = f"IMG: {src[-1] if src else ''}"
    elif "<svg" in pre:
        tag = "INLINE SVG"
    elif "mermaid" in pre:
        tag = "MERMAID"
    print(f"{fig_num}: {fig_title.strip()[:40]} -> {tag}")

