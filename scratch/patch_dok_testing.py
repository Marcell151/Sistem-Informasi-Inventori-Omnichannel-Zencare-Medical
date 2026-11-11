# Update dokumentasi_testing.php to remove bab IV numbering and refine step-by-step visuals
import re

with open('inventori/dokumentasi_testing.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update header badge and titles
content = content.replace("BAB IV. PENGUJIAN SISTEM (BLACK-BOX TESTING)", "DOKUMENTASI PENGUJIAN SISTEM (BLACK-BOX TESTING)")
content = content.replace("Dokumentasi Pengujian Black-Box: Modul Stok Opname Fisik &amp; Rekonsiliasi Inventori", "Dossier Hasil Pengujian Black-Box: Modul Stok Opname &amp; Rekonsiliasi Inventori")
content = content.replace("4.2.1 Metodologi &amp; Prinsip Rekayasa Pengujian (Black-Box Testing)", "Metodologi &amp; Prinsip Rekayasa Pengujian (Black-Box Testing)")
content = content.replace("4.2.2 Tabel Matriks Pengujian Black-Box (Uji Coba Fungsional Inti)", "Tabel Matriks Hasil Pengujian Black-Box Modul Stok Opname")
content = content.replace("Tabel 4.1 Dokumen Laporan Tugas Akhir", "Matriks Rekaman Uji Coba Black-Box Fungsional")
content = content.replace("4.2.3 Pembuktian Tangkapan Layar Pelaksanaan Pengujian Langkah demi Langkah", "Rekaman Visual Pengujian Langkah demi Langkah (Step-by-Step Testing Log)")

# Add note in header
old_sub = '<p class="text-xs text-zcMut mt-1 leading-relaxed">\n                    Sistem Informasi Inventori dan Penjualan Omnichannel Toko Kesehatan <strong>ZenCare Medical</strong>\n                </p>'
new_sub = '''<p class="text-xs text-zcMut mt-1 leading-relaxed">
                    Sistem Informasi Inventori dan Penjualan Omnichannel Toko Kesehatan <strong>ZenCare Medical</strong>
                </p>
                <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[11px] text-slate-600">
                    <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span><strong>Catatan Pengujian:</strong> Halaman ini memuat rekaman dokumentasi jejak langkah uji coba (audit trial) fungsionalitas sistem secara nyata (Black-Box Testing) untuk verifikasi teknis dan demonstrasi sidang, bukan bagian bab naskah tugas akhir formal.</span>
                </div>'''

if old_sub in content:
    content = content.replace(old_sub, new_sub, 1)

# Check for reset database label
content = content.replace("<span>Reset Database (Clean State)</span>", "<span>[Utilitas Demo] Reset Database (Baseline Bersih)</span>")

with open('inventori/dokumentasi_testing.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated titles and notices in dokumentasi_testing.php")

