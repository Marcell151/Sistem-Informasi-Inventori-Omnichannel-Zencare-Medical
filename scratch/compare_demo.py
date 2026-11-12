import re

with open('Demo_Bersih.sql', 'r', encoding='utf-8', errors='ignore') as f:
    demo_clean = f.read()

tables_demo = re.findall(r'CREATE TABLE\s+[`"]?([a-zA-Z0-9_]+)[`"]?', demo_clean)
print(f"Demo_Bersih.sql total tables: {len(tables_demo)}")
print("Tables in Demo_Bersih.sql:", tables_demo)

# Check active sessions in Demo_Bersih.sql
m_sesi = re.search(r'INSERT INTO `sesi_opname` VALUES (.*?);', demo_clean)
if m_sesi:
    print("\nsesi_opname rows:")
    print(m_sesi.group(1))

# Check users table in Demo_Bersih.sql
m_users = re.search(r'INSERT INTO `users` VALUES (.*?);', demo_clean)
if m_users:
    print("\nusers rows:")
    print(m_users.group(1)[:400])

