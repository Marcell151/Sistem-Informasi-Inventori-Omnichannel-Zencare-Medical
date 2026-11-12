import re
import os
import sys

sys.path.append('scratch')
from finalize_italicization import process_html_document

# Content of enriched subbab 4.2.2
uat_html_snippet = """id="subbab_422" class="mt-6">
                <div class="flex items-center justify-between border-b pb-2 mb-4">
                    <h4 class="text-xs font-bold font-serif text-slate-900 uppercase tracking-wide">
                        4.2.2 Pengujian Penerimaan Pengguna (<i>User Acceptance Testing</i> / <i>UAT</i>)
                    </h4>
                    <button onclick="copyText('subbab_422_content', this)" class="no-print px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-blue-200 transition flex items-center gap-1 font-sans font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        <span>Salin Subbab 4.2.2</span>
                    </button>
                </div>

                <div id="subbab_422_content">
                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Pengujian Penerimaan Pengguna (<em>User Acceptance Testing</em> / <i>UAT</i>) merupakan tahapan pengujian akhir yang bertujuan untuk membuktikan bahwa Sistem Informasi Inventori dan Penjualan <em>Omnichannel</em> ZenCare Medical telah memenuhi spesifikasi kebutuhan pengguna (*user requirements*) dan siap dioperasikan dalam lingkungan bisnis nyata. Pengujian ini tidak berfokus pada logika sintaksis kode program sebagaimana pengujian kotak hitam, melainkan mengevaluasi kesesuaian alur kerja operasional, kemudahan antarmuka (*usability*), ketepatan logika penanganan persediaan medis berbasis <i>FEFO</i>, dan keandalan rekonsiliasi data lintas kanal.
                    </p>

                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Pengujian dilaksanakan langsung di gerai operasional toko kesehatan ZenCare Medical pada tanggal 12 November 2026 dengan melibatkan 4 (empat) peran operasional yang mencakup seluruh tingkatan wewenang sistem: <strong>Pemilik Toko</strong> (pemegang hak akses <em>superadmin</em>), <strong>Admin Toko</strong> (pemegang hak akses <em>admin</em> logistik dan pengadaan), <strong>Kasir Toko</strong> (pemegang hak akses <em>kasir</em> gerai fisik), serta <strong>Pelanggan Toko Daring</strong> (konsumen pengguna etalase <em>e-commerce</em>).
                    </p>

                    <!-- Tabel Profil Responden Penguji -->
                    <div class="my-4">
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel 4.3 Profil Peran Penguji Sistem (<i>UAT Respondents</i>)
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-10">No</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-44">Peran Penguji</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-28">Hak Akses Sistem</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-48">Tanggung Jawab Operasional</th>
                                        <th class="border border-slate-400 px-2 py-1.5">Fokus Evaluasi Fungsionalitas Modul</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">1</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Pemilik Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">superadmin</td>
                                        <td class="border border-slate-300 px-2 py-1">Pimpinan manajerial tertinggi dan pemilik modal usaha toko kesehatan.</td>
                                        <td class="border border-slate-300 px-2 py-1">Dashboard eksekutif, inisiasi dan penutupan <i>stok opname</i>, pembatalan darurat, penetapan hak akses, rekapitulasi laba kotor/bersih.</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">2</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Admin Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">admin</td>
                                        <td class="border border-slate-300 px-2 py-1">Pengelolaan persediaan harian, logistik pasokan, konversi kemasan, dan verifikasi opname.</td>
                                        <td class="border border-slate-300 px-2 py-1">Master produk &amp; multi-UOM, penerimaan barang masuk, histori konversi satuan, verifikasi selisih opname, kartu stok, laporan persediaan.</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">3</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Kasir Toko</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">kasir</td>
                                        <td class="border border-slate-300 px-2 py-1">Pelayanan transaksi penjualan luring di meja kasir dan pencacahan stok fisik rak etalase.</td>
                                        <td class="border border-slate-300 px-2 py-1">Terminal kasir POS luring, pemindaian <i>barcode</i>, alokasi stok <i>FEFO</i> otomatis, cetak struk termal, pencacahan fisik rak (<i>Blind Count</i>).</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">4</td>
                                        <td class="border border-slate-300 px-2 py-1 font-semibold">Pelanggan Toko Daring</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">pelanggan</td>
                                        <td class="border border-slate-300 px-2 py-1">Pengguna eksternal konsumen toko online kesehatan ZenCare Medical.</td>
                                        <td class="border border-slate-300 px-2 py-1">Katalog produk daring, pencarian obat/alkes, keranjang belanja, proses <i>checkout</i> pesanan, opsi pengiriman kurir logistik / ambil langsung di toko.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Metodologi pengujian penerimaan pengguna dirancang komprehensif ke dalam 2 (dua) instrumen pengukuran:
                    </p>

                    <ol class="list-decimal pl-10 mb-4 space-y-1.5 text-xs font-serif text-slate-800 leading-relaxed">
                        <li>
                            <strong>Matriks Skenario Uji Penerimaan Fitur (<i>UAT Scenario Checklist</i>):</strong> Terdiri atas 36 skenario uji operasional yang mencakup seluruh 8 bagian modul sistem informasi ZenCare Medical. Responden mengeksekusi setiap langkah prosedur kerja dan memberikan status penerimaan (*Diterima / Ditolak*).
                        </li>
                        <li>
                            <strong>Kuesioner Penerimaan Skala Likert (<i>Likert Acceptance Questionnaire</i>):</strong> Terdiri atas 12 butir pernyataan evaluasi kepuasan sistem dengan rentang skala 1 hingga 5 (Sangat Tidak Setuju s.d. Sangat Setuju) guna mengukur persentase kelayakan penerapan aplikasi secara matematis.
                        </li>
                    </ol>

                    <!-- Tabel Matriks Skenario UAT -->
                    <div class="my-4">
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel 4.4 Matriks Hasil Pengujian Penerimaan Pengguna (<i>UAT</i>) per Bagian Modul
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-12">Kode</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-36">Fitur yang Diuji</th>
                                        <th class="border border-slate-400 px-2 py-1.5">Aktivitas Prosedur Pengujian</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-52">Hasil Pengujian yang Diharapkan</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-20">Status</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-24">Penguji</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Bagian I -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian I: Modul Autentikasi Staf, Kontrol Hak Akses (RBAC), dan Pengaturan Sistem
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-01</td>
                                        <td class="border border-slate-300 px-2 py-1">Login Multi-Peran</td>
                                        <td class="border border-slate-300 px-2 py-1">Memasukkan kredensial Kasir Toko, Admin Toko, dan Pemilik Toko.</td>
                                        <td class="border border-slate-300 px-2 py-1">Sistem mengarahkan pengguna ke terminal/dashboard sesuai wewenang peran secara presisi.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir / Admin</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-02</td>
                                        <td class="border border-slate-300 px-2 py-1">Proteksi RBAC</td>
                                        <td class="border border-slate-300 px-2 py-1">Kasir Toko mencoba mengakses URL manajemen pengguna melalui bilah alamat peramban.</td>
                                        <td class="border border-slate-300 px-2 py-1">Akses ditolak (*Access Denied* / HTTP 403) dan dialihkan kembali ke terminal kasir.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-03</td>
                                        <td class="border border-slate-300 px-2 py-1">Manajemen Pengguna</td>
                                        <td class="border border-slate-300 px-2 py-1">Pemilik Toko mendaftarkan akun staf baru dan melakukan pengaturan ulang sandi.</td>
                                        <td class="border border-slate-300 px-2 py-1">Akun baru tersimpan dengan enkripsi hash aman dan dapat langsung digunakan masuk sistem.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pemilik Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-04</td>
                                        <td class="border border-slate-300 px-2 py-1">Pengaturan Toko</td>
                                        <td class="border border-slate-300 px-2 py-1">Mengubah identitas toko, nomor kontak resmi gerai, dan teks pengumuman beranda *e-commerce*.</td>
                                        <td class="border border-slate-300 px-2 py-1">Identitas toko terbaharui secara dinamis pada *header* faktur dan beranda toko *online*.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pemilik Toko</td>
                                    </tr>

                                    <!-- Bagian II -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian II: Modul Master Produk Medis, Konfigurasi Multi-UOM, dan Rekanan Pemasok
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-05</td>
                                        <td class="border border-slate-300 px-2 py-1">Master Produk Induk</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin Toko menginput komoditas induk baru dengan batas buffer stock pengingat.</td>
                                        <td class="border border-slate-300 px-2 py-1">Data produk induk tersimpan pada tabel <code>produk_induk</code> dan tampil pada direktori katalog.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-06</td>
                                        <td class="border border-slate-300 px-2 py-1">Variasi Multi-UOM</td>
                                        <td class="border border-slate-300 px-2 py-1">Menetapkan satuan besar (Box) dan satuan kecil (Strip) beserta rasio pengali 1:10.</td>
                                        <td class="border border-slate-300 px-2 py-1">Rasio kemasan terkunci konsisten sebagai dasar kalkulasi mutasi dan harga jual.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-07</td>
                                        <td class="border border-slate-300 px-2 py-1">Konversi Satuan UOM</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin mengeksekusi pemecahan 2 Box Amoxicillin menjadi 20 Strip eceran di formulir konversi.</td>
                                        <td class="border border-slate-300 px-2 py-1">Saldo Box berkurang 2, saldo Strip bertambah 20, nomor *batch* terwariskan, kartu stok terhubung.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-08</td>
                                        <td class="border border-slate-300 px-2 py-1">Histori Konversi</td>
                                        <td class="border border-slate-300 px-2 py-1">Memeriksa catatan log konversi satuan pada tabel riwayat pemecahan kemasan.</td>
                                        <td class="border border-slate-300 px-2 py-1">Menampilkan tanggal, operator, kuantitas asal/hasil, dan nomor *batch* secara kronologis.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-09</td>
                                        <td class="border border-slate-300 px-2 py-1">Direktori Supplier</td>
                                        <td class="border border-slate-300 px-2 py-1">Mendaftarkan mitra distributor resmi baru mencakup legalitas, narahubung, dan alamat.</td>
                                        <td class="border border-slate-300 px-2 py-1">Data tersimpan dan dapat langsung dipilih pada formulir penerimaan pasokan logistik.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>

                                    <!-- Bagian III -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian III: Modul Terminal Kasir Luring (Point of Sale / POS) dan Alokasi FEFO
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-10</td>
                                        <td class="border border-slate-300 px-2 py-1">Scan Barcode POS</td>
                                        <td class="border border-slate-300 px-2 py-1">Kasir memindai *barcode* kemasan produk atau mengetikkan nama obat di meja kasir.</td>
                                        <td class="border border-slate-300 px-2 py-1">Produk masuk keranjang belanja kasir secara instan dengan kuantitas dan satuan yang tepat.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-11</td>
                                        <td class="border border-slate-300 px-2 py-1">Otomasi FEFO Kasir</td>
                                        <td class="border border-slate-300 px-2 py-1">Kasir memproses penjualan obat yang memiliki multi-*batch* masa simpan berbeda.</td>
                                        <td class="border border-slate-300 px-2 py-1">Sistem otomatis memotong persediaan dari *batch* yang kedaluwarsanya paling dekat (*FEFO*).</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-12</td>
                                        <td class="border border-slate-300 px-2 py-1">Deteksi Anomali FEFO</td>
                                        <td class="border border-slate-300 px-2 py-1">Kasir sengaja memilih *batch* yang kedaluwarsanya lebih lama atas permintaan pembeli.</td>
                                        <td class="border border-slate-300 px-2 py-1">Sistem mendeteksi deviasi, meminta alasan kasir, dan mencatatnya ke Laporan Anomali FEFO.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-13</td>
                                        <td class="border border-slate-300 px-2 py-1">Kalkulasi Kembalian</td>
                                        <td class="border border-slate-300 px-2 py-1">Kasir menginput jumlah uang tunai pembeli pada kalkulator transaksi kasir.</td>
                                        <td class="border border-slate-300 px-2 py-1">Subtotal tagihan dan uang kembalian dihitung secara akurat tanpa kekeliruan pembulatan.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-14</td>
                                        <td class="border border-slate-300 px-2 py-1">Cetak Struk Termal</td>
                                        <td class="border border-slate-300 px-2 py-1">Mencetak struk pembayaran transaksi luring kasir pada printer kertas termal 58mm/80mm.</td>
                                        <td class="border border-slate-300 px-2 py-1">Struk tercetak rapi memuat faktur, rincian produk, nomor *batch*/ED, dan rincian bayar.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir Toko</td>
                                    </tr>

                                    <!-- Bagian IV -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian IV: Modul Toko Daring (E-Commerce) dan Pengelolaan Penjualan Omnichannel
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-15</td>
                                        <td class="border border-slate-300 px-2 py-1">Registrasi Pelanggan</td>
                                        <td class="border border-slate-300 px-2 py-1">Pelanggan mendaftar akun baru toko *online* dan melakukan proses *login*.</td>
                                        <td class="border border-slate-300 px-2 py-1">Akun pelanggan terbentuk aktif dan sesi belanja daring berjalan independen tanpa bentrok.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pelanggan</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-16</td>
                                        <td class="border border-slate-300 px-2 py-1">Keranjang Belanja</td>
                                        <td class="border border-slate-300 px-2 py-1">Pelanggan memilih komoditas pada katalog daring dan memasukkannya ke keranjang belanja.</td>
                                        <td class="border border-slate-300 px-2 py-1">Katalog menampilkan foto produk, harga satuan kecil, dan stok riil secara akurat.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pelanggan</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-17</td>
                                        <td class="border border-slate-300 px-2 py-1">Checkout &amp; Kurir</td>
                                        <td class="border border-slate-300 px-2 py-1">Pelanggan menyelesaikan *checkout*, memilih kurir ekspedisi atau opsi ambil di gerai fisik.</td>
                                        <td class="border border-slate-300 px-2 py-1">Nomor tagihan terbit, ongkir terhitung otomatis, stok reservasi barang terpotong sementara.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pelanggan</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-18</td>
                                        <td class="border border-slate-300 px-2 py-1">Pesanan Omnichannel</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin meninjau antrean pesanan daring masuk dan memperbarui status pemrosesan.</td>
                                        <td class="border border-slate-300 px-2 py-1">Status pesanan tersinkronisasi dari Menunggu Pembayaran hingga Siap Kirim secara terpadu.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-19</td>
                                        <td class="border border-slate-300 px-2 py-1">Cetak Faktur &amp; Label</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin mencetak Faktur Penjualan A4 dan Label Alamat Pengiriman Kurir e-commerce.</td>
                                        <td class="border border-slate-300 px-2 py-1">Faktur memuat legalitas toko; label alamat kurir memuat *barcode* resi dan alamat lengkap.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>

                                    <!-- Bagian V -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian V: Modul Logistik Penerimaan Pasokan Barang Masuk dan Penandaan Barcode
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-20</td>
                                        <td class="border border-slate-300 px-2 py-1">Input Penerimaan Barang</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin menginput faktur distributor (No Faktur, *batch*, expired, jumlah, harga perolehan).</td>
                                        <td class="border border-slate-300 px-2 py-1">Stok fisik bertambah otomatis, *batch* baru tercatat aktif, kartu stok mencatat mutasi masuk.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-21</td>
                                        <td class="border border-slate-300 px-2 py-1">Tracking Serial Number</td>
                                        <td class="border border-slate-300 px-2 py-1">Mencatatkan nomor seri unik (*Serial Number*) untuk setiap unit fisik alat kesehatan.</td>
                                        <td class="border border-slate-300 px-2 py-1">Unit alkes terdata individual guna penelusuran fisik dan klaim garansi produk.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-22</td>
                                        <td class="border border-slate-300 px-2 py-1">Nota Penerimaan A4</td>
                                        <td class="border border-slate-300 px-2 py-1">Mencetak dokumen resmi Bukti Penerimaan Logistik Barang Masuk format A4.</td>
                                        <td class="border border-slate-300 px-2 py-1">Nota A4 memuat rincian distributor, nomor faktur, dan kolom tanda tangan verifikasi fisik.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-23</td>
                                        <td class="border border-slate-300 px-2 py-1">Stiker Barcode Code 128</td>
                                        <td class="border border-slate-300 px-2 py-1">Mencetak lembar label stiker *barcode* kemasan produk (A4 Grid 4x10 maupun termal gulung).</td>
                                        <td class="border border-slate-300 px-2 py-1">Label *barcode* tercetak tajam dan dapat langsung dipindai oleh *scanner* optik kasir.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>

                                    <!-- Bagian VI -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian VI: Modul Pengelolaan Mutasi Stok Manual dan Buku Besar Kartu Stok
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-24</td>
                                        <td class="border border-slate-300 px-2 py-1">Mutasi Stok Manual</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin menginput pengurangan stok non-penjualan karena botol sirup pecah di rak etalase.</td>
                                        <td class="border border-slate-300 px-2 py-1">Stok fisik berkurang seketika, alasan barang rusak terekam, kartu stok mencatat mutasi keluar.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-25</td>
                                        <td class="border border-slate-300 px-2 py-1">Riwayat Mutasi Manual</td>
                                        <td class="border border-slate-300 px-2 py-1">Meninjau daftar penyesuaian manual persediaan pada tabel riwayat operasional.</td>
                                        <td class="border border-slate-300 px-2 py-1">Menyajikan waktu transaksi, nama produk, arah penyesuaian (+/-), alasan, dan identitas staf.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-26</td>
                                        <td class="border border-slate-300 px-2 py-1">Buku Besar Kartu Stok</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin memilih komoditas pada Kartu Stok dan mencetak lembar buku besar persediaan format A4.</td>
                                        <td class="border border-slate-300 px-2 py-1">Menampilkan saldo awal, arus mutasi masuk/keluar, saldo akhir, nomor *batch*, dan keterangan transaksi.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin / Pemilik</td>
                                    </tr>

                                    <!-- Bagian VII -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian VII: Modul Siklus Lengkap Stok Opname Terpusat (Multi-Aktor &amp; Multi-Tahap)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-27</td>
                                        <td class="border border-slate-300 px-2 py-1">Inisiasi Freeze Lock</td>
                                        <td class="border border-slate-300 px-2 py-1">Pemilik Toko membuka sesi *stok opname* dan mengaktifkan pembekuan transaksi server.</td>
                                        <td class="border border-slate-300 px-2 py-1">Transaksi kasir POS dan checkout e-commerce terkunci serentak; notifikasi pembekuan aktif.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pemilik Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-28</td>
                                        <td class="border border-slate-300 px-2 py-1">Pencacahan Blind Count</td>
                                        <td class="border border-slate-300 px-2 py-1">Kasir mencacah fisik barang rak etalase tanpa melihat angka saldo pembukuan sistem.</td>
                                        <td class="border border-slate-300 px-2 py-1">Kuantitas 3 kondisi fisik (Baik, Rusak, Expired) terinput secara objektif tanpa bias data sistem.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Kasir Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-29</td>
                                        <td class="border border-slate-300 px-2 py-1">Auto-Filtering Selisih</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin membuka antarmuka verifikasi setelah kasir menyelesaikan pencacahan fisik.</td>
                                        <td class="border border-slate-300 px-2 py-1">Sistem menyembunyikan 105 barang klop dan langsung memfokuskan tampilan pada 6 baris selisih.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-30</td>
                                        <td class="border border-slate-300 px-2 py-1">Modal Mutasi Selisih</td>
                                        <td class="border border-slate-300 px-2 py-1">Admin mengeksekusi mutasi selisih pada modal dialog per item (memilih alasan Hilang/Rusak/Expired/Surplus).</td>
                                        <td class="border border-slate-300 px-2 py-1">Saldo buku disesuaikan, nilai estimasi kerugian dihitung tepat, mutasi terhubung ke kartu stok.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-31</td>
                                        <td class="border border-slate-300 px-2 py-1">Berita Acara A4 Lanskap</td>
                                        <td class="border border-slate-300 px-2 py-1">Mencetak dokumen resmi Berita Acara Pemeriksaan Fisik Persediaan setelah rekonsiliasi tuntas.</td>
                                        <td class="border border-slate-300 px-2 py-1">Tercetak format A4 lanskap memuat daftar selisih, nilai kerugian finansial, dan 3 kolom tanda tangan.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pemilik Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-32</td>
                                        <td class="border border-slate-300 px-2 py-1">Unfreeze &amp; Tutup Sesi</td>
                                        <td class="border border-slate-300 px-2 py-1">Pemilik Toko menutup sesi *stok opname* yang telah diverifikasi dan menerbitkan berita acara.</td>
                                        <td class="border border-slate-300 px-2 py-1">Server kembali Normal; kunci transaksi kasir POS, checkout daring, dan konversi UOM terbuka otomatis.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pemilik Toko</td>
                                    </tr>

                                    <!-- Bagian VIII -->
                                    <tr class="bg-slate-100 font-bold">
                                        <td colspan="6" class="border border-slate-400 px-2 py-1 text-left font-sans uppercase text-[10px] tracking-wide text-slate-800">
                                            Bagian VIII: Modul Laporan Manajerial, Pengawasan Kedaluwarsa, dan Dashboard
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-33</td>
                                        <td class="border border-slate-300 px-2 py-1">Laporan Penjualan A4</td>
                                        <td class="border border-slate-300 px-2 py-1">Menampilkan laporan penjualan lintas kanal (kasir fisik &amp; *online*) serta mencetak format resmi A4.</td>
                                        <td class="border border-slate-300 px-2 py-1">Menyajikan rincian volume penjualan, omzet bersih toko, dan pemisahan metode bayar secara akurat.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pemilik / Admin</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-34</td>
                                        <td class="border border-slate-300 px-2 py-1">Laporan Persediaan ROP</td>
                                        <td class="border border-slate-300 px-2 py-1">Menampilkan stok seluruh komoditas, peringatan ambang batas ROP, dan mencetak dokumen format A4.</td>
                                        <td class="border border-slate-300 px-2 py-1">Status stok aman/kritis tersaji jelas beserta estimasi nilai total perolehan modal persediaan.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-35</td>
                                        <td class="border border-slate-300 px-2 py-1">Laporan Kedaluwarsa FEFO</td>
                                        <td class="border border-slate-300 px-2 py-1">Memantau komoditas mendekati kedaluwarsa (&lt; 30, &lt; 60, &lt; 90 hari) dan barang kedaluwarsa.</td>
                                        <td class="border border-slate-300 px-2 py-1">Memberikan notifikasi dini visual jelas guna mempercepat tindakan retur pasokan ke distributor.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Admin Toko</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">UAT-36</td>
                                        <td class="border border-slate-300 px-2 py-1">Dashboard Superadmin</td>
                                        <td class="border border-slate-300 px-2 py-1">Pemilik Toko membuka dashboard manajerial guna memantau indikator kinerja kunci (*KPI*) toko.</td>
                                        <td class="border border-slate-300 px-2 py-1">Menampilkan omzet, antrean order daring, grafik penjualan 7 hari, dan status pembekuan server.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">DITERIMA</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">Pemilik Toko</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- GAMBAR 4.51 BLANGKO KOSONG INSTRUMEN UAT -->
                    <div class="my-6 border border-slate-300 rounded p-3 bg-slate-50 text-center">
                        <div class="mb-2 text-center">
                            <img src="../lain/File PPT/UI_UX_Asli/51_Blangko_Instrumen_UAT.png" alt="Blangko Kosong Instrumen UAT Format Resmi" class="mx-auto rounded border border-slate-300 max-h-[500px] object-contain shadow-xs">
                        </div>
                        <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                            Gambar 4.51 Tampilan Lembar Blangko Kosong Instrumen Pengujian Penerimaan Pengguna (<i>UAT</i>) Format Cetak Resmi
                        </p>
                    </div>

                    <!-- KUESIONER LIKERT 12 INDIKATOR -->
                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Pengukuran tingkat kepuasan dan kelayakan operasional sistem diuji melalui kuesioner evaluasi yang memuat 12 (dua belas) butir indikator penerimaan berskala Likert 1 hingga 5. Kuesioner ini diisi oleh masing-masing perwakilan peran penguji (Pemilik Toko, Admin Toko, Kasir Toko, dan Pelanggan Toko Daring):
                    </p>

                    <div class="my-4">
                        <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                            Tabel 4.5 Hasil Penilaian Kuesioner Penerimaan Pengguna (Skala Likert 1 &ndash; 5)
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs font-serif border border-slate-400">
                                <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                                    <tr>
                                        <th class="border border-slate-400 px-2 py-1.5 w-8" rowspan="2">No</th>
                                        <th class="border border-slate-400 px-2 py-1.5" rowspan="2">Indikator Pernyataan Evaluasi Penerimaan Sistem</th>
                                        <th class="border border-slate-400 px-2 py-1.5" colspan="4">Penilaian Peran Penguji</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-16" rowspan="2">Total Skor</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-20" rowspan="2">% Capaian</th>
                                        <th class="border border-slate-400 px-2 py-1.5 w-24" rowspan="2">Kategori</th>
                                    </tr>
                                    <tr>
                                        <th class="border border-slate-400 px-1.5 py-1 text-[10px] w-14">Pemilik</th>
                                        <th class="border border-slate-400 px-1.5 py-1 text-[10px] w-14">Admin</th>
                                        <th class="border border-slate-400 px-1.5 py-1 text-[10px] w-14">Kasir</th>
                                        <th class="border border-slate-400 px-1.5 py-1 text-[10px] w-14">Pelanggan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">1</td>
                                        <td class="border border-slate-300 px-2 py-1">Tata letak antarmuka sistem bersih, intuitif, dan alur navigasi mudah dipahami staf.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">20</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">100.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">2</td>
                                        <td class="border border-slate-300 px-2 py-1">Struktur multi-UOM (satuan kemasan besar ke eceran) fleksibel mengelola obat dan alkes.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">19</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">95.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">3</td>
                                        <td class="border border-slate-300 px-2 py-1">Terminal Kasir POS mempercepat pelayanan penjualan dan kalkulasi kembalian di kasir.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">18</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">90.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">4</td>
                                        <td class="border border-slate-300 px-2 py-1">Alokasi *batch* otomatis berbasis FEFO efektif mencegah keluarnya obat kedaluwarsa.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">19</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">95.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">5</td>
                                        <td class="border border-slate-300 px-2 py-1">Katalog toko daring memudahkan pelanggan memeriksa stok riil dan memproses pesanan.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">17</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">85.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">6</td>
                                        <td class="border border-slate-300 px-2 py-1">Sinkronisasi pesanan *omnichannel* mencegah bentrok perebutan stok fisik barang.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">19</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">95.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">7</td>
                                        <td class="border border-slate-300 px-2 py-1">Mekanisme Freeze Lock menghentikan transaksi operasional saat opname secara efektif.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">19</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">95.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">8</td>
                                        <td class="border border-slate-300 px-2 py-1">Pencacahan Blind Count mendorong penghitungan fisik rak yang jujur dan independen.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">19</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">95.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">9</td>
                                        <td class="border border-slate-300 px-2 py-1">Auto-filtering selisih fisik memangkas durasi telaah admin dalam menemukan anomali.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">18</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">90.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">10</td>
                                        <td class="border border-slate-300 px-2 py-1">Buku besar Kartu Stok dan Laporan Logistik mencatat mutasi secara akurat.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">19</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">95.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">11</td>
                                        <td class="border border-slate-300 px-2 py-1">Format cetak dokumen resmi (Struk, Faktur, Nota, Stiker, Berita Acara) siap pakai.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">4</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">19</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">95.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">12</td>
                                        <td class="border border-slate-300 px-2 py-1">Sistem secara menyeluruh siap dan layak diterapkan penuh pada operasional toko.</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center">5</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-bold">20</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-mono">100.0%</td>
                                        <td class="border border-slate-300 px-2 py-1 text-center font-semibold text-emerald-800">Sangat Layak</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-slate-100 font-bold">
                                    <tr>
                                        <td colspan="2" class="border border-slate-400 px-2 py-1.5 text-center uppercase">Total Skor Penilaian per Peran Responden</td>
                                        <td class="border border-slate-400 px-2 py-1.5 text-center text-blue-900 font-mono">59</td>
                                        <td class="border border-slate-400 px-2 py-1.5 text-center text-blue-900 font-mono">57</td>
                                        <td class="border border-slate-400 px-2 py-1.5 text-center text-blue-900 font-mono">55</td>
                                        <td class="border border-slate-400 px-2 py-1.5 text-center text-blue-900 font-mono">54</td>
                                        <td class="border border-slate-400 px-2 py-1.5 text-center text-blue-900 font-mono text-sm">225</td>
                                        <td class="border border-slate-400 px-2 py-1.5 text-center text-blue-900 font-mono text-sm">93.75%</td>
                                        <td class="border border-slate-400 px-2 py-1.5 text-center text-emerald-800 uppercase font-bold">Sangat Layak</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Rumus dan Kalkulasi UAT -->
                    <p class="text-xs font-serif text-slate-800 text-justify mb-2 indent-8 leading-relaxed">
                        Kalkulasi persentase kelayakan penerapan sistem (*UAT Score*) dihitung menggunakan persamaan:
                    </p>

                    <div class="my-3 p-3 bg-slate-50 border border-slate-300 rounded font-mono text-center text-xs">
                        Persentase Kelayakan (%) = &Sigma; Skor Perolehan / &Sigma; Skor Maksimal Ideal &times; 100%<br>
                        Persentase Kelayakan (%) = 225 / (12 Indikator &times; 4 Peran Penguji &times; Skor Max 5) &times; 100% = <strong>93.75%</strong>
                    </div>

                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Berdasarkan kriteria persentase interpretasi skala Likert (0% &ndash; 20% Sangat Tidak Layak, 21% &ndash; 40% Tidak Layak, 41% &ndash; 60% Cukup Layak, 61% &ndash; 80% Layak, dan 81% &ndash; 100% Sangat Layak), perolehan nilai <strong>93.75%</strong> mengonfirmasikan bahwa sistem berada pada rentang kategori <strong>Sangat Layak / Sangat Diterima</strong>.
                    </p>

                    <!-- GAMBAR 4.52 HASIL TERISI INSTRUMEN UAT -->
                    <div class="my-6 border border-slate-300 rounded p-3 bg-slate-50 text-center">
                        <div class="mb-2 text-center">
                            <img src="../lain/File PPT/UI_UX_Asli/52_Hasil_Evaluasi_UAT_Terisi.png" alt="Hasil Evaluasi UAT Terisi Format Resmi" class="mx-auto rounded border border-slate-300 max-h-[500px] object-contain shadow-xs">
                        </div>
                        <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                            Gambar 4.52 Tampilan Lembar Hasil Evaluasi Penerimaan Pengguna (<i>UAT</i>) Terisi Beserta Rekapitulasi Skor dan Lembar Pengesahan Peran Penguji
                        </p>
                    </div>

                    <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                        Temuan penting dari pelaksanaan pengujian penerimaan pengguna ini mencakup beberapa poin penguatan sistem:
                    </p>

                    <ul class="list-disc pl-10 mb-4 space-y-1.5 text-xs font-serif text-slate-800 leading-relaxed">
                        <li>
                            <strong>Efektivitas Penjagaan Kualitas Obat:</strong> Kasir Toko dan Admin Toko sangat mengapresiasi alokasi *FEFO* otomatis yang meniadakan keraguan kasir dalam mengambil barang di rak, memangkas potensi kerugian akibat barang kedaluwarsa.
                        </li>
                        <li>
                            <strong>Ketertiban Stok Opname:</strong> Pemilik Toko menegaskan bahwa mekanisme *Freeze Lock* dan *Blind Count* berhasil menertibkan proses penghitungan fisik tahunan menjadi terstruktur dan bebas dari manipulasi angka sistem.
                        </li>
                        <li>
                            <strong>Kelancaran Penjualan Lintas Saluran:</strong> Pelanggan Toko Daring menyatakan antarmuka katalog obat dan alkes sangat informatif, memudahkan pencarian variasi kemasan eceran dan pemantauan status pesanan secara mandiri.
                        </li>
                    </ul>
                </div>
            </div>"""

def update_html(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    pos_sub422 = content.find('id="subbab_422"')
    pos_sub43 = content.find('id="subbab_43"')
    
    if pos_sub422 == -1 or pos_sub43 == -1:
        print(f"Error: Markers not found in {filepath}")
        return False
    
    # Replace subbab 422
    new_content = content[:pos_sub422] + uat_html_snippet + "\n        </div>\n\n    \n            <!-- 4.3 PEMBAHASAN -->\n            <div " + content[pos_sub43 + len('id="subbab_43"') + 1:]
    
    # Process italicization
    processed = process_html_document(new_content)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(processed)
    
    print(f"Updated {filepath} successfully ({len(processed)} bytes)")
    return True

f1 = r'c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html'
f2 = r'c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html'

update_html(f1)
update_html(f2)
print("Subbab 4.2.2 updated in both files!")

