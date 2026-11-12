import re

def inspect_file(filename):
    print(f"\n==================== {filename} ====================")
    with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
        text = f.read()
    
    # Check sesi_opname table
    m_sesi = re.search(r'(<table[^>]*>.*?sesi_opname.*?</table>)', text, re.DOTALL | re.IGNORECASE)
    if not m_sesi:
        # maybe header is before table
        m_sesi = re.search(r'(Tabel 3\.17.*?<table[^>]*>.*?</table>)', text, re.DOTALL | re.IGNORECASE)
    if m_sesi:
        table_html = m_sesi.group(0)
        rows = re.findall(r'<tr[^>]*>(.*?)</tr>', table_html, re.DOTALL)
        print(f"sesi_opname table found! Total rows: {len(rows)}")
        # print field names
        fields = re.findall(r'<td[^>]*>(?:<code[^>]*>)?([a-zA-Z0-9_]+)', table_html)
        print("Fields in sesi_opname:", fields[:15])
    else:
        print("sesi_opname table not found via regex!")
        
    # Check detail_opname_batch table
    m_dob = re.search(r'(<table[^>]*>.*?detail_opname_batch.*?</table>)', text, re.DOTALL | re.IGNORECASE)
    if not m_dob:
        m_dob = re.search(r'(Tabel 3\.18.*?<table[^>]*>.*?</table>)', text, re.DOTALL | re.IGNORECASE)
    if m_dob:
        table_html = m_dob.group(0)
        rows = re.findall(r'<tr[^>]*>(.*?)</tr>', table_html, re.DOTALL)
        print(f"detail_opname_batch table found! Total rows: {len(rows)}")
        fields = re.findall(r'<td[^>]*>(?:<code[^>]*>)?([a-zA-Z0-9_]+)', table_html)
        print("Fields in detail_opname_batch:", fields[:28])
    else:
        print("detail_opname_batch table not found via regex!")

inspect_file('lain/kamus_data_zencare.html')
inspect_file('laporan_ta/BAB_III_ZENCARE_MEDICAL.html')
inspect_file('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html')
