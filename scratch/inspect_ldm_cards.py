with open('lain/logical_database_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

import re
matches = re.findall(r'(<div[^>]*class="[^"]*table-card[^"]*"[^>]*>[\s\S]*?</div>\s*</div>)', c)
print(f"Total table cards found: {len(matches)}")

# Let's search for sesi_opname card
pos_sesi = c.find('sesi_opname')
while pos_sesi != -1:
    print("Found sesi_opname at:", pos_sesi, "context:", c[pos_sesi-30:pos_sesi+80].replace('\n', ' '))
    pos_sesi = c.find('sesi_opname', pos_sesi + 1)

