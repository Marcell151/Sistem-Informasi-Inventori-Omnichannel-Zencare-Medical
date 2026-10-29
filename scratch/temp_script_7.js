

function toggleBatchTerlewatPanel() {
    const panel = document.getElementById("panel_batch_terlewat");
    if (!panel) return;
    panel.style.display = "block";
    const drawer = document.getElementById("drawer_batch_content");
    const lbl = document.getElementById("label_toggle_drawer");
    const icon = document.getElementById("icon_toggle_drawer");
    const pillArrow = document.getElementById("pill_arrow_indicator");
    if (!drawer) return;
    if (drawer.style.display === "none") {
        drawer.style.display = "block";
        if (lbl) lbl.innerText = "Tutup Rincian";
        if (icon) icon.innerText = "▴";
        if (pillArrow) pillArrow.innerText = "▴";
    } else {
        drawer.style.display = "none";
        if (lbl) lbl.innerText = "Buka Rincian";
        if (icon) icon.innerText = "▾";
        if (pillArrow) pillArrow.innerText = "▾";
    }
}

function openKasirAnomaliModal() {
    /* php */
    
    var modalHtml = `
    <div style="text-align:left; font-family: Inter, sans-serif;">
        <div style="margin-bottom:12px; font-size:12px; color:#64748b;">
            Daftar penukaran batch obat yang tercatat menyimpang dari urutan kedaluwarsa terdekat (FEFO) pada operasional kasir hari ini:
        </div>
        <div style="max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:10px;">
            <table style="width:100%; border-collapse:collapse; font-size:12px;">
                <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0; position:sticky; top:0; z-index:10;">
                    <tr>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">#</th>
                        <th style="padding:8px 10px; text-align:left; color:#64748b; font-size:11px;">Invoice / Waktu</th>
                        <th style="padding:8px 10px; text-align:left; color:#64748b; font-size:11px;">Nama Obat</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Batch Seharusnya (FEFO)</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Batch Diambil Kasir</th>
                        <th style="padding:8px 10px; text-align:center; color:#64748b; font-size:11px;">Qty</th>
                    </tr>
                </thead>
                <tbody style="background:#ffffff;">
                    "mock_php_val"
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px; padding:10px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe; font-size:11px; color:#1e40af; line-height:1.4;">
            <strong>ℹ️ Catatan Standar SOP:</strong> Bila pelanggan menolak batch terdepan karena alasan kemasan atau permintaan khusus, kasir diperbolehkan melayani dengan batch lain, namun sistem otomatis mencatat anomali ini untuk laporan pengawasan inventori.
        </div>
    </div>`;

    Swal.fire({
        title: '<span style="font-size:18px; font-weight:800; color:#1e293b;">📋 Log Pengawasan Anomali FEFO Hari Ini</span>',
        html: modalHtml,
        width: 800,
        showCloseButton: true,
        showConfirmButton: true,
        confirmButtonText: 'Tutup Panel',
        confirmButtonColor: '#1a75d2'
    });
}
