with open('lain/logical_database_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    ldm = f.read()

import re
matches = [m.start() for m in re.finditer('fisik_baik', ldm)]
print("Matches for fisik_baik in logical_database_zencare.html:", len(matches))
for pos in matches:
    print(ldm[pos-50:pos+100])

