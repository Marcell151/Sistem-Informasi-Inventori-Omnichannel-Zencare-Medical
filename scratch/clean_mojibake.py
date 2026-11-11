with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

replacements = {
    'â„¹': '',
    'â€”': '—',
    'â–·': '>',
    'â•': '—',
}

for k, v in replacements.items():
    if k in text:
        text = text.replace(k, v)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Cleaned double encoded characters in stok_opname.php")

