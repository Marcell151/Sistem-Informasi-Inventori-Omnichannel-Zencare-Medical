with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos = 353943
print("Around 353943 in BAB_III:")
print(text[pos-200:pos+500])

