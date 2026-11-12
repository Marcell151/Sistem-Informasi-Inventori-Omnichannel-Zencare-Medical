for file in ['laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Heading
    content = content.replace(
        '<h5 class="font-bold text-slate-800 mb-2">H. Kelola Stok Opname &amp; Audit Fisik (Kunci Kasir &amp; Berita Acara)</h5>',
        '<h5 class="font-bold text-slate-800 mb-2">H. Kelola Stok Opname &amp; Pemeriksaan Fisik (Freeze Lock, Input Fisik Kasir, Verifikasi Admin, &amp; Berita Acara)</h5>'
    )
    content = content.replace(
        '<!-- H. Kelola Stok Opname & Audit Fisik -->',
        '<!-- H. Kelola Stok Opname & Pemeriksaan Fisik -->'
    )

    # 2. Caption
    content = content.replace(
        '<p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.17</b> Activity Diagram Kelola Stok Opname &amp; Audit Fisik (Kunci Kasir &amp; Berita Acara)</p>',
        '<p class="text-xs text-slate-500 font-sans mt-2"><b>Gambar 3.17</b> Activity Diagram Kelola Stok Opname (Freeze Lock, Input Fisik Kasir, Verifikasi Selisih Admin, &amp; Berita Acara)</p>'
    )

    # 3. Text in narrative
    content = content.replace('selama audit berlangsung', 'selama proses pemeriksaan fisik berlangsung')
    content = content.replace('untuk resmi mengakhiri audit', 'untuk resmi mengakhiri sesi stok opname')

    # 4. Clean other occurrences in 3.3.4
    content = content.replace('laporan audit, dan pengaturan sistem', 'laporan manajerial, dan pengaturan sistem')
    content = content.replace('modul laporan manajerial dan audit persediaan', 'modul laporan manajerial dan pemantauan persediaan')

    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Updated Activity Diagram in", file)

