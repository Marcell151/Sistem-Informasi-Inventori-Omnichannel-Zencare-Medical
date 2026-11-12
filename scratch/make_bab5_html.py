import os

html_v = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAB V KESIMPULAN DAN SARAN - ZenCare Medical</title>
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
        <a href="BAB_IV_ZENCARE_MEDICAL.html" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded shadow-xs text-xs font-medium text-slate-700 hover:bg-slate-50 transition">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Bab IV
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-3 py-1.5 bg-white border border-slate-300 rounded shadow-xs text-xs font-medium text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Dokumen
            </button>
            <button onclick="copyText('full_bab_v_content', this)" class="px-3 py-1.5 bg-blue-600 border border-blue-700 rounded shadow-xs text-xs font-semibold text-white hover:bg-blue-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                Salin Bab V Lengkap
            </button>
        </div>
    </div>

    <!-- Paper Sheet Container -->
    <div id="full_bab_v_content" class="paper max-w-4xl mx-auto border border-slate-300 p-8 sm:p-12 text-slate-900 leading-relaxed font-serif">

        <!-- Header Bab V -->
        <div class="text-center mb-8 border-b pb-6">
            <h1 class="text-base font-bold font-serif uppercase tracking-wider text-slate-900 mb-1">
                BAB V
            </h1>
            <h2 class="text-lg font-bold font-serif uppercase tracking-wide text-slate-900">
                KESIMPULAN DAN SARAN
            </h2>
        </div>

        <!-- 5.1 Kesimpulan -->
        <div class="mb-8">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-3 uppercase tracking-wide">
                5.1 Kesimpulan
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                Berdasarkan hasil analisis, perancangan, implementasi, dan pengujian yang telah dilaksanakan pada Sistem Informasi Inventori dan Penjualan <em>Omnichannel</em> Toko Kesehatan ZenCare Medical, maka dapat ditarik beberapa kesimpulan utama yang menjawab rumusan masalah dan tujuan penelitian sebagai berikut:
            </p>
            <ol class="list-decimal pl-6 text-xs font-serif text-slate-800 space-y-3 text-justify leading-relaxed">
                <li>
                    <strong>Keberhasilan Integrasi Omnichannel dan Penanggulangan Overselling</strong>: Sistem informasi inventori yang dibangun berhasil mengintegrasikan operasional terminal kasir toko fisik (POS) dengan etalase toko daring (<em>e-commerce</em>) mandiri ke dalam satu basis data terpusat secara waktu nyata (<em>real-time</em>). Mekanisme reservasi stok daring (<em>reserved stock</em>) terbukti efektif meniadakan disparitas data dan mencegah terjadinya pesanan ganda (<em>overselling</em>) lintas saluran, sehingga ketersediaan fisik barang di gerai selalu sinkron dengan etalase daring.
                </li>
                <li>
                    <strong>Otomatisasi Konversi Multi-Satuan (Multi-UOM) yang Akurat</strong>: Penerapan arsitektur bertingkat antara entitas <em>produk_induk</em> dan <em>produk_variasi</em> berhasil memfasilitasi kebutuhan penjualan multi-satuan secara dinamis. Operasi pemecahan kemasan besar (Dus/Box) ke kemasan kecil (Strip/Pcs) dieksekusi secara atomik dengan mereplikasi nomor <em>batch</em> dan tanggal kedaluwarsa yang identik ke unit eceran, serta mencatatkan mutasi saldo secara transparan ke buku besar kartu stok (<em>running balance</em>) tanpa merusak integritas kemasan utuh.
                </li>
                <li>
                    <strong>Penegakan Protokol FEFO dan Pelacakan Garansi Alat Kesehatan</strong>: Sistem terbukti andal dalam menegakkan pengeluaran persediaan obat berdasarkan tanggal kedaluwarsa terdekat (<em>First Expired, First Out</em> / FEFO) secara otomatis pada meja kasir. Modul deteksi anomali pengeluaran FEFO yang mewajibkan penginputan alasan operasional saat terjadi loncatan angkatan berhasil meningkatkan akuntabilitas petugas serta meminimalisasi risiko penumpukan obat kedaluwarsa. Selain itu, pencatatan nomor seri unik (<em>serial number</em>) pada komoditas alat kesehatan bernilai tinggi berhasil memfasilitasi verifikasi klaim garansi produk secara akurat.
                </li>
                <li>
                    <strong>Ketertiban Pengawasan Fisik (Stok Opname) yang Objektif dan Aman</strong>: Mekanisme pembekuan transaksi server (<em>Freeze Lock</em>) terbukti berhasil mencegah perebutan data (<em>race condition</em>) selama pemeriksaan fisik berlangsung. Penerapan metode pencacahan tanpa saldo buku (<em>Blind Count</em>) yang dilengkapi validasi otomatis kondisi fisik (Fisik Baik, Fisik Rusak, dan Fisik Kedaluwarsa) berhasil menjamin objektivitas hitungan kasir di lapangan, mempermudah identifikasi selisih fisik oleh staf admin, serta memfasilitasi penerbitan Berita Acara resmi berkala.
                </li>
                <li>
                    <strong>Tingkat Kelayakan dan Keberterimaan Pengguna Tinggi</strong>: Hasil pengujian penerimaan pengguna (<em>User Acceptance Testing</em> / UAT) yang diselenggarakan langsung di lingkungan operasional ZenCare Medical dengan melibatkan 8 responden lintas 4 peran pengguna (Pemilik Toko, Admin Toko, Kasir Toko, dan Pelanggan Daring) terhadap 16 indikator evaluasi fungsional pada 8 bagian modul sistem memperoleh total skor aktual sebesar 300 dari skor maksimal 320, dengan persentase capaian <strong>93,75%</strong> yang berkategori <strong>Sesuai Kebutuhan</strong>. Hal ini membuktikan bahwa perangkat lunak yang dibangun telah memenuhi seluruh spesifikasi kebutuhan bisnis operasional toko kesehatan ZenCare Medical.
                </li>
            </ol>
        </div>

        <!-- 5.2 Saran -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-3 uppercase tracking-wide">
                5.2 Saran
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify mb-4 indent-8 leading-relaxed">
                Meskipun sistem informasi yang dibangun telah berhasil menyelesaikan kendala operasional inventori dan penjualan *omnichannel* di ZenCare Medical, terdapat beberapa saran pengembangan yang dapat dijadikan bahan pertimbangan untuk penyempurnaan sistem pada masa mendatang:
            </p>
            <ol class="list-decimal pl-6 text-xs font-serif text-slate-800 space-y-3 text-justify leading-relaxed">
                <li>
                    <strong>Implementasi Layanan Pihak Ketiga pada Lingkungan Produksi Langsung</strong>: Pengembangan selanjutnya disarankan untuk meningkatkan integrasi gerbang pembayaran digital (*payment gateway*) dan antarmuka pemrograman aplikasi (API) ekspedisi kurir dari lingkungan pengujian (*sandbox*) menuju lingkungan produksi langsung (*live production*), sehingga pembayaran non-tunai otomatis dan pelacakan resi kurir logistik dapat beroperasi secara penuh bagi pelanggan publik.
                </li>
                <li>
                    <strong>Pengembangan Notifikasi Dini Terjadwal (Automated Notification)</strong>: Menambahkan modul pengiriman notifikasi otomatis terprogram melalui integrasi pesan instan (WhatsApp Gateway atau surat elektronik) kepada staf pengadaan ketika persediaan mencapai titik pemesanan kembali (<em>Reorder Point</em>) dan kepada staf teknis ketika terdeteksi komoditas obat yang memasuki masa kedaluwarsa kritis (30 hari).
                </li>
                <li>
                    <strong>Dukungan Pengelolaan Multi-Gerai (Multi-Store / Multi-Outlet)</strong>: Mengembangkan struktur basis data dan arsitektur otorisasi sistem agar mampu mengakomodasi pembukaan cabang-cabang gerai fisik baru di lokasi geografis lain, lengkap dengan mekanisme transfer stok antar-gerai (<em>inter-branch transfer</em>) yang terkoordinasi di bawah satu manajemen terpusat.
                </li>
                <li>
                    <strong>Pengembangan Aplikasi Pemindai Bergerak (Mobile Scanner POS)</strong>: Merancang aplikasi berbasis perangkat bergerak (<em>mobile web / Android</em>) yang terhubung nirkabel dengan pemindai *barcode* fisik atau memanfaatkan kamera ponsel pintar guna mempermudah mobilitas staf gudang dalam melakukan pencacahan fisik rak etalase saat siklus stok opname berlangsung.
                </li>
            </ol>
        </div>

    </div>

    <!-- Bottom Notification (No Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center text-xs text-slate-500 no-print">
        Dokumen Laporan Tugas Akhir &bull; Marcell Chandra (322310015) &bull; Sistem Informasi Inventori <i>Omnichannel</i> ZenCare Medical
    </div>

</body>
</html>
"""

with open('laporan_ta/BAB_V_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(html_v)

print("Created laporan_ta/BAB_V_ZENCARE_MEDICAL.html successfully!")

