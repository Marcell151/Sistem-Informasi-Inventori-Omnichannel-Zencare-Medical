import os
import subprocess
from PIL import Image

# Extract full CSS from wireframe_zencare.html
with open('lain/wireframe_zencare.html', 'r', encoding='utf-8') as f:
    wf_html = f.read()

idx_style_start = wf_html.find('<style>')
idx_style_end = wf_html.find('</style>') + 8
css = wf_html[idx_style_start:idx_style_end]

# 1. Stok Opname Wireframe HTML
stok_opname_html = f'''<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
{css}
<style>
body {{ background: #fff !important; margin: 0; padding: 15px; width: 1560px; height: 672px; overflow: hidden; }}
.wf {{ border: 2px solid #000 !important; width: 100%; height: 100%; display: flex; flex-direction: column; }}
</style>
</head>
<body>
<div class="wf">
    <div class="wf-topbar" style="height:44px; padding:0 20px;">
        <div class="wf-logo" style="width:24px;height:24px;"></div>
        <div style="font-weight:bold;font-size:12px;">ZenCare Medical &mdash; Back-Office Logistik</div>
        <div class="flex1"></div>
        <div class="row" style="gap:10px;">
            <span style="font-size:10px;font-weight:bold;border:1px solid #000;padding:2px 8px;background:#000;color:#fff !important;">STOK OPNAME AKTIF</span>
            <span style="font-size:10px;">Petugas: Admin Gudang</span>
            <div class="av" style="width:20px;height:20px;"></div>
        </div>
    </div>
    <div style="display:flex; flex:1; overflow:hidden;">
        <!-- Sidebar -->
        <div class="wf-sidebar" style="width:170px;">
            <div class="wf-sl">UTAMA</div>
            <div class="wf-si">Dashboard</div>
            <div class="wf-sl">TRANSAKSI</div>
            <div class="wf-si">Kasir (POS)</div>
            <div class="wf-si">Transaksi</div>
            <div class="wf-sl">LOGISTIK & STOK</div>
            <div class="wf-si">Penerimaan Barang</div>
            <div class="wf-si">Mutasi Stok</div>
            <div class="wf-si">Kartu Stok</div>
            <div class="wf-si on">Stok Opname</div>
            <div class="wf-si">Konversi Satuan</div>
        </div>
        <!-- Content -->
        <div class="content flex1" style="padding:15px 20px; overflow-y:auto; display:flex; flex-direction:column; gap:12px;">
            <!-- Header Banner Status Sesi & Freeze Lock -->
            <div class="card row" style="justify-content:space-between; padding:10px 14px; background:#fff !important; border:2px solid #000;">
                <div class="col" style="gap:2px;">
                    <div style="font-size:11px;font-weight:bold;">SESI OPNAME: OPN-20261105-01 &bull; STATUS: DIBEKUKAN (FREEZE LOCK)</div>
                    <div style="font-size:9px;">Transaksi Kasir POS Offline & Pesanan E-Commerce Dikunci Sementara Demi Akurasi Cacahan Fisik.</div>
                </div>
                <div class="row" style="gap:8px;">
                    <div class="btn" style="font-size:9px;">Batalkan Sesi</div>
                    <div class="btn btnp" style="font-size:9px; background:#000; color:#fff !important;">+ Input Hasil Cacahan</div>
                    <div class="btn btnp" style="font-size:9px; background:#000; color:#fff !important;">Eksekusi Mutasi Selisih</div>
                </div>
            </div>

            <!-- Stat Cards Metric Opname -->
            <div class="g4">
                <div class="sc">
                    <div style="font-size:8px;font-weight:bold;">TOTAL BATCH OBAT</div>
                    <div style="font-size:16px;font-weight:bold;margin:3px 0;">48 Batch</div>
                    <div style="font-size:8px;">Metode Blind Count</div>
                </div>
                <div class="sc">
                    <div style="font-size:8px;font-weight:bold;">ALAT KESEHATAN (SN)</div>
                    <div style="font-size:16px;font-weight:bold;margin:3px 0;">32 Unit</div>
                    <div style="font-size:8px;">Audit Serial Number</div>
                </div>
                <div class="sc">
                    <div style="font-size:8px;font-weight:bold;">SELISIH KURANG (DEFISIT)</div>
                    <div style="font-size:16px;font-weight:bold;margin:3px 0;">-2 Item</div>
                    <div style="font-size:8px;">Perlu Mutasi Koreksi</div>
                </div>
                <div class="sc">
                    <div style="font-size:8px;font-weight:bold;">SESUAI FISIK (BALANCE)</div>
                    <div style="font-size:16px;font-weight:bold;margin:3px 0;">46 Batch</div>
                    <div style="font-size:8px;">Akurasi Stok 95.8%</div>
                </div>
            </div>

            <!-- Tabs Nav & Filter -->
            <div class="row" style="justify-content:space-between; border-bottom:1px solid #000; padding-bottom:4px;">
                <div class="row" style="gap:5px;">
                    <div class="btn btnp" style="font-size:9px; background:#000; color:#fff !important;">Tab 1: Pencacahan Sediaan Obat (Batch & FEFO)</div>
                    <div class="btn" style="font-size:9px;">Tab 2: Pencacahan Alat Kesehatan Berseri (Serial Number)</div>
                </div>
                <div class="row" style="gap:6px;">
                    <div class="inp" style="width:180px; height:22px;"><div class="blk" style="height:6px;width:70%;"></div></div>
                    <div class="btn" style="font-size:8px;">Filter Kategori</div>
                </div>
            </div>

            <!-- Table Blind Count & Audit -->
            <div class="card" style="padding:0; flex:1;">
                <table class="tbl" style="width:100%; border-collapse:collapse; font-size:8.5px;">
                    <thead>
                        <tr style="border-bottom:1px solid #000; background:#f4f4f4;">
                            <th style="padding:5px 8px; text-align:center; width:30px;">NO</th>
                            <th style="padding:5px 8px; text-align:left;">NAMA KOMODITAS / OBAT</th>
                            <th style="padding:5px 8px; text-align:left;">SATUAN</th>
                            <th style="padding:5px 8px; text-align:left;">NOMOR BATCH</th>
                            <th style="padding:5px 8px; text-align:center;">TGL EXP</th>
                            <th style="padding:5px 8px; text-align:center;">STOK SISTEM</th>
                            <th style="padding:5px 8px; text-align:center; width:90px;">HITUNG FISIK</th>
                            <th style="padding:5px 8px; text-align:center;">SELISIH</th>
                            <th style="padding:5px 8px; text-align:center;">STATUS MUTASI</th>
                            <th style="padding:5px 8px; text-align:center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #000;">
                            <td style="padding:4px 8px; text-align:center;">1</td>
                            <td style="padding:4px 8px; font-weight:bold;">Amoxicillin 500mg Trihydrate</td>
                            <td style="padding:4px 8px;">Strip</td>
                            <td style="padding:4px 8px;">AMX-2026-B1</td>
                            <td style="padding:4px 8px; text-align:center;">12/2027</td>
                            <td style="padding:4px 8px; text-align:center; font-style:italic;">[Terkunci]</td>
                            <td style="padding:4px 8px; text-align:center;"><div class="inp" style="height:18px; width:70px; margin:0 auto; font-weight:bold; text-align:center;">50</div></td>
                            <td style="padding:4px 8px; text-align:center; font-weight:bold;">0 (Cocok)</td>
                            <td style="padding:4px 8px; text-align:center;">Sesuai</td>
                            <td style="padding:4px 8px; text-align:center;"><div class="btn" style="font-size:7.5px; padding:1px 4px;">Koreksi</div></td>
                        </tr>
                        <tr style="border-bottom:1px solid #000;">
                            <td style="padding:4px 8px; text-align:center;">2</td>
                            <td style="padding:4px 8px; font-weight:bold;">Paracetamol Sirup 120mg/5ml</td>
                            <td style="padding:4px 8px;">Botol</td>
                            <td style="padding:4px 8px;">PCT-2026-B4</td>
                            <td style="padding:4px 8px; text-align:center;">08/2026</td>
                            <td style="padding:4px 8px; text-align:center; font-style:italic;">[Terkunci]</td>
                            <td style="padding:4px 8px; text-align:center;"><div class="inp" style="height:18px; width:70px; margin:0 auto; font-weight:bold; text-align:center;">18</div></td>
                            <td style="padding:4px 8px; text-align:center; font-weight:bold; color:#000;">-2 (Kurang)</td>
                            <td style="padding:4px 8px; text-align:center;">Belum Dimutasi</td>
                            <td style="padding:4px 8px; text-align:center;"><div class="btn" style="font-size:7.5px; padding:1px 4px;">Hitung Ulang</div></td>
                        </tr>
                        <tr style="border-bottom:1px solid #000;">
                            <td style="padding:4px 8px; text-align:center;">3</td>
                            <td style="padding:4px 8px; font-weight:bold;">Tensimeter Digital OMRON HEM-7120</td>
                            <td style="padding:4px 8px;">Unit</td>
                            <td style="padding:4px 8px;">SN-OMR-7120-X1</td>
                            <td style="padding:4px 8px; text-align:center;">Garansi 2028</td>
                            <td style="padding:4px 8px; text-align:center; font-style:italic;">[Terkunci]</td>
                            <td style="padding:4px 8px; text-align:center;"><div class="inp" style="height:18px; width:70px; margin:0 auto; font-weight:bold; text-align:center;">10</div></td>
                            <td style="padding:4px 8px; text-align:center; font-weight:bold;">0 (Cocok)</td>
                            <td style="padding:4px 8px; text-align:center;">Sesuai</td>
                            <td style="padding:4px 8px; text-align:center;"><div class="btn" style="font-size:7.5px; padding:1px 4px;">Detail SN</div></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Action Toolbar -->
            <div class="row" style="justify-content:space-between; font-size:9px;">
                <div>Menampilkan 3 dari 48 rekaman logistik opname &bull; Verifikasi oleh: Superadmin & Petugas Gudang</div>
                <div class="row" style="gap:8px;">
                    <div class="btn" style="font-size:9px;">Cetak Lembar Kerja Fisik (Kertas Hitung)</div>
                    <div class="btn btnp" style="font-size:9px; background:#000; color:#fff !important;">Cetak Berita Acara Opname (BA)</div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>'''

with open('scratch/wf_stok_opname.html', 'w', encoding='utf-8') as f:
    f.write(stok_opname_html)

# 2. Konversi Satuan Wireframe HTML
konversi_html = f'''<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
{css}
<style>
body {{ background: #fff !important; margin: 0; padding: 15px; width: 1560px; height: 672px; overflow: hidden; }}
.wf {{ border: 2px solid #000 !important; width: 100%; height: 100%; display: flex; flex-direction: column; }}
</style>
</head>
<body>
<div class="wf">
    <div class="wf-topbar" style="height:44px; padding:0 20px;">
        <div class="wf-logo" style="width:24px;height:24px;"></div>
        <div style="font-weight:bold;font-size:12px;">ZenCare Medical &mdash; Back-Office Logistik</div>
        <div class="flex1"></div>
        <div class="row" style="gap:10px;">
            <span style="font-size:10px;font-weight:bold;border:1px solid #000;padding:2px 8px;">MULTI-UOM AKTIF</span>
            <span style="font-size:10px;">Petugas: Admin Gudang / Kasir</span>
            <div class="av" style="width:20px;height:20px;"></div>
        </div>
    </div>
    <div style="display:flex; flex:1; overflow:hidden;">
        <!-- Sidebar -->
        <div class="wf-sidebar" style="width:170px;">
            <div class="wf-sl">UTAMA</div>
            <div class="wf-si">Dashboard</div>
            <div class="wf-sl">TRANSAKSI</div>
            <div class="wf-si">Kasir (POS)</div>
            <div class="wf-si">Transaksi</div>
            <div class="wf-sl">LOGISTIK & STOK</div>
            <div class="wf-si">Penerimaan Barang</div>
            <div class="wf-si">Mutasi Stok</div>
            <div class="wf-si">Kartu Stok</div>
            <div class="wf-si">Stok Opname</div>
            <div class="wf-si on">Konversi Satuan</div>
        </div>
        <!-- Content -->
        <div class="content flex1" style="padding:15px 20px; overflow-y:auto; display:flex; flex-direction:column; gap:12px;">
            <div class="row" style="justify-content:space-between; border-bottom:1px solid #000; padding-bottom:6px;">
                <div class="col" style="gap:2px;">
                    <div style="font-size:12px;font-weight:bold;">FORM KONVERSI SATUAN & PECAH KEMASAN (MULTI-UOM)</div>
                    <div style="font-size:9px;">Bongkar kemasan Dus / Box besar pabrik menjadi satuan eceran (Strip / Pcs) siap jual di etalase toko.</div>
                </div>
                <div class="row" style="gap:6px;">
                    <div class="btn" style="font-size:9px;">Petunjuk Multi-UOM</div>
                    <div class="btn btnp" style="font-size:9px; background:#000; color:#fff !important;">Riwayat Konversi (Histori)</div>
                </div>
            </div>

            <div class="g2" style="grid-template-columns: 1.1fr 1fr; gap:15px; flex:1;">
                <!-- Left: Form Pemecahan Satuan -->
                <div class="card col" style="gap:10px; padding:14px;">
                    <div style="font-size:10px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:4px;">1. Parameter Pembongkaran Dus</div>
                    
                    <div>
                        <div style="font-size:8.5px;margin-bottom:2px;font-weight:bold;">PILIH KOMODITAS MEDIS (MULTI-UOM)</div>
                        <div class="sel" style="height:26px;"><div style="font-size:9px;">Amoxicillin 500mg Trihydrate [SKU: MED-AMX-01]</div><div class="tri"></div></div>
                    </div>

                    <!-- Ratio info box -->
                    <div class="card" style="background:#f4f4f4 !important; border:1px solid #000; padding:8px;">
                        <div class="row" style="justify-content:space-between; font-size:8.5px; margin-bottom:3px;">
                            <span>KEMASAN BESAR: <b>Box / Dus Pabrik</b></span>
                            <span>KEMASAN KECIL: <b>Strip (Eceran)</b></span>
                        </div>
                        <div style="font-size:9.5px; font-weight:bold; border-top:1px dashed #000; padding-top:4px;">
                            RASIO KONVERSI: 1 Box = 10 Strip &bull; Sisa Dus Tersedia: 15 Box
                        </div>
                    </div>

                    <div>
                        <div style="font-size:8.5px;margin-bottom:2px;font-weight:bold;">PILIH BATCH DUS BESAR YANG DIBONGKAR</div>
                        <div class="sel" style="height:26px;"><div style="font-size:9px;">Batch: AMX-2026-B1 (Exp: 12/2027 &bull; Saldo: 15 Box)</div><div class="tri"></div></div>
                    </div>

                    <div class="g2">
                        <div>
                            <div style="font-size:8.5px;margin-bottom:2px;font-weight:bold;">JUMLAH DUS DIBONGKAR</div>
                            <div class="inp" style="height:26px;"><div style="font-size:10px;font-weight:bold;">1 Box</div></div>
                        </div>
                        <div>
                            <div style="font-size:8.5px;margin-bottom:2px;font-weight:bold;">HASIL SATUAN ECERAN</div>
                            <div class="inp" style="height:26px; background:#f4f4f4;"><div style="font-size:10px;font-weight:bold;">+10 Strip</div></div>
                        </div>
                    </div>

                    <div>
                        <div style="font-size:8.5px;margin-bottom:2px;font-weight:bold;">NOMOR BATCH TURUNAN BARU (OTOMATIS)</div>
                        <div class="inp" style="height:26px;"><div style="font-size:9px;font-family:monospace;">AMX-2026-B1-SUB1 (FEFO Terhubung Induk)</div></div>
                    </div>

                    <div class="btn btnp" style="height:32px; font-size:10px; justify-content:center; background:#000; color:#fff !important; margin-top:5px;">
                        BONGKAR KEMASAN & MUTASIKAN KE RAK TOKO
                    </div>
                </div>

                <!-- Right: Riwayat Terakhir Konversi -->
                <div class="card col" style="gap:8px; padding:14px;">
                    <div style="font-size:10px;font-weight:bold;border-bottom:1px solid #000;padding-bottom:4px;">2. Log Aktivitas Pemecahan Dus Terkini</div>
                    
                    <table class="tbl" style="width:100%; border-collapse:collapse; font-size:8px;">
                        <thead>
                            <tr style="border-bottom:1px solid #000; background:#f4f4f4;">
                                <th style="padding:4px; text-align:left;">WAKTU</th>
                                <th style="padding:4px; text-align:left;">PRODUK</th>
                                <th style="padding:4px; text-align:center;">DIBONGKAR</th>
                                <th style="padding:4px; text-align:center;">HASIL ECERAN</th>
                                <th style="padding:4px; text-align:center;">SISA BOX</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom:1px solid #000;">
                                <td style="padding:4px;">05/11 14:20</td>
                                <td style="padding:4px; font-weight:bold;">Paracetamol 500mg</td>
                                <td style="padding:4px; text-align:center;">2 Box</td>
                                <td style="padding:4px; text-align:center; font-weight:bold;">+20 Strip</td>
                                <td style="padding:4px; text-align:center;">8 Box</td>
                            </tr>
                            <tr style="border-bottom:1px solid #000;">
                                <td style="padding:4px;">05/11 10:15</td>
                                <td style="padding:4px; font-weight:bold;">Amoxicillin 500mg</td>
                                <td style="padding:4px; text-align:center;">1 Box</td>
                                <td style="padding:4px; text-align:center; font-weight:bold;">+10 Strip</td>
                                <td style="padding:4px; text-align:center;">15 Box</td>
                            </tr>
                            <tr style="border-bottom:1px solid #000;">
                                <td style="padding:4px;">04/11 16:45</td>
                                <td style="padding:4px; font-weight:bold;">Jarum Suntik 3cc</td>
                                <td style="padding:4px; text-align:center;">1 Box</td>
                                <td style="padding:4px; text-align:center; font-weight:bold;">+100 Pcs</td>
                                <td style="padding:4px; text-align:center;">4 Box</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="card" style="margin-top:auto; background:#fff; border:1px solid #000; padding:8px; font-size:8px;">
                        <div style="font-weight:bold; margin-bottom:2px;">KETERANGAN INTEGRASI LOGISTIK:</div>
                        <div>Setiap eksekusi pemecahan dus secara otomatis mendebit stok kemasan besar di <code>stok_batch</code>, mengkredit saldo eceran di <code>stok_toko</code>, dan mencatat mutasi permanen ke <code>kartu_stok</code>.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>'''

with open('scratch/wf_konversi_satuan.html', 'w', encoding='utf-8') as f:
    f.write(konversi_html)

# 3. Laporan Anomali FEFO Wireframe HTML
anomali_html = f'''<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
{css}
<style>
body {{ background: #fff !important; margin: 0; padding: 15px; width: 1560px; height: 672px; overflow: hidden; }}
.wf {{ border: 2px solid #000 !important; width: 100%; height: 100%; display: flex; flex-direction: column; }}
</style>
</head>
<body>
<div class="wf">
    <div class="wf-topbar" style="height:44px; padding:0 20px;">
        <div class="wf-logo" style="width:24px;height:24px;"></div>
        <div style="font-weight:bold;font-size:12px;">ZenCare Medical &mdash; Dashboard Superadmin (Pemilik)</div>
        <div class="flex1"></div>
        <div class="row" style="gap:10px;">
            <span style="font-size:10px;font-weight:bold;border:1px solid #000;padding:2px 8px;background:#000;color:#fff !important;">AUDIT KEPATUHAN</span>
            <span style="font-size:10px;">Akses: Superadmin</span>
            <div class="av" style="width:20px;height:20px;"></div>
        </div>
    </div>
    <div style="display:flex; flex:1; overflow:hidden;">
        <!-- Sidebar -->
        <div class="wf-sidebar" style="width:170px;">
            <div class="wf-sl">MANAJERIAL</div>
            <div class="wf-si">Dashboard</div>
            <div class="wf-sl">PELAPORAN</div>
            <div class="wf-si">Lap. Penjualan</div>
            <div class="wf-si">Lap. Persediaan</div>
            <div class="wf-si">Lap. Logistik Medis</div>
            <div class="wf-si on">Lap. Anomali FEFO</div>
            <div class="wf-sl">PENGATURAN</div>
            <div class="wf-si">Manajemen Pengguna</div>
            <div class="wf-si">Pengaturan Web</div>
        </div>
        <!-- Content -->
        <div class="content flex1" style="padding:15px 20px; overflow-y:auto; display:flex; flex-direction:column; gap:12px;">
            <div class="card row" style="justify-content:space-between; padding:10px 14px; background:#f4f4f4 !important; border:1px solid #000;">
                <div class="col" style="gap:2px;">
                    <div style="font-size:11px;font-weight:bold;">LAPORAN AUDIT ANOMALI FEFO (KEPATUHAN PENGELUARAN OBAT KASIR)</div>
                    <div style="font-size:9px;">Merekam insiden deviasi saat kasir toko mengeluarkan nomor batch obat yang berbeda dari rekomendasi kedaluwarsa terdekat.</div>
                </div>
                <div class="row" style="gap:6px;">
                    <div class="btn" style="font-size:9px;">Ekspor Excel</div>
                    <div class="btn btnp" style="font-size:9px; background:#000; color:#fff !important;">Cetak Rekapitulasi Audit</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="card row" style="gap:12px; padding:8px 12px; font-size:8.5px;">
                <div class="col" style="gap:2px;">
                    <b>DARI TANGGAL</b>
                    <div class="inp" style="height:22px; width:110px;"><div style="font-size:8px;">01/11/2026</div></div>
                </div>
                <div class="col" style="gap:2px;">
                    <b>SAMPAI TANGGAL</b>
                    <div class="inp" style="height:22px; width:110px;"><div style="font-size:8px;">05/11/2026</div></div>
                </div>
                <div class="col" style="gap:2px;">
                    <b>KANAL PENJUALAN</b>
                    <div class="sel" style="height:22px; width:130px;"><div style="font-size:8px;">Semua Kanal (POS & Online)</div><div class="tri"></div></div>
                </div>
                <div class="col" style="gap:2px;">
                    <b>STATUS PENYELESAIAN</b>
                    <div class="sel" style="height:22px; width:120px;"><div style="font-size:8px;">Semua Status</div><div class="tri"></div></div>
                </div>
                <div class="btn btnp" style="align-self:flex-end; height:22px; font-size:8.5px; background:#000; color:#fff !important;">Terapkan Filter</div>
            </div>

            <!-- Summary Cards -->
            <div class="g3">
                <div class="sc">
                    <div style="font-size:8px;font-weight:bold;">TOTAL INSIDEN ANOMALI</div>
                    <div style="font-size:16px;font-weight:bold;margin:2px 0;">8 Kasus</div>
                    <div style="font-size:8px;">Tercatat dari 320 Transaksi</div>
                </div>
                <div class="sc">
                    <div style="font-size:8px;font-weight:bold;">RATA-RATA SELISIH HARI EXP</div>
                    <div style="font-size:16px;font-weight:bold;margin:2px 0;">+45 Hari</div>
                    <div style="font-size:8px;">Deviasi Mengambil Stok Lebih Baru</div>
                </div>
                <div class="sc">
                    <div style="font-size:8px;font-weight:bold;">TINGKAT KEPATUHAN FEFO</div>
                    <div style="font-size:16px;font-weight:bold;margin:2px 0;">97.5%</div>
                    <div style="font-size:8px;">Standar Kepatuhan Sangat Baik</div>
                </div>
            </div>

            <!-- Table Anomali -->
            <div class="card" style="padding:0; flex:1;">
                <table class="tbl" style="width:100%; border-collapse:collapse; font-size:8.5px;">
                    <thead>
                        <tr style="border-bottom:1px solid #000; background:#f4f4f4;">
                            <th style="padding:5px 8px; text-align:left;">TANGGAL / NO FAKTUR</th>
                            <th style="padding:5px 8px; text-align:left;">KASIR</th>
                            <th style="padding:5px 8px; text-align:left;">NAMA OBAT</th>
                            <th style="padding:5px 8px; text-align:left;">BATCH DIAMBIL (EXP)</th>
                            <th style="padding:5px 8px; text-align:left;">BATCH SEHARUSNYA (EXP)</th>
                            <th style="padding:5px 8px; text-align:center;">SELISIH</th>
                            <th style="padding:5px 8px; text-align:left;">ALASAN DEVIASI KASIR</th>
                            <th style="padding:5px 8px; text-align:center;">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #000;">
                            <td style="padding:4px 8px;">05/11 11:24<br><b>ZNC-20261105-0012</b></td>
                            <td style="padding:4px 8px;">Kasir 1 (Siti)</td>
                            <td style="padding:4px 8px; font-weight:bold;">Amoxicillin 500mg</td>
                            <td style="padding:4px 8px;">AMX-2026-B3<br>(Exp: 08/2028)</td>
                            <td style="padding:4px 8px;">AMX-2026-B1<br>(Exp: 12/2026)</td>
                            <td style="padding:4px 8px; text-align:center; font-weight:bold;">+600 Hari</td>
                            <td style="padding:4px 8px;">Permintaan khusus pasien butuh masa simpan lama ke luar pulau.</td>
                            <td style="padding:4px 8px; text-align:center;"><span style="border:1px solid #000; padding:1px 4px; font-size:7.5px;">Diverifikasi</span></td>
                        </tr>
                        <tr style="border-bottom:1px solid #000;">
                            <td style="padding:4px 8px;">04/11 15:40<br><b>ZNC-20261104-0045</b></td>
                            <td style="padding:4px 8px;">Kasir 2 (Budi)</td>
                            <td style="padding:4px 8px; font-weight:bold;">Cefixime 100mg</td>
                            <td style="padding:4px 8px;">CFX-2026-B2<br>(Exp: 05/2027)</td>
                            <td style="padding:4px 8px;">CFX-2026-B1<br>(Exp: 02/2027)</td>
                            <td style="padding:4px 8px; text-align:center; font-weight:bold;">+90 Hari</td>
                            <td style="padding:4px 8px;">Fisik batch terdekat tertumpuk di rak paling belakang.</td>
                            <td style="padding:4px 8px; text-align:center;"><span style="border:1px solid #000; padding:1px 4px; font-size:7.5px;">Teguran</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="row" style="justify-content:space-between; font-size:8.5px;">
                <div>Rekaman audit terikat permanen pada tabel <code>log_anomali_fefo</code> untuk inspeksi akreditasi toko obat.</div>
                <div>Halaman 1 dari 1</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>'''

with open('scratch/wf_anomali_fefo.html', 'w', encoding='utf-8') as f:
    f.write(anomali_html)

print("Generated 3 new wireframe HTML files in scratch/ successfully!")

