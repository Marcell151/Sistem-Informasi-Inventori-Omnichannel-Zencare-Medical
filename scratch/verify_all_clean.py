import re
import os

files_to_check = [
    r'laporan_ta\BAB_I_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_II_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_V_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\LAPORAN_TA_ZENCARE_MEDICAL_FULL.html'
]

FORBIDDEN = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
emoji_pattern = re.compile(r'[\U00010000-\U0010ffff]', flags=re.UNICODE)

print("=== VERIFYING FORBIDDEN WORDS AND EMOJIS ===")
all_clean = True
for fpath in files_to_check:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    violations = []
    for word in FORBIDDEN:
        if word == 'Bab 4':
            m = re.findall(r'\bBab 4\b', text, re.IGNORECASE)
        elif word == 'HPP':
            m = re.findall(r'\bHPP\b', text)
        else:
            m = re.findall(re.escape(word), text, re.IGNORECASE)
        if m:
            violations.append((word, len(m)))

    emojis = emoji_pattern.findall(text)

    bname = os.path.basename(fpath)
    if violations or emojis:
        all_clean = False
        print(f"[FAIL] {bname}: Violations: {violations}, Emojis: {len(emojis)}")
    else:
        print(f"[PASS] {bname}: 0 forbidden words, 0 emojis.")

if all_clean:
    print("\nSUCCESS: All 7 files have ZERO forbidden words and ZERO emojis!")
else:
    print("\nFAIL: Some files still have forbidden words or emojis.")

