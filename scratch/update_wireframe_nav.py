with open(r'lain\wireframe_zencare.html', 'r', encoding='utf-8') as f:
    content = f.read()

target = '''<div class="nav-links">
            <a href="../laporan_ta/BAB_III_ZENCARE_MEDICAL.html#sec_3_6_wrapper" class="btn-link btn-outline">'''

replacement = '''<div class="nav-links">
            <a href="katalog_ui_ux_asli_zencare.html" class="btn-link" style="background:#059669; color:#ffffff; font-weight:700;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Komparasi UI Asli vs Wireframe (25 Halaman)
            </a>
            <a href="../laporan_ta/BAB_III_ZENCARE_MEDICAL.html#sec_3_6_wrapper" class="btn-link btn-outline">'''

norm_c = content.replace('\r\n', '\n')
norm_t = target.replace('\r\n', '\n')
norm_r = replacement.replace('\r\n', '\n')

if norm_t in norm_c:
    norm_c = norm_c.replace(norm_t, norm_r, 1)
    with open(r'lain\wireframe_zencare.html', 'w', encoding='utf-8') as f:
        f.write(norm_c.replace('\n', '\r\n'))
    print("Successfully updated wireframe_zencare.html")
else:
    print("Target not found in wireframe_zencare.html")

