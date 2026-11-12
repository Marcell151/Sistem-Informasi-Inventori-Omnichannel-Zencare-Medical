with open("laporan_ta/BAB_II_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos = text.find('disempurnakan')
print(repr(text[pos-50:pos+50]))

