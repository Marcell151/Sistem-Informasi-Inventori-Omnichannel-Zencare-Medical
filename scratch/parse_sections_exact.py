import re

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_start = text.find('<tbody>', text.find('Tabel 4.2'))
idx_end = text.find('</tbody>', idx_start)

tbody_content = text[idx_start+7:idx_end]

# Split by comment <!-- Bagian ... -->
parts = re.split(r'<!--\s*(Bagian\s+[IVX]+)\s*-->', tbody_content)
print("Parts length:", len(parts))

parsed_sections = []
for i in range(1, len(parts), 2):
    part_num = parts[i].strip()
    part_body = parts[i+1]
    
    # Extract section title from <td colspan="6"...>
    m_title = re.search(r'<td colspan="6"[^>]*>(.*?)</td>', part_body, re.DOTALL)
    title = m_title.group(1).strip() if m_title else part_num
    
    # Extract rows (excluding the header row)
    all_tr = re.findall(r'<tr>(.*?)</tr>', part_body, re.DOTALL)
    # Filter rows that have UAT-
    scenario_rows = [tr for tr in all_tr if 'UAT-' in tr]
    
    parsed_sections.append({
        'part': part_num,
        'title': title,
        'rows': scenario_rows
    })

print(f"Successfully parsed {len(parsed_sections)} sections:")
for s in parsed_sections:
    print(f"- {s['part']}: {len(s['rows'])} scenarios -> {s['title'][:50]}...")

