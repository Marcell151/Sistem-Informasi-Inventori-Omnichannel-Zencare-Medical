with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

print('Length of stok_opname.php:', len(code))

# Check actions in stok_opname.php
import re
actions = re.findall(r"(\$_POST\['action'\]|\$_GET\['action'\]|case\s+'[^']+')", code)
print('Actions found:', set(actions))

