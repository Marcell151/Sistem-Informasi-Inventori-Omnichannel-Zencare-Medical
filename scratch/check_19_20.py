import re

for filename in ['lain/kamus_data_zencare.html', 'laporan_ta/BAB_III_ZENCARE_MEDICAL.html']:
    with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
        text = f.read()
    print(f"\nChecking 3.5.19 & 3.5.20 in {filename}:")
    for sec, tname in [('3.5.19', 'detail_opname_sn'), ('3.5.20', 'riwayat_hitung_opname')]:
        pos = text.find(tname)
        if pos != -1:
            pos_tbody = text.find('<tbody>', pos)
            pos_tbody_end = text.find('</tbody>', pos_tbody)
            tbody = text[pos_tbody:pos_tbody_end]
            rows = re.findall(r'<tr[^>]*>(.*?)</tr>', tbody, re.DOTALL)
            print(f"  {sec} {tname}: {len(rows)} rows")
            for idx, r in enumerate(rows):
                tds = re.findall(r'<td[^>]*>(.*?)</td>', r, re.DOTALL)
                if tds:
                    no = re.sub(r'<[^>]+>', '', tds[0]).strip()
                    fn = re.sub(r'<[^>]+>', '', tds[1]).strip()
                    print(f"     No={no:2} | {fn}")

