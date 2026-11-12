with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos = 137052
print("=== AROUND POS 137052 IN BAB IV ===")
print(text[pos-200:pos+500])

