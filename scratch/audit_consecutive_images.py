import re

files = [
    'laporan_ta/BAB_I_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_II_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_V_ZENCARE_MEDICAL.html'
]

for fn in files:
    with open(fn, 'r', encoding='utf-8') as f:
        html = f.read()

    print(f"\n=== {fn} ===")
    tokens = re.findall(r'(<h[345][^>]*>.*?</h[345]>|<img[^>]+>|<div class="mermaid[^>]*>.*?</div>|<p[^>]*>.*?</p>)', html, re.DOTALL)
    cur_h = '(Top)'
    cur_imgs = []
    for t in tokens:
        if t.startswith('<h'):
            if len(cur_imgs) > 1:
                print(f"  [{len(cur_imgs)} gambar berturut-turut] di '{cur_h}': {cur_imgs}")
            cur_imgs = []
            cur_h = re.sub(r'<[^>]+>', '', t).strip()
            cur_h = re.sub(r'\s+', ' ', cur_h)
        elif t.startswith('<img'):
            m_alt = re.search(r'alt="([^"]+)"', t)
            lbl = m_alt.group(1) if m_alt else t[:40]
            cur_imgs.append(lbl)
        elif t.startswith('<div class="mermaid'):
            cur_imgs.append('Mermaid Diagram')
        elif t.startswith('<p'):
            txt = re.sub(r'<[^>]+>', '', t).strip()
            txt = re.sub(r'\s+', ' ', txt)
            if not txt.startswith('Gambar ') and not txt.startswith('Tabel ') and len(txt) > 40:
                if len(cur_imgs) > 1:
                    print(f"  [{len(cur_imgs)} gambar berturut-turut] di '{cur_h}': {cur_imgs}")
                cur_imgs = []
    if len(cur_imgs) > 1:
        print(f"  [{len(cur_imgs)} gambar berturut-turut] di '{cur_h}': {cur_imgs}")
