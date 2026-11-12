import re

forbidden_words = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
emoji_pattern = re.compile(r'[\U00010000-\U0010ffff]', flags=re.UNICODE)

files_to_check = [
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    'lain/semua_sitemap_zencare.html',
    'scratch/sitemap_lr.html',
    'lain/semua_usecase_zencare.html',
    'lain/semua_activity_diagram_zencare.html',
    'lain/logical_database_zencare.html',
    'lain/kamus_data_zencare.html'
]

has_error = False

for path in files_to_check:
    with open(path, 'r', encoding='utf-8') as f:
        text = f.read()
    
    # check emojis
    emojis = emoji_pattern.findall(text)
    if emojis:
        print(f"[!] EMOJI found in {path}: {set(emojis)}")
        has_error = True

    # check forbidden words (case insensitive, except words where case matters)
    for fw in forbidden_words:
        # For 'Bab 4', in Bab 3 alone it shouldn't exist; in BAB_III_DAN_IV it's chapter 4, but let's check
        if path == 'laporan_ta/BAB_III_ZENCARE_MEDICAL.html' and fw == 'Bab 4':
            matches = re.findall(rf'\b{re.escape(fw)}\b', text, re.IGNORECASE)
            if matches:
                print(f"[!] FORBIDDEN WORD '{fw}' in {path}: {len(matches)} occurrences")
                has_error = True
        elif fw in ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Sidang', 'Sempurna']:
            matches = re.findall(rf'\b{re.escape(fw)}\b', text, re.IGNORECASE)
            if matches:
                print(f"[!] FORBIDDEN WORD '{fw}' in {path}: {len(matches)} occurrences -> {matches[:3]}")
                has_error = True

if not has_error:
    print("ALL FILES ARE 100% CLEAN OF FORBIDDEN WORDS AND EMOJIS!")

