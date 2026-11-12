with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

import re
pos = c.find('id="tab_h"')
if pos != -1:
    print(c[pos-50:pos+3500])

