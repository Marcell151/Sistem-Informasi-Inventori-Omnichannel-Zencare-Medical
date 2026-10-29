
function openAnomaliModal() {
    const dataAnomali = "mock_php_val";
    if (!dataAnomali || dataAnomali.length === 0) {
        Swal.fire({
            title: 'Kepatuhan FEFO Tertib ✓',
            html: '<div class="text-sm text-slate-600 mt-2">Tidak ada anomali atau penukaran batch fisik yang terjadi hari ini.<br><br><span class="text-xs text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 font-bold inline-block">Seluruh transaksi tertib mengikuti urutan First-Expired, First-Out (FEFO)</span></div>',
            icon: 'success',
            confirmButtonColor: '#1a75d2'
        });
        return;
    }

    let rowsHtml = '';
    dataAnomali.forEach(an => {
        rowsHtml += `
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px;font-family:monospace;font-weight:700;color:#1e293b;">${an.no_invoice}</td>
                <td style="padding:8px;text-align:left;color:#334155;font-weight:600;">${an.nama_produk}</td>
                <td style="padding:8px;color:#1e40af;font-family:monospace;font-weight:700;background:#eff6ff;">${an.batch_seharusnya}</td>
                <td style="padding:8px;color:#991b1b;font-family:monospace;font-weight:700;background:#fef2f2;">${an.batch_diambil}</td>
                <td style="padding:8px;text-align:right;font-weight:700;color:#dc2626;">${an.qty}</td>
                <td style="padding:8px;color:#64748b;">${an.operator} (${an.created_at.substring(11, 16)})</td>
            </tr>
        `;
    });

    Swal.fire({
        title: 'Rekap Anomali FEFO Hari Ini',
        html: `
            <div style="text-align:left;font-size:12px;margin-bottom:10px;color:#475569;">
                Daftar transaksi kasir di mana kasir mengambil batch fisik yang berbeda dari rekomendasi FEFO sistem:
            </div>
            <div style="max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;text-align:center;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #cbd5e1;position:sticky;top:0;">
                        <tr>
                            <th style="padding:7px;">Invoice</th>
                            <th style="padding:7px;text-align:left;">Produk</th>
                            <th style="padding:7px;">Seharusnya</th>
                            <th style="padding:7px;">Diambil (Ditukar)</th>
                            <th style="padding:7px;text-align:right;">Qty</th>
                            <th style="padding:7px;">Kasir</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </div>
            <div style="font-size:11px;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;padding:8px 12px;border-radius:8px;margin-top:12px;text-align:left;font-weight:600;">
                ⚠ Catatan SOP: Penukaran fisik obat harus diminimalkan untuk mencegah resiko obat kedaluwarsa tertinggal di rak.
            </div>
        `,
        width: '780px',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#1a75d2'
    });
}

function openReturModal() {
    const listRetur = "mock_php_val";
    if (!listRetur || listRetur.length === 0) {
        Swal.fire({
            title: 'Monitoring Retur Bersih ✓',
            text: 'Tidak ada barang rusak penerimaan PO yang sedang menunggu penggantian.',
            icon: 'info',
            confirmButtonColor: '#1a75d2'
        });
        return;
    }

    let rowsHtml = '';
    listRetur.forEach(r => {
        const isWaiting = (r.status_retur === 'menunggu_pengganti');
        const badge = isWaiting 
            ? '<span style="padding:2px 8px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:700;font-size:10px;">⏳ Menunggu Penggantian</span>'
            : `<span style="padding:2px 8px;border-radius:4px;background:#dcfce7;color:#166534;font-weight:700;font-size:10px;">✓ Selesai Diganti (Batch: ${r.no_batch_pengganti || '-'})</span>`;
        rowsHtml += `
            <tr style="border-bottom:1px solid #e2e8f0;">
                <td style="padding:8px;font-family:monospace;font-weight:700;">${r.no_referensi}</td>
                <td style="padding:8px;text-align:left;">${r.nama_supplier}</td>
                <td style="padding:8px;text-align:left;font-weight:600;">${r.nama_produk}</td>
                <td style="padding:8px;text-align:right;font-weight:700;color:#dc2626;">${r.qty_rusak}</td>
                <td style="padding:8px;">${badge}</td>
            </tr>
        `;
    });

    Swal.fire({
        title: 'Monitoring Retur Barang (Penerimaan PO)',
        html: `
            <div style="text-align:left;font-size:12px;margin-bottom:10px;color:#475569;">
                Status tindak lanjut barang rusak/cacat yang dikembalikan ke supplier:
            </div>
            <div style="max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px;">
                <table style="width:100%;border-collapse:collapse;font-size:11px;text-align:center;">
                    <thead style="background:#f8fafc;border-bottom:1px solid #cbd5e1;position:sticky;top:0;">
                        <tr>
                            <th style="padding:7px;">Ref. PO</th>
                            <th style="padding:7px;text-align:left;">Supplier</th>
                            <th style="padding:7px;text-align:left;">Produk</th>
                            <th style="padding:7px;text-align:right;">Qty Rusak</th>
                            <th style="padding:7px;">Status Penggantian</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </div>
        `,
        width: '780px',
        showCancelButton: true,
        cancelButtonText: 'Tutup',
        confirmButtonText: 'Buka Halaman Penerimaan & Retur',
        confirmButtonColor: '#1a75d2'
    }).then(res => {
        if (res.isConfirmed) {
            window.location.href = 'inventori/tambah_stok.php';
        }
    });
}
