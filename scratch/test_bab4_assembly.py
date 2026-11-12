with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

# Find Piece A: from start of file up to where the old 4.1.1 starts
idx_old_411 = text.find('<!-- 4.1.1.1 Halaman Login Staf -->')
if idx_old_411 == -1:
    idx_old_411 = text.find('4.1.1.1 Halaman Login Staf')
    # look back for <div class="mt-4"> or similar
    idx_old_411 = text.rfind('<div class="mt-4">', 0, idx_old_411)

print(f"idx_old_411: {idx_old_411}")
piece_a = text[:idx_old_411]

# Find Piece B: in second_part, from 4.1.1 to 4.2
pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')
second_part = text[pos_paper_close:]

idx_b_start = second_part.find('<!-- 4.1.1 Implementasi Sistem Terpadu -->')
if idx_b_start == -1:
    idx_b_start = second_part.find('4.1.1 Implementasi Sistem Terpadu')
    idx_b_start = second_part.rfind('<div', 0, idx_b_start)

idx_b_end = second_part.find('<!-- 4.2 PENGUJIAN SISTEM')
if idx_b_end == -1:
    idx_b_end = second_part.find('4.2 PENGUJIAN SISTEM')
    idx_b_end = second_part.rfind('<div', 0, idx_b_end)

print(f"idx_b_start: {idx_b_start}, idx_b_end: {idx_b_end}")
piece_b = second_part[idx_b_start:idx_b_end]

# Find Piece C: from first part, the clean 4.2 UAT section
idx_c_start = text.find('<div id="subbab_42"')
if idx_c_start == -1:
    idx_c_start = text.find('4.2 PENGUJIAN SISTEM DENGAN METODE USER ACCEPTANCE TESTING')
    idx_c_start = text.rfind('<div', 0, idx_c_start)

idx_c_end = pos_paper_close

print(f"idx_c_start: {idx_c_start}, idx_c_end: {idx_c_end}")
piece_c = text[idx_c_start:idx_c_end]

print(f"Piece A length: {len(piece_a)}")
print(f"Piece B length: {len(piece_b)}")
print(f"Piece C length: {len(piece_c)}")

# Check figures in Piece B
figs_b = re.findall(r'Gambar 4\.(\d+)', piece_b)
nums_b = sorted(list(set(int(x) for x in figs_b)))
print(f"Figures in Piece B: count {len(nums_b)}, min {min(nums_b) if nums_b else None}, max {max(nums_b) if nums_b else None}")

# Check tables in Piece C
tbls_c = re.findall(r'Tabel 4\.(\d+)', piece_c)
nums_c = sorted(list(set(int(x) for x in tbls_c)))
print(f"Tables in Piece C: count {len(nums_c)}, min {min(nums_c) if nums_c else None}, max {max(nums_c) if nums_c else None}")

