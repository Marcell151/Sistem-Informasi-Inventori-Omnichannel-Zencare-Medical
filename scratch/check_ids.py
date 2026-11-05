with open(r'c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

print('Has full_bab3_content:', 'id="full_bab3_content"' in text)
print('Has full_bab4_content:', 'id="full_bab4_content"' in text)
print('Has subbab_41_content:', 'id="subbab_41_content"' in text)
print('Has subbab_411_content:', 'id="subbab_411_content"' in text)
print('Button copy counts:', text.count('copyText('))

