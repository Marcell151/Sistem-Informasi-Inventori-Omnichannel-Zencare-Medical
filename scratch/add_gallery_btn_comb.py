with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    c = f.read()

target = '<div class="flex items-center gap-2">'
btn = """<div class="flex items-center gap-2">
                <a href="katalog_download_ui_ux_zencare.html" class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition border border-emerald-300 flex items-center gap-1.5 font-sans">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Galeri &amp; Unduh Semua UI/UX
                </a>"""

if target in c and 'katalog_download_ui_ux_zencare.html' not in c:
    c = c.replace(target, btn, 1)
    with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(c)
    print("Updated BAB_III_DAN_IV_ZENCARE_MEDICAL.html")
else:
    print("Already updated or target not found")

