with open(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
idx_start = text.find('<tbody>')
idx_end = text.find('</tbody>', idx_start)
tbody_content = text[idx_start+7:idx_end]

parts = re.split(r'<tr class="bg-slate-100 font-bold">\s*<td colspan="6"[^>]*>(.*?)</td>\s*</tr>', tbody_content)
print("Parts count:", len(parts))
for i in range(1, len(parts), 2):
    title = re.sub(r'<[^>]+>', '', parts[i]).strip()
    rows = re.findall(r'<tr[^>]*>(.*?)</tr>', parts[i+1], re.DOTALL)
    uat_codes = re.findall(r'UAT-\d+', parts[i+1])
    print(f"Section {i//2 + 1}: {title} | Scenarios: {len(uat_codes)} ({uat_codes})")

