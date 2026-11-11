with open('pos/pos.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'href=[\'"][^\'"]*logout[^\'"]*[\'"]', text)
print("In pos/pos.php:", matches)

with open('config/layout.php', 'r', encoding='utf-8') as f:
    text2 = f.read()
matches2 = re.findall(r'href=[\'"][^\'"]*logout[^\'"]*[\'"]', text2)
print("In config/layout.php:", matches2)

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text3 = f.read()
matches3 = re.findall(r'href=[\'"][^\'"]*logout[^\'"]*[\'"]', text3)
print("In inventori/stok_opname.php:", matches3)

