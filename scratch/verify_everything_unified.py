import re
import os

files_to_check = [
    r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html',
    r'lain\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html',
    r'laporan_ta\katalog_download_ui_ux_zencare.html',
    r'lain\katalog_download_ui_ux_zencare.html'
]

forbidden_patterns = [
    r'\bfarmasi\b',
    r'\bapotek\b',
    r'\bHPP\b',
    r'\bAkuntansi\b',
    r'\bAudit\b',
    r'\bDosen\b',
    r'\bBab 4\b',
    r'\bSidang\b',
    r'\bSempurna\b'
]

emoji_pattern = re.compile(r'[\U00010000-\U0010ffff]', flags=re.UNICODE)

for fpath in files_to_check:
    print(f"\n==========================================")
    print(f"VERIFYING: {fpath}")
    print(f"==========================================")
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    # 1. Check forbidden words
    has_violation = False
    for pat in forbidden_patterns:
        matches = re.findall(pat, text, flags=re.IGNORECASE)
        if matches:
            print(f"[VIOLATION] Found forbidden '{pat}' in {fpath}: count {len(matches)}")
            has_violation = True
            
    # 2. Check emojis
    emojis = emoji_pattern.findall(text)
    if emojis:
        print(f"[VIOLATION] Found emojis in {fpath}: count {len(emojis)}")
        has_violation = True

    if not has_violation:
        print("[SUCCESS] Zero forbidden words and zero emojis!")

    if 'BAB_' in fpath:
        # 3. Check Table numbering in Bab IV
        tbls = sorted(list(set(re.findall(r'Tabel 4\.(\d+)', text))))
        tbl_nums = [int(x) for x in tbls]
        print(f"Tables in Bab IV: {sorted(tbl_nums)}")
        expected_tbls = list(range(1, 11))
        if sorted(tbl_nums) == expected_tbls:
            print(f"[SUCCESS] Tables 4.1 through 4.10 are perfectly continuous!")
        else:
            print(f"[ERROR] Table mismatch! Found {sorted(tbl_nums)}, expected {expected_tbls}")

        # 4. Check Figure numbering in Bab IV
        figs = sorted(list(set(int(x) for x in re.findall(r'Gambar 4\.(\d+)', text))))
        print(f"Figures in Bab IV: min {min(figs)}, max {max(figs)}, total distinct {len(figs)}")
        expected_figs = list(range(1, 51))
        if figs == expected_figs:
            print(f"[SUCCESS] Figures 4.1 through 4.50 are perfectly continuous (No figures in UAT 4.2)!")
        else:
            print(f"[ERROR] Figure mismatch! Expected 1..50, got: {figs}")

        # 5. Check calculation mentions
        assert '93.75%' in text, "Percentage 93.75% missing!"
        assert '300 / 320' in text, "Score 300 / 320 missing!"
        print("[SUCCESS] Math 300/320 and 93.75% verified in Bab IV!")

        # 6. Check Paper Layer in BAB_IV_ZENCARE_MEDICAL.html
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
                    if depth == 0:
                        break
                else:
                    depth += 1
            
            print(f"[PAPER CHECK] Subbab 4.2 inside paper layer: {subbab_42_inside}")
            print(f"[PAPER CHECK] Depth at end of paper: {depth}")
            assert subbab_42_inside and depth == 0
