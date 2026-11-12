with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos_leak = text.find('Kesehatan ZenCare Medical dinyatakan telah memenuhi seluruh spesifikasi kebutuhan bisnis pengguna serta siap dioperasikan penuh pada gerai operasional.')
print('Found pos_leak:', pos_leak)
print(text[pos_leak:pos_leak+1000])

