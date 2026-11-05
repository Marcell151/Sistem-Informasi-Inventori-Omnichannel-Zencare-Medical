# -*- coding: utf-8 -*-
with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

target = '''                        <p class="text-justify indent-8 mb-4">
                            Setelah lolos validasi hak akses, Superadmin menentukan pilihan jenis laporan kuantitas medis yang murni berfokus pada volume pergerakan fisik barang tanpa melibatkan perhitungan omset atau laba finansial, meliputi: Laporan Penjualan Omnichannel (komparasi volume penjualan kasir POS vs transaksi daring e-commerce); Laporan Persediaan &amp; Kartu Stok (ringkasan volume fisik total per unit); Laporan Logistik Medis &amp; Audit Deviasi FEFO (jejak serial number alkes, pemantauan obat mendekati batas kedaluwarsa, dan log anomali pengambilan kasir); serta Laporan Riwayat Penerimaan, Mutasi, dan Konversi UOM. Sistem memproses kueri agregasi basis data dan menampilkan data tabular di layar. Superadmin dapat meninjau data atau menekan tombol cetak untuk menerbitkan dokumen resmi berformat PDF sebagai arsip manajerial toko. Alur ini divisualisasikan pada Gambar 3.16.
                        </p>'''

replacement = '''                        <p class="text-justify indent-8 mb-4">
                            Setelah lolos validasi hak akses, Superadmin menentukan parameter penarikan data dari <b>6 (enam) modul pelaporan manajerial dan audit logistik</b> yang terintegrasi secara komprehensif dalam sistem, meliputi:
                        </p>
                        <ol class="list-decimal list-outside ml-12 mb-4 space-y-1.5 text-justify text-slate-700">
                            <li><b>Laporan Penjualan Omnichannel</b>: menyajikan komparasi volume pergerakan fisik barang keluar lintas saluran antara meja kasir toko fisik (<i>walk-in POS</i>) dan pesanan daring <i>e-commerce</i>, grafik tren transaksi harian/bulanan, serta kurasi produk terlaris (<i>Top 5</i>) secara objektif tanpa kalkulasi finansial laba/rugi.</li>
                            <li><b>Laporan Persediaan Stok</b>: memantau sisa kuantitas fisik riil persediaan di rak pajangan dan gudang penyimpanan, indikator batas persediaan minimum, serta status stok kritis (<i>Out of Stock</i> dan <i>Low Stock</i>).</li>
                            <li><b>Laporan Logistik Medis</b>: melacak keabsahan nomor batch aktif, tanggal kedaluwarsa (<i>expiration date</i>) komoditas obat berkaidah FEFO, dan jejak individual <i>Serial Number</i> (SN) alat kesehatan beserta masa berlaku garansi pabrik.</li>
                            <li><b>Laporan Penerimaan Barang</b>: mengaudit arus pasokan logistik masuk dari distributor rekanan, riwayat nomor surat jalan/faktur pemasok, tanggal terima fisik, dan verifikasi kuantitas awal.</li>
                            <li><b>Laporan Mutasi &amp; Konversi UOM</b>: mendokumentasikan rekapitulasi penyesuaian kuantitas fisik non-transaksional (barang rusak di etalase, obat kedaluwarsa, selisih hitung fisik, klaim retur pemasok) serta histori dekonstruksi kemasan bertingkat (<i>Bongkar Box</i> menjadi eceran <i>Sub-Batch .A</i>).</li>
                            <li><b>Laporan Audit Anomali FEFO</b>: merekam rekapitulasi deviasi/penyimpangan pengambilan nomor batch oleh kasir di meja POS (ketika pembeli mengambil fisik kemasan yang berbeda dari batch FEFO rekomendasi sistem) beserta nama kasir, waktu kejadian, nomor transaksi, dan justifikasi operasional guna kebutuhan audit kepatuhan tanpa mengorbankan kecepatan transaksi kasir.</li>
                        </ol>
                        <p class="text-justify indent-8 mb-4">
                            Berdasarkan kategori laporan yang dipilih dan filter data yang ditetapkan, sistem mengeksekusi kueri agregasi ke basis data persediaan dan menyajikan visualisasi data tabular di layar. Superadmin meninjau rekapitulasi fisik tersebut dan memiliki opsi untuk menekan tombol cetak guna menerbitkan berkas laporan resmi dalam format PDF sebagai dokumen arsip manajerial perusahaan. Alur aktivitas ini divisualisasikan secara lengkap pada Gambar 3.16.
                        </p>'''

if target in text:
    text = text.replace(target, replacement)
    with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
        f.write(text)
    print("SUCCESS: BAB_III_ZENCARE_MEDICAL.html updated for Point G!")
else:
    print("ERROR: Target text not found in BAB_III_ZENCARE_MEDICAL.html")

