import re

with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

# Let's search for every single occurrence of "Gambar 4." with its line number and position
lines = text.splitlines()
for idx, line in enumerate(lines):
    if "Gambar 4." in line:
        print(f"L{idx+1}: {line.strip()[:100]}")

