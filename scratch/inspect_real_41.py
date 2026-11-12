with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')
second_part = text[pos_paper_close:]

# Find where 4.1 starts in second_part and where 4.2 starts in second_part
idx_41 = second_part.find('4.1.1 Implementasi Sistem Terpadu')
idx_42 = second_part.find('4.2 PENGUJIAN SISTEM')

print(f"idx_41: {idx_41}, idx_42: {idx_42}")
real_41 = second_part[idx_41:idx_42]
print(f"Length of real_41: {len(real_41)}")

import re
figs_in_real_41 = re.findall(r'Gambar 4\.(\d+)', real_41)
fig_nums = sorted(list(set(int(x) for x in figs_in_real_41)))
print(f"Figures in real_41: min {min(fig_nums)}, max {max(fig_nums)}, count {len(fig_nums)}")
print(f"Figure numbers: {fig_nums}")

