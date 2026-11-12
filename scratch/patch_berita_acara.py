with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update @page to landscape
content = content.replace('size: A4 portrait;', 'size: A4 landscape;')

# 2. Add border styling for anomali row
css_addition = """
        thead {
            display: table-header-group;
        }
        tr {
            page-break-inside: avoid;
        }
        tr.row-anomali td {
            border-top: 2.5px solid #dc2626 !important;
            border-bottom: 2.5px solid #dc2626 !important;
            background-color: #fef2f2 !important;
            font-weight: 600;
        }
        tr.row-anomali td:first-child {
            border-left: 2.5px solid #dc2626 !important;
        }
        tr.row-anomali td:last-child {
            border-right: 2.5px solid #dc2626 !important;
        }
        .page-footer-num {
            position: fixed;
            bottom: 0;
            right: 0;
            font-size: 8pt;
            color: #64748b;
        }
"""
content = content.replace('table.data-table td {', css_addition + '        table.data-table td {')

# 3. Replace the tr inline style with class row-anomali
old_tr = '<tr style="border-bottom: 1px solid #cbd5e1; <?= $isAnomali ? \'background-color: #fff1f2; outline: 2px solid #dc2626; border: 2px solid #dc2626; font-weight: 600;\' : \'\' ?>">'
new_tr = '<tr class="<?= $isAnomali ? \'row-anomali\' : \'\' ?>" style="border-bottom: 1px solid #cbd5e1;">'
content = content.replace(old_tr, new_tr)

# Verify forbidden words
forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = content.lower().count(w)
    if c > 0:
        found[w] = c
print("Cetak Berita Acara forbidden words:", found)

with open('inventori/cetak_berita_acara_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated cetak_berita_acara_opname.php successfully.")

