import re
import os

def build_subbab_42_html():
    # Read the current UAT section from BAB_IV_ZENCARE_MEDICAL.html
    with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
        text = f.read()

    idx_422 = text.find('id="subbab_422"')
    assert idx_422 != -1, "subbab_422 not found!"
    
    # We want everything inside subbab_422_content
    idx_content_start = text.find('<div id="subbab_422_content">', idx_422)
    assert idx_content_start != -1, "subbab_422_content not found!"
    
    # Find the end of subbab_422
    # In BAB_IV_ZENCARE_MEDICAL.html, it ends before the bottom notification
    idx_end = text.find('<!-- Bottom Notification (No Print) -->')
    assert idx_end != -1, "Bottom Notification not found!"
    
    content_raw = text[idx_content_start + len('<div id="subbab_422_content">') : idx_end]
    
    # Strip trailing closing divs of subbab_422 and subbab_42
    # Let's count how many </div> are at the end
    content_raw = content_raw.rstrip()
    while content_raw.endswith('</div>'):
        content_raw = content_raw[:-6].rstrip()
        
    # Now replace in content_raw:
    # 1. DITERIMA -> BERHASIL
    content_raw = content_raw.replace('text-center font-bold text-emerald-700">DITERIMA</td>', 'text-center font-bold text-emerald-700">BERHASIL</td>')
    
    # 2. Table numbering:
    # Tabel 4.3 -> Tabel 4.1
    # Tabel 4.4 -> Tabel 4.2
    # Tabel 4.5 -> Tabel 4.3
    content_raw = content_raw.replace('Tabel 4.3 Profil Peran Penguji Sistem', 'Tabel 4.1 Profil Peran Penguji Sistem')
    content_raw = content_raw.replace('dirangkum pada Tabel 4.3.', 'dirangkum pada Tabel 4.1.')
    
    content_raw = content_raw.replace('Tabel 4.4 Matriks Hasil Pengujian Penerimaan Pengguna', 'Tabel 4.2 Matriks Hasil Pengujian Penerimaan Pengguna')
    content_raw = content_raw.replace('sebagaimana dirangkum pada Tabel 4.4.', 'sebagaimana dirangkum pada Tabel 4.2.')
    
    content_raw = content_raw.replace('Tabel 4.5 Hasil Penilaian Kuesioner Penerimaan Pengguna', 'Tabel 4.3 Hasil Penilaian Kuesioner Penerimaan Pengguna')
    content_raw = content_raw.replace('dirangkum pada Tabel 4.5.', 'dirangkum pada Tabel 4.3.')
    content_raw = content_raw.replace('Tabel 4.5:', 'Tabel 4.3:')

    # 3. Clean any mentions of "kotak hitam" in the UAT intro paragraph:
    # Replace: "Pengujian ini tidak berfokus pada logika sintaksis kode program sebagaimana pengujian kotak hitam, melainkan mengevaluasi..."
    old_intro_phrase = "Pengujian ini tidak berfokus pada logika sintaksis kode program sebagaimana pengujian kotak hitam, melainkan mengevaluasi kesesuaian alur kerja operasional, kemudahan antarmuka (*usability*), ketepatan logika penanganan persediaan medis berbasis <i>FEFO</i>, dan keandalan rekonsiliasi data lintas kanal."
    new_intro_phrase = "Pengujian ini berfokus langsung pada pembuktian kesesuaian alur kerja operasional nyata, kemudahan antarmuka (*usability*), ketepatan logika penanganan persediaan medis berbasis <i>FEFO</i>, keandalan mekanisme pembekuan stok opname (*freeze lock*), dan rekonsiliasi data persediaan lintas kanal."
    
    # Use flexible regex replace for the phrase
    content_raw = re.sub(
        r'Pengujian ini tidak berfokus pada logika sintaksis kode program sebagaimana pengujian kotak hitam, melainkan mengevaluasi kesesuaian alur kerja operasional, kemudahan antarmuka \(<i>usability</i>\), ketepatan logika penanganan persediaan medis berbasis <i>FEFO</i>, dan keandalan rekonsiliasi data lintas kanal\.',
        new_intro_phrase,
        content_raw
    )
    content_raw = re.sub(
        r'Pengujian ini tidak berfokus pada logika sintaksis kode program sebagaimana pengujian kotak hitam, melainkan mengevaluasi kesesuaian alur kerja operasional, kemudahan antarmuka \(\*usability\*\), ketepatan logika penanganan persediaan medis berbasis <i>FEFO</i>, dan keandalan rekonsiliasi data lintas kanal\.',
        new_intro_phrase,
        content_raw
    )

    # Build new subbab_42 block
    new_subbab_42 = f"""        <!-- 4.2 PENGUJIAN SISTEM (UAT) -->
        <div id="subbab_42" class="mt-8 pt-6 border-t border-slate-300">
            <div class="flex items-center justify-between border-b pb-2 mb-4">
                <h3 class="text-sm font-bold font-serif text-slate-900 uppercase tracking-wide">
                    4.2 PENGUJIAN SISTEM DENGAN METODE <i>USER ACCEPTANCE TESTING</i> (<i>UAT</i>)
                </h3>
                <button onclick="copyText('subbab_42_content', this)" class="no-print px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-blue-200 transition flex items-center gap-1 font-sans font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                    <span>Salin Subbab 4.2</span>
                </button>
            </div>

            <div id="subbab_42_content">
{content_raw}
            </div>
        </div>
"""
    return new_subbab_42

new_42 = build_subbab_42_html()

# Apply to BAB_IV_ZENCARE_MEDICAL.html
with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_bab4 = f.read()

idx_start = text_bab4.find('<div id="subbab_42"')
assert idx_start != -1
idx_end = text_bab4.find('<!-- Bottom Notification (No Print) -->')
assert idx_end != -1

updated_bab4 = text_bab4[:idx_start] + new_42 + "\n    " + text_bab4[idx_end:]

with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_bab4)
print("Updated laporan_ta\\BAB_IV_ZENCARE_MEDICAL.html")

# Apply to BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_comb = f.read()

idx_start_comb = text_comb.find('<div id="subbab_42"')
assert idx_start_comb != -1
idx_end_comb = text_comb.find('<!-- Bottom Notification (No Print) -->')
assert idx_end_comb != -1

updated_comb = text_comb[:idx_start_comb] + new_42 + "\n    " + text_comb[idx_end_comb:]

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(updated_comb)
print("Updated laporan_ta\\BAB_III_DAN_IV_ZENCARE_MEDICAL.html")

