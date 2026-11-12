from check_new_bab5 import kesimpulan, saran

with open('laporan_ta/BAB_V_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace 5.1 Kesimpulan paragraph
import re

# Find the paragraph under 5.1 Kesimpulan
idx_51 = text.find('5.1 Kesimpulan')
assert idx_51 != -1
pos_p1_start = text.find('<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">', idx_51)
pos_p1_end = text.find('</p>', pos_p1_start) + 4

text = text[:pos_p1_start] + f'<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">\n                {kesimpulan}\n            </p>' + text[pos_p1_end:]

# Find the paragraph under 5.2 Saran
idx_52 = text.find('5.2 Saran')
assert idx_52 != -1
pos_p2_start = text.find('<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">', idx_52)
pos_p2_end = text.find('</p>', pos_p2_start) + 4

text = text[:pos_p2_start] + f'<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">\n                {saran}\n            </p>' + text[pos_p2_end:]

with open('laporan_ta/BAB_V_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated BAB_V_ZENCARE_MEDICAL.html successfully!")

