import re

forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

found = {}
for word in forbidden:
    matches = list(re.finditer(re.escape(word), text, re.IGNORECASE))
    if matches:
        found[word] = len(matches)
        print(f'Found "{word}": {len(matches)} times')
        for m in matches[:5]:
            start = max(0, m.start() - 50)
            end = min(len(text), m.end() + 50)
            snippet = text[start:end].replace('\n', ' ')
            print(f'   line ...{snippet}...')

if not found:
    print('ALL CLEAN! No forbidden words found.')

