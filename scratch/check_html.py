# -*- coding: utf-8 -*-
with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('id="tab_c"')
print("Found id=tab_c at:", idx)
print(text[idx:idx+600])

