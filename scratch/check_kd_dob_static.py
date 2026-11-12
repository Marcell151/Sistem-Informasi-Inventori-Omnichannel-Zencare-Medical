with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('id="detail_opname_batch"')
pos_table = text.find('<table>', pos)
pos_table_end = text.find('</table>', pos_table)
block = text[pos_table:pos_table_end]

import re
rows = re.findall(r'<tr>(.*?)</tr>', block, re.DOTALL)
print(f"Total rows in detail_opname_batch static table in kamus_data_zencare.html: {len(rows)}")
for idx, r in enumerate(rows):
    tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.DOTALL)
    if tds:
        no = re.sub(r'<[^>]+>', '', tds[0]).strip()
        fn = re.sub(r'<[^>]+>', '', tds[1]).strip()
        print(f"  {idx}: No={no:3} | {fn:25}")

