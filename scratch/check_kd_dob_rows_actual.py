with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('id="detail_opname_batch"')
pos_tbody = text.find('<tbody>', pos)
pos_tbody_end = text.find('</tbody>', pos_tbody)
tbody_html = text[pos_tbody:pos_tbody_end]

import re
rows = re.findall(r'<tr[^>]*>(.*?)</tr>', tbody_html, re.DOTALL)
print(f"Rows in detail_opname_batch: {len(rows)}")
for idx, r in enumerate(rows):
    tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.DOTALL)
    if tds:
        no = re.sub(r'<[^>]+>', '', tds[0]).strip()
        fn = re.sub(r'<[^>]+>', '', tds[1]).strip()
        print(f"  {idx+1}: No={no:3} | {fn:25}")

