with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

print('Length of cetak_berita_acara_opname.php:', len(code))

import re
# check page styling
print('A4 landscape present:', '@page' in code or 'landscape' in code)
print('Repeating header (thead):', 'thead' in code)
print('Page number style:', 'counter(page)' in code)
# check how discrepancies are highlighted (border vs outline vs background)
discrepancy_matches = re.findall(r'selisih\s*!==\s*0.*', code)
for m in discrepancy_matches[:5]:
    print("Discrepancy match:", m)

