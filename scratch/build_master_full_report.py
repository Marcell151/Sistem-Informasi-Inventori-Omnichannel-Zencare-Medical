import re
import os

print("Assembling master full report from Bab I through Bab V...")

# 1. Read Bab I
with open('laporan_ta/BAB_I_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b1 = f.read()
pos_p1 = text_b1.find('id="full_bab_i_content"')
pos_open1 = text_b1.find('>', pos_p1) + 1
pos_b1_end = text_b1.rfind('<!-- Bottom Notification')
pos_close1 = text_b1.rfind('</div>', 0, pos_b1_end)
inner_b1 = text_b1[pos_open1:pos_close1].strip()
print(f"Bab I inner length: {len(inner_b1)}")

# 2. Read Bab II
with open('laporan_ta/BAB_II_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b2 = f.read()
pos_p2 = text_b2.find('id="full_bab_ii_content"')
pos_open2 = text_b2.find('>', pos_p2) + 1
pos_b2_end = text_b2.rfind('<!-- Bottom Notification')
pos_close2 = text_b2.rfind('</div>', 0, pos_b2_end)
inner_b2 = text_b2[pos_open2:pos_close2].strip()
print(f"Bab II inner length: {len(inner_b2)}")

# 3. Read Bab III (from clean BAB_III_ZENCARE_MEDICAL.html)
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b3 = f.read()
pos_p3 = text_b3.find('id="full_bab3_content"')
pos_open3 = text_b3.find('>', pos_p3) + 1
pos_close3 = text_b3.find('</div> <!-- Closes full_bab3_content paper -->')
inner_b3 = text_b3[pos_open3:pos_close3].strip()
print(f"Bab III inner length: {len(inner_b3)}")

# 4. Read Bab IV (from clean BAB_IV_ZENCARE_MEDICAL.html)
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b4 = f.read()
pos_p4 = text_b4.find('id="full_bab4_content"')
assert pos_p4 != -1, "pos_p4 not found!"
pos_open4 = text_b4.find('>', pos_p4) + 1
pos_b4_end = text_b4.rfind('<!-- Bottom Notification')
pos_close4 = text_b4.rfind('</div>', 0, pos_b4_end)
inner_b4 = text_b4[pos_open4:pos_close4].strip()
print(f"Bab IV inner length: {len(inner_b4)}")

# 5. Read Bab V
with open('laporan_ta/BAB_V_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b5 = f.read()
pos_p5 = text_b5.find('id="full_bab_v_content"')
pos_open5 = text_b5.find('>', pos_p5) + 1
pos_b5_end = text_b5.rfind('<!-- Bottom Notification')
pos_close5 = text_b5.rfind('</div>', 0, pos_b5_end)
inner_b5 = text_b5[pos_open5:pos_close5].strip()
print(f"Bab V inner length: {len(inner_b5)}")

# Divider template
def make_divider(next_chapter_name, anchor_id):
    return f"""
    <!-- Page Break & Divider for {next_chapter_name} (No Print) -->
    <div id="{anchor_id}" class="max-w-4xl mx-auto my-12 flex items-center justify-center gap-4 no-print scroll-mt-20">
        <div class="h-0.5 flex-1 bg-gradient-to-r from-transparent via-slate-300 to-slate-400"></div>
        <span class="px-4 py-1.5 bg-white border border-slate-300 rounded-full text-xs font-bold text-slate-700 shadow-xs flex items-center gap-1.5 uppercase tracking-wider">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            Lanjutan Laporan: Masuk ke {next_chapter_name}
        </span>
        <div class="h-0.5 flex-1 bg-gradient-to-r from-slate-400 via-slate-300 to-transparent"></div>
    </div>
"""

master_html = f"""<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tugas Akhir Lengkap (BAB I - V) - ZenCare Medical</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Mermaid.js CDN for HIPO diagrams -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Times+New+Roman&display=swap" rel="stylesheet">
    <style>
        @media print {{
            .no-print {{ display: none !important; }}
            body {{ background: white !important; padding: 0 !important; }}
            .paper {{ box-shadow: none !important; border: none !important; padding: 0 !important; margin: 0 !important; }}
            .page-break {{ page-break-before: always; }}
        }}
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }}
        .paper h1, .paper h2, .paper h3, .paper h4, .paper h5, .paper h6 {{
            font-family: 'Times New Roman', Times, serif;
            color: #000;
        }}
    </style>
    <script>
        function copyText(elementId, btnElement) {{
            const el = document.getElementById(elementId);
            if (!el) return;
            const plainText = el.innerText || el.textContent;
            const originalText = btnElement.innerHTML;

            function showSuccess() {{
                btnElement.innerHTML = `<svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!`;
                btnElement.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                setTimeout(() => {{
                    btnElement.innerHTML = originalText;
                    btnElement.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                }}, 2000);
            }}

            if (navigator.clipboard && window.isSecureContext) {{
                navigator.clipboard.writeText(plainText).then(showSuccess).catch(err => {{
                    fallbackCopy(plainText);
                }});
            }} else {{
                fallbackCopy(plainText);
            }}

            function fallbackCopy(text) {{
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {{
                    document.execCommand('copy');
                    showSuccess();
                }} catch (err) {{
                    alert('Gagal menyalin teks otomatis. Silakan salin manual.');
                }}
                document.body.removeChild(textArea);
            }}
        }}

        document.addEventListener("DOMContentLoaded", function() {{
            if (typeof mermaid !== 'undefined') {{
                mermaid.initialize({{
                    startOnLoad: true,
                    theme: 'default',
                    flowchart: {{ useMaxWidth: true, htmlLabels: true, curve: 'basis' }},
                    securityLevel: 'loose'
                }});
            }}
        }});
    </script>
</head>
<body class="py-8 px-4 sm:px-6 lg:px-8">

    <!-- Top Sticky Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-6 bg-white p-4 rounded-lg shadow-xs border border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print sticky top-4 z-50">
        <div>
            <h1 class="text-sm font-bold text-slate-800">Laporan Tugas Akhir Lengkap (BAB I - BAB V)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Inventori <i>Omnichannel</i> - ZenCare Medical</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1 text-xs font-medium">
                <a href="#bab1_anchor" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition">Bab I</a>
                <a href="#bab2_anchor" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition">Bab II</a>
                <a href="#bab3_anchor" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition">Bab III</a>
                <a href="#bab4_anchor" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition">Bab IV</a>
                <a href="#bab5_anchor" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 border border-slate-200 transition">Bab V</a>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg shadow-xs text-xs font-medium text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Laporan Lengkap
                </button>
                <button onclick="copyText('full_master_report', this)" class="px-3 py-1.5 bg-blue-600 border border-blue-700 rounded-lg shadow-xs text-xs font-semibold text-white hover:bg-blue-700 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    Salin Seluruh Naskah (Bab I-V)
                </button>
            </div>
        </div>
    </div>

    <!-- Master Report Container -->
    <div id="full_master_report">

        <!-- ========================================== -->
        <!-- BAB I PENDAHULUAN                          -->
        <!-- ========================================== -->
        <div id="bab1_anchor" class="scroll-mt-20">
            <div id="chapter_1_paper" class="paper max-w-4xl mx-auto border border-slate-300 p-8 sm:p-12 text-slate-900 leading-relaxed font-serif rounded-lg mb-8">
                {inner_b1}
            </div>
        </div>

        {make_divider("Bab II (Tinjauan Pustaka)", "bab2_anchor")}

        <!-- ========================================== -->
        <!-- BAB II TINJAUAN PUSTAKA                    -->
        <!-- ========================================== -->
        <div class="scroll-mt-20">
            <div id="chapter_2_paper" class="paper max-w-4xl mx-auto border border-slate-300 p-8 sm:p-12 text-slate-900 leading-relaxed font-serif rounded-lg mb-8 page-break">
                {inner_b2}
            </div>
        </div>

        {make_divider("Bab III (Analisis dan Perancangan Sistem)", "bab3_anchor")}

        <!-- ========================================== -->
        <!-- BAB III ANALISIS DAN PERANCANGAN SISTEM   -->
        <!-- ========================================== -->
        <div class="scroll-mt-20">
            <div id="chapter_3_paper" class="paper max-w-4xl mx-auto border border-slate-300 p-8 sm:p-12 text-slate-900 leading-relaxed font-serif rounded-lg mb-8 page-break">
                {inner_b3}
            </div>
        </div>

        {make_divider("Bab IV (Hasil dan Pembahasan)", "bab4_anchor")}

        <!-- ========================================== -->
        <!-- BAB IV HASIL DAN PEMBAHASAN                -->
        <!-- ========================================== -->
        <div class="scroll-mt-20">
            <div id="chapter_4_paper" class="paper max-w-4xl mx-auto border border-slate-300 p-8 sm:p-12 text-slate-900 leading-relaxed font-serif rounded-lg mb-8 page-break">
                {inner_b4}
            </div>
        </div>

        {make_divider("Bab V (Kesimpulan dan Saran)", "bab5_anchor")}

        <!-- ========================================== -->
        <!-- BAB V KESIMPULAN DAN SARAN                -->
        <!-- ========================================== -->
        <div class="scroll-mt-20">
            <div id="chapter_5_paper" class="paper max-w-4xl mx-auto border border-slate-300 p-8 sm:p-12 text-slate-900 leading-relaxed font-serif rounded-lg page-break">
                {inner_b5}
            </div>
        </div>

    </div> <!-- Closes full_master_report -->

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-8 mb-12 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir Lengkap (Bab I s.d. Bab V) &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori <i>Omnichannel</i> ZenCare Medical
    </div>

</body>
</html>
"""

with open('laporan_ta/LAPORAN_TA_ZENCARE_MEDICAL_FULL.html', 'w', encoding='utf-8') as f:
    f.write(master_html)

print("Created laporan_ta/LAPORAN_TA_ZENCARE_MEDICAL_FULL.html successfully!")
