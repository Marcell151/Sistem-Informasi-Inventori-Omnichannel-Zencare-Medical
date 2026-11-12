import re

with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')

print("=== HEADINGS BEFORE PAPER CLOSE ===")
h_before = re.findall(r'<h[1-6][^>]*>(.*?)</h[1-6]>', text[:pos_paper_close], re.DOTALL)
for h in h_before:
    clean = re.sub(r'<[^>]+>', '', h).strip()
    if clean:
        print(" ", clean[:80])

print("\n=== HEADINGS AFTER PAPER CLOSE ===")
h_after = re.findall(r'<h[1-6][^>]*>(.*?)</h[1-6]>', text[pos_paper_close:], re.DOTALL)
for h in h_after:
    clean = re.sub(r'<[^>]+>', '', h).strip()
    if clean:
        print(" ", clean[:80])

