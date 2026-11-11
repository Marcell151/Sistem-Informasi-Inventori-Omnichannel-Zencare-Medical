files = ['inventori/stok_opname.php', 'inventori/dokumentasi_testing.php', 'inventori/cetak_berita_acara_opname.php']
forbidden = ['dosen', 'pembimbing', 'bimbingan', 'bab 4', 'tugas akhir', 'farmasi', 'apotek']

total_found = 0
for fpath in files:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()
    print(f'Checking {fpath}...')
    for word in forbidden:
        c = text.lower().count(word)
        if c > 0:
            print(f'  FOUND {c}x "{word}" in {fpath}')
            total_found += c
    # Check emojis
    import re
    emojis = re.findall(r'[\U00010000-\U0010ffff\u2600-\u26ff\u2700-\u27bf]', text)
    # Exclude common symbols if any like bullet points or checkmarks if not emoji
    emojis = [e for e in emojis if ord(e) > 127]
    if emojis:
        print(f'  FOUND emojis: {set(emojis)}')
        total_found += len(emojis)

if total_found == 0:
    print('ALL CLEAN! 0 forbidden words, 0 emojis.')

