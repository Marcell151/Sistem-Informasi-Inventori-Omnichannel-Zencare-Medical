import re
import os

print("Assembling combined Bab III & Bab IV report...")

# 1. Read clean Bab III
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b3 = f.read()

pos_p3 = text_b3.find('id="full_bab3_content"')
pos_open3 = text_b3.find('>', pos_p3) + 1
pos_close3 = text_b3.find('</div> <!-- Closes full_bab3_content paper -->')
inner_b3 = text_b3[pos_open3:pos_close3].strip()
print(f"Bab III inner length: {len(inner_b3)}")

# 2. Read clean Bab IV
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_b4 = f.read()

pos_p4 = text_b4.find('id="full_bab4_content"')
pos_open4 = text_b4.find('>', pos_p4) + 1
pos_b4_end = text_b4.rfind('<!-- Bottom Notification')
pos_close4 = text_b4.rfind('</div>', 0, pos_b4_end)
inner_b4 = text_b4[pos_open4:pos_close4].strip()
print(f"Bab IV inner length: {len(inner_b4)}")

combined_html = f"""<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naskah Gabungan Bab III & Bab IV - ZenCare Medical</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
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
            
            .page-break {{
                page-break-before: always;
            }}
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
    <div class="max-w-4xl mx-auto mb-6 bg-white p-4 rounded-lg shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print sticky top-4 z-50">
        <div>
            <h1 class="text-sm font-bold text-slate-800">Naskah Tugas Akhir: Bab III & Bab IV</h1>
            <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Inventori <i>Omnichannel</i> - ZenCare Medical</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="BAB_I_ZENCARE_MEDICAL.html" class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded border border-slate-300 transition">Bab I</a>
            <a href="BAB_II_ZENCARE_MEDICAL.html" class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded border border-slate-300 transition">Bab II</a>
            <a href="BAB_III_ZENCARE_MEDICAL.html" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded border border-blue-200 font-semibold">Bab III</a>
            <a href="BAB_IV_ZENCARE_MEDICAL.html" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded border border-blue-200 font-semibold">Bab IV</a>
            <a href="BAB_V_ZENCARE_MEDICAL.html" class="px-2.5 py-1 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 rounded border border-slate-300 transition">Bab V</a>
            <a href="LAPORAN_TA_ZENCARE_MEDICAL_FULL.html" class="px-2.5 py-1 text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded border border-indigo-200 transition font-medium">Laporan Penuh</a>
            <button onclick="window.print()" class="px-2.5 py-1 text-xs bg-slate-800 hover:bg-slate-900 text-white rounded transition flex items-center gap-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak PDF
            </button>
        </div>
    </div>

    <!-- Paper Container Bab III -->
    <div id="full_bab3_content" class="paper max-w-4xl mx-auto p-8 sm:p-14 rounded-lg">
        {inner_b3}
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

    <!-- Paper Container Bab IV -->
    <div id="full_bab4_content" class="paper max-w-4xl mx-auto p-8 sm:p-14 rounded-lg mt-8 page-break">
        {inner_b4}
    </div> <!-- Closes full_bab4_content paper -->

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori <i>Omnichannel</i> ZenCare Medical
    </div>

</body>
</html>
"""

with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(combined_html)

print("Saved clean BAB_III_DAN_IV_ZENCARE_MEDICAL.html successfully!")

