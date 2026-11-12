import re

targets = [
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
]

new_opname_narrative = """Antarmuka pelaksanaan stok opname fisik persediaan berkala dengan segregasi peran operasional: form kasir menggunakan metode pencacahan buta (<i>blind count</i>) tanpa menampilkan saldo buku sistem yang dilengkapi 3 kolom kondisi fisik (Baik, Rusak, Expired) serta tombol simpan parsial bertahap; antarmuka verifikasi Admin Toko menampilkan ringkasan metrik rekonsiliasi, penyaringan status hitung, modul eksekusi mutasi selisih (rusak, expired, hilang, kelebihan kirim), serta verifikasi nomor seri fisik alat kesehatan; sedangkan Superadmin berwenang menginisiasi pembekuan transaksi sementara (<i>freeze lock</i>) pada kasir POS dan penjualan daring, pembukaan kembali sesi operasional, serta penerbitan dokumen resmi Berita Acara Opname."""

for fpath in targets:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    # Intro subbab 3.6
    text = text.replace('kepatuhan audit logistik kesehatan', 'kepatuhan pengendalian logistik kesehatan')
    text = text.replace('panel audit manajerial', 'panel pengawasan manajerial')

    # 9. Kartu Stok
    text = text.replace('9. Kartu Stok Audit', '9. Kartu Stok Persediaan')
    text = text.replace('Gambar 3.28 Wireframe Antarmuka Kartu Stok Audit', 'Gambar 3.28 Wireframe Antarmuka Kartu Stok Persediaan')
    text = text.replace('alt="Wireframe Antarmuka Kartu Stok Audit"', 'alt="Wireframe Antarmuka Kartu Stok Persediaan"')
    text = text.replace('keterlacakan audit persediaan', 'keterlacakan mutasi persediaan')

    # 10. Stok Opname
    text = text.replace('10. Stok Opname & Audit Fisik', '10. Stok Opname Persediaan')
    text = text.replace('Gambar 3.29 Wireframe Antarmuka Stok Opname & Audit Fisik', 'Gambar 3.29 Wireframe Antarmuka Stok Opname Persediaan')
    text = text.replace('alt="Wireframe Antarmuka Stok Opname & Audit Fisik"', 'alt="Wireframe Antarmuka Stok Opname Persediaan"')
    
    # Replace the narrative for Gambar 3.29
    # Match the paragraph under Gambar 3.29
    pattern_p = r'(<p class="text-xs font-bold font-serif text-slate-900 mt-2">\s*Gambar 3\.29 Wireframe Antarmuka Stok Opname Persediaan\s*</p>\s*<p class="text-xs font-serif text-slate-700 text-justify mt-2 indent-8 leading-relaxed px-2">)(.*?)(</p>)'
    text = re.sub(pattern_p, r'\g<1>\n                        ' + new_opname_narrative + r'\n                    \g<3>', text, flags=re.DOTALL)

    # 7. Laporan Anomali FEFO
    text = text.replace('Pusat audit manajerial', 'Pusat pengawasan manajerial')
    text = text.replace('7. Laporan Audit Anomali FEFO', '7. Laporan Anomali FEFO')
    text = text.replace('Gambar 3.37 Wireframe Antarmuka Laporan Audit Anomali FEFO', 'Gambar 3.37 Wireframe Antarmuka Laporan Anomali FEFO')
    text = text.replace('alt="Wireframe Antarmuka Laporan Audit Anomali FEFO"', 'alt="Wireframe Antarmuka Laporan Anomali FEFO"')
    text = text.replace('Laporan audit kepatuhan kasir', 'Laporan evaluasi kepatuhan kasir')

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Updated {fpath} successfully!")

