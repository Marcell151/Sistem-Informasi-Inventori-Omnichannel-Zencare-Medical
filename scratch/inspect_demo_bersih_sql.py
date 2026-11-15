import re

with open('Demo_Bersih.sql', 'r', encoding='utf-8', errors='ignore') as f:
    sql = f.read()

# Find all CREATE TABLE statements
tables = re.findall(r'CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?(\w+)`?', sql, re.IGNORECASE)
print(f"Total tables found in Demo_Bersih.sql: {len(tables)}")
print("Table names:", tables)

# Check ENGINE for each table
for t in tables:
    # find create table block
    pat = rf'CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?{t}`?\s*\((.*?)\)\s*ENGINE\s*=\s*(\w+)'
    m = re.search(pat, sql, re.DOTALL | re.IGNORECASE)
    if m:
        engine = m.group(2)
        body = m.group(1)
        fks = re.findall(r'CONSTRAINT\s+`?(\w+)`?\s+FOREIGN KEY', body, re.IGNORECASE)
        print(f"  - {t}: Engine={engine}, FKs={len(fks)}")
    else:
        # maybe ENGINE is slightly different format
        pat2 = rf'CREATE TABLE\s+(?:IF NOT EXISTS\s+)?`?{t}`?\s*\((.*?)\)([^;]+);'
        m2 = re.search(pat2, sql, re.DOTALL | re.IGNORECASE)
        if m2:
            tail = m2.group(2)
            m_eng = re.search(r'ENGINE\s*=\s*(\w+)', tail, re.IGNORECASE)
            eng = m_eng.group(1) if m_eng else 'UNKNOWN'
            body = m2.group(1)
            fks = re.findall(r'CONSTRAINT\s+`?(\w+)`?\s+FOREIGN KEY', body, re.IGNORECASE)
            print(f"  - {t}: Engine={eng}, FKs={len(fks)}")
        else:
            print(f"  - {t}: Could not parse definition")

