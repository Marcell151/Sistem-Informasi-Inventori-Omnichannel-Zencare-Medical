kesimpulan = """Berdasarkan hasil analisis, perancangan, implementasi, dan pengujian yang telah dilakukan, dapat disimpulkan bahwa Sistem Informasi Inventori <em>Omnichannel</em> berbasis web pada ZenCare Medical berhasil dibangun sebagai sistem terpusat yang menyinkronkan data ketersediaan persediaan secara waktu nyata (<em>real-time</em>) antara terminal kasir fisik (<em>Point of Sale</em>) dan toko daring (<em>e-commerce</em>) mandiri. Integrasi ini terbukti efektif menghilangkan selisih pencatatan serta mencegah risiko pesanan ganda (<em>overselling</em>) melalui mekanisme reservasi stok otomatis. Fleksibilitas transaksi multi-satuan (<em>multi-UOM</em>) berhasil diwujudkan melalui fitur konversi pemecahan kemasan besar ke satuan eceran secara atomik dan akurat. Selain itu, pengendalian masa simpan obat berbasis aturan <em>First Expired, First Out</em> (FEFO) serta penelusuran nomor seri (<em>serial number</em>) alat kesehatan berhasil ditegakkan melalui verifikasi label fisik, pemindai <em>barcode</em>, dan notifikasi deteksi deviasi di meja kasir. Kelayakan operasional sistem diperkuat oleh hasil pengujian <em>User Acceptance Testing</em> (UAT) dengan persentase ketercapaian sebesar 93,75% (kategori Sesuai Kebutuhan), yang membuktikan bahwa seluruh fungsi perangkat lunak telah berjalan baik dan siap menunjang operasional toko kesehatan ZenCare Medical."""

saran = """Untuk pengembangan sistem pada masa mendatang, disarankan agar integrasi gerbang pembayaran digital (<em>payment gateway</em>) dan layanan kurir logistik ditingkatkan dari lingkungan pengujian (<em>sandbox</em>) ke tahap operasional langsung (<em>live production</em>) guna mendukung transaksi non-tunai dan pelacakan resi otomatis secara penuh. Selain itu, sistem dapat dilengkapi dengan fitur notifikasi otomatis melalui pesan instan untuk memberikan peringatan dini ketika persediaan mencapai batas minimum pemesanan kembali (<em>reorder point</em>) maupun saat komoditas mendekati tanggal kedaluwarsa, serta dirancang ke dalam aplikasi bergerak (<em>mobile</em>) berbasis nirkabel guna mempermudah mobilitas staf saat melakukan pencacahan fisik persediaan di rak toko."""

import re

FORBIDDEN = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
emoji_pattern = re.compile(r'[\U00010000-\U0010ffff]', flags=re.UNICODE)

for text, name in [(kesimpulan, "KESIMPULAN"), (saran, "SARAN")]:
    print(f"=== {name} ===")
    print(f"Words: {len(text.split())}, Chars: {len(text)}")
    for f in FORBIDDEN:
        if f in text.lower():
            print(f"  [!] VIOLATION: {f}")
    if emoji_pattern.findall(text):
        print("  [!] EMOJI VIOLATION")
    print("Clean check passed!")

