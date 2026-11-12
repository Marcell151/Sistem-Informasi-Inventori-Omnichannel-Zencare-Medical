with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
idx = text.find('4.1 Hasil')
print("Surrounding 4.1 Hasil:\n", text[idx-50:idx+350])

