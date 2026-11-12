with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

# find tables 4.2 to 4.9
for t in range(2, 10):
    m = re.search(rf'Tabel 4\.{t}[^<]*', text)
    if m:
        start = m.start()
        tbl_text = text[start:start+4000]
        # find rows
        rows = re.findall(r'<tr[^>]*>.*?</tr>', tbl_text, re.DOTALL)
        print(f"=== Tabel 4.{t} ({m.group(0).strip()}) ===")
        print(f"Total rows: {len(rows)}")
        if len(rows) > 1:
            print("Row 1 cells:")
            tds = re.findall(r'<td[^>]*>(.*?)</td>', rows[1], re.DOTALL)
            for c in tds:
                print("  ", re.sub(r'<[^>]+>', '', c).strip())

