import re

forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
files = [
    'laporan_ta/BAB_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    'laporan_ta/katalog_download_ui_ux_zencare.html',
    'lain/katalog_download_ui_ux_zencare.html'
]

for fname in files:
    with open(fname, 'r', encoding='utf-8', errors='ignore') as f:
        text = f.read()

    found = []
    for word in forbidden:
        if word == 'Bab 4':
            matches = list(re.finditer(r'\bbab\s+4\b', text, re.IGNORECASE))
        else:
            matches = list(re.finditer(r'\b' + re.escape(word) + r'\b', text, re.IGNORECASE))
        if matches:
            found.append((word, len(matches)))

    if found:
        print(f"[{fname}] FAILED! Found:", found)
    else:
        print(f"[{fname}] 100% CLEAN! Zero forbidden words.")

