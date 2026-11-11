with open('inventori/dokumentasi_testing.php', 'r', encoding='utf-8') as f:
    text = f.read()

text = text.replace('5 Kasus Uji Nyata', '6 Kasus Uji Nyata')
text = text.replace('116 Item (95.8%)', '115 Item (95.0%)')
text = text.replace('5 Anomali', '6 Anomali')
text = text.replace('5 skenario anomali', '6 skenario anomali')
text = text.replace('5 / 5 Anomali', '6 / 6 Anomali')
text = text.replace('seluruh 5 anomali', 'seluruh 6 anomali')

with open('inventori/dokumentasi_testing.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated dokumentasi_testing.php to 6 anomaly cases!")

