# -*- coding: utf-8 -*-
with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx_g = text.find('id="tab_g"')
print(text[idx_g:idx_g+2500])

