with open('inventori/dokumentasi_testing.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'.{0,50}5 (?:Kasus|Kasus Uji|Skenario|Anomali).{0,50}', text, re.IGNORECASE)
for m in matches:
    print(m)

