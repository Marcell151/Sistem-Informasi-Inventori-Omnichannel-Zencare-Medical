with open('Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

import re
# Check products with category Alat Kesehatan
matches = re.findall(r"\(\d+,\s*'([^']+)',\s*'([^']+)',\s*[^,]+,\s*[^,]+,\s*[^,]+,\s*'Alat Kesehatan'", sql)
for m in matches:
    print('Alat Kesehatan:', m[0], '-', m[1])

