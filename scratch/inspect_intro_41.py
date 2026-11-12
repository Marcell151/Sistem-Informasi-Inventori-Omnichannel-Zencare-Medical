with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

idx_bab4_start = text.find('HASIL DAN PEMBAHASAN')
idx_subbab41 = text.find('4.1 Hasil')
if idx_subbab41 == -1:
    idx_subbab41 = text.find('4.1\tImplementasi Sistem')
if idx_subbab41 == -1:
    idx_subbab41 = text.find('4.1 ')

print(text[idx_bab4_start:idx_bab4_start+1500])

