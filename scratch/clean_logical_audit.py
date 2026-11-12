with open('lain/logical_database_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

text = text.replace('Buku Besar Audit', 'Buku Besar Logistik')
text = text.replace('badge-audit', 'badge-riwayat')
text = text.replace('.badge-audit', '.badge-riwayat')
text = text.replace('jejak audit riwayat koreksi', 'rekam jejak riwayat koreksi')
text = text.replace('audit riwayat koreksi', 'rekam jejak koreksi')
text = text.replace('Audit', 'Pemeriksaan')
text = text.replace('audit', 'pelacakan')

with open('lain/logical_database_zencare.html', 'w', encoding='utf-8') as f:
    f.write(text)

print("Cleaned audit in logical_database_zencare.html")

