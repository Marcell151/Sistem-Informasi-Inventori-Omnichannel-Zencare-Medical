with open('laporan_ta/DOKUMENTASI_TESTING_STOK_OPNAME.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'<th[^>]*>(.*?)</th>', text)
print('Table headers found:', set(matches))

