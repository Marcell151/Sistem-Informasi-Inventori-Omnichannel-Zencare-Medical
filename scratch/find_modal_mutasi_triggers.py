with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'onclick=[\'"][^\'"]*modal_mutasi[^\'"]*[\'"]', text)
print(matches[:5])
matches2 = re.findall(r'function \w*[Mm]utasi\w*\(', text)
print(matches2)

