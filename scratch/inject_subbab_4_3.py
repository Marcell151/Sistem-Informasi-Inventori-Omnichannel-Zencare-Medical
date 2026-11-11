# inject_subbab_4_3.py
import re

subbab_43_html = '''
            <!-- 4.3 PEMBAHASAN -->
            <div id="subbab_43" class="mt-8 pt-6 border-t border-slate-300">
                <h3 class="text-sm font-bold font-serif text-slate-900 uppercase tracking-wider mb-3">
                    4.3 PEMBAHASAN
                </h3>

                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Berdasarkan rangkaian perancangan, implementasi antarmuka, hingga pengujian fungsional modul-modul sistem informasi inventori dan penjualan <em>omnichannel</em> pada toko kesehatan ZenCare Medical, terdapat sejumlah temuan penting dan evaluasi mendalam terkait kinerja sistem dalam menjawab permasalahan operasional yang dihadapi toko kesehatan. Pembahasan ini berfokus pada efektivitas kendali integritas data melalui mekanisme <em>Freeze Lock</em> dan <em>Blind Count</em>, signifikansi efisiensi penyaringan anomali otomatis pada proses rekonsiliasi stok opname, serta keterpaduan penyesuaian mutasi persediaan dengan prinsip FEFO (<em>First Expired, First Out</em>).
                </p>

                <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                    4.3.1 Analisis Pengaruh Mekanisme Blind Count dan Freeze Lock terhadap Integritas Data
                </h4>
                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Penerapan mekanisme pembekuan transaksi (<em>Freeze Lock</em>) terbukti efektif dalam memitigasi risiko terjadinya <em>race condition</em> atau disparitas pencatatan persediaan. Pada operasional toko kesehatan yang memadukan penjualan langsung di kasir toko fisik dan etalase daring (<em>e-commerce</em>), penarikan stok barang yang terjadi secara simultan dengan proses pencacahan fisik berpotensi besar menghasilkan selisih semu (<em>false discrepancy</em>). Dengan diterapkannya <em>Freeze Lock</em>, seluruh kanal transaksi dikunci sementara secara otomatis sejak sesi opname dibuka oleh Superadmin, memastikan saldo sistem yang dijadikan acuan pembanding bersifat mutlak dan konsisten selama audit berlangsung.
                </p>
                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Sementara itu, pembagian hak akses pencacahan buta (<em>Blind Count</em>) bagi staf Kasir terbukti mampu meningkatkan objektivitas dan keakuratan penghitungan di lapangan. Dengan disembunyikannya data saldo sistem dan nilai selisih dari antarmuka Kasir, petugas diwajibkan melakukan pemindaian kode batang dan penghitungan fisik secara riil tanpa terpengaruh oleh angka teoretis yang tertera pada basis data. Hal ini secara efektif mengeliminasi kecenderungan staf untuk melakukan <em>confirmation bias</em> atau pembenaran angka secara sepihak, sehingga setiap penyusutan barang (<em>shrinkage</em>) maupun anomali penataan produk di rak toko dapat teridentifikasi secara transparan.
                </p>

                <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                    4.3.2 Evaluasi Efisiensi Penyaringan Otomatis (Auto-Filtering) Anomali Persediaan
                </h4>
                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Fitur penyaringan anomali otomatis yang diimplementasikan pada antarmuka verifikasi Admin memberikan peningkatan efisiensi kerja yang sangat signifikan. Pada pengelolaan inventori toko kesehatan dengan volume komoditas medis yang beragam—mencakup 121 entitas stok fisik aktif dari 53 jenis produk—metode audit konvensional umumnya menuntut verifikator untuk memeriksa setiap baris data satu per satu guna memastikan kecocokan stok. Beban kognitif dan waktu telaah yang dibutuhkan menjadi sangat tinggi seiring bertambahnya variasi produk dan nomor <em>batch</em>.
                </p>
                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Melalui logika <em>auto-filtering</em>, sistem secara mandiri mengidentifikasi dan menyembunyikan 116 entitas barang yang telah memiliki kesesuaian fisik 100% (selisih nol), dan secara cerdas hanya menyajikan 5 entitas produk yang mengalami anomali selisih kepada verifikator. Mekanisme ini memangkas waktu analisis rekapitulasi audit hingga lebih dari 90%, memungkinkan Admin untuk langsung mencurahkan perhatian pada investigasi penyebab selisih dan penentuan tindakan penyesuaian tanpa terdistraksi oleh tumpukan data barang yang sudah akurat.
                </p>

                <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide mb-2">
                    4.3.3 Akuntabilitas Pembebanan Selisih dan Keterpaduan Logistik FEFO
                </h4>
                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Penyediaan opsi kategorisasi beban mutasi pada jendela dialog verifikasi (mencakup beban barang rusak, kedaluwarsa, hilang, atau koreksi selisih lebih) berhasil menjamin akuntabilitas pembukuan persediaan barang medis. Alokasi jenis mutasi yang spesifik tidak hanya memulihkan keseimbangan kuantitas stok di sistem, melainkan juga secara otomatis mencatatkan rekam jejak audit (<em>audit trail</em>) yang terperinci ke dalam kartu stok digital dan tabel mutasi logistik.
                </p>
                <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                    Lebih lanjut, integrasi penyesuaian mutasi pada level nomor <em>batch</em> dan nomor seri (<em>serial number</em>) memperkuat penegakan aturan bisnis FEFO (<em>First Expired, First Out</em>). Ketika suatu <em>batch</em> obat mengalami kerusakan fisik atau kedaluwarsa, pengurangan stok dilakukan secara presisi pada <em>batch</em> yang bersangkutan, sehingga sistem tidak akan lagi menjadwalkan atau merekomendasikan <em>batch</em> tersebut untuk dikeluarkan pada transaksi kasir maupun pesanan daring. Begitu pula pada produk alat kesehatan dengan nomor seri unik, penghapusan unit yang rusak atau hilang langsung membekukan nomor seri terkait, sehingga garansi dan penelusuran purnajual alat kesehatan tetap valid dan terlindungi.
                </p>
            </div>
'''

target_tag = '<!-- Bottom Notification (No Print) -->'

for filepath in ['laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if 'id="subbab_43"' in content:
        print(f'Already has subbab 4.3 in {filepath}')
        continue
    
    # Insert before the closing </div> of paper container or before Bottom Notification
    pos = content.rfind('<!-- Bottom Notification (No Print) -->')
    if pos != -1:
        # Find the last </div> before target_tag
        div_pos = content.rfind('</div>', 0, pos)
        if div_pos != -1:
            new_content = content[:div_pos] + subbab_43_html + '\n        </div>' + content[div_pos + 6:]
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f'Successfully added Subbab 4.3 to {filepath}')
        else:
            print(f'Could not find div before tag in {filepath}')
    else:
        print(f'Could not find insertion point in {filepath}')

