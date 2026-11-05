with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
matches = re.finditer(r'<p class="font-bold mb-2">([0-9]\.\s*Sitemap[^<]+)</p>', text)
for m in matches:
    print(m.group(1))
    idx = m.start()
    # find mermaid graph under it
    m_mermaid = re.search(r'graph LR(.*?)</div>', text[idx:idx+2500], re.DOTALL)
    if m_mermaid:
        print("  Mermaid items:", [line.strip() for line in m_mermaid.group(1).splitlines() if '-->' in line][:8])
