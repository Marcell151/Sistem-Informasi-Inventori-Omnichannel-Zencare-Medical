# Patch inventori/stok_opname.php to remove all academic/dosen comments and buttons,
# and ensure clean corporate phrasing.

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Line 432
text = text.replace(
    '// --- 2B. AUTO-FILL SKENARIO DEMO UJI COBA (Khusus Superadmin, Pintasan Demo Bab 4) ---',
    '// --- 2B. AUTO-FILL SKENARIO PENGUJIAN (Khusus Superadmin / Simulasi Uji Coba) ---'
)

# 2. Line 486
text = text.replace(
    '// 1. CONTOH DOSEN (U4): Cefadroxil 500mg Kapsul (Dus/Box) -> Sistem 10: Baik 5, Rusak 3, Expired 2',
    '// 1. SKENARIO MULTI-KONDISI: Cefadroxil 500mg Kapsul (Dus/Box) -> Sistem 10: Baik 5, Rusak 3, Expired 2'
)

# 3. Line 1313
text = text.replace(
    '// ── LOGIKA FILTER DEFAULT (REVISI BIMBINGAN DOSEN PEMBIMBING) ─────────────',
    '// ── LOGIKA FILTER DEFAULT VERIFIKASI ANOMALI ─────────────'
)

# 4. Lines 1707 & 1708
text = text.replace(
    "<!-- Tombol Auto-Generate Skenario Uji Coba Bab 4 (Khusus Superadmin) -->\n                            <form method=\"POST\" class=\"inline\" onsubmit=\"return confirm('Jalankan Auto-Generate Skenario Uji Coba Bab 4? Sistem akan mengisi 121 baris fisik dengan 5 anomali terencana (Cefadroxil rusak, Sanmol expired, Amoxicillin hilang, Omron hilang, Betadine surplus) dan 116 item cocok.');\">\n                                <input type=\"hidden\" name=\"aksi\" value=\"isi_cepat_demo\">\n                                <button type=\"submit\" class=\"px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer\" title=\"Pintasan Uji Coba: Generate 121 hasil fisik dengan 5 anomali terencana\">\n                                    <svg class=\"w-3.5 h-3.5 text-slate-600\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M13 2L3 14h9l-1 8 10-12h-9l1-8z\"/></svg>\n                                    <span>Auto-Fill Skenario Uji Coba (121 Item)</span>\n                                </button>\n                            </form>",
    "<!-- Tombol Auto-Generate Skenario Pengujian (Khusus Superadmin) -->\n                            <form method=\"POST\" class=\"inline\" onsubmit=\"return confirm('Jalankan Pengisian Fisik Otomatis untuk Simulasi? Sistem akan mengisi 121 baris fisik dengan 5 anomali terencana (Cefadroxil rusak, Sanmol expired, Amoxicillin hilang, Omron hilang, Betadine surplus) dan 116 item cocok.');\">\n                                <input type=\"hidden\" name=\"aksi\" value=\"isi_cepat_demo\">\n                                <button type=\"submit\" class=\"px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer\" title=\"Pintasan Simulasi: 121 hasil fisik dengan 5 anomali terencana\">\n                                    <svg class=\"w-3.5 h-3.5 text-slate-600\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M13 2L3 14h9l-1 8 10-12h-9l1-8z\"/></svg>\n                                    <span>Simulasi Input Fisik Lengkap (121 Item)</span>\n                                </button>\n                            </form>"
)

# 5. Line 2112
text = text.replace(
    '<!-- Notice Otomatisasi Filter Anomali Sesuai Arahan Dosen -->',
    '<!-- Notice Otomatisasi Filter Anomali Selisih -->'
)

# 6. Line 2332
text = text.replace(
    '<!-- KATEGORISASI ALASAN MUTASI UTAMA (SESUAI ARAHAN DOSEN PEMBIMBING) -->',
    '<!-- KATEGORISASI ALASAN MUTASI UTAMA -->'
)

# 7. Line 2762
text = text.replace(
    '// Fungsi Penerapan Kategori Beban Selisih (Dropdown Mutasi) Sesuai Arahan Dosen',
    '// Fungsi Penerapan Kategori Beban Selisih (Dropdown Mutasi Penyesuaian)'
)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated inventori/stok_opname.php successfully!")

