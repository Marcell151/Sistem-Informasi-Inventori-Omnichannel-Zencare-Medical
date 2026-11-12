import re

with open('scratch/latest_user_prompt.txt', 'r', encoding='utf-8') as f:
    text = f.read()

with open('Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    sql_text = f.read()

# Extract tables from sql
sql_tables = {}
table_blocks = re.findall(r'CREATE TABLE `?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*ENGINE', sql_text, re.DOTALL)
for t_name, block in table_blocks:
    cols = []
    for line in block.split('\n'):
        line = line.strip()
        m = re.match(r'^`([a-zA-Z0-9_]+)`', line)
        if m:
            cols.append(m.group(1))
    sql_tables[t_name] = cols

# Extract tables from user text
user_tables = {}
table_chunks = re.split(r'3\.5\.\d+\s+Tabel\s+', text)[1:]
for chunk in table_chunks:
    lines = chunk.strip().split('\n')
    t_name = lines[0].strip()
    cols = []
    for line in lines:
        m = re.match(r'^\d+\t([a-zA-Z0-9_]+)(?:\s*\([^)]*\))?\t', line.strip())
        if m:
            cols.append(m.group(1))
    user_tables[t_name] = cols

print("Comparing user tables vs sql_tables:")
for t in user_tables:
    u_cols = user_tables[t]
    s_cols = sql_tables.get(t, [])
    if u_cols == s_cols:
        print(f"OK: {t} ({len(u_cols)} cols match 100%)")
    else:
        print(f"DIFF: {t}")
        print("  User cols:", u_cols)
        print("  SQL cols :", s_cols)

