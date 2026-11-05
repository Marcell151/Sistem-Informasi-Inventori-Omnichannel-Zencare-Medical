# -*- coding: utf-8 -*-
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
start = text.find('id="sec_3_3_4"')
end = text.find('id="sec_3_3_5"', start) if text.find('id="sec_3_3_5"', start) != -1 else text.find('<!-- 3.3.5', start)
if end == -1:
    end = text.find('<!-- 3.4', start)
if end == -1:
    end = start + 15000

print(f"Start: {start}, End: {end}, Length: {end - start}")
content = text[start:end]

# Print headings inside 3.3.4
for h in re.findall(r'<h[456][^>]*>([\s\S]*?)</h[456]>', content):
    print("Heading:", h.strip())

# Check farmasi count
print("'farmasi' in sec_3_3_4:", len(re.findall(r'farmasi', content, re.IGNORECASE)))

