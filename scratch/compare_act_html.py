with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    html_text = f.read()

with open('scratch/user_step_22579.txt', 'r', encoding='utf-8') as f:
    user_text = f.read()

print('HTML length:', len(html_text))
print('User text length:', len(user_text))

# Let's check if Gambar 3.17 in HTML has the latest wording
idx17 = html_text.find('H. Kelola Stok Opname')
if idx17 != -1:
    print('HTML H section snippet:')
    print(html_text[idx17:idx17+1200])

