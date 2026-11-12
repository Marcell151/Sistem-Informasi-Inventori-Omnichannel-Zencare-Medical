with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

print("Last 1000 characters of BAB_IV:")
print(text[-1000:])

