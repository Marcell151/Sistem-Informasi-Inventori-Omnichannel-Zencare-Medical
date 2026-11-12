import re
import os

files_to_check = [
    r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
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
    expected_figs = list(range(1, 53))
    if figs == expected_figs:
        print(f"[SUCCESS] Figures 4.1 through 4.52 are perfectly continuous!")
    else:
        print(f"[ERROR] Figure mismatch! Missing: {set(expected_figs) - set(figs)}")

    # 5. Check UAT status counts
    berhasil_cnt = text.count('text-center font-bold text-emerald-700">BERHASIL</td>')
    diterima_cnt = text.count('text-center font-bold text-emerald-700">DITERIMA</td>')
    print(f"UAT Status count: BERHASIL = {berhasil_cnt}, DITERIMA = {diterima_cnt}")
    assert berhasil_cnt == 36, f"Expected 36 BERHASIL, got {berhasil_cnt}"
    assert diterima_cnt == 0, f"Expected 0 DITERIMA, got {diterima_cnt}"
    print("[SUCCESS] All 36 scenarios have status BERHASIL!")

    # 6. Check sections A through H headings
    sec_headings = re.findall(r'<h5[^>]*>([A-I]\..*?)</h5>', text)
    print("Section headings found:")
    for sh in sec_headings:
        clean_sh = re.sub(r'<[^>]+>', '', sh).strip()
        print(f"  - {clean_sh}")

