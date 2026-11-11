with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'id=[\'"]modal_[^\'"]+[\'"]', text)
print("Modals found:", matches)

