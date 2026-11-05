import os

with open(r'laporan_ta\BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    content = f.read()

target = '''<a href="../lain/wireframe_zencare.html" target="_blank"
                    class="text-xs text-blue-700 hover:text-blue-900 border border-blue-300 bg-blue-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Wireframe Interaktif (25 Halaman)
                </a>'''

replacement = '''<a href="../lain/katalog_ui_ux_asli_zencare.html" target="_blank"
                    class="text-xs text-emerald-800 hover:text-emerald-950 border border-emerald-300 bg-emerald-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Buka Komparasi UI/UX Asli vs Wireframe (25 Halaman)
                </a>
                <a href="../lain/wireframe_zencare.html" target="_blank"
                    class="text-xs text-blue-700 hover:text-blue-900 border border-blue-300 bg-blue-50 px-2.5 py-1 rounded transition inline-flex items-center gap-1 font-sans">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Buka Wireframe Interaktif (25 Halaman)
                </a>'''

norm_content = content.replace('\r\n', '\n')
norm_target = target.replace('\r\n', '\n')
norm_replacement = replacement.replace('\r\n', '\n')

if norm_target in norm_content:
    norm_content = norm_content.replace(norm_target, norm_replacement, 1)
    with open(r'laporan_ta\BAB_III_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(norm_content.replace('\n', '\r\n'))
    print('Successfully added link to BAB_III_ZENCARE_MEDICAL.html')
else:
    print('Target not found!')

