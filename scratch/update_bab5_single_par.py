import re

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

        <!-- 5.1 Kesimpulan (1 Paragraf Utuh) -->
        <div class="mb-8">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-3 uppercase tracking-wide">
                5.1 Kesimpulan
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Berdasarkan tahapan analisis, perancangan, implementasi, dan pengujian yang telah dilaksanakan pada Bab I hingga Bab IV, dapat disimpulkan bahwa Sistem Informasi Inventori <em>Omnichannel</em> berbasis web pada ZenCare Medical berhasil dirancang dan dibangun untuk menjawab rumusan masalah penelitian melalui pengintegrasian terminal kasir fisik (<em>Point of Sale</em>) dan platform <em>e-commerce</em> mandiri ke dalam satu basis data relasional terpusat secara waktu nyata (<em>real-time</em>). Sistem terbukti efektif meniadakan disparitas data dan mencegah risiko pesanan ganda (<em>overselling</em>) melalui mekanisme reservasi stok daring (<em>reserved stock</em>), mengotomatisasi konversi satuan bertingkat (<em>multi-UOM</em>) berbasis pembentukan identitas turunan (<em>sub-batch</em>) dari kemasan besar ke eceran secara atomik tanpa merusak integritas kemasan utuh, serta menegakkan kepatuhan pelacakan masa kedaluwarsa obat berbasis FEFO (<em>First Expired, First Out</em>) melalui validasi label fisik, pemindai <em>barcode</em>, dan dialog deteksi anomali di meja kasir. Pelacakan nomor seri unik (<em>serial number</em>) pada alat kesehatan berhasil memfasilitasi verifikasi klaim garansi, sementara integritas pengawasan fisik (<em>stok opname</em>) berhasil dijaga melalui fitur pembekuan transaksi server (<em>freeze lock</em>) dan pencacahan objektif (<em>blind count</em>). Keberhasilan perwujudan sistem ini diperkuat oleh hasil evaluasi penerimaan pengguna (<em>User Acceptance Testing</em>) yang melibatkan 8 responden lintas empat peran operasional dengan capaian skor sebesar 300 dari 320 atau setara 93,75% berkategori Sesuai Kebutuhan, membuktikan bahwa perangkat lunak telah memenuhi seluruh spesifikasi operasional bisnis toko kesehatan ZenCare Medical.
            </p>
        </div>

        <!-- 5.2 Saran (1 Paragraf Utuh) -->
        <div class="mb-6">
            <h3 class="text-sm font-bold font-serif text-slate-900 mb-3 uppercase tracking-wide">
                5.2 Saran
            </h3>
            <p class="text-xs font-serif text-slate-800 text-justify indent-8 leading-relaxed">
                Guna menyempurnakan implementasi dan memperluas manfaat sistem informasi yang telah dibangun, disarankan agar pengembangan sistem pada masa mendatang meningkatkan integrasi modul gerbang pembayaran digital (<em>payment gateway</em>) dan antarmuka pemrograman aplikasi (API) ekspedisi logistik dari lingkungan pengujian (<em>sandbox</em>) menuju lingkungan produksi langsung (<em>live production</em>) agar transaksi non-tunai dan pelacakan resi otomatis dapat beroperasi penuh bagi publik. Selain itu, sistem dapat diperkaya dengan modul pengiriman notifikasi otomatis terprogram (melalui WhatsApp Gateway atau surat elektronik) untuk memberikan sinyal peringatan dini saat persediaan mencapai titik pemesanan kembali (<em>reorder point</em>) serta saat komoditas mendekati masa kedaluwarsa kritis, memperluas struktur basis data agar mendukung manajemen multi-gerai (<em>multi-store</em>) dengan mekanisme mutasi antarcabang, serta merancang aplikasi pemindai bergerak (<em>mobile application</em>) berbasis nirkabel guna mempermudah mobilitas staf gudang saat melakukan pencacahan fisik di rak persediaan.
            </p>
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

print("Updated laporan_ta/BAB_V_ZENCARE_MEDICAL.html successfully with single-paragraph Kesimpulan and single-paragraph Saran!")

