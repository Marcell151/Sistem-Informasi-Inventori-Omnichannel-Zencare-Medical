import re

with open('c:/xampp/htdocs/inventory_zencare/Demo_Bersih.sql', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

m = re.findall(r'INSERT INTO `users`\s*(?:\([^)]+\)\s*)?VALUES\s*(.*?);', text, re.DOTALL | re.IGNORECASE)
for ins in m:
    for row in ins.split('),('):
        print(row[:140])
