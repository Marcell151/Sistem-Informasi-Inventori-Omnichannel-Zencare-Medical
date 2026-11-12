import re

with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

m_sesi = re.search(r'(<!-- Table: sesi_opname -->.*?)</g>\s*<!--', text, re.DOTALL)
if m_sesi:
    print("=== SESI_OPNAME ===")
    print(m_sesi.group(1))

m_dob = re.search(r'(<!-- Table: detail_opname_batch -->.*?)</g>\s*<!--', text, re.DOTALL)
if m_dob:
    print("=== DETAIL_OPNAME_BATCH ===")
    print(m_dob.group(1))
