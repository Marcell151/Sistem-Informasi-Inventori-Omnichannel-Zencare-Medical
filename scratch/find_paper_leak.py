with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
# Find all occurrences of "paper"
m = list(re.finditer(r'<div[^>]*class="[^"]*paper[^"]*"[^>]*>', text))
print("Paper occurrences:", len(m))
for match in m:
    print(match.group(0), "at index", match.start())

# Let's inspect around index 10414
idx = text.find('id="full_bab_iv_content"')
print(text[idx-50:idx+600])

