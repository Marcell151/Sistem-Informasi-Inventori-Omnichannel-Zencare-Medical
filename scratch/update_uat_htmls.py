import re

def update_uat_html(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        text = f.read()

    # 1. Change status DITERIMA to BERHASIL
    text = text.replace('status-col font-bold text-emerald-700">DITERIMA</td>', 'status-col font-bold text-emerald-700">BERHASIL</td>')
    
    # 2. Change recommendation text
    text = text.replace('<em>Sangat Layak / Sangat Diterima</em>', '<em>Sangat Layak</em>')
    text = text.replace('DINYATAKAN DITERIMA SECARA PENUH UNTUK DITERAPKAN PADA GERAI OPERASIONAL', 'DINYATAKAN BERHASIL SERTA LAYAK PENUH UNTUK DITERAPKAN PADA GERAI OPERASIONAL')
    
    # 3. Update table header if needed: "Status Penerimaan" -> "Status Hasil Uji"
    text = text.replace('Status Penerimaan', 'Status Hasil Uji')

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Updated {file_path}")

update_uat_html(r'laporan_ta\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html')
update_uat_html(r'lain\INSTRUMEN_DAN_HASIL_UAT_ZENCARE.html')

