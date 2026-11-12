import re

with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

pos_paper_close = text.find('</div> <!-- Closes full_bab_iv_content (paper) -->')
print(f"Paper close found at pos: {pos_paper_close}")

first_part = text[:pos_paper_close]

# Count figures in first_part
figs = re.findall(r'<b>(Gambar 4\.\d+)</b>', first_part)
print(f"Figures in first_part: {len(figs)}")
fig_nums = [int(re.search(r'\d+', f.split('.')[1]).group(0)) for f in figs]
print(f"Min fig: {min(fig_nums) if fig_nums else None}, Max fig: {max(fig_nums) if fig_nums else None}")

# Check missing figures
expected_figs = set(range(1, 51))
actual_figs = set(fig_nums)
missing_figs = expected_figs - actual_figs
print(f"Missing figures 1-50 in first_part: {missing_figs}")

# Count tables in first_part
tabs = re.findall(r'<b>(Tabel 4\.\d+)</b>', first_part)
print(f"Tables in first_part: {len(tabs)}")
tab_nums = [int(re.search(r'\d+', t.split('.')[1]).group(0)) for t in tabs]
print(f"Min tab: {min(tab_nums) if tab_nums else None}, Max tab: {max(tab_nums) if tab_nums else None}")
expected_tabs = set(range(1, 11))
actual_tabs = set(tab_nums)
missing_tabs = expected_tabs - actual_tabs
print(f"Missing tables 1-10 in first_part: {missing_tabs}")

