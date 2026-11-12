with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace the broken CSS selector
old_css_broken = """        table.data-table th, 
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
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            line-height: 1.35;
        }
        table.data-table th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            font-size: 8pt;
            letter-spacing: 0.3px;
        }"""

new_css_clean = """        thead {
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
        table.data-table th,
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            line-height: 1.35;
        }
        table.data-table th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            font-size: 8pt;
            letter-spacing: 0.3px;
            display: table-cell;
            vertical-align: middle;
        }"""

if old_css_broken in text:
    text = text.replace(old_css_broken, new_css_clean)
    print("Found and replaced old_css_broken successfully!")
else:
    print("Exact match not found, let's search for table.data-table th,")
    pos = text.find('table.data-table th,')
    print(text[pos:pos+500])

with open('inventori/cetak_berita_acara_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

