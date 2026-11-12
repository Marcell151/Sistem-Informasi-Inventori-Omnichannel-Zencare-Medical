with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

import re

text = text.replace('.badge-audit', '.badge-riwayat')
text = text.replace('badge-audit', 'badge-riwayat')
text = text.replace("filterTables('audit', this)", "filterTables('riwayat', this)")
text = text.replace('data-cat="audit"', 'data-cat="riwayat"')
text = text.replace('jejak audit (audit trail)', 'rekam jejak persediaan (stock movement trail)')
text = text.replace('jejak audit', 'rekam jejak')
text = text.replace('saat audit', 'saat pelaksanaan stok opname')
text = text.replace('audit berkala', 'pemeriksaan fisik berkala')
text = text.replace('audit fisik', 'pemeriksaan fisik')
text = text.replace('audit stok opname', 'pemeriksaan stok opname')
text = text.replace('audit opname', 'pemeriksaan opname')
text = re.sub(r'\baudit\b', 'pemeriksaan', text, flags=re.IGNORECASE)

with open('lain/kamus_data_zencare.html', 'w', encoding='utf-8') as f:
    f.write(text)

print("Sanitized audit in kamus_data_zencare.html")

