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

print("=== CHECKING FORBIDDEN WORDS AND EMOJIS ===")
has_violation = False
for file_path in files_to_check:
    if not os.path.exists(file_path):
        print(f"File NOT FOUND: {file_path}")
        continue
    with open(file_path, 'r', encoding='utf-8') as f:
        text = f.read()
    
    # Check forbidden words
    for pat in forbidden_patterns:
        matches = re.findall(pat, text, flags=re.IGNORECASE)
        # Note: check if exact match matters or if case matters. The requirement says:
        # Strictly NEVER use: farmasi, apotek, HPP, Akuntansi, Audit, Dosen, Bab 4 (use Bab IV), Sidang, Sempurna.
        if matches:
            print(f"[VIOLATION] Found forbidden '{pat}' in {file_path}: count {len(matches)}")
            has_violation = True
            
    # Check emojis
    emojis = emoji_pattern.findall(text)
    if emojis:
        print(f"[VIOLATION] Found emojis {emojis[:5]} in {file_path}: count {len(emojis)}")
        has_violation = True

if not has_violation:
    print("[SUCCESS] Zero forbidden words and zero emojis across all checked HTML files!")

print("\n=== CHECKING FIGURES 4.1 to 4.52 IN BAB_IV_ZENCARE_MEDICAL.html ===")
with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    bab4_content = f.read()

figs = re.findall(r'Gambar 4\.(\d+)', bab4_content)
fig_nums = sorted(list(set(int(x) for x in figs)))
print(f"Total distinct figure references found: {len(fig_nums)}")
print(f"Range: min {min(fig_nums)}, max {max(fig_nums)}")
expected = list(range(1, 53))
if fig_nums == expected:
    print("[SUCCESS] All figures Gambar 4.1 through Gambar 4.52 are present and continuous!")
else:
    missing = set(expected) - set(fig_nums)
    extra = set(fig_nums) - set(expected)
    if missing:
        print(f"[ERROR] Missing figures: {missing}")
    if extra:
        print(f"[ERROR] Extra figures: {extra}")

print("\n=== CHECKING PNG FILES IN lain/File PPT/UI_UX_Asli/ ===")
img_dir = r'lain\File PPT\UI_UX_Asli'
files = os.listdir(img_dir)
png_files = [f for f in files if f.endswith('.png')]
print(f"Total PNG files in directory: {len(png_files)}")
assert '51_Blangko_Instrumen_UAT.png' in png_files, "51_Blangko_Instrumen_UAT.png missing!"
assert '52_Hasil_Evaluasi_UAT_Terisi.png' in png_files, "52_Hasil_Evaluasi_UAT_Terisi.png missing!"
print("[SUCCESS] 51_Blangko_Instrumen_UAT.png and 52_Hasil_Evaluasi_UAT_Terisi.png exist!")

