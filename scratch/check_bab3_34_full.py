with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = 126985
end_pos = text.find('3.5 Kamus Data', pos)
print(text[pos:end_pos])

