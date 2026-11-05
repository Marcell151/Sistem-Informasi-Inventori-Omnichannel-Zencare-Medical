import re

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Update Mermaid diagram
mermaid_pattern = re.compile(r'<div class="mermaid text-left">.*?</div>', re.DOTALL)

new_mermaid = '''<div class="mermaid text-left">
                                flowchart LR
                                subgraph Boundary ["Sistem Informasi Inventori Omnichannel ZenCare Medical"]
                                UC1(["Autentikasi & Kelola Akun Profil"])
                                UC2(["Eksplorasi Katalog & Keranjang"])
                                UC3(["Checkout Pesanan"])
                                UC3_1(["Pembayaran Gateway"])
                                UC3_2(["Pilih Metode Pengambilan / Pengiriman"])
                                UC4(["Kelola Riwayat Pesanan"])

                                UC5(["Kelola Transaksi Kasir (POS)"])
                                UC5_1(["Riwayat Transaksi & Cetak Ulang Struk"])

                                UC6(["Kelola Master Data"])
                                UC6_1(["Master Satuan"])
                                UC6_2(["Master Supplier"])
                                UC6_3(["Master Produk"])
                                UC6_4(["Import Data Barang (Excel)"])

                                UC7(["Dashboard Analitik & Operasional"])

                                UC8(["Kelola Pesanan Online"])
                                UC8_1(["Batalkan Pesanan"])

                                UC9(["Kelola Penerimaan Barang"])
                                UC9_1(["Cetak Label"])
                                UC9_2(["Validasi Identitas Logistik (Batch/SN)"])

                                UC10(["Lakukan Mutasi Stok"])
                                UC11(["Kelola Stok Opname"])
                                UC12(["Akses Kartu Stok"])
                                UC13(["Kelola User"])
                                UC14(["Akses Laporan Kuantitas & Logistik"])
                                UC15(["Pengaturan Sistem (API & Web)"])

                                UC3 -.->|&lt;&lt;include&gt;&gt;| UC3_1
                                UC3 -.->|&lt;&lt;include&gt;&gt;| UC3_2
                                UC5_1 -.->|&lt;&lt;extend&gt;&gt;| UC5
                                UC6 -.->|&lt;&lt;include&gt;&gt;| UC6_1
                                UC6 -.->|&lt;&lt;include&gt;&gt;| UC6_2
                                UC6 -.->|&lt;&lt;include&gt;&gt;| UC6_3
                                UC6_4 -.->|&lt;&lt;extend&gt;&gt;| UC6_3
                                UC8_1 -.->|&lt;&lt;extend&gt;&gt;| UC8
                                UC9 -.->|&lt;&lt;include&gt;&gt;| UC9_1
                                UC9_2 -.->|&lt;&lt;extend&gt;&gt;| UC9
                                end

                                Pelanggan((Pelanggan)) --> UC1
                                Pelanggan --> UC2
                                Pelanggan --> UC3
                                Pelanggan --> UC4

                                Kasir((Kasir)) --> UC5

                                Admin((Admin Staff)) --> UC6
                                Admin --> UC7
                                Admin --> UC8
                                Admin --> UC9
                                Admin --> UC10
                                Admin --> UC11
                                Admin --> UC12

                                Superadmin((Superadmin)) --> UC5
                                Superadmin --> UC6
                                Superadmin --> UC7
                                Superadmin --> UC8
                                Superadmin --> UC9
                                Superadmin --> UC10
                                Superadmin --> UC11
                                Superadmin --> UC12
                                Superadmin --> UC13
                                Superadmin --> UC14
                                Superadmin --> UC15
                            </div>'''

html, count_m = mermaid_pattern.subn(new_mermaid, html, count=1)
print(f'Mermaid replaced: {count_m}')

# Update Admin Toko description: add Kelola Stok Opname between Lakukan Mutasi Stok and Akses Kartu Stok
target_admin = '''                                <li><b>Lakukan Mutasi Stok:</b> Mengeksekusi penyesuaian fisik persediaan secara manual
                                    dan pencatatan histori konversi satuan kemasan dus ke eceran, serta mengakomodasi
                                    pencatatan barang rusak di rak, obat kedaluwarsa, atau klaim garansi alat kesehatan
                                    berkala.</li>
                                <li><b>Akses Kartu Stok:</b> Memeriksa buku besar kartu stok (<i>stock ledger</i>) per'''

replacement_admin = '''                                <li><b>Lakukan Mutasi Stok:</b> Mengeksekusi penyesuaian fisik persediaan secara manual
                                    dan pencatatan histori konversi satuan kemasan dus ke eceran, serta mengakomodasi
                                    pencatatan barang rusak di rak, obat kedaluwarsa, atau klaim garansi alat kesehatan
                                    berkala.</li>
                                <li><b>Kelola Stok Opname:</b> Melaksanakan proses penghitungan dan pencacahan fisik persediaan di etalase dan rak penyimpanan (<i>blind count</i>) selama sesi stok opname aktif tanpa melihat angka kuantitas buku sistem guna menjamin integritas dan objektivitas verifikasi data persediaan.</li>
                                <li><b>Akses Kartu Stok:</b> Memeriksa buku besar kartu stok (<i>stock ledger</i>) per'''

if target_admin in html:
    html = html.replace(target_admin, replacement_admin)
    print('Admin Toko updated')
else:
    print('Target Admin not found')

# Update Superadmin description: add Kelola Stok Opname and update Otorisasi Master Data
target_super = '''                                <li><b>Otorisasi Master Data &amp; Kebijakan:</b> Memiliki wewenang mutlak dalam
                                    penetapan kebijakan harga jual produk (baik harga eceran kasir maupun harga grosir
                                    daring), penentuan nilai rasio konversi satuan kemasan pada <i>Multi-UOM</i>,
                                    pelaksanaan impor masal data produk baru dari lembar kerja Excel melalui relasi
                                    <i>&lt;&lt;extend&gt;&gt;</i>, serta otorisasi pembukaan dan penutupan sesi <i>Stok
                                        Opname</i> fisik (yang memicu penguncian transaksi global / <i>System Freeze
                                        Lock</i>, validasi selisih hitung fisik etalase, dan pengesahan Berita Acara
                                    Stok Opname bertandatangan resmi).</li>'''

replacement_super = '''                                <li><b>Otorisasi Master Data &amp; Kebijakan:</b> Memiliki wewenang mutlak dalam
                                    penetapan kebijakan harga jual produk (baik harga eceran kasir maupun harga grosir
                                    daring), penentuan nilai rasio konversi satuan kemasan pada <i>Multi-UOM</i>,
                                    serta pelaksanaan impor masal data produk baru dari lembar kerja Excel melalui relasi
                                    <i>&lt;&lt;extend&gt;&gt;</i>.</li>
                                <li><b>Kelola Stok Opname:</b> Memegang kendali manajerial mutlak dalam pembukaan sesi stok opname yang secara otomatis memicu penguncian transaksi global (<i>System Freeze Lock</i> pada kasir POS dan pemesanan daring), mengevaluasi dan memverifikasi deviasi selisih fisik vs sistem (varian minus/plus), melakukan rekonsiliasi penyesuaian stok buku, memberikan otorisasi penutupan sesi, serta mengesahkan dan mencetak dokumen resmi Berita Acara Stok Opname bertandatangan.</li>'''

if target_super in html:
    html = html.replace(target_super, replacement_super)
    print('Superadmin updated')
else:
    print('Target Superadmin not found')

with open('laporan_ta/BAB_III_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(html)
print('Done!')

