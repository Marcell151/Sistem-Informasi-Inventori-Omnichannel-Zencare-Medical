# -*- coding: utf-8 -*-
import os
import subprocess

chrome_exe = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
wf_dir = os.path.abspath(r'lain\File PPT\Wireframe')

# 1. Wireframe HTML: Laporan Penerimaan Barang
html_lap_penerimaan = """<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500;600&display=swap');
    * { box-sizing: border-box; margin: 0; padding: 0; border-radius: 0 !important; color: #000 !important; }
    body { font-family: 'DM Mono', monospace !important; background: #fff !important; min-height: 100vh; filter: grayscale(100%) contrast(120%); }
    .shell { display: flex; height: 100vh; overflow: hidden; }
    .nav-panel { width: 230px; flex-shrink: 0; background: #fff !important; border-right: 2px solid #000 !important; display: flex; flex-direction: column; }
    .nav-header { padding: 14px 12px; border-bottom: 2px solid #000 !important; }
    .nav-title { font-size: 11px; font-weight: 600; }
    .nav-sub { font-size: 8px; margin-top: 2px; }
    .nav-list { flex: 1; overflow-y: auto; padding: 6px 0; }
    .nav-sec { font-size: 8px; font-weight: 600; text-transform: uppercase; padding: 8px 12px 3px; }
    .nav-item { display: flex; align-items: center; gap: 8px; padding: 5px 12px; font-size: 9px; border: 1px solid transparent; }
    .nav-item.active { background: #000 !important; color: #fff !important; }
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #fdfdfd; }
    .topbar { height: 42px; border-bottom: 2px solid #000 !important; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; background: #fff; }
    .content { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 14px; }
    .btn { border: 1.5px solid #000; padding: 5px 10px; font-size: 9px; font-weight: 600; background: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
    .btn-dark { background: #000 !important; color: #fff !important; }
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .kpi-card { border: 1.5px solid #000; padding: 10px; background: #fff; }
    .kpi-lbl { font-size: 8px; text-transform: uppercase; font-weight: 600; }
    .kpi-val { font-size: 16px; font-weight: 600; margin-top: 4px; }
    .kpi-sub { font-size: 7.5px; margin-top: 2px; }
    .filter-box { border: 1.5px solid #000; padding: 10px; background: #fff; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .f-grp { display: flex; flex-direction: column; gap: 3px; }
    .f-lbl { font-size: 8px; font-weight: 600; text-transform: uppercase; }
    .f-inp { border: 1px solid #000; padding: 4px 8px; font-size: 9px; font-family: inherit; background: #fff; min-width: 110px; }
    .tbl-box { border: 1.5px solid #000; background: #fff; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 8.5px; text-align: left; }
    th { border-bottom: 2px solid #000; padding: 6px 8px; font-weight: 600; text-transform: uppercase; background: #f0f0f0; }
    td { border-bottom: 1px solid #000; padding: 6px 8px; }
</style>
</head>
<body>
<div class="shell">
    <div class="nav-panel">
        <div class="nav-header">
            <div class="nav-title">[+] ZENCARE MEDICAL</div>
            <div class="nav-sub">PANEL SUPERADMIN &bull; LOGISTIK</div>
        </div>
        <div class="nav-list">
            <div class="nav-sec">UTAMA</div>
            <div class="nav-item">Dashboard Eksekutif</div>
            <div class="nav-sec">LAPORAN MANAJERIAL</div>
            <div class="nav-item">Lap. Penjualan Omnichannel</div>
            <div class="nav-item">Lap. Ketersediaan Stok</div>
            <div class="nav-item">Lap. Logistik Medis &amp; Exp</div>
            <div class="nav-item active">[v] Lap. Penerimaan Barang</div>
            <div class="nav-item">Lap. Mutasi &amp; Konversi</div>
            <div class="nav-item">Lap. Anomali FEFO Kasir</div>
            <div class="nav-sec">PENGATURAN</div>
            <div class="nav-item">Manajemen Pengguna</div>
            <div class="nav-item">Pengaturan Web Toko</div>
        </div>
    </div>
    <div class="main">
        <div class="topbar">
            <div style="font-size:11px; font-weight:600;">LAPORAN PENERIMAAN BARANG &amp; AUDIT RETUR INBOUND</div>
            <div style="font-size:9px;">USER: SUPERADMIN (PEMILIK)</div>
        </div>
        <div class="content">
            <div class="filter-box">
                <div class="f-grp">
                    <span class="f-lbl">Rentang Tanggal Terima</span>
                    <div style="display:flex; gap:4px; align-items:center;">
                        <input class="f-inp" type="text" value="01/10/2026">
                        <span style="font-size:9px;">s/d</span>
                        <input class="f-inp" type="text" value="05/11/2026">
                    </div>
                </div>
                <div class="f-grp">
                    <span class="f-lbl">Mitra Supplier / PBF</span>
                    <select class="f-inp"><option>Semua Mitra Distributor</option><option>PT Kimia Sehat Farma</option><option>PT Alkesindo Jaya</option></select>
                </div>
                <div class="f-grp">
                    <span class="f-lbl">Status Penerimaan</span>
                    <select class="f-inp"><option>Semua Status Inbound</option><option>Sempurna (Full)</option><option>Retur Sebagian</option></select>
                </div>
                <div class="f-grp" style="flex:1;">
                    <span class="f-lbl">Cari Faktur / Batch / Produk</span>
                    <input class="f-inp" type="text" placeholder="Ketik No. Surat Jalan, No. Batch, SKU..." style="width:100%;">
                </div>
                <button class="btn btn-dark">FILTER DATA</button>
                <button class="btn">[+] EKSPOR EXCEL</button>
                <button class="btn">[v] CETAK LAPORAN</button>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-lbl">Total Faktur Inbound</div>
                    <div class="kpi-val">48 Dokumen</div>
                    <div class="kpi-sub">Periode 1 Bulan Terakhir</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-lbl">Total Nominal Pembelian</div>
                    <div class="kpi-val">Rp 184.500.000</div>
                    <div class="kpi-sub">Total Valuasi Pengadaan</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-lbl">Kuantitas Fisik Diterima</div>
                    <div class="kpi-val">1.420 Box / Dus</div>
                    <div class="kpi-sub">100% Masuk ke Stok Gudang</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-lbl">Unit Rusak / Diretur</div>
                    <div class="kpi-val">12 Dus (0.8%)</div>
                    <div class="kpi-sub">Retur ke PBF Rekanan</div>
                </div>
            </div>

            <div class="tbl-box">
                <table>
                    <thead>
                        <tr>
                            <th style="width:30px;">NO</th>
                            <th>NO. REFERENSI</th>
                            <th>TGL TERIMA</th>
                            <th>DISTRIBUTOR / PBF</th>
                            <th>PRODUK &amp; VARIASI</th>
                            <th>NO. BATCH &amp; EXP</th>
                            <th>QTY PESAN</th>
                            <th>QTY TERIMA</th>
                            <th>RETUR</th>
                            <th>HARGA BELI</th>
                            <th>STATUS</th>
                            <th>PENERIMA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><b>RCV-202611-001</b></td>
                            <td>04/11/2026</td>
                            <td>PT Medika Sejahtera Distribusi</td>
                            <td>Amoxicillin 500mg (Box 10x10)</td>
                            <td>BATCH-AMX-2028<br><small>EXP: 15/08/2028</small></td>
                            <td>50 Box</td>
                            <td><b>50 Box</b></td>
                            <td>0</td>
                            <td>Rp 65.000</td>
                            <td>[DITERIMA LENGKAP]</td>
                            <td>Admin Gudang</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td><b>RCV-202611-002</b></td>
                            <td>03/11/2026</td>
                            <td>PT Alkesindo Utama Perkasa</td>
                            <td>Tensimeter Digital Apex (Unit)</td>
                            <td>SN-TNS-88491 s/d 88500<br><small>GARANSI: 2 TAHUN</small></td>
                            <td>10 Unit</td>
                            <td><b>10 Unit</b></td>
                            <td>0</td>
                            <td>Rp 320.000</td>
                            <td>[DITERIMA LENGKAP]</td>
                            <td>Admin Gudang</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td><b>RCV-202610-089</b></td>
                            <td>28/10/2026</td>
                            <td>PT Bina Logistik Husada</td>
                            <td>Hufagrip Sirup Merah (Karton 50 Botol)</td>
                            <td>BATCH-HFG-091<br><small>EXP: 10/12/2027</small></td>
                            <td>20 Karton</td>
                            <td><b>18 Karton</b></td>
                            <td><b>2 (Pecah)</b></td>
                            <td>Rp 820.000</td>
                            <td>[RETUR PARSIAL]</td>
                            <td>Admin Gudang</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td><b>RCV-202610-075</b></td>
                            <td>22/10/2026</td>
                            <td>PT Kalbe Farma Tbk</td>
                            <td>Promag Tablet Kunyah (Dus 3x10)</td>
                            <td>BATCH-PMG-772<br><small>EXP: 05/04/2028</small></td>
                            <td>100 Dus</td>
                            <td><b>100 Dus</b></td>
                            <td>0</td>
                            <td>Rp 42.000</td>
                            <td>[DITERIMA LENGKAP]</td>
                            <td>Admin Toko</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td><b>RCV-202610-061</b></td>
                            <td>15/10/2026</td>
                            <td>PT Kimia Distributor Nasional</td>
                            <td>Paracetamol 500mg (Box 10x10)</td>
                            <td>BATCH-PCT-441<br><small>EXP: 20/06/2027</small></td>
                            <td>80 Box</td>
                            <td><b>80 Box</b></td>
                            <td>0</td>
                            <td>Rp 38.000</td>
                            <td>[DITERIMA LENGKAP]</td>
                            <td>Admin Gudang</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
"""

# 2. Wireframe HTML: Laporan Mutasi & Konversi Satuan
html_lap_mutasi = """<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500;600&display=swap');
    * { box-sizing: border-box; margin: 0; padding: 0; border-radius: 0 !important; color: #000 !important; }
    body { font-family: 'DM Mono', monospace !important; background: #fff !important; min-height: 100vh; filter: grayscale(100%) contrast(120%); }
    .shell { display: flex; height: 100vh; overflow: hidden; }
    .nav-panel { width: 230px; flex-shrink: 0; background: #fff !important; border-right: 2px solid #000 !important; display: flex; flex-direction: column; }
    .nav-header { padding: 14px 12px; border-bottom: 2px solid #000 !important; }
    .nav-title { font-size: 11px; font-weight: 600; }
    .nav-sub { font-size: 8px; margin-top: 2px; }
    .nav-list { flex: 1; overflow-y: auto; padding: 6px 0; }
    .nav-sec { font-size: 8px; font-weight: 600; text-transform: uppercase; padding: 8px 12px 3px; }
    .nav-item { display: flex; align-items: center; gap: 8px; padding: 5px 12px; font-size: 9px; border: 1px solid transparent; }
    .nav-item.active { background: #000 !important; color: #fff !important; }
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #fdfdfd; }
    .topbar { height: 42px; border-bottom: 2px solid #000 !important; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; background: #fff; }
    .content { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 14px; }
    .btn { border: 1.5px solid #000; padding: 5px 10px; font-size: 9px; font-weight: 600; background: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; }
    .btn-dark { background: #000 !important; color: #fff !important; }
    .tab-bar { display: flex; gap: 2px; border-bottom: 2px solid #000; }
    .tab-btn { padding: 6px 14px; font-size: 9px; font-weight: 600; border: 1.5px solid #000; border-bottom: none; background: #f0f0f0; cursor: pointer; }
    .tab-btn.active { background: #000 !important; color: #fff !important; }
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .kpi-card { border: 1.5px solid #000; padding: 10px; background: #fff; }
    .kpi-lbl { font-size: 8px; text-transform: uppercase; font-weight: 600; }
    .kpi-val { font-size: 16px; font-weight: 600; margin-top: 4px; }
    .kpi-sub { font-size: 7.5px; margin-top: 2px; }
    .filter-box { border: 1.5px solid #000; padding: 10px; background: #fff; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .f-grp { display: flex; flex-direction: column; gap: 3px; }
    .f-lbl { font-size: 8px; font-weight: 600; text-transform: uppercase; }
    .f-inp { border: 1px solid #000; padding: 4px 8px; font-size: 9px; font-family: inherit; background: #fff; min-width: 110px; }
    .tbl-box { border: 1.5px solid #000; background: #fff; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 8.5px; text-align: left; }
    th { border-bottom: 2px solid #000; padding: 6px 8px; font-weight: 600; text-transform: uppercase; background: #f0f0f0; }
    td { border-bottom: 1px solid #000; padding: 6px 8px; }
</style>
</head>
<body>
<div class="shell">
    <div class="nav-panel">
        <div class="nav-header">
            <div class="nav-title">[+] ZENCARE MEDICAL</div>
            <div class="nav-sub">PANEL SUPERADMIN &bull; AUDIT STOK</div>
        </div>
        <div class="nav-list">
            <div class="nav-sec">UTAMA</div>
            <div class="nav-item">Dashboard Eksekutif</div>
            <div class="nav-sec">LAPORAN MANAJERIAL</div>
            <div class="nav-item">Lap. Penjualan Omnichannel</div>
            <div class="nav-item">Lap. Ketersediaan Stok</div>
            <div class="nav-item">Lap. Logistik Medis &amp; Exp</div>
            <div class="nav-item">Lap. Penerimaan Barang</div>
            <div class="nav-item active">[v] Lap. Mutasi &amp; Konversi</div>
            <div class="nav-item">Lap. Anomali FEFO Kasir</div>
            <div class="nav-sec">PENGATURAN</div>
            <div class="nav-item">Manajemen Pengguna</div>
            <div class="nav-item">Pengaturan Web Toko</div>
        </div>
    </div>
    <div class="main">
        <div class="topbar">
            <div style="font-size:11px; font-weight:600;">LAPORAN AUDIT MUTASI PENYESUAIAN &amp; KONVERSI SATUAN UOM</div>
            <div style="font-size:9px;">USER: SUPERADMIN (PEMILIK)</div>
        </div>
        <div class="content">
            <div class="tab-bar">
                <button class="tab-btn active">[TAB 1] KONVERSI PECAH DUS (UOM)</button>
                <button class="tab-btn">[TAB 2] MUTASI STOK PENYESUAIAN MANUAL</button>
            </div>

            <div class="filter-box">
                <div class="f-grp">
                    <span class="f-lbl">Rentang Tanggal</span>
                    <div style="display:flex; gap:4px; align-items:center;">
                        <input class="f-inp" type="text" value="01/10/2026">
                        <span style="font-size:9px;">s/d</span>
                        <input class="f-inp" type="text" value="05/11/2026">
                    </div>
                </div>
                <div class="f-grp">
                    <span class="f-lbl">Filter Produk</span>
                    <select class="f-inp"><option>Semua Komoditas Medis</option><option>Amoxicillin 500mg</option><option>Paracetamol 500mg</option></select>
                </div>
                <div class="f-grp" style="flex:1;">
                    <span class="f-lbl">Pencarian Batch / Aktor</span>
                    <input class="f-inp" type="text" placeholder="Ketik No. Batch Asal, Batch Hasil, Nama Staf..." style="width:100%;">
                </div>
                <button class="btn btn-dark">FILTER DATA</button>
                <button class="btn">[+] EKSPOR EXCEL</button>
                <button class="btn">[v] CETAK AUDIT</button>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-lbl">Total Sesi Pemecahan Dus</div>
                    <div class="kpi-val">35 Sesi</div>
                    <div class="kpi-sub">Pemecahan Kemasan Grosir ke Eceran</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-lbl">Total Kemasan Induk Dibongkar</div>
                    <div class="kpi-val">85 Dus / Box</div>
                    <div class="kpi-sub">Stok Kemasan Besar Dikurangi</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-lbl">Total Eceran Terbentuk</div>
                    <div class="kpi-val">850 Strip / Pcs</div>
                    <div class="kpi-sub">100% Mewarisi Batch &amp; Exp Asal</div>
                </div>
            </div>

            <div class="tbl-box">
                <table>
                    <thead>
                        <tr>
                            <th style="width:30px;">NO</th>
                            <th>WAKTU KONVERSI</th>
                            <th>PRODUK &amp; VARIASI</th>
                            <th>BATCH ASAL (DUS)</th>
                            <th>BATCH HASIL (ECERAN)</th>
                            <th>QTY DUS DIBONGKAR</th>
                            <th>HASIL ECERAN</th>
                            <th>SISA DUS GUDANG</th>
                            <th>PETUGAS OPERASIONAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>04/11/2026 14:15</td>
                            <td>Amoxicillin 500mg (Box ke Strip)</td>
                            <td>BATCH-AMX-2028</td>
                            <td><b>BATCH-AMX-2028-CV1</b></td>
                            <td>2 Box</td>
                            <td><b>20 Strip</b> (Rasio 10)</td>
                            <td>48 Box</td>
                            <td>Admin Toko (Budi)</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>02/11/2026 10:30</td>
                            <td>Paracetamol 500mg (Dus ke Strip)</td>
                            <td>BATCH-PCT-441</td>
                            <td><b>BATCH-PCT-441-CV1</b></td>
                            <td>5 Dus</td>
                            <td><b>50 Strip</b> (Rasio 10)</td>
                            <td>75 Dus</td>
                            <td>Kasir Toko (Siti)</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>29/10/2026 16:40</td>
                            <td>Hufagrip Sirup Merah (Karton ke Btl)</td>
                            <td>BATCH-HFG-091</td>
                            <td><b>BATCH-HFG-091-CV1</b></td>
                            <td>1 Karton</td>
                            <td><b>50 Botol</b> (Rasio 50)</td>
                            <td>17 Karton</td>
                            <td>Admin Toko (Budi)</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>25/10/2026 09:20</td>
                            <td>Promag Tablet Kunyah (Dus ke Strip)</td>
                            <td>BATCH-PMG-772</td>
                            <td><b>BATCH-PMG-772-CV1</b></td>
                            <td>3 Dus</td>
                            <td><b>30 Strip</b> (Rasio 10)</td>
                            <td>97 Dus</td>
                            <td>Admin Toko (Budi)</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>20/10/2026 11:50</td>
                            <td>Ibuprofen 400mg (Box ke Strip)</td>
                            <td>BATCH-IBP-512</td>
                            <td><b>BATCH-IBP-512-CV1</b></td>
                            <td>2 Box</td>
                            <td><b>20 Strip</b> (Rasio 10)</td>
                            <td>38 Box</td>
                            <td>Kasir Toko (Siti)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
"""

# Write HTML templates to scratch
with open(r'scratch\wf_lap_penerimaan.html', 'w', encoding='utf-8') as f:
    f.write(html_lap_penerimaan)

with open(r'scratch\wf_lap_mutasi.html', 'w', encoding='utf-8') as f:
    f.write(html_lap_mutasi)

# Capture with Chrome to File PPT/Wireframe/
for name, html_f in [('Laporan Penerimaan Barang.png', 'wf_lap_penerimaan.html'), ('Laporan Mutasi & Konversi.png', 'wf_lap_mutasi.html')]:
    out_png = os.path.join(wf_dir, name)
    file_url = 'file:///' + os.path.abspath(os.path.join(r'scratch', html_f)).replace('\\', '/')
    cmd = [chrome_exe, '--headless=new', '--disable-gpu', '--no-sandbox', f'--screenshot={out_png}', '--window-size=1440,900', file_url]
    res = subprocess.run(cmd, capture_output=True, text=True)
    if os.path.exists(out_png):
        print(f"Generated wireframe: {name} ({os.path.getsize(out_png)} bytes)")
    else:
        print(f"Failed to generate: {name}")

