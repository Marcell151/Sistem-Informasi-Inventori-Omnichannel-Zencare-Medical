import re

with open('scratch/dump_test.sql', 'r', encoding='utf-8', errors='ignore') as f:
    sql = f.read()

# Split by CREATE TABLE
parts = re.split(r'CREATE TABLE\s+(?:IF NOT EXISTS\s+)?', sql, flags=re.IGNORECASE)

print(f"Total CREATE TABLE blocks: {len(parts)-1}")

for p in parts[1:]:
    # get table name
    m_name = re.match(r'`?(\w+)`?', p.strip())
    if not m_name:
        continue
    tbl_name = m_name.group(1)
    
    # get body inside parentheses
    open_p = p.find('(')
    close_p = p.rfind(') ENGINE')
    if close_p == -1:
        close_p = p.rfind(')')
    body = p[open_p+1:close_p]
    
    # find all foreign keys
    fks = re.findall(r'CONSTRAINT\s+`?(\w+)`?\s+FOREIGN KEY\s+\(`?(\w+)`?\)\s+REFERENCES\s+`?(\w+)`?\s+\(`?(\w+)`?\)', body, re.IGNORECASE)
    
    # find all columns ending with _id or id_
    cols = re.findall(r'`?(\w+)`?\s+(?:int|varchar|bigint)', body, re.IGNORECASE)
    id_cols = [c for c in cols if (c.startswith('id_') or c.endswith('_id')) and c != 'id']
    
    fk_cols = [fk[1] for fk in fks]
    missing_fk = [c for c in id_cols if c not in fk_cols]
    
    print(f"\nTable: {tbl_name}")
    print(f"  Existing FKs ({len(fks)}):")
    for fk in fks:
        print(f"    {fk[1]} -> {fk[2]}({fk[3]}) [Constraint: {fk[0]}]")
    if missing_fk:
        print(f"  Potential missing FKs ({len(missing_fk)}): {missing_fk}")
