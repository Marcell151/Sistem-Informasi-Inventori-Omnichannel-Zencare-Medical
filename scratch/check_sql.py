import re

with open('Demo_Bersih.sql', 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

def find_table(name):
    pattern = rf'CREATE TABLE\s+[`"]?{name}[`"]?\s*\((.*?)\)\s*ENGINE'
    m = re.search(pattern, content, re.DOTALL | re.IGNORECASE)
    if m:
        print(f"=== TABLE {name} ===")
        print(m.group(1).strip()[:1500])
    else:
        print(f"=== TABLE {name} NOT FOUND ===")

find_table('sesi_opname')
find_table('detail_opname_batch')
find_table('detail_opname_sn')
find_table('riwayat_hitung_opname')

# Check records in sesi_opname
m_insert = re.findall(rf'INSERT INTO\s+[`"]?sesi_opname[`"]?.*?;', content, re.DOTALL | re.IGNORECASE)
print(f"\nTotal INSERTs for sesi_opname: {len(m_insert)}")
for ins in m_insert[:3]:
    print(ins[:300])

# Check records in detail_opname_batch
m_insert_batch = re.findall(rf'INSERT INTO\s+[`"]?detail_opname_batch[`"]?.*?;', content, re.DOTALL | re.IGNORECASE)
print(f"\nTotal INSERTs for detail_opname_batch: {len(m_insert_batch)}")
for ins in m_insert_batch[:3]:
    print(ins[:300])

