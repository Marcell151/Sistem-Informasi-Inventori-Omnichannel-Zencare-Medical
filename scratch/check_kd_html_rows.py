with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

for t in ['sesi_opname', 'detail_opname_batch']:
    pos = text.find(f'id="{t}"')
    if pos != -1:
        pos_end = text.find('<div class="table-card"', pos + 10)
        if pos_end == -1: pos_end = pos + 4000
        block = text[pos:pos_end]
        rows = re.findall(r'<tr>(.*?)</tr>', block, re.DOTALL)
        print(f"\n=== kamus_data_zencare.html: {t} (rows: {len(rows)}) ===")
        for idx, r in enumerate(rows):
            tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.DOTALL)
            if tds:
                no = re.sub(r'<[^>]+>', '', tds[0]).strip()
                fn = re.sub(r'<[^>]+>', '', tds[1]).strip()
                ft = re.sub(r'<[^>]+>', '', tds[2]).strip()
                print(f"  {idx}: No={no:3} | {fn:25} | {ft:10}")

