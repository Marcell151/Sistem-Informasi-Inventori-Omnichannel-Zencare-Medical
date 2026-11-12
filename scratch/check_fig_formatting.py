import re

with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')

matches_all = re.findall(r'Gambar 4\.\d+', text)
print(f"Total 'Gambar 4.x' in entire file: {len(matches_all)}")

matches_before = re.findall(r'Gambar 4\.\d+', text[:pos_paper_close])
print(f"Total 'Gambar 4.x' before paper close: {len(matches_before)}")

matches_after = re.findall(r'Gambar 4\.\d+', text[pos_paper_close:])
print(f"Total 'Gambar 4.x' after paper close: {len(matches_after)}")

print("\nSamples before paper close:")
for m in matches_before[:10]:
    print(" ", m)

print("\nSamples after paper close:")
for m in matches_after[:10]:
    print(" ", m)

