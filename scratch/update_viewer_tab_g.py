# -*- coding: utf-8 -*-
with open('lain/semua_activity_diagram_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

target = '''                <p>
                    Setelah verifikasi berhasil, Superadmin memilih salah satu dari 4 modul laporan kuantitas fisik medis yang murni berfokus pada volume pergerakan fisik tanpa variabel finansial omzet:
                </p>
                <ol>
                    <li><b>Laporan Penjualan Omnichannel:</b> Membandingkan volume kuantitas fisik penjualan kasir POS vs transaksi daring e-commerce berdasarkan rentang tanggal.</li>
                    <li><b>Laporan Persediaan &amp; Kartu Stok:</b> Menampilkan rekapitulasi kuantitas fisik per produk dan pergerakan keluar-masuk barang secara komprehensif.</li>
                    <li><b>Laporan Logistik Medis &amp; Audit Deviasi FEFO:</b> Memantau daftar obat mendekati masa kedaluwarsa, rekapitulasi riwayat nomor seri alkes, serta audit log deviasi pengambilan anomali FEFO oleh kasir.</li>
                    <li><b>Laporan Riwayat Penerimaan, Mutasi, dan Konversi UOM:</b> Menampilkan histori pasokan barang dari pemasok dan penyesuaian stok manual.</li>
                </ol>'''

replacement = '''                <p>
                    Setelah verifikasi berhasil, Superadmin diberikan akses penuh ke <b>6 (enam) modul pelaporan manajerial dan audit logistik</b> yang murni berfokus pada volume pergerakan fisik tanpa variabel finansial omzet:
                </p>
                <ol>
                    <li><b>Laporan Penjualan Omnichannel:</b> Membandingkan volume kuantitas fisik penjualan kasir POS vs transaksi daring e-commerce, grafik tren pergerakan barang, serta kurasi produk terlaris (Top 5).</li>
                    <li><b>Laporan Persediaan Stok:</b> Memantau saldo fisik riil persediaan di rak pajangan dan gudang, indikator batas minimum stok, serta status persediaan kritis (Out of Stock / Low Stock).</li>
                    <li><b>Laporan Logistik Medis:</b> Melacak keabsahan nomor batch aktif, tanggal kedaluwarsa (ED) obat berkaidah FEFO, dan jejak individual Serial Number (SN) alat kesehatan beserta garansi.</li>
                    <li><b>Laporan Penerimaan Barang:</b> Mengaudit arus pasokan logistik masuk dari distributor rekanan, nomor surat jalan/faktur pemasok, tanggal terima fisik, dan kuantitas masuk.</li>
                    <li><b>Laporan Mutasi &amp; Konversi UOM:</b> Mendokumentasikan rekapitulasi penyesuaian fisik non-transaksional (rusak, kedaluwarsa, selisih hitung fisik, retur) serta histori konversi Bongkar Box ke Sub-Batch .A.</li>
                    <li><b>Laporan Audit Anomali FEFO:</b> Merekam rekapitulasi penyimpangan pengambilan batch oleh kasir di meja POS (saat pembeli mengambil fisik berbeda dari rekomendasi FEFO sistem) beserta justifikasi staf demi audit kepatuhan.</li>
                </ol>'''

if target in text:
    text = text.replace(target, replacement)
    with open('lain/semua_activity_diagram_zencare.html', 'w', encoding='utf-8') as f:
        f.write(text)
    print("SUCCESS: semua_activity_diagram_zencare.html updated for Tab G!")
else:
    print("ERROR: Target text not found in semua_activity_diagram_zencare.html")

