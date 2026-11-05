with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_3_5 = text.find('id="sec_3_5"')
print("3.5 starts at:", idx_3_5)
idx_close_paper = text.find('<!-- Closes full_bab3_content paper -->')
print("Paper closes at:", idx_close_paper)
print(text[idx_close_paper-300:idx_close_paper+200].encode('ascii', errors='replace').decode('ascii'))

