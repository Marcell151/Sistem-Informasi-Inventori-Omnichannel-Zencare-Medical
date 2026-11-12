import re

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_start = text.find('<tbody>', text.find('Tabel 4.2'))
idx_end = text.find('</tbody>', idx_start)
tbody_content = text[idx_start+7:idx_end]
parts = re.split(r'<!--\s*(Bagian\s+[IVX]+)\s*-->', tbody_content)

for i in range(1, len(parts), 2):
    part_num = parts[i].strip()
    part_body = parts[i+1]
    m_title = re.search(r'<td colspan="6"[^>]*>(.*?)</td>', part_body, re.DOTALL)
    title = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', m_title.group(1))).strip() if m_title else part_num
    
    all_tr = re.findall(r'<tr>(.*?)</tr>', part_body, re.DOTALL)
    scenario_rows = [tr for tr in all_tr if 'UAT' in tr]
    print(f"{part_num}: {len(scenario_rows)} rows | Title: {title}")

