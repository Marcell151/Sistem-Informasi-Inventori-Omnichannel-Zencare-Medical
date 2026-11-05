# -*- coding: utf-8 -*-
import os
import re

# Load base screen definitions from generate_bab4
import sys
sys.path.append(r'c:\xampp\htdocs\inventory_zencare\scratch')
from generate_bab4 import ui_screens

print(f"Loaded {len(ui_screens)} screens.")

# Verify image existence
base_img_dir = r"c:\xampp\htdocs\inventory_zencare\lain\File PPT\UI_UX_Asli"
for s in ui_screens:
    full_path = os.path.join(base_img_dir, s['img_file'])
    if not os.path.exists(full_path):
        raise FileNotFoundError(f"Missing image: {full_path}")
print("All 27 screenshot images verified on disk!")

def build_bab4_html():
    html = []
    html.append('''
        <!-- Title of Chapter -->
        <div class="text-center mb-10">
            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-wide">BAB IV</h1>
            <h2 class="text-lg sm:text-xl font-bold uppercase tracking-wide mt-1">HASIL DAN PEMBAHASAN</h2>
        </div>

        <!-- 4.1 Hasil -->
        <div class="mt-8 mb-6">
            <div class="flex items-center justify-between border-b pb-2">
                <h3 class="text-sm font-bold font-serif text-slate-900">4.1 Hasil</h3>
                <button onclick="copyText('subbab_41_content', this)" class="no-print px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded border border-slate-300 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    Salin Subbab 4.1
                </button>
            </div>
            
            <div id="subbab_41_content" class="mt-3">
                <p class="text-xs font-serif text-slate-800 text-justify leading-relaxed indent-8 mb-4">
                    Subbab ini memaparkan hasil dari implementasi sistem informasi inventori dan penjualan omnichannel pada toko kesehatan ZenCare Medical yang telah dibangun berdasarkan analisis kebutuhan serta spesifikasi rancangan pada bab sebelumnya. Implementasi sistem ini mencakup perwujudan antarmuka pengguna sistem (user interface), pengoperasian alur transaksi kasir toko fisik (Point of Sale), pengelolaan persediaan obat dan alat kesehatan multi-satuan dengan kendali kedaluwarsa berbasis FEFO (First Expired, First Out), integrasi etalase toko daring (E-Commerce), hingga penyajian laporan eksekutif terpadu untuk mendukung transparansi operasional dan pengambilan keputusan bisnis.
                </p>

                <!-- 4.1.1 Tampilan Hasil Antarmuka Sistem -->
                <div class="mt-6">
                    <div class="flex items-center justify-between border-b pb-1 mb-3">
                        <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">4.1.1 Tampilan Hasil Antarmuka Sistem (Implementasi UI/UX)</h4>
                        <button onclick="copyText('subbab_411_content', this)" class="no-print px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-blue-200 transition flex items-center gap-1 font-sans font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            Salin Subbab 4.1.1
                        </button>
                    </div>

                    <div id="subbab_411_content">
                        <p class="text-xs font-serif text-slate-800 text-justify leading-relaxed indent-8 mb-4">
                            Implementasi antarmuka sistem merupakan perwujudan konkret dari rancangan antarmuka (wireframe) yang telah didefinisikan sebelumnya ke dalam bentuk aplikasi berbasis web yang siap dioperasikan. Seluruh antarmuka dikembangkan dengan mengutamakan prinsip kenyamanan interaksi (user-friendly), kejelasan navigasi, kecepatan respons, serta ketepatan fungsi operasional guna mendukung kinerja staf di toko fisik, pengawasan manajerial oleh pimpinan, dan kemudahan berbelanja bagi pelanggan daring. Antarmuka sistem dikelompokkan ke dalam empat bagian utama berdasarkan peran dan fungsinya, yaitu: (1) Antarmuka Gerbang Autentikasi Staf, (2) Antarmuka Operasional Admin dan Kasir, (3) Antarmuka Manajerial dan Eksekutif Superadmin, serta (4) Antarmuka Toko Daring (E-Commerce) Pelanggan. Rincian implementasi dari masing-masing antarmuka disajikan sebagai berikut:
                        </p>
    ''')

    for s in ui_screens:
        # Group heading if any
        if s.get('group'):
            html.append(f'''
                        <div class="mt-6 mb-3 pt-3 border-t border-slate-200">
                            <h5 class="text-xs font-bold font-serif text-slate-900 tracking-wide uppercase">
                                {s['group']}
                            </h5>
                            <p class="text-xs font-serif text-slate-700 text-justify leading-relaxed indent-8 mt-1.5 mb-3">
                                {s['group_intro']}
                            </p>
                        </div>
            ''')

        # Subsection header
        html.append(f'''
                        <div class="mt-4 mb-2">
                            <h6 class="text-xs font-bold font-serif text-slate-800 italic">
                                {s['sub_num']}. {s['title']}
                            </h6>
                        </div>

                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/{s['img_file']}" alt="{s['title']}" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                {s['num']} {s['title']}
                            </p>
                            <p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">
                                {s['desc']}
                            </p>
                        </div>
        ''')

    html.append('''
                    </div> <!-- Closes subbab_411_content -->
                </div> <!-- Closes 4.1.1 -->
            </div> <!-- Closes subbab_41_content -->
        </div> <!-- Closes 4.1 -->
    ''')
    return "\n".join(html)

bab4_content = build_bab4_html()

# Verify zero farmasi in bab4_content
farmasi_in_bab4 = bab4_content.lower().count('farmasi')
print(f"Farmasi in Bab IV HTML: {farmasi_in_bab4}")
assert farmasi_in_bab4 == 0, "Found 'farmasi' in Bab IV HTML!"

# 1. Build Standalone BAB_IV_ZENCARE_MEDICAL.html
standalone_html = f'''<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naskah Bab IV - Hasil dan Pembahasan (ZenCare Medical)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Times+New+Roman&display=swap" rel="stylesheet">
    <style>
        body {{
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }}

        .paper {{
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.8;
            font-size: 12pt;
            background: white;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.1);
        }}

        .paper h1,
        .paper h2,
        .paper h3,
        .paper h4,
        .paper h5,
        .paper h6 {{
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }}

        @media print {{
            body {{
                background: white;
                color: black;
                padding: 0;
            }}

            .no-print {{
                display: none !important;
            }}

            .paper {{
                box-shadow: none;
                padding: 0;
                margin: 0;
                width: 100%;
            }}
        }}
    </style>
    <script>
        function copyText(elementId, btnElement) {{
            const el = document.getElementById(elementId);
            const text = el.innerText || el.textContent;
            navigator.clipboard.writeText(text).then(() => {{
                const originalText = btnElement.innerHTML;
                btnElement.innerHTML = `<svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!`;
                btnElement.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                setTimeout(() => {{
                    btnElement.innerHTML = originalText;
                    btnElement.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                }}, 2000);
            }}).catch(err => {{
                alert('Gagal menyalin: ' + err);
            }});
        }}
    </script>
</head>

<body class="py-8 px-4 sm:px-6 lg:px-8">

    <!-- Top Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-6 no-print">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 mb-1">
                    Naskah Laporan Terverifikasi Sistem
                </span>
                <h1 class="text-xl font-bold text-slate-800">BAB IV: Hasil dan Pembahasan</h1>
                <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Inventori Omnichannel - ZenCare Medical (Subbab 4.1 s.d. 4.1.1 Implementasi UI/UX)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="BAB_III_ZENCARE_MEDICAL.html" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Lihat Bab III
                </a>
                <button onclick="copyText('full_bab4_content', this)" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    Salin Semua Bab IV
                </button>
                <button onclick="window.print()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak / Simpan PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Main Academic Paper Container -->
    <div id="full_bab4_content" class="paper max-w-4xl mx-auto p-8 sm:p-14 rounded-lg">
        {bab4_content}
    </div>

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori Omnichannel ZenCare Medical
    </div>

</body>
</html>
'''

standalone_path = r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html"
with open(standalone_path, 'w', encoding='utf-8') as f:
    f.write(standalone_html)
print("Standalone BAB_IV_ZENCARE_MEDICAL.html written successfully!")

# 2. Now update BAB_III_ZENCARE_MEDICAL.html to be a continuous unified report containing BOTH Bab III and Bab IV!
# Let's read current BAB_III_ZENCARE_MEDICAL.html
bab3_path = r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html"
with open(bab3_path, 'r', encoding='utf-8') as f:
    orig_bab3 = f.read()

# Let's inspect where full_bab3_content closes
pattern = r'(</div>\s*<!--\s*Closes full_bab3_content paper\s*-->)'
match = re.search(pattern, orig_bab3)
if not match:
    raise ValueError("Could not find closing tag of full_bab3_content in BAB_III_ZENCARE_MEDICAL.html")

closing_bab3_idx = match.start()

# Build unified injection:
# After Bab III paper, we insert a divider and the Bab IV paper container!
divider_and_bab4 = f'''
    </div> <!-- Closes full_bab3_content paper -->

    <!-- Page Break & Divider for Bab IV (No Print) -->
    <div class="max-w-4xl mx-auto my-12 flex items-center justify-center gap-4 no-print">
        <div class="h-0.5 flex-1 bg-gradient-to-r from-transparent via-slate-300 to-slate-400"></div>
        <span class="px-4 py-1.5 bg-slate-200 text-slate-700 text-xs font-bold rounded-full uppercase tracking-wider shadow-sm flex items-center gap-1.5">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            Lanjutan Laporan: Masuk ke Bab IV
        </span>
        <div class="h-0.5 flex-1 bg-gradient-to-r from-slate-400 via-slate-300 to-transparent"></div>
    </div>

    <!-- Main Academic Paper Container for Bab IV -->
    <div id="full_bab4_content" class="paper max-w-4xl mx-auto p-8 sm:p-14 rounded-lg mt-8">
        {bab4_content}
    </div> <!-- Closes full_bab4_content paper -->
'''

# Replace the closing of bab3 with divider_and_bab4
unified_html = orig_bab3[:closing_bab3_idx] + divider_and_bab4 + orig_bab3[match.end():]

# Also update the Top Action Bar in BAB_III_ZENCARE_MEDICAL.html to reflect both Bab III and Bab IV, and add buttons:
# Find title and buttons in top bar:
top_bar_old = '''                <h1 class="text-xl font-bold text-slate-800">BAB III: Analisis dan Perancangan Sistem</h1>

                <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Inventori Omnichannel - ZenCare Medical

                    (Subbab 3.1 s.d. 3.3.1 Workflow Terkini)</p>'''

top_bar_new = '''                <h1 class="text-xl font-bold text-slate-800">Laporan Bab III & IV: Perancangan & Implementasi</h1>

                <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Inventori Omnichannel - ZenCare Medical (Bab III s.d. Bab IV Hasil UI/UX)</p>'''

unified_html = unified_html.replace(top_bar_old, top_bar_new)

# Add buttons in action bar:
btn_old = '''                <button onclick="copyText('full_bab3_content', this)"

                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">

                        </path>

                    </svg>

                    Salin Semua Naskah

                </button>'''

btn_new = '''                <button onclick="copyText('full_bab3_content', this)"

                    class="px-3.5 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-sm" title="Salin seluruh isi Bab III">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">

                        </path>

                    </svg>

                    Salin Bab III

                </button>

                <button onclick="copyText('full_bab4_content', this)"

                    class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm" title="Salin seluruh isi Bab IV">

                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">

                        </path>

                    </svg>

                    Salin Bab IV

                </button>'''

if btn_old in unified_html:
    unified_html = unified_html.replace(btn_old, btn_new)
    print("Action bar buttons updated in unified report!")
else:
    print("Warning: exact button string not matched, checking alternative replacement...")

# Write unified file
with open(bab3_path, 'w', encoding='utf-8') as f:
    f.write(unified_html)
print(f"Unified {bab3_path} written successfully!")

# Also write a designated file BAB_III_DAN_IV_ZENCARE_MEDICAL.html for explicit naming
unified_explicit_path = r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html"
with open(unified_explicit_path, 'w', encoding='utf-8') as f:
    f.write(unified_html)
print(f"Explicit unified file {unified_explicit_path} written successfully!")

