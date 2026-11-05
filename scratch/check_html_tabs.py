# -*- coding: utf-8 -*-
with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.findall(r'<button[^>]*class="tab-btn[^"]*"[^>]*onclick="([^"]+)"[^>]*>([\s\S]*?)</button>', text)
for onclick, title in matches:
    print(onclick, '-->', title.strip())

