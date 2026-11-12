import re

for fpath in ['laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    print(f'\n=== CHECKING {fpath} ===')
    # 1. Figures in Bab IV
    figs = sorted(list(set(int(x) for x in re.findall(r'Gambar 4\.(\d+)', text))))
    print(f'Figures in Bab IV: min {min(figs) if figs else 0}, max {max(figs) if figs else 0}, total {len(figs)}')
    assert figs == list(range(1, 51)), f'Expected 1..50, got {figs}'
    print('SUCCESS: Figures 4.1 to 4.50 are continuous, no 4.51 or 4.52!')

    # 2. Tables in Bab IV
    tbls = sorted(list(set(int(x) for x in re.findall(r'Tabel 4\.(\d+)', text))))
    print(f'Tables in Bab IV: {tbls}')
    assert tbls == list(range(1, 11)), f'Expected 1..10, got {tbls}'
    print('SUCCESS: Tables 4.1 to 4.10 are continuous!')

    # 3. Paper check
    if 'BAB_IV_' in fpath:
        idx_start = text.find('id="full_bab_iv_content"')
        pos = text.find('>', idx_start) + 1
        depth = 1
        tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
        subbab_42_inside = False
        idx_42 = text.find('id="subbab_42"')
        while pos < len(text):
            m = tag_re.search(text, pos)
            if not m: break
            tag = m.group(0)
            pos = m.end()
            if pos > idx_42 and depth > 0:
                subbab_42_inside = True
            if tag.startswith('</'):
                depth -= 1
                if depth == 0: break
            else:
                depth += 1
        print(f'Subbab 4.2 inside paper layer: {subbab_42_inside}, depth at end: {depth}')
        assert subbab_42_inside and depth == 0

    # 4. Check forbidden words
    forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
    for pat in forbidden:
        m = re.findall(rf'\b{pat}\b', text, re.IGNORECASE)
        assert len(m) == 0, f'Found forbidden word {pat} ({len(m)})'
    print('SUCCESS: Zero forbidden words!')

