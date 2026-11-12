with open("laporan_ta/BAB_II_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

target = 'dapat disempurnakan.'
replacement = 'dapat dilengkapi dan dikembangkan lebih lanjut.'

assert target in text, "Target not found in BAB_II"
text = text.replace(target, replacement)

with open("laporan_ta/BAB_II_ZENCARE_MEDICAL.html", "w", encoding="utf-8") as f:
    f.write(text)

print("Replaced 'disempurnakan' in BAB_II successfully!")

