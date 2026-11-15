with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

target = """                <td colspan="8" style="text-align: right; padding: 8px 10px; font-size: 8.5pt; text-transform: uppercase;">
                    Total Estimasi Nilai Acuan Kerugian Persediaan:
                </td>"""

replacement = """                <!-- === ESTIMASI NILAI KERUGIAN OPNAME (BERBASIS HARGA BELI) === -->
                <td colspan="8" style="text-align: right; padding: 8px 10px; font-size: 8.5pt; text-transform: uppercase;">
                    Total Keseluruhan (Grand Total) Estimasi Nilai Kerugian Persediaan:
                </td>"""

assert target in text, "Target in cetak_berita_acara_opname.php not found"
text = text.replace(target, replacement, 1)

with open('inventori/cetak_berita_acara_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated cetak_berita_acara_opname.php successfully!")

