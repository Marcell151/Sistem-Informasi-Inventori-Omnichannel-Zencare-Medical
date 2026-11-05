# -*- coding: utf-8 -*-
import sys

with open('config/layout.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re
idx = text.find('sidebar-section')
while idx != -1:
    section_end = text.find('</p>', idx)
    section_name = text[idx:section_end+4]
    # Clean non-ascii
    cleaned_name = ''.join(c if ord(c) < 128 else ' ' for c in section_name)
    sys.stdout.write("SECTION: " + cleaned_name + "\n")
    next_idx = text.find('sidebar-section', section_end)
    chunk = text[section_end:next_idx] if next_idx != -1 else text[section_end:section_end+1500]
    links = re.findall(r"\$link\('([^']+)',\s*'([^']+)',\s*'([^']+)'", chunk)
    for l in links:
        cleaned_title = ''.join(c if ord(c) < 128 else ' ' for c in l[2])
        sys.stdout.write("  -> " + l[0] + " | " + cleaned_title + "\n")
    idx = next_idx
