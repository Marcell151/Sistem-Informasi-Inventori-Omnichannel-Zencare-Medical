import re

forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
for fname in ['laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(fname, 'r', encoding='utf-8', errors='ignore') as f:
        text = f.read()

    found = {}
    for word in forbidden:
        if word == 'Bab 4':
            matches = list(re.finditer(r'\bbab\s+4\b', text, re.IGNORECASE))
        else:
            matches = list(re.finditer(r'\b' + re.escape(word) + r'\b', text, re.IGNORECASE))
        if matches:
            found[word] = len(matches)
            print(f'[{fname}] Found "{word}": {len(matches)} times')

    if not found:
        print(f'[{fname}] ALL CLEAN! No forbidden words found.')
