with open('scratch/pos_output.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
matches = re.findall(r'(Warning|Notice|Fatal error|Parse error):[^\n<]+', text, re.IGNORECASE)
print('Total PHP errors/warnings in fetched page:', len(matches))
for m in matches:
    print('  -', m)

print('Has toggleBatchTerlewatPanel:', 'toggleBatchTerlewatPanel' in text)
print('Has openKasirAnomaliModal:', 'openKasirAnomaliModal' in text)
print('Has modalTableRows:', 'modalTableRows' in text)
print('Has panel_batch_terlewat:', 'id="panel_batch_terlewat"' in text)
print('Has drawer_batch_content:', 'id="drawer_batch_content"' in text)

