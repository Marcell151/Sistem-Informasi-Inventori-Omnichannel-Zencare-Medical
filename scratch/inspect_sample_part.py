with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
idx_start = text.find('<tbody>', text.find('Tabel 4.2'))
idx_end = text.find('</tbody>', idx_start)
tbody_content = text[idx_start+7:idx_end]
parts = re.split(r'<!--\s*(Bagian\s+[IVX]+)\s*-->', tbody_content)
print("Sample part_body 1:\n", parts[2][:600])

