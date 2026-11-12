import re

with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos_p4 = text.find('id="full_bab4_content"')
print('pos_p4:', pos_p4)
pos_open4 = text.find('>', pos_p4) + 1
pos_b4_end = text.rfind('<!-- Bottom Notification')
print('pos_b4_end:', pos_b4_end)
pos_close4 = text.rfind('</div>', 0, pos_b4_end)
print('pos_close4:', pos_close4)

inner = text[pos_open4:pos_close4]
figs = sorted(list(set(int(x) for x in re.findall(r'Gambar 4\.(\d+)', inner))))
print(f'Figs in inner: {len(figs)} (min {min(figs)}, max {max(figs)})')
assert figs == list(range(1, 51))
tbls = sorted(list(set(int(x) for x in re.findall(r'Tabel 4\.(\d+)', inner))))
print(f'Tables in inner: {tbls}')
assert tbls == list(range(1, 11))
print("PERFECT SLICE FOR BAB IV!")
