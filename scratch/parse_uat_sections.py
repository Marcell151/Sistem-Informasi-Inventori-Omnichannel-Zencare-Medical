import re

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Extract rows between <tbody> and </tbody> in the UAT table
idx_start = text.find('<tbody>', text.find('Tabel 4.2'))
idx_end = text.find('</tbody>', idx_start)

tbody_content = text[idx_start+7:idx_end]

# Split by section headers
sections = re.split(r'<tr class="bg-slate-100 font-bold">\s*<td colspan="6"[^>]*>(.*?)</td>\s*</tr>', tbody_content)

print("Total elements in split:", len(sections))
# sections[0] is before first header (empty or whitespace)
# sections[1] is header 1, sections[2] is rows 1, etc.
for i in range(1, len(sections), 2):
    header = re.sub(r'<[^>]+>', '', sections[i]).strip()
    rows = re.findall(r'<tr>(.*?)</tr>', sections[i+1], re.DOTALL)
    print(f"Header: {header} -> {len(rows)} rows")

