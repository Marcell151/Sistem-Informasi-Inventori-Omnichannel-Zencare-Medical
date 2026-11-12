import os
import re
import zipfile
import json
from finalize_italicization import process_html_document
from build_complete_50_bab4 import sections

# 1. Build Subbab 4.1.1 HTML block
html_sections = []
for sec in sections:
    sec_num = sec["num"]
    sec_title = sec["title"]
    sec_desc = sec["desc"]
    
    figs_html = []
    for fig_label, fig_title, img_fname, alt_text in sec["figures"]:
        f_html = f"""
                        <div class="my-4 p-3 bg-white border border-slate-300 rounded shadow-sm text-center">
                            <div class="max-w-4xl mx-auto border border-black p-1 bg-slate-50 mb-2 overflow-hidden">
                                <img src="../lain/File PPT/UI_UX_Asli/{img_fname}" alt="{alt_text}" class="w-full h-auto object-contain mx-auto" loading="lazy">
                            </div>
                            <p class="text-xs font-bold font-serif text-slate-900 mt-2">
                                {fig_label} {fig_title}
                            </p>
                        </div>"""
        figs_html.append(f_html)
    
    joined_figs = "\n".join(figs_html)
    sec_block = f"""
                    <div class="mt-6 mb-2">
                        <h5 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                            {sec_num} {sec_title}
                        </h5>
                    </div>
                    {joined_figs}
                    <div class="my-3 px-2">
                        <p class="text-xs font-serif text-slate-700 text-justify indent-8 leading-relaxed">
                            {sec_desc}
                        </p>
                    </div>"""
    html_sections.append(sec_block)

full_41_content = "\n".join(html_sections)

intro_text = """
                        <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                            Implementasi sistem merupakan tahap perwujudan dari rancangan logika basis data, alur proses bisnis, dan tata letak antarmuka pengguna yang telah dirumuskan pada Bab III ke dalam bentuk modul-modul fungsional perangkat lunak yang beroperasi secara utuh. Guna menyajikan gambaran operasional yang terstruktur, padu, dan mudah dipahami, dokumentasi implementasi sistem dikelompokkan ke dalam 25 (dua puluh lima) subbab fungsional yang berurutan sesuai alur kerja nyata: mulai dari autentikasi staf, operasional kasir dan transaksi penjualan omnichannel, manajemen master data dan logistik persediaan, siklus lengkap pemeriksaan fisik persediaan (stok opname), dashboard eksekutif dan seluruh lembar cetak laporan manajerial resmi, konfigurasi sistem, hingga portal toko daring (e-commerce) pelanggan. Pada setiap subbab, tampilan antarmuka interaktif disandingkan secara langsung dengan dokumen cetak fisik atau formulir operasional terkait agar alur kerja sistem terdokumentasi secara utuh dan terpadu. Rincian implementasi sistem diuraikan sebagai berikut:
                        </p>
"""

new_subbab_411 = f"""
                <!-- 4.1.1 Implementasi Sistem Terpadu -->
                <div class="mt-4">
                    <div class="flex items-center justify-between border-b pb-2 mb-4">
                        <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                            4.1.1 Implementasi Sistem Terpadu dan Alur Antarmuka Operasional
                        </h4>
                        <button onclick="copyText('subbab_411_content', this)" class="no-print px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-blue-200 transition flex items-center gap-1 font-sans font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            Salin Subbab 4.1.1
                        </button>
                    </div>

                    <div id="subbab_411_content">
{intro_text}
{full_41_content}
                    </div> <!-- Closes subbab_411_content -->
                </div> <!-- Closes 4.1.1 -->
"""

# Read existing BAB_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    orig_iv = f.read()

# Locate subbab 4.1.1 insertion point
start_marker = '<!-- 4.1.1 Implementasi Sistem Terpadu -->'
start_idx = orig_iv.find(start_marker)
if start_idx == -1:
    start_marker = '4.1.1 Implementasi Sistem Terpadu'
    start_idx = orig_iv.find(start_marker)
    start_idx = orig_iv.rfind('<div', 0, start_idx)

end_marker = '4.2 PENGUJIAN SISTEM'
end_idx = orig_iv.find(end_marker)
if end_idx != -1:
    end_idx = orig_iv.rfind('<div', 0, end_idx)

new_iv = orig_iv[:start_idx] + new_subbab_411 + "\n        </div> <!-- Closes subbab_41_content -->\n        </div> <!-- Closes 4.1 -->\n\n        " + orig_iv[end_idx:]

# Process italics on new_iv
new_iv_processed = process_html_document(new_iv)

with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_iv_processed)

print("Updated and italicized BAB_IV_ZENCARE_MEDICAL.html successfully!")

# Update BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    orig_iii_iv = f.read()

start_idx_34 = orig_iii_iv.find(start_marker)
if start_idx_34 == -1:
    start_marker_alt = '4.1.1 Implementasi Sistem Terpadu'
    start_idx_34 = orig_iii_iv.find(start_marker_alt)
    start_idx_34 = orig_iii_iv.rfind('<div', 0, start_idx_34)

end_idx_34 = orig_iii_iv.find(end_marker)
if end_idx_34 != -1:
    end_idx_34 = orig_iii_iv.rfind('<div', 0, end_idx_34)

new_iii_iv = orig_iii_iv[:start_idx_34] + new_subbab_411 + "\n        </div> <!-- Closes subbab_41_content -->\n        </div> <!-- Closes 4.1 -->\n\n        " + orig_iii_iv[end_idx_34:]
new_iii_iv_processed = process_html_document(new_iii_iv)

with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_iii_iv_processed)

print("Updated and italicized BAB_III_DAN_IV_ZENCARE_MEDICAL.html successfully!")

