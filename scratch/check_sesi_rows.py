with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

pos = text.find('3.5.17 Tabel sesi_opname')
pos_end = text.find('3.5.18 Tabel detail_opname_batch')
block = text[pos:pos_end]

rows = re.findall(r'<tr>(.*?)</tr>', block, re.DOTALL)
print(f"Total rows in 3.5.17: {len(rows)}")
for idx, r in enumerate(rows):
    tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.DOTALL)
    if tds:
        no = re.sub(r'<[^>]+>', '', tds[0]).strip()
        fn = re.sub(r'<[^>]+>', '', tds[1]).strip()
        ft = re.sub(r'<[^>]+>', '', tds[2]).strip()
        pj = re.sub(r'<[^>]+>', '', tds[3]).strip()
        ket = re.sub(r'<[^>]+>', '', tds[4]).strip()[:40]
        print(f"{idx}: No={no:3} | {fn:25} | {ft:10} | {pj:5} | {ket}")

