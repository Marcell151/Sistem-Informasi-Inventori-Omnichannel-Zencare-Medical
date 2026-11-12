with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
# check for non-ascii characters that might be emojis
emojis = []
for char in text:
    if ord(char) > 0x1F000 and ord(char) < 0x1FAFF:
        emojis.append(char)
print('Emojis found in stok_opname.php:', set(emojis))

