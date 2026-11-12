with open('laporan_ta/DOKUMENTASI_TESTING_STOK_OPNAME.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re
forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']

for w in forbidden:
    matches = re.finditer(re.escape(w), text, re.IGNORECASE)
    for m in matches:
        start = max(0, m.start() - 40)
        end = min(len(text), m.end() + 40)
        print(f"[{w}] at {m.start()}: ...{text[start:end]}...")

