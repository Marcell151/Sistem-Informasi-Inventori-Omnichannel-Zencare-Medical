with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

# 1. Piece 1: Top up to before old 4.1.1
idx_old_411_start = text.find('<!-- 4.1.1 Implementasi Sistem -->')
assert idx_old_411_start != -1
piece_top = text[:idx_old_411_start]

# 2. Piece 2: The real 50-figure 4.1
pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')
second_part = text[pos_paper_close:]

idx_b_start = second_part.find('<!-- 4.1.1 Implementasi Sistem Terpadu -->')
assert idx_b_start != -1

idx_b_end = second_part.find('<!-- 4.2 PENGUJIAN SISTEM')
if idx_b_end == -1:
    idx_b_end = second_part.find('4.2 PENGUJIAN SISTEM')
    idx_b_end = second_part.rfind('<div', 0, idx_b_end)

piece_50figs = second_part[idx_b_start:idx_b_end].strip()

# 3. Piece 3: The pure 10-table 4.2 UAT section
idx_c_start = text.find('<div id="subbab_42"')
assert idx_c_start != -1
piece_uat_tables = text[idx_c_start:pos_paper_close].strip()

# 4. Piece 4: Footer
footer = """
    </div> <!-- Closes full_bab_iv_content (paper) -->

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori <i>Omnichannel</i> ZenCare Medical
    </div>

</body>
</html>
"""

# Let's inspect Piece 2 (50 figs) to ensure it starts and ends cleanly with balanced divs
print(f"piece_top length: {len(piece_top)}")
print(f"piece_50figs length: {len(piece_50figs)}")
print(f"piece_uat_tables length: {len(piece_uat_tables)}")

# Check figures in piece_50figs
figs = re.findall(r'Gambar 4\.(\d+)', piece_50figs)
nums = sorted(list(set(int(x) for x in figs)))
print(f"Figures in piece_50figs: count {len(nums)}, min {min(nums)}, max {max(nums)}")

# Check tables in piece_uat_tables
tbls = re.findall(r'Tabel 4\.(\d+)', piece_uat_tables)
nums_tbl = sorted(list(set(int(x) for x in tbls)))
print(f"Tables in piece_uat_tables: count {len(nums_tbl)}, min {min(nums_tbl)}, max {max(nums_tbl)}")

