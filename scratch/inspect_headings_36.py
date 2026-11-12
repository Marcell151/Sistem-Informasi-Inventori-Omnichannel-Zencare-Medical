with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
h_tags = re.findall(r'<h[4-6][^>]*>(.*?)</h[4-6]>', text)
for h in h_tags:
    if any(k in h for k in ['Wireframe', '3.6', 'Login', 'Kasir', 'Admin', 'Superadmin', 'Pelanggan']):
        print(h.strip())

