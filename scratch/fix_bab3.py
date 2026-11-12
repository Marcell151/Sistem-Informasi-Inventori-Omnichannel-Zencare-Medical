import re
import os

with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

# 1. Truncate after Bab III closing paper tag
end_marker = "</div> <!-- Closes full_bab3_content paper -->"
idx_end = text.find(end_marker)
assert idx_end != -1, "Cannot find end marker"

bab3_content = text[:idx_end + len(end_marker)]

# 2. Replace Gambar 3.1 to 3.4 containers with clean img tags
replacements = [
    (
        r'<!-- Diagram Gambar 3\.1 -->.*?</p>\s*</div>',
        '''<!-- Diagram Gambar 3.1 -->
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded-lg shadow-sm inline-block max-w-full">
                                <img src="../lain/File PPT/Workflow/Workflow Barang Keluar (Berjalan).png" alt="Workflow Barang Keluar (Berjalan)" class="max-w-full h-auto mx-auto rounded" />
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-3"><b>Gambar 3.1</b> Workflow Barang Keluar (Berjalan)</p>
                        </div>'''
    ),
    (
        r'<!-- Diagram Gambar 3\.2 -->.*?</p>\s*</div>',
        '''<!-- Diagram Gambar 3.2 -->
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded-lg shadow-sm inline-block max-w-full">
                                <img src="../lain/File PPT/Workflow/Workflow Barang Masuk (Berjalan).png" alt="Workflow Barang Masuk (Berjalan)" class="max-w-full h-auto mx-auto rounded" />
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-3"><b>Gambar 3.2</b> Workflow Barang Masuk (Berjalan)</p>
                        </div>'''
    ),
    (
        r'<!-- Diagram Gambar 3\.3 -->.*?</p>\s*</div>',
        '''<!-- Diagram Gambar 3.3 -->
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded-lg shadow-sm inline-block max-w-full">
                                <img src="../lain/File PPT/Workflow/Workflow Barang Keluar (Sistem Usulan).png" alt="Workflow Barang Keluar (Sistem Usulan)" class="max-w-full h-auto mx-auto rounded" />
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-3"><b>Gambar 3.3</b> Workflow Barang Keluar (Sistem Usulan)</p>
                        </div>'''
    ),
    (
        r'<!-- Diagram Gambar 3\.4 -->.*?</p>\s*</div>',
        '''<!-- Diagram Gambar 3.4 -->
                        <div class="diagram-container text-center mb-6">
                            <div class="overflow-x-auto p-4 bg-white border border-slate-200 rounded-lg shadow-sm inline-block max-w-full">
                                <img src="../lain/File PPT/Workflow/Workflow Barang Masuk & Logistik (Sistem Usulan).png" alt="Workflow Barang Masuk & Logistik (Sistem Usulan)" class="max-w-full h-auto mx-auto rounded" />
                            </div>
                            <p class="text-xs text-slate-500 font-sans mt-3"><b>Gambar 3.4</b> Workflow Barang Masuk (Sistem Usulan)</p>
                        </div>'''
    )
]

for pat, repl in replacements:
    match = re.search(pat, bab3_content, re.DOTALL)
    assert match, f"Pattern not matched: {pat}"
    bab3_content = re.sub(pat, repl, bab3_content, count=1, flags=re.DOTALL)

# 3. Add clean footer and closing scripts
footer_html = """

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori <i>Omnichannel</i> ZenCare Medical
    </div>

    <script>
        function copyText(elementId, btn) {
            const el = document.getElementById(elementId);
            if (!el) return;
            const text = el.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="text-emerald-600 font-bold">Tersalin!</span>';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        }
        
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof mermaid !== 'undefined') {
                mermaid.initialize({
                    startOnLoad: true,
                    theme: 'default',
                    flowchart: { useMaxWidth: true, htmlLabels: true, curve: 'basis' },
                    securityLevel: 'loose'
                });
            }
        });
    </script>
</body>
</html>
"""

final_bab3 = bab3_content + footer_html

with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "w", encoding="utf-8") as f:
    f.write(final_bab3)

print("BAB_III_ZENCARE_MEDICAL.html updated successfully!")

