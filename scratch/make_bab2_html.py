import os

html_content = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAB II TINJAUAN PUSTAKA - ZenCare Medical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .paper { box-shadow: none !important; border: none !important; padding: 0 !important; }
        }
        .paper {
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
    <script>
        function copyText(elementId, btnElement) {
            const el = document.getElementById(elementId);
            if (!el) return;
            const plainText = el.innerText || el.textContent;
            const originalText = btnElement.innerHTML;

            function showSuccess() {
                btnElement.innerHTML = `<svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!`;
                btnElement.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                setTimeout(() => {
                    btnElement.innerHTML = originalText;
                    btnElement.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                }, 2000);
            }

            if (navigator.clipboard && window.ClipboardItem) {
                const blobHtml = new Blob([el.innerHTML], { type: 'text/html' });
                const blobText = new Blob([plainText], { type: 'text/plain' });
                navigator.clipboard.write([
                    new ClipboardItem({
                        'text/html': blobHtml,
                        'text/plain': blobText
                    })
                ]).then(showSuccess).catch(() => {
                    navigator.clipboard.writeText(plainText).then(showSuccess);
                });
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(plainText).then(showSuccess);
            }
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 font-sans text-slate-800">

    <!-- Top Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-4 flex items-center justify-between no-print">
        <div class="flex items-center gap-2">
            <a href="BAB_I_ZENCARE_MEDICAL.html" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-slate-300 rounded shadow-xs text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Bab I
            </a>
            <a href="BAB_III_ZENCARE_MEDICAL.html" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-slate-300 rounded shadow-xs text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
                Bab III
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-xs text-xs font-medium text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Dokumen
            </button>
            <button onclick="copyText('full_bab_ii_content', this)" class="px-3 py-1.5 bg-blue-600 border border-blue-700 rounded shadow-xs text-xs font-semibold text-white hover:bg-blue-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                Salin Bab II Lengkap
            </button>
        </div>
    </div>

    <!-- Paper Sheet Container -->
    <div id="full_bab_ii_content" class="paper max-w-4xl mx-auto border border-slate-300 p-8 sm:p-12 text-slate-900 leading-relaxed font-serif">

        <!-- Header Bab II -->
        <div class="text-center mb-8 border-b pb-6">
            <h1 class="text-base font-bold font-serif uppercase tracking-wider text-slate-900 mb-1">
                BAB II
            </h1>
            <h2 class="text-lg font-bold font-serif uppercase tracking-wide text-slate-900">
                TINJAUAN PUSTAKA
            </h2>
        </div>

        <!-- 2.1 Penelitian Terdahulu -->
        <div class="mb-8">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-3 uppercase tracking-wide">
                2.1 Penelitian Terdahulu
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                Kajian terhadap penelitian terdahulu dilakukan sebagai landasan perbandingan dalam pengembangan sistem informasi inventori <em>omnichannel</em> pada ZenCare Medical. Melalui analisis literatur, dapat diidentifikasi keunggulan teknologi serta celah penelitian (<em>research gap</em>) yang dapat disempurnakan. Matriks perbandingan antara sistem yang diusulkan dengan penelitian sebelumnya disajikan pada Tabel 2.1 dan Tabel 2.2.
            </p>

            <!-- Tabel 2.1 -->
            <div class="my-4">
                <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                    Tabel 2.1 Perbandingan Penelitian Terdahulu (Bagian 1: Metodologi dan Fokus Kajian)
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs font-serif border border-slate-400">
                        <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                            <tr>
                                <th class="border border-slate-400 px-2.5 py-1.5 w-36">Komponen Analisis</th>
                                <th class="border border-slate-400 px-2.5 py-1.5">Penelitian 1</th>
                                <th class="border border-slate-400 px-2.5 py-1.5">Penelitian 2</th>
                                <th class="border border-slate-400 px-2.5 py-1.5">Penelitian 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-slate-300 px-2.5 py-1.5 font-bold bg-slate-50">Peneliti & Tahun</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Rachman, Hartanto, & Hadiwijaya (2024)</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Nuralisa, Nirsal, & Tangkelangi (2024)</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Mattegunta (2025)</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-2.5 py-1.5 font-bold bg-slate-50">Judul Penelitian</td>
                                <td class="border border-slate-300 px-2.5 py-1.5 italic">Rancang Bangun Sistem Informasi Persediaan Obat Berbasis Web Pada Toko Obat Murah Bung Tomo</td>
                                <td class="border border-slate-300 px-2.5 py-1.5 italic">Rancang Bangun Sistem Informasi Persediaan Obat Berbasis Website pada Toko Obat Amanah Karya Farma</td>
                                <td class="border border-slate-300 px-2.5 py-1.5 italic">Retail-Time Inventory Visibility: Transforming Retail Operations Through Real-Time Data Integration</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-2.5 py-1.5 font-bold bg-slate-50">Fokus & Metode</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">
                                    <strong>Fokus:</strong> Migrasi tata kelola stok data obat masuk/keluar dari manual (buku) ke sistem digital.<br>
                                    <strong>Metode:</strong> <em>Waterfall</em>.
                                </td>
                                <td class="border border-slate-300 px-2.5 py-1.5">
                                    <strong>Fokus:</strong> Mengatasi pencarian data yang lambat serta mempermudah proses laporan data obat.<br>
                                    <strong>Metode:</strong> R&D (<em>Waterfall</em>).
                                </td>
                                <td class="border border-slate-300 px-2.5 py-1.5">
                                    <strong>Fokus:</strong> Pengelolaan stok secara <em>real-time</em> lintas saluran fisik dan digital pada ekosistem ritel <em>omnichannel</em>.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabel 2.2 -->
            <div class="my-4">
                <p class="text-xs font-serif font-bold text-slate-900 mb-2">
                    Tabel 2.2 Perbandingan Penelitian Terdahulu (Bagian 2: Platform, Hasil, dan Celah Riset)
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs font-serif border border-slate-400">
                        <thead class="bg-slate-100 text-slate-900 font-bold text-center">
                            <tr>
                                <th class="border border-slate-400 px-2.5 py-1.5 w-36">Komponen Analisis</th>
                                <th class="border border-slate-400 px-2.5 py-1.5">Penelitian 1</th>
                                <th class="border border-slate-400 px-2.5 py-1.5">Penelitian 2</th>
                                <th class="border border-slate-400 px-2.5 py-1.5">Penelitian 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-slate-300 px-2.5 py-1.5 font-bold bg-slate-50">Platform & Teknologi</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Berbasis Web (JavaScript dengan kerangka kerja React.js, Express.js, dan MySQL).</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Berbasis Web (PHP Laravel 10, Bootstrap, MySQL).</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Sistem <em>Point of Sale</em> (POS) yang terintegrasi langsung dengan platform berbasis <em>cloud</em>.</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-2.5 py-1.5 font-bold bg-slate-50">Hasil Penelitian</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Berhasil mengintegrasikan data obat, meminimalkan kesalahan input, dan mempercepat pembuatan laporan operasional.</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Sistem mempermudah karyawan mengelola persediaan dan meningkatkan efektivitas serta efisiensi tenaga, biaya, dan waktu.</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Integrasi data transaksi langsung mampu meningkatkan akurasi inventaris hingga 95–97%, meminimalkan kehabisan stok (<em>stockout</em>), dan menaikkan efisiensi operasional.</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-300 px-2.5 py-1.5 font-bold bg-slate-50">Perbedaan dengan ZenCare</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Sistem bersifat <em>stand-alone</em> untuk toko fisik saja tanpa sinkronisasi ke saluran daring, serta belum menerapkan algoritma konversi multi-satuan (<em>Multi-UOM</em>) maupun pelacakan kedaluwarsa berbasis <em>batch</em> (FEFO).</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Sistem berfokus pada pencarian dan pelaporan internal, namun belum mengadopsi sinkronisasi <em>omnichannel</em> dengan penjualan daring sehingga tidak memiliki mekanisme pencegahan <em>overselling</em> lintas kanal.</td>
                                <td class="border border-slate-300 px-2.5 py-1.5">Fokus pada ritel umum tanpa mempertimbangkan karakteristik khusus industri medis, seperti kewajiban pelacakan kedaluwarsa berbasis FEFO pada obat maupun pencatatan garansi <em>Serial Number</em> pada alat kesehatan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                Penelitian terdahulu menunjukkan bahwa digitalisasi manajemen inventori terbukti krusial bagi operasional bisnis medis. Rachman et al. (2024) dan Nuralisa et al. (2024) membuktikan bahwa sistem berbasis web mampu menekan risiko kesalahan pencatatan manual, namun arsitektur yang mereka bangun masih bersifat tertutup (<em>single-channel</em>) dan hanya difokuskan untuk operasional pencatatan stok secara luring. Sebagai solusi untuk sinkronisasi lintas saluran, kajian dari Mattegunta (2025) menyoroti bahwa penerapan <em>omnichannel</em> dengan visibilitas data <em>real-time</em> sangat efektif untuk meningkatkan akurasi inventaris hingga 97% dan meminimalkan risiko kehabisan stok (<em>stockout</em>).
            </p>
            <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                Mengisi celah penelitian (<em>research gap</em>) tersebut, pengembangan Sistem Informasi Inventori ZenCare Medical mengusulkan rancang bangun arsitektur <em>omnichannel</em> mandiri yang mengadopsi konsep integrasi data terpusat yang dikembangkan oleh Mattegunta (2025), namun disesuaikan dengan kompleksitas komoditas medis. Perbedaan utamanya terletak pada karakteristik barang dan logika sistem. Berbeda dengan penelitian Mattegunta yang berfokus pada ritel umum, kebaruan sistem ini dibanding penelitian Rachman et al. (2024) dan Nuralisa et al. (2024) adalah diterapkannya pelacakan masa kedaluwarsa berbasis FEFO serta algoritma konversi multi-satuan (<em>Multi-UOM</em>) pada basis data. Logika ini memungkinkan transaksi eceran di toko fisik maupun grosir di <em>e-commerce</em> terpotong secara otomatis pada satu riwayat kartu stok yang sama, memberikan solusi operasional yang belum terakomodasi pada ketiga penelitian sebelumnya.
            </p>
        </div>

        <!-- 2.2 Sistem Informasi -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.2 Sistem Informasi
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Sistem informasi merupakan kumpulan komponen yang saling terkait untuk mengubah data mentah menjadi informasi yang dapat digunakan dalam pengambilan keputusan. Sistem informasi bukan sekadar kumpulan perangkat lunak; elemen manusia, prosedur kerja, dan data itu sendiri sama-sama menjadi bagian dari satu kesatuan yang mendukung jalannya operasional organisasi. Ketika elemen-elemen ini tertata dengan baik, setiap masukan (*input*) yang masuk dapat diproses secara sistematis hingga menghasilkan keluaran (*output*) yang dapat diandalkan oleh pihak manajemen maupun pelaksana teknis dalam aktivitas sehari-hari (Harahap et al., 2022).
            </p>
        </div>

        <!-- 2.3 Sistem Informasi Inventori -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.3 Sistem Informasi Inventori
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Kebutuhan mengelola persediaan secara lebih tertib mendorong diterapkannya sistem informasi inventori, terutama pada organisasi yang sebelumnya masih mengandalkan lembar kerja *spreadsheet* atau catatan pembukuan manual. Peralihan ke sistem terkomputerisasi membuat pengawasan ketersediaan barang menjadi lebih terstruktur, karena riwayat mutasi persediaan dapat dipantau setiap saat tanpa harus menelusuri catatan fisik satu per satu (Anggraini & Syahputra, 2024). Manfaat ini terasa langsung pada operasional logistik medis: selisih antara catatan sistem dengan jumlah fisik barang di rak penyimpanan dapat ditekan, sekaligus mempercepat proses validasi ketersediaan barang saat dibutuhkan.
            </p>
        </div>

        <!-- 2.4 Kartu Stok (Stock Ledger) -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.4 Kartu Stok (Stock Ledger)
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Kartu stok berfungsi sebagai instrumen pencatatan historis yang merekam seluruh jejak pergerakan barang, mulai dari stok awal, kuantitas masuk, jumlah keluar, hingga saldo akhir berjalan (*running balance*). Tanpa dokumentasi semacam ini, jejak mutasi barang mudah hilang dan berujung pada selisih inventaris yang sulit ditelusuri sumbernya. Penerapan kartu stok yang bersifat *immutable* (tidak dapat diubah atau dihapus sepihak) memungkinkan penelusuran riwayat pergerakan barang menjadi jauh lebih akurat, karena setiap unit barang yang berpindah tangan selalu terdokumentasi dan dapat divalidasi ketersediaan fisiknya (Pitasari et al., 2025).
            </p>
        </div>

        <!-- 2.5 Reorder Point (ROP) dan Safety Stock -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.5 Reorder Point (ROP) dan Safety Stock
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Dalam manajemen persediaan, penentuan titik waktu untuk memesan kembali barang sebelum stoknya habis dikenal dengan istilah <em>Reorder Point</em> (ROP). Penerapan peringatan ROP pada sistem inventori terbukti efektif mencegah masalah kehabisan stok (<em>stockout</em>) yang berisiko mengganggu pelayanan konsumen. Walaupun perhitungan teoretis ROP umumnya menggunakan rumus rata-rata penjualan dan waktu pengiriman barang (*lead time*), penerapannya di lapangan berpatokan langsung pada nilai stok pengaman (<em>safety stock</em>). Di dalam ekosistem *multi-satuan*, parameter batas minimum stok ditetapkan secara terpisah berdasarkan tingkatan satuan barang. Dengan pendekatan ini, sistem informasi tidak hanya berfungsi memunculkan peringatan pengadaan barang ke pemasok saat stok utama menipis, tetapi juga dapat memfasilitasi instruksi operasional internal, seperti peringatan untuk melakukan pembongkaran kemasan utuh apabila stok eceran di etalase telah mencapai titik kritis (Maulidi & Listianti, 2023).
            </p>
        </div>

        <!-- 2.6 Pemeriksaan Persediaan (Stock Opname) dan Blind Count -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.6 Pemeriksaan Persediaan (Stock Opname) dan Blind Count
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Pelaksanaan <em>stok opname</em> merupakan aktivitas krusial untuk mencocokkan fisik persediaan di gudang dengan pencatatan data buku sistem. Menurut Fazillah & Devitra (2022), digitalisasi pemantauan stok opname melalui sistem berbasis web terbukti mampu menekan tingkat ketidakakuratan data dan menghindari pencatatan ganda yang sering muncul pada metode manual. Guna memperketat pengawasan dan menekan celah manipulasi angka oleh petugas lapangan saat melakukan perhitungan, proses pemeriksaan persediaan dapat didukung dengan metode pencacahan buta (<em>blind count</em>). Di samping itu, penerapan mekanisme pembekuan transaksi sementara (<em>freeze lock / system lockout</em>) selama penghitungan berlangsung sangat diperlukan agar tidak timbul selisih data baru akibat adanya pergerakan stok dari transaksi kasir maupun penjualan daring di saat yang bersamaan.
            </p>
        </div>

        <!-- 2.7 Omnichannel -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.7 Omnichannel
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Strategi <em>omnichannel</em> merujuk pada integrasi berbagai kanal penjualan ke dalam satu ekosistem yang saling terhubung erat. Konsep ini menjembatani aktivitas transaksi dari berbagai platform, baik daring maupun luring, sehingga pengelolaan informasi penjualan dan ketersediaan barang dapat dikoordinasikan secara terpusat. Dari sudut pandang sistem informasi, implementasi <em>omnichannel</em> dapat dilihat pada ekosistem operasional ritel kesehatan modern atau platform niaga elektronik terkemuka. Pada ekosistem tersebut, pemusatan data memungkinkan pelanggan memesan produk secara daring dan mengambilnya secara langsung di gerai fisik (<em>In-Store Pickup</em>), dengan jaminan bahwa persediaan yang ditampilkan di layar sinkron dengan fisik di lokasi. Melalui pemusatan data tersebut, tingkat ketersediaan stok fisik di berbagai saluran akan selalu terkoordinasi secara seketika (<em>real-time</em>) setiap kali terjadi transaksi pemotongan barang (Sarasi et al., 2024). Pendekatan ini sangat krusial untuk mencegah terjadinya pesanan ganda (<em>overselling</em>) terhadap satu barang yang sama dari jalur penjualan yang berbeda (Mattegunta, 2025).
            </p>
        </div>

        <!-- 2.8 First Expired First Out (FEFO) dan Sistem Batch -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.8 First Expired First Out (FEFO) dan Sistem Batch
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Dalam manajemen logistik, terdapat tiga metode utama pengeluaran barang: <em>First In, First Out</em> (FIFO), <em>Last In, First Out</em> (LIFO), dan <em>First Expired, First Out</em> (FEFO). Metode FIFO mengeluarkan barang yang pertama kali masuk ke gudang. Sebaliknya, metode LIFO mengeluarkan barang yang terakhir masuk, di mana metode ini sangat dihindari dalam penanganan komoditas medis karena akan membiarkan stok lama menumpuk dan rusak. Pengelolaan obat-obatan menuntut standar keamanan khusus melalui metode FEFO, yang memprioritaskan pengeluaran stok murni berdasarkan tanggal kedaluwarsa terdekat tanpa melihat urutan kedatangan barang (Hafidzhoh, 2024).
            </p>

            <!-- GAMBAR 2.1 ILUSTRASI FIFO VS FEFO VS LIFO -->
            <div class="my-6 border border-slate-300 rounded p-4 bg-slate-50 text-center">
                <div class="max-w-xl mx-auto bg-white p-4 border border-slate-300 rounded shadow-xs mb-2">
                    <svg viewBox="0 0 600 220" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
                        <!-- FIFO Box -->
                        <g transform="translate(10, 20)">
                            <rect width="180" height="150" rx="6" fill="#f8fafc" stroke="#3b82f6" stroke-width="2"/>
                            <rect width="180" height="30" rx="6" fill="#3b82f6"/>
                            <text x="90" y="20" fill="white" font-size="12" font-family="sans-serif" font-weight="bold" text-anchor="middle">FIFO (First In, First Out)</text>
                            <text x="90" y="55" fill="#1e293b" font-size="10" font-family="sans-serif" text-anchor="middle">Barang Masuk Pertama</text>
                            <path d="M90 65 L90 95" stroke="#3b82f6" stroke-width="2" marker-end="url(#arrow)"/>
                            <circle cx="90" cy="110" r="16" fill="#dbeafe" stroke="#3b82f6" stroke-width="1.5"/>
                            <text x="90" y="114" fill="#1e40af" font-size="10" font-family="sans-serif" font-weight="bold" text-anchor="middle">Batch 1</text>
                            <text x="90" y="145" fill="#047857" font-size="10" font-family="sans-serif" font-weight="bold" text-anchor="middle">&rarr; Keluar Pertama</text>
                        </g>

                        <!-- FEFO Box (Primary Highlight) -->
                        <g transform="translate(210, 10)">
                            <rect width="180" height="170" rx="6" fill="#f0fdf4" stroke="#10b981" stroke-width="2.5"/>
                            <rect width="180" height="32" rx="6" fill="#10b981"/>
                            <text x="90" y="21" fill="white" font-size="12" font-family="sans-serif" font-weight="bold" text-anchor="middle">FEFO (First Expired, First Out)</text>
                            <text x="90" y="55" fill="#065f46" font-size="10" font-family="sans-serif" font-weight="bold" text-anchor="middle">Prioritas Kedaluwarsa</text>
                            <rect x="25" y="70" width="130" height="26" rx="4" fill="#fee2e2" stroke="#ef4444" stroke-width="1"/>
                            <text x="90" y="86" fill="#991b1b" font-size="9" font-family="sans-serif" font-weight="bold" text-anchor="middle">ED Terdekat (Prioritas 1)</text>
                            <path d="M90 100 L90 125" stroke="#10b981" stroke-width="2"/>
                            <text x="90" y="145" fill="#047857" font-size="10" font-family="sans-serif" font-weight="bold" text-anchor="middle">&rarr; Wajib Keluar Pertama</text>
                            <text x="90" y="162" fill="#64748b" font-size="8.5" font-family="sans-serif" text-anchor="middle">(Standar Keamanan Medis)</text>
                        </g>

                        <!-- LIFO Box -->
                        <g transform="translate(410, 20)">
                            <rect width="180" height="150" rx="6" fill="#f8fafc" stroke="#94a3b8" stroke-width="2"/>
                            <rect width="180" height="30" rx="6" fill="#64748b"/>
                            <text x="90" y="20" fill="white" font-size="12" font-family="sans-serif" font-weight="bold" text-anchor="middle">LIFO (Last In, First Out)</text>
                            <text x="90" y="55" fill="#1e293b" font-size="10" font-family="sans-serif" text-anchor="middle">Barang Masuk Terakhir</text>
                            <path d="M90 65 L90 95" stroke="#64748b" stroke-width="2"/>
                            <circle cx="90" cy="110" r="16" fill="#f1f5f9" stroke="#94a3b8" stroke-width="1.5"/>
                            <text x="90" y="114" fill="#475569" font-size="10" font-family="sans-serif" font-weight="bold" text-anchor="middle">Batch 3</text>
                            <text x="90" y="145" fill="#b91c1c" font-size="9.5" font-family="sans-serif" font-weight="bold" text-anchor="middle">&cross; Dihindari di Medis</text>
                        </g>
                    </svg>
                </div>
                <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                    Gambar 2.1 Perbandingan Karakteristik Alur Pengeluaran Barang: FIFO vs FEFO vs LIFO (Sumber: Bhakar, 2023)
                </p>
            </div>

            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Dalam implementasinya secara digital, metode FEFO membutuhkan sistem pencatatan berbasis nomor identitas *batch*. Sistem tidak menyatukan persediaan ke dalam satu angka tunggal, melainkan memecahnya berdasarkan *batch* yang mengikat pada tanggal kedaluwarsanya masing-masing. Saat terjadi transaksi penjualan, algoritma sistem akan merekomendasikan pemotongan stok pada *batch* yang memiliki masa pakai terpendek. Otomatisasi metode FEFO ini sangat efektif untuk meminimalisasi kerugian akibat penumpukan barang rusak sekaligus menjamin keselamatan konsumen (Hafidzhoh, 2024).
            </p>
        </div>

        <!-- 2.9 E-Commerce -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.9 E-Commerce
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                <em>E-commerce</em> memfasilitasi transaksi jual beli produk melalui media digital dan infrastruktur internet. Bukan hanya menyajikan katalog produk, platform ini juga mendukung seluruh alur pemesanan secara interaktif, mulai dari penelusuran komoditas, seleksi variasi kemasan, pemilihan ekspedisi pengiriman, hingga konfirmasi pembayaran. Kehadiran etalase digital memberi ruang bagi penjual untuk menjangkau pembeli tanpa terhalang batasan waktu maupun lokasi geografis. Peran <em>e-commerce</em> juga mencakup pencatatan digital transaksi daring dengan rincian kuantitas produk yang dipesan, sehingga proses jual beli menjadi lebih terstruktur dan mudah ditelusuri kembali (Najwa et al., 2022).
            </p>
        </div>

        <!-- 2.10 Point of Sale (POS) -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.10 Point of Sale (POS)
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Terminal <em>Point of Sale</em> (POS) menangani transaksi tatap muka secara luring di meja kasir gerai fisik secara terkomputerisasi. Setiap transaksi di lokasi fisik dapat dieksekusi lebih cepat dan minim kesalahan hitung dibanding pencatatan manual. Yang membuat POS relevan bagi sistem inventori terintegrasi adalah pengaruhnya terhadap saldo persediaan: begitu pembayaran disahkan dan bukti belanja diterbitkan, kuantitas stok di basis data otomatis terpotong tanpa perlu campur tangan admin secara manual (Atmoko et al., 2025). Riwayat penjualan dan saldo barang fisik pun tetap sinkron dalam satu waktu nyata.
            </p>
        </div>

        <!-- 2.11 Metode RAD -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.11 Metode Rapid Application Development (RAD)
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Dalam proses pengembangan perangkat lunak ini, metodologi <em>Rapid Application Development</em> (RAD) diterapkan karena relevansinya yang tinggi terhadap kebutuhan sistem. Karakteristik utama dari metode ini terletak pada siklus pengerjaannya yang iteratif dan berdurasi cepat, memungkinkan pengembang merespons perubahan kebutuhan dengan lebih adaptif. Pendekatan ini memprioritaskan pembuatan rancangan awal berupa purwarupa (<em>prototype</em>) yang dapat langsung dievaluasi sebelum masuk ke tahap pengkodean akhir. RAD menitikberatkan pada keterlibatan aktif pengguna melalui tiga fase utama, meliputi perencanaan kebutuhan (<em>requirements planning</em>), proses pengembangan purwarupa secara berulang berdasarkan umpan balik (<em>construction</em>), serta tahap implementasi (<em>cutover</em>) (Nur Adiya et al., 2024).
            </p>

            <!-- GAMBAR 2.2 SIKLUS METODE RAD -->
            <div class="my-6 border border-slate-300 rounded p-4 bg-slate-50 text-center">
                <div class="max-w-xl mx-auto bg-white p-4 border border-slate-300 rounded shadow-xs mb-2">
                    <svg viewBox="0 0 600 180" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
                        <!-- Phase 1: Requirements Planning -->
                        <g transform="translate(20, 30)">
                            <rect width="160" height="110" rx="6" fill="#eff6ff" stroke="#3b82f6" stroke-width="2"/>
                            <circle cx="80" cy="30" r="16" fill="#3b82f6"/>
                            <text x="80" y="35" fill="white" font-size="12" font-family="sans-serif" font-weight="bold" text-anchor="middle">1</text>
                            <text x="80" y="65" fill="#1e3a8a" font-size="11" font-family="sans-serif" font-weight="bold" text-anchor="middle">Requirements Planning</text>
                            <text x="80" y="85" fill="#475569" font-size="9" font-family="sans-serif" text-anchor="middle">Identifikasi Masalah &</text>
                            <text x="80" y="100" fill="#475569" font-size="9" font-family="sans-serif" text-anchor="middle">Analisis Kebutuhan Toko</text>
                        </g>

                        <!-- Arrow 1 to 2 -->
                        <g transform="translate(185, 80)">
                            <line x1="0" y1="0" x2="30" y2="0" stroke="#3b82f6" stroke-width="2.5"/>
                            <polygon points="30,-4 38,0 30,4" fill="#3b82f6"/>
                        </g>

                        <!-- Phase 2: User Design & Construction (Iterative Loop) -->
                        <g transform="translate(225, 20)">
                            <rect width="170" height="130" rx="6" fill="#f0fdf4" stroke="#10b981" stroke-width="2.5"/>
                            <circle cx="85" cy="25" r="16" fill="#10b981"/>
                            <text x="85" y="30" fill="white" font-size="12" font-family="sans-serif" font-weight="bold" text-anchor="middle">2</text>
                            <text x="85" y="58" fill="#065f46" font-size="11" font-family="sans-serif" font-weight="bold" text-anchor="middle">Design & Construction</text>
                            <text x="85" y="76" fill="#047857" font-size="9" font-family="sans-serif" font-weight="bold" text-anchor="middle">&bull; Purwarupa (Prototyping)</text>
                            <text x="85" y="92" fill="#047857" font-size="9" font-family="sans-serif" font-weight="bold" text-anchor="middle">&bull; Pengkodean Modul</text>
                            <text x="85" y="108" fill="#047857" font-size="9" font-family="sans-serif" font-weight="bold" text-anchor="middle">&bull; Evaluasi Pengguna Iteratif</text>
                            <!-- Feedback loop indicator -->
                            <path d="M 20 120 Q 85 132 150 120" fill="none" stroke="#10b981" stroke-width="1.5" stroke-dasharray="3,3"/>
                            <text x="85" y="125" fill="#059669" font-size="8" font-family="sans-serif" text-anchor="middle">Siklus Revisi Cepat</text>
                        </g>

                        <!-- Arrow 2 to 3 -->
                        <g transform="translate(400, 80)">
                            <line x1="0" y1="0" x2="30" y2="0" stroke="#10b981" stroke-width="2.5"/>
                            <polygon points="30,-4 38,0 30,4" fill="#10b981"/>
                        </g>

                        <!-- Phase 3: Cutover -->
                        <g transform="translate(440, 30)">
                            <rect width="140" height="110" rx="6" fill="#faf5ff" stroke="#8b5cf6" stroke-width="2"/>
                            <circle cx="70" cy="30" r="16" fill="#8b5cf6"/>
                            <text x="70" y="35" fill="white" font-size="12" font-family="sans-serif" font-weight="bold" text-anchor="middle">3</text>
                            <text x="70" y="65" fill="#5b21b6" font-size="11" font-family="sans-serif" font-weight="bold" text-anchor="middle">Cutover Phase</text>
                            <text x="70" y="85" fill="#475569" font-size="9" font-family="sans-serif" text-anchor="middle">Pengujian UAT &</text>
                            <text x="70" y="100" fill="#475569" font-size="9" font-family="sans-serif" text-anchor="middle">Penerapan Operasional</text>
                        </g>
                    </svg>
                </div>
                <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                    Gambar 2.2 Tahapan Siklus Pengembangan Perangkat Lunak Rapid Application Development (Sumber: Rahmawati, 2022)
                </p>
            </div>

            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Pemilihan RAD pada penelitian ini didasarkan pada fleksibilitasnya dibandingkan metode tradisional seperti <em>Waterfall</em>. Pada metode tradisional, alur pengembangan berjalan linier dan kaku, di mana pengembang tidak dapat kembali ke tahap perancangan jika sudah masuk ke fase penulisan kode, sehingga rentan gagal jika ada penyesuaian kebutuhan operasional. Sebaliknya, melalui siklus iteratif berbasis purwarupa (<em>prototyping</em>), metode RAD memungkinkan pengembang merespons perubahan logika bisnis toko kesehatan secara cepat dan tepat.
            </p>
        </div>

        <!-- 2.12 PHP & MySQL -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.12 PHP dan MySQL
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Pembangunan aplikasi web yang dinamis membutuhkan bahasa pemrograman sekaligus sistem manajemen basis data relasional yang saling mendukung. PHP berperan sebagai bahasa pemrograman sisi peladen (<em>server-side</em>) yang memproses logika komputasi aplikasi, menerjemahkan instruksi dari antarmuka pengguna hingga menjembatani komunikasi menuju basis data. MySQL, di sisi lain, berfungsi menyimpan, mengatur, dan merelasikan data secara terstruktur dalam format tabel-tabel terindeks. Kombinasi keduanya memungkinkan setiap manipulasi data persediaan—penambahan pasokan masuk, konversi kemasan, pembaruan stok, maupun pemotongan transaksi kasir—berjalan responsif dan dapat diandalkan (Hidayatullah & Tubagus, 2024).
            </p>
        </div>

        <!-- 2.13 Application Programming Interface (API) -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.13 Application Programming Interface (API)
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Untuk memperluas fungsionalitas dan keterhubungan sebuah perangkat lunak, diperlukan jembatan komunikasi yang dikenal dengan istilah <em>Application Programming Interface</em> (API). Teknologi ini memungkinkan sebuah aplikasi web untuk bertukar data dan menginstruksikan perintah ke layanan eksternal secara sistematis, tanpa harus merombak struktur kode utama dari layanan tersebut. Dalam implementasi praktisnya, API sering dimanfaatkan untuk mendelegasikan proses-proses yang spesifik kepada penyedia layanan pihak ketiga yang sudah terspesialisasi, seperti modul gerbang pembayaran digital maupun kalkulasi logistik pengiriman barang secara instan. Pada tahap pengembangan, pertukaran data melalui jalur ini diujicobakan di dalam lingkungan pengujian (<em>sandbox</em>) untuk memastikan keamanannya sebelum diterapkan untuk publik (Dalimonthe et al., 2023).
            </p>
        </div>

        <!-- 2.14 Unified Modeling Language (UML) -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-2 uppercase tracking-wide">
                2.14 Unified Modeling Language (UML)
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Perancangan arsitektur sistem membutuhkan bahasa pemodelan visual yang terstandarisasi, yakni <em>Unified Modeling Language</em> (UML), untuk membantu pengembang memvisualisasikan, mendefinisikan spesifikasi, dan mendokumentasikan cetak biru perangkat lunak. Tanpa pemodelan yang jelas, penerjemahan kebutuhan bisnis ke dalam kode teknis rawan salah tafsir antara pengembang dan pengguna. Melalui diagram-diagram spesifik seperti <em>use case diagram</em> maupun <em>activity diagram</em>, alur interaksi antara aktor pengguna dengan sistem dapat dipetakan secara rinci, yang kemudian menjadi acuan dalam merancang struktur basis data serta logika antarmuka agar sistem yang dibangun sesuai dengan kebutuhan yang telah dianalisis sebelumnya (Narulita et al., 2024).
            </p>

            <!-- GAMBAR 2.3 KLASIFIKASI DIAGRAM UML -->
            <div class="my-6 border border-slate-300 rounded p-4 bg-slate-50 text-center">
                <div class="max-w-xl mx-auto bg-white p-4 border border-slate-300 rounded shadow-xs mb-2">
                    <svg viewBox="0 0 600 190" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
                        <!-- Root Node -->
                        <rect x="210" y="10" width="180" height="35" rx="6" fill="#1e293b"/>
                        <text x="300" y="32" fill="white" font-size="12" font-family="sans-serif" font-weight="bold" text-anchor="middle">UML Diagrams</text>

                        <!-- Connector lines -->
                        <line x1="300" y1="45" x2="300" y2="65" stroke="#64748b" stroke-width="2"/>
                        <line x1="160" y1="65" x2="440" y2="65" stroke="#64748b" stroke-width="2"/>
                        <line x1="160" y1="65" x2="160" y2="85" stroke="#64748b" stroke-width="2"/>
                        <line x1="440" y1="65" x2="440" y2="85" stroke="#64748b" stroke-width="2"/>

                        <!-- Left Branch: Structural -->
                        <rect x="70" y="85" width="180" height="32" rx="5" fill="#3b82f6"/>
                        <text x="160" y="105" fill="white" font-size="11" font-family="sans-serif" font-weight="bold" text-anchor="middle">Structure Diagrams</text>
                        <rect x="70" y="123" width="180" height="50" rx="5" fill="#eff6ff" stroke="#bfdbfe"/>
                        <text x="160" y="142" fill="#1e40af" font-size="9.5" font-family="sans-serif" text-anchor="middle">&bull; Class Diagram</text>
                        <text x="160" y="158" fill="#1e40af" font-size="9.5" font-family="sans-serif" text-anchor="middle">&bull; Entity Relationship Diagram (ERD)</text>

                        <!-- Right Branch: Behavioral (Highlighted) -->
                        <rect x="350" y="85" width="180" height="32" rx="5" fill="#10b981"/>
                        <text x="440" y="105" fill="white" font-size="11" font-family="sans-serif" font-weight="bold" text-anchor="middle">Behavior Diagrams</text>
                        <rect x="350" y="123" width="180" height="50" rx="5" fill="#f0fdf4" stroke="#bbf7d0"/>
                        <text x="440" y="142" fill="#065f46" font-size="9.5" font-family="sans-serif" font-weight="bold" text-anchor="middle">&bull; Use Case Diagram (Hak Akses)</text>
                        <text x="440" y="158" fill="#065f46" font-size="9.5" font-family="sans-serif" font-weight="bold" text-anchor="middle">&bull; Activity Diagram (Alur Kerja)</text>
                    </svg>
                </div>
                <p class="text-xs font-serif font-bold text-slate-800 mt-2">
                    Gambar 2.3 Klasifikasi Diagram Unified Modeling Language (UML) dalam Rekayasa Perangkat Lunak (Sumber: Greg, 2021)
                </p>
            </div>
        </div>

    </div>

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori <i>Omnichannel</i> ZenCare Medical
    </div>

</body>
</html>
"""

with open('laporan_ta/BAB_II_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print("Created laporan_ta/BAB_II_ZENCARE_MEDICAL.html successfully!")

