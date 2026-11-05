# -*- coding: utf-8 -*-
import re

with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

tabs = re.findall(r"switchTab\('([^']+)'\)", text)
print("Unique tabs in semua_activity_diagram_zencare.html:", list(dict.fromkeys(tabs)))

# Check images referenced
imgs = re.findall(r'src="([^"]+\.svg)"', text)
print("SVG images referenced in HTML:", list(dict.fromkeys(imgs)))

