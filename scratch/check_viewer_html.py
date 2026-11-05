# -*- coding: utf-8 -*-
with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
farmasi_count = len(re.findall(r'farmasi', text, re.IGNORECASE))
print("Farmasi count in semua_activity_diagram_zencare.html:", farmasi_count)

# Check all images linked in HTML
imgs = re.findall(r'src="([^"]+)"', text)
print("Images linked:", imgs)

