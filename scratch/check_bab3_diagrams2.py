import re

with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    c = f.read()

for num in range(1, 20):
    fig = f"Gambar 3.{num}"
    pos = c.find(fig)
    if pos != -1:
        snippet = c[max(0, pos-1200):min(len(c), pos+300)]
        type_found = []
        if "<svg" in snippet:
            type_found.append("SVG")
        if "<img" in snippet:
            type_found.append("IMG")
        if "mermaid" in snippet:
            type_found.append("MERMAID")
        print(f"Gambar 3.{num}: {', '.join(type_found)}")
    else:
        print(f"Gambar 3.{num}: NOT FOUND")

