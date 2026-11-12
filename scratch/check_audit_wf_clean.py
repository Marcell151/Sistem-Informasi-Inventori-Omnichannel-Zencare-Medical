import re

for filename in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'lain/wireframe_zencare.html']:
    with open(filename, 'r', encoding='utf-8', errors='ignore') as f:
        c = f.read()
    
    pos = c.find('3.6 Wireframe') if '3.6 Wireframe' in c else 0
    pos_end = c.find('BAB IV', pos) if 'BAB IV' in c else len(c)
    block = c[pos:pos_end]

    forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Sidang', 'Sempurna']
    found = []
    for w in forbidden:
        m = re.findall(r'\b' + re.escape(w) + r'\b', block, re.IGNORECASE)
        if m:
            found.append(f"{w} ({len(m)})")
    print(f"{filename} in Subbab 3.6: forbidden words -> {found if found else 'NONE (Clean!)'}")

