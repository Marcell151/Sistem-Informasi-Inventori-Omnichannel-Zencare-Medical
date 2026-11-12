import re

master_file = r'laporan_ta\LAPORAN_TA_ZENCARE_MEDICAL_FULL.html'

with open(master_file, 'r', encoding='utf-8') as f:
    text = f.read()

print(f"Total length of master document: {len(text):,} characters")

# 1. Check all 5 chapters
for ch in ['BAB I', 'BAB II', 'BAB III', 'BAB IV', 'BAB V']:
    assert ch in text, f"Missing {ch}!"
    print(f"Chapter check: {ch} is present!")

# 2. Check forbidden words
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

has_viol = False
for pat in forbidden_patterns:
    m = re.findall(pat, text, re.IGNORECASE)
    if m:
        print(f"[VIOLATION] Found forbidden '{pat}': count {len(m)}")
        has_viol = True
assert not has_viol, "Forbidden words found in master report!"
print("[SUCCESS] Zero forbidden words in Master Report!")

# 3. Check emojis
emojis = re.findall(r'[\U00010000-\U0010ffff]', text)
assert len(emojis) == 0, f"Found emojis: {len(emojis)}"
print("[SUCCESS] Zero emojis in Master Report!")

# 4. Check figures in Bab IV
figs_b4 = sorted(list(set(int(x) for x in re.findall(r'Gambar 4\.(\d+)', text))))
print(f"Figures in Bab IV: min {min(figs_b4)}, max {max(figs_b4)}, total {len(figs_b4)}")
assert figs_b4 == list(range(1, 51)), f"Expected 1..50 in Bab IV, got {figs_b4}"
print("[SUCCESS] Figures 4.1 to 4.50 perfectly continuous!")

# 5. Check tables in Bab IV
tbls_b4 = sorted(list(set(int(x) for x in re.findall(r'Tabel 4\.(\d+)', text))))
print(f"Tables in Bab IV: {tbls_b4}")
assert tbls_b4 == list(range(1, 11)), f"Expected 1..10 in Bab IV, got {tbls_b4}"
print("[SUCCESS] Tables 4.1 to 4.10 perfectly continuous!")

# 6. Check math
assert '93.75%' in text
assert '300 / 320' in text
print("[SUCCESS] Math 300/320 and 93.75% verified!")

print("\n=== MASTER REPORT (BAB I - BAB V) VERIFICATION COMPLETE: 100% PERFECT! ===")

