import re

with open('database/Demo_Bersih.sql', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

tables = re.findall(r'CREATE TABLE\s+[`"]?([a-zA-Z0-9_]+)[`"]?', text)
print("Tables in database/Demo_Bersih.sql:", tables)
