kesimpulan_opsi1 = """Berdasarkan hasil analisis, perancangan, implementasi, dan pengujian yang telah dilakukan, dapat disimpulkan bahwa Sistem Informasi Inventori <em>Omnichannel</em> berbasis web pada ZenCare Medical berhasil dibangun sebagai sistem terpusat yang menyinkronkan data ketersediaan persediaan secara waktu nyata (<em>real-time</em>) antara terminal kasir fisik (<em>Point of Sale</em>) dan toko daring (<em>e-commerce</em>) mandiri. Integrasi ini terbukti efektif menghilangkan selisih pencatatan serta mencegah risiko pesanan ganda (<em>overselling</em>) melalui mekanisme reservasi stok otomatis. Fleksibilitas transaksi multi-satuan (<em>multi-UOM</em>) berhasil diwujudkan melalui fitur konversi pemecahan kemasan besar ke satuan eceran secara atomik dan akurat. Selain itu, pengendalian masa simpan obat berbasis aturan <em>First Expired, First Out</em> (FEFO) serta penelusuran nomor seri (<em>serial number</em>) alat kesehatan berhasil ditegakkan melalui verifikasi label fisik, pemindai <em>barcode</em>, dan notifikasi deteksi deviasi di meja kasir."""

saran_opsi1 = """Untuk pengembangan sistem pada masa mendatang, disarankan agar integrasi gerbang pembayaran digital (<em>payment gateway</em>) dan layanan kurir logistik ditingkatkan dari lingkungan pengujian (<em>sandbox</em>) ke tahap operasional langsung (<em>live production</em>) guna mendukung transaksi non-tunai dan pelacakan resi otomatis secara penuh. Selain itu, sistem dapat dilengkapi dengan fitur notifikasi otomatis melalui pesan instan untuk memberikan peringatan dini ketika persediaan mencapai batas minimum pemesanan kembali (<em>reorder point</em>) maupun saat komoditas mendekati tanggal kedaluwarsa, serta dirancang ke dalam aplikasi bergerak (<em>mobile</em>) berbasis nirkabel guna mempermudah mobilitas staf saat melakukan pencacahan fisik persediaan di rak toko."""

with open('laporan_ta/BAB_V_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Update 5.1 Kesimpulan
idx_51 = text.find('5.1 Kesimpulan')
assert idx_51 != -1
pos_p1_start = text.find('<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">', idx_51)
pos_p1_end = text.find('</p>', pos_p1_start) + 4

text = text[:pos_p1_start] + f'<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">\n                {kesimpulan_opsi1}\n            </p>' + text[pos_p1_end:]

# Update 5.2 Saran
idx_52 = text.find('5.2 Saran')
assert idx_52 != -1
pos_p2_start = text.find('<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">', idx_52)
pos_p2_end = text.find('</p>', pos_p2_start) + 4

text = text[:pos_p2_start] + f'<p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">\n                {saran_opsi1}\n            </p>' + text[pos_p2_end:]

with open('laporan_ta/BAB_V_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(text)

print("Applied Opsi 1 to BAB_V_ZENCARE_MEDICAL.html successfully!")

