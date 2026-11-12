import re

files = [
    'lain/kamus_data_zencare.html',
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
]

for filename in files:
    print(f"\n==================== {filename} ====================")
    with open(filename, 'r', encoding='utf-8') as f:
        c = f.read()
    
    # Check sesi_opname
    pos_s = c.find('sesi_opname')
    pos_tb_s = c.find('<tbody>', pos_s)
    pos_tbe_s = c.find('</tbody>', pos_tb_s)
    rows_s = re.findall(r'<tr[^>]*>(.*?)</tr>', c[pos_tb_s:pos_tbe_s], re.DOTALL)
    print(f"sesi_opname rows: {len(rows_s)}")
    nos_s = []
    fns_s = []
    for r in rows_s:
        tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.DOTALL)
        nos_s.append(re.sub(r'<[^>]+>', '', tds[0]).strip())
        fns_s.append(re.sub(r'<[^>]+>', '', tds[1]).strip().split()[0])
    print("  Numbers:", nos_s)
    print("  Fields :", fns_s)
    
    # Check detail_opname_batch
    pos_d = c.find('detail_opname_batch')
    # make sure not jump pill
    if 'jump-pill' in c[pos_d-50:pos_d]:
        pos_d = c.find('detail_opname_batch', pos_d + 50)
    pos_tb_d = c.find('<tbody>', pos_d)
    pos_tbe_d = c.find('</tbody>', pos_tb_d)
    rows_d = re.findall(r'<tr[^>]*>(.*?)</tr>', c[pos_tb_d:pos_tbe_d], re.DOTALL)
    print(f"detail_opname_batch rows: {len(rows_d)}")
    nos_d = []
    fns_d = []
    for r in rows_d:
        tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.DOTALL)
        nos_d.append(re.sub(r'<[^>]+>', '', tds[0]).strip())
        fns_d.append(re.sub(r'<[^>]+>', '', tds[1]).strip().split()[0])
    print("  Numbers:", nos_d)
    print("  Fields :", fns_d)

