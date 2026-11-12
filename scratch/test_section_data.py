import re

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_start = text.find('<tbody>', text.find('Tabel 4.2'))
idx_end = text.find('</tbody>', idx_start)
tbody_content = text[idx_start+7:idx_end]
parts = re.split(r'<!--\s*(Bagian\s+[IVX]+)\s*-->', tbody_content)

section_data = []
pat = re.compile(r'UAT-\d+')
for i in range(1, len(parts), 2):
    part_num = parts[i].strip()
    part_body = parts[i+1]
    m_title = re.search(r'<td colspan="6"[^>]*>(.*?)</td>', part_body, re.DOTALL)
    title = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', m_title.group(1))).strip() if m_title else part_num
    
    all_tr = re.findall(r'<tr>(.*?)</tr>', part_body, re.DOTALL)
    scenario_rows = ['<tr>' + tr + '</tr>' for tr in all_tr if 'UAT' in tr]
    
    section_data.append({
        'part_num': part_num,
        'title': title,
        'count': len(scenario_rows),
        'rows_html': '\n'.join(scenario_rows)
    })

for s in section_data:
    print(f"{s['part_num']}: {s['count']} rows | Title: {s['title'][:40]}...")

