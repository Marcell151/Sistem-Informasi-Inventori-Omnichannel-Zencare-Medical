with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')
second_part = text[pos_paper_close:]

print("First 500 chars of second_part:")
print(second_part[:500])

