import re
from pathlib import Path

BASE = Path(r"c:\xampp\htdocs\inventory_zencare\laporan_ta")

bab1_path = BASE / "BAB_I_ZENCARE_MEDICAL.html"
bab4_path = BASE / "BAB_IV_ZENCARE_MEDICAL.html"
bab5_path = BASE / "BAB_V_ZENCARE_MEDICAL.html"
bab34_path = BASE / "BAB_III_DAN_IV_ZENCARE_MEDICAL.html"
full_path = BASE / "LAPORAN_TA_ZENCARE_MEDICAL_FULL.html"
uat_path = BASE / "INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html"

# 1. Clean stray *...* in INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html body (before <script>)
uat_text = uat_path.read_text(encoding="utf-8")
script_idx = uat_text.find("<script>")
uat_body, uat_tail = uat_text[:script_idx], uat_text[script_idx:]
# Replace *word(s)* patterns cleanly inside HTML body
uat_body_clean = re.sub(r"\*([^*\n<>]+?)\*", r"<i>\1</i>", uat_body)
if uat_body_clean != uat_body:
    uat_path.write_text(uat_body_clean + uat_tail, encoding="utf-8")
    print("Cleaned stray markdown asterisks in INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html")

# 2. Extract authoritative blocks from BAB_I, BAB_IV, BAB_V
bab1_text = bab1_path.read_text(encoding="utf-8")
bab4_text = bab4_path.read_text(encoding="utf-8")
bab5_text = bab5_path.read_text(encoding="utf-8")

def extract_block(text, start_marker, end_marker):
    s = text.find(start_marker)
    assert s != -1, f"Start marker not found: {start_marker}"
    e = text.find(end_marker, s)
    assert e != -1, f"End marker not found: {end_marker}"
    return text[s:e]

# Bab I: Sistematika p + ul block
bab1_sistematika = extract_block(
    bab1_text,
    "<!-- 1.8 Sistematika Penulisan -->",
    "</div>",
) + "</div>"

# Bab IV: #subbab_42_content div block
# Extract from '<div id="subbab_42_content">' up to matching close before '</div>\n        </div>\n\n\n    </div> <!-- Closes full_bab_iv_content'
subbab42_start = bab4_text.find('<div id="subbab_42_content">')
assert subbab42_start != -1, "#subbab_42_content start missing in BAB_IV"
subbab42_end_anchor = bab4_text.find("<!-- Closes full_bab_iv_content", subbab42_start)
assert subbab42_end_anchor != -1
# Walk back before the two closing </div></div> of subbab_42 wrapper and full wrapper
# Specifically: subbab_42_content ends right before '\n\n            </div>\n        </div>\n\n\n    </div> <!-- Closes'
m4 = re.search(r'(<div id="subbab_42_content">.*?</div>)\s*</div>\s*</div>\s*</div>\s*<!-- Closes full_bab_iv_content', bab4_text, re.DOTALL)
assert m4, "Regex failed for #subbab_42_content in BAB_IV"
subbab42_block = m4.group(1)

# Bab V: 5.2 Saran block
m5 = re.search(r'(<!-- 5\.2 Saran.*?</div>)', bab5_text, re.DOTALL)
assert m5, "Regex failed for 5.2 Saran in BAB_V"
saran_block = m5.group(1)

# 3. Sync into BAB_III_DAN_IV_ZENCARE_MEDICAL.html
bab34_text = bab34_path.read_text(encoding="utf-8")
bab34_new, count34 = re.subn(
    r'<div id="subbab_42_content">.*?</div>(?=\s*</div>\s*</div>\s*<!-- Bottom Notification|\s*</div>\s*</div>\s*</div>\s*<!-- Closes)',
    lambda _: subbab42_block,
    bab34_text,
    count=1,
    flags=re.DOTALL,
)
assert count34 == 1, f"Expected 1 replacement in BAB_III_DAN_IV, got {count34}"
bab34_path.write_text(bab34_new, encoding="utf-8")
print("Synced #subbab_42_content into BAB_III_DAN_IV_ZENCARE_MEDICAL.html")

# 4. Sync into LAPORAN_TA_ZENCARE_MEDICAL_FULL.html
full_text = full_path.read_text(encoding="utf-8")

# Sync Bab I sistematika section
full_new, c_f1 = re.subn(
    r'<!-- 1\.8 Sistematika Penulisan -->.*?</div>',
    lambda _: bab1_sistematika,
    full_text,
    count=1,
    flags=re.DOTALL,
)
assert c_f1 == 1, f"Expected 1 Bab I sistematika replacement in FULL, got {c_f1}"

# Sync #subbab_42_content
full_new, c_f4 = re.subn(
    r'<div id="subbab_42_content">.*?</div>(?=\s*</div>\s*</div>\s*<!-- Page Break & Divider for Bab V|\s*</div>\s*<!-- Page Break)',
    lambda _: subbab42_block,
    full_new,
    count=1,
    flags=re.DOTALL,
)
assert c_f4 == 1, f"Expected 1 #subbab_42_content replacement in FULL, got {c_f4}"

# Sync 5.2 Saran
full_new, c_f5 = re.subn(
    r'<!-- 5\.2 Saran.*?</div>',
    lambda _: saran_block,
    full_new,
    count=1,
    flags=re.DOTALL,
)
assert c_f5 == 1, f"Expected 1 Saran replacement in FULL, got {c_f5}"

full_path.write_text(full_new, encoding="utf-8")
print("Synced Bab I 1.8, Bab IV 4.2, and Bab V 5.2 into LAPORAN_TA_ZENCARE_MEDICAL_FULL.html")

# 5. Verification checks
print("\n=== VERIFICATION REPORT ===")
stale_patterns = [
    (r"27\s+tabel", "stale 27 tabel"),
    (r"Likert", "Likert mention"),
    (r"93[.,]75", "93.75 / 93,75"),
    (r"Persentase Kelayakan", "Persentase Kelayakan"),
    (r"upload bukti transfer", "upload bukti transfer"),
    (r"mengunggah bukti pembayaran", "mengunggah bukti pembayaran"),
    (r"bukti transfer manual", "bukti transfer manual"),
    (r"Sangat Layak", "Sangat Layak"),
    (r"\b300\s*/\s*320\b", "300 / 320"),
    (r"Subtotal Capaian", "Subtotal Capaian"),
    (r"go[\s-]*live", "go-live jargon"),
    (r"Siap Go-Live", "Siap Go-Live"),
    (r"Disetujui Seluruh Peran", "Disetujui Seluruh Peran"),
]

forbidden_words = [
    r"\bfarmasi\b",
    r"\bapotek\b",
    r"\bhpp\b",
    r"\bakuntansi\b",
    r"\baudit\b",
    r"\bdosen\b",
    r"\bbab\s*4\b",
    r"\bsidang\b",
    r"\bsempurna\b",
]

emoji_re = re.compile(r"[\U0001F300-\U0001FAFF\u2600-\u27BF]")

files_to_check = [bab1_path, bab4_path, bab5_path, bab34_path, full_path, uat_path]
all_ok = True

for p in files_to_check:
    txt = p.read_text(encoding="utf-8")
    issues = []
    for pat, label in stale_patterns:
        matches = re.findall(pat, txt, re.IGNORECASE)
        if matches:
            issues.append(f"{label}: {len(matches)}")
    # Check forbidden words in HTML visible/source text
    for fw in forbidden_words:
        matches = re.findall(fw, txt, re.IGNORECASE)
        if matches:
            issues.append(f"FORBIDDEN({fw}): {len(matches)}")
    emojis = emoji_re.findall(txt)
    if emojis:
        issues.append(f"EMOJIS: {len(emojis)}")
    # Check stray markdown * in BAB_I, BAB_V, UAT body, and #subbab_42_content
    if p == uat_path:
        body_part = txt[: txt.find("<script>")]
        md = re.findall(r"\*[^*\n<>]+\*", body_part)
        if md:
            issues.append(f"STRAY_ASTERISK_UAT: {md}")
    if "#subbab_42_content" in txt:
        m = re.search(r'<div id="subbab_42_content">.*?(?=<!-- Bottom Notification|<!-- Page Break & Divider for Bab V)', txt, re.DOTALL)
        if m:
            md42 = re.findall(r"\*[^*\n<>]+\*", m.group(0))
            if md42:
                issues.append(f"STRAY_ASTERISK_42: {md42}")
    print(f"- {p.name}: {'OK' if not issues else issues}")
    if issues:
        all_ok = False

# Positive content checks
assert "Sistematika penulisan laporan tugas akhir ini disusun untuk memberikan gambaran yang jelas" in bab1_path.read_text(encoding="utf-8")
assert "Sistematika penulisan laporan tugas akhir ini disusun untuk memberikan gambaran yang jelas" in full_path.read_text(encoding="utf-8")
for p in [bab5_path, full_path]:
    t = p.read_text(encoding="utf-8")
    assert "membership" in t and "rekomendasi penjualan" in t and "sandbox" in t and "live production" in t, f"Missing Saran terms in {p.name}"
for p in [bab4_path, bab34_path, full_path, uat_path]:
    t = p.read_text(encoding="utf-8")
    assert "Berhasil" in t and "Peran Penguji" in t, f"Missing UAT terms in {p.name}"

print(f"\nALL CHECKS PASSED: {all_ok}")
