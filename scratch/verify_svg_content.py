with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8') as f:
    svg = f.read()

import re

for term in ['fisik_baik', 'fisik_rusak', 'fisik_expired', 'status_hitung', 'tahap', 'ditutup_hitung_oleh', 'ditutup_hitung_pada']:
    print(f"Term '{term}' in SVG: {term in svg}")

m_sesi = re.search(r'<text[^>]+>sesi_opname</text>\s*<text[^>]+>(.*?)</text>', svg)
if m_sesi:
    print("sesi_opname badge:", m_sesi.group(1))

m_dob = re.search(r'<text[^>]+>detail_opname_batch</text>\s*<text[^>]+>(.*?)</text>', svg)
if m_dob:
    print("detail_opname_batch badge:", m_dob.group(1))

