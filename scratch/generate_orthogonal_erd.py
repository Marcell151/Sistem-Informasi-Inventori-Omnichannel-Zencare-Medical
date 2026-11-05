import sys
import os

# Definition of the 20 tables in db_inventory (demo) 3.sql
tables_data = {
    'supplier': {
        'col': 1, 'y': 60,
        'title': 'supplier',
        'fields': [
            ('id_supplier', 'int', True, False),
            ('nama', 'varchar(100)', False, False),
            ('kontak', 'varchar(50)', False, False),
            ('email', 'varchar(150)', False, False),
            ('alamat', 'text', False, False),
            ('is_active', 'boolean', False, False)
        ]
    },
    'penerimaan_stok': {
        'col': 1, 'y': 250,
        'title': 'penerimaan_stok',
        'fields': [
            ('id_penerimaan', 'int', True, False),
            ('no_referensi', 'varchar(50)', False, False),
            ('sumber', 'varchar(50)', False, False),
            ('id_supplier', 'int', False, True),
            ('tanggal_terima', 'date', False, False),
            ('catatan', 'text', False, False),
            ('file_nota', 'varchar(255)', False, False),
            ('dibuat_oleh', 'int', False, True),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'users': {
        'col': 1, 'y': 510,
        'title': 'users',
        'fields': [
            ('id_user', 'int', True, False),
            ('username', 'varchar(50)', False, False),
            ('password', 'varchar(255)', False, False),
            ('nama_lengkap', 'varchar(100)', False, False),
            ('role', 'varchar(50)', False, False),
            ('email', 'varchar(150)', False, False),
            ('telepon', 'varchar(20)', False, False),
            ('alamat', 'text', False, False),
            ('kota_id', 'int', False, False),
            ('is_active', 'boolean', False, False),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'penjualan': {
        'col': 1, 'y': 810,
        'title': 'penjualan',
        'fields': [
            ('id_penjualan', 'int', True, False),
            ('no_invoice', 'varchar(50)', False, False),
            ('id_user', 'int', False, True),
            ('tipe_transaksi', 'varchar(50)', False, False),
            ('metode_pengambilan', 'varchar(50)', False, False),
            ('status_pesanan', 'varchar(50)', False, False),
            ('total_harga', 'decimal(12,2)', False, False),
            ('metode_bayar_pos', 'varchar(50)', False, False),
            ('ongkir', 'decimal(10,2)', False, False),
            ('nama_penerima', 'varchar(100)', False, False),
            ('telepon_penerima', 'varchar(20)', False, False),
            ('alamat_lengkap', 'text', False, False),
            ('kota_tujuan', 'varchar(100)', False, False),
            ('kurir', 'varchar(50)', False, False),
            ('snap_token', 'varchar(255)', False, False),
            ('paid_at', 'timestamp', False, False),
            ('kode_pickup', 'varchar(20)', False, False),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'penerimaan_detail': {
        'col': 2, 'y': 60,
        'title': 'penerimaan_detail',
        'fields': [
            ('id_penerimaan_detail', 'int', True, False),
            ('id_penerimaan', 'int', False, True),
            ('id_variasi', 'int', False, True),
            ('qty_pesan', 'int', False, False),
            ('qty_terima', 'int', False, False),
            ('qty_rusak', 'int', False, False),
            ('harga_beli', 'decimal(15,2)', False, False),
            ('no_batch', 'varchar(50)', False, False),
            ('tgl_exp', 'date', False, False),
            ('status_retur', 'varchar(50)', False, False),
            ('catatan_retur', 'text', False, False)
        ]
    },
    'kartu_stok': {
        'col': 2, 'y': 355,
        'title': 'kartu_stok',
        'fields': [
            ('id_kartu_stok', 'int', True, False),
            ('id_variasi', 'int', False, True),
            ('id_batch', 'int', False, True),
            ('satuan_tipe', 'varchar(50)', False, False),
            ('jenis_mutasi', 'varchar(50)', False, False),
            ('kanal', 'varchar(50)', False, False),
            ('alasan_mutasi', 'varchar(50)', False, False),
            ('no_ref_dokumen', 'varchar(100)', False, False),
            ('qty', 'int', False, False),
            ('sisa_stok', 'int', False, False),
            ('keterangan', 'text', False, False),
            ('dibuat_oleh', 'int', False, True),
            ('tanggal', 'timestamp', False, False)
        ]
    },
    'detail_penjualan': {
        'col': 2, 'y': 695,
        'title': 'detail_penjualan',
        'fields': [
            ('id_detail_penjualan', 'int', True, False),
            ('id_penjualan', 'int', False, True),
            ('id_variasi', 'int', False, True),
            ('qty', 'int', False, False),
            ('harga_satuan', 'decimal(12,2)', False, False),
            ('catatan_logistik', 'text', False, False)
        ]
    },
    'pesanan_batch_alokasi': {
        'col': 2, 'y': 895,
        'title': 'pesanan_batch_alokasi',
        'fields': [
            ('id_alokasi_batch', 'int', True, False),
            ('id_penjualan', 'int', False, True),
            ('id_detail_penjualan', 'int', False, True),
            ('id_batch', 'int', False, True),
            ('qty_satuan_kecil', 'int', False, False),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'unit_serial': {
        'col': 2, 'y': 1095,
        'title': 'unit_serial',
        'fields': [
            ('id_unit_serial', 'int', True, False),
            ('id_variasi', 'int', False, True),
            ('serial_number', 'varchar(100)', False, False),
            ('status', 'varchar(50)', False, False),
            ('id_penjualan', 'int', False, True),
            ('catatan', 'varchar(255)', False, False),
            ('tgl_habis_garansi', 'date', False, False),
            ('created_at', 'timestamp', False, False),
            ('updated_at', 'timestamp', False, False)
        ]
    },
    'produk_induk': {
        'col': 3, 'y': 60,
        'title': 'produk_induk',
        'fields': [
            ('id_produk_induk', 'int', True, False),
            ('sku_induk', 'varchar(50)', False, False),
            ('nama_produk', 'varchar(150)', False, False),
            ('deskripsi', 'text', False, False),
            ('spesifikasi', 'text', False, False),
            ('info_pengiriman', 'text', False, False),
            ('kategori', 'varchar(50)', False, False),
            ('gambar', 'varchar(255)', False, False),
            ('is_active', 'boolean', False, False),
            ('batas_hari_expired', 'int', False, False),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'stok_batch': {
        'col': 3, 'y': 355,
        'title': 'stok_batch',
        'fields': [
            ('id_stok_batch', 'int', True, False),
            ('id_variasi', 'int', False, True),
            ('no_batch', 'varchar(100)', False, False),
            ('tgl_exp', 'date', False, False),
            ('stok_sisa', 'int', False, False),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'produk_variasi': {
        'col': 3, 'y': 555,
        'title': 'produk_variasi',
        'fields': [
            ('id_variasi', 'int', True, False),
            ('id_produk_induk', 'int', False, True),
            ('sku_variasi', 'varchar(50)', False, False),
            ('nama_variasi', 'varchar(100)', False, False),
            ('satuan_kecil', 'varchar(50)', False, False),
            ('satuan_besar', 'varchar(50)', False, False),
            ('rasio_konversi', 'smallint', False, False),
            ('harga_jual_kecil', 'decimal(12,2)', False, False),
            ('harga_jual_besar', 'decimal(12,2)', False, False),
            ('stok_minimum_kecil', 'int', False, False),
            ('stok_minimum_besar', 'int', False, False),
            ('berat', 'int', False, False),
            ('tampil_di_online', 'boolean', False, False),
            ('is_active', 'boolean', False, False),
            ('gambar', 'varchar(500)', False, False)
        ]
    },
    'histori_konversi': {
        'col': 3, 'y': 935,
        'title': 'histori_konversi',
        'fields': [
            ('id_histori_konversi', 'int', True, False),
            ('id_variasi', 'int', False, True),
            ('batch_asal', 'varchar(100)', False, False),
            ('batch_hasil', 'varchar(100)', False, False),
            ('qty_box_buka', 'int', False, False),
            ('qty_pcs_hasil', 'int', False, False),
            ('sisa_dus_akhir', 'int', False, False),
            ('dibuat_oleh', 'int', False, True),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'log_anomali_fefo': {
        'col': 3, 'y': 1195,
        'title': 'log_anomali_fefo',
        'fields': [
            ('id_log_anomali', 'int', True, False),
            ('id_penjualan', 'int', False, True),
            ('id_user', 'int', False, True),
            ('id_variasi', 'int', False, True),
            ('batch_diambil', 'varchar(100)', False, False),
            ('batch_seharusnya', 'varchar(100)', False, False),
            ('qty', 'int', False, False),
            ('satuan_tipe', 'varchar(50)', False, False),
            ('status', 'varchar(50)', False, False),
            ('qty_disesuaikan', 'int', False, False),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'stok_toko': {
        'col': 4, 'y': 60,
        'title': 'stok_toko',
        'fields': [
            ('id_stok_toko', 'int', True, False),
            ('id_variasi', 'int', False, True),
            ('stok', 'int', False, False)
        ]
    },
    'pengaturan_web': {
        'col': 4, 'y': 185,
        'title': 'pengaturan_web',
        'fields': [
            ('id_pengaturan', 'int', True, False),
            ('nama_toko', 'varchar(100)', False, False),
            ('deskripsi', 'text', False, False),
            ('telepon', 'varchar(20)', False, False),
            ('whatsapp', 'varchar(20)', False, False),
            ('alamat', 'text', False, False),
            ('logo', 'varchar(255)', False, False),
            ('banner_promosi', 'varchar(255)', False, False),
            ('email_cs', 'varchar(150)', False, False)
        ]
    },
    'sesi_opname': {
        'col': 4, 'y': 445,
        'title': 'sesi_opname',
        'fields': [
            ('id_sesi_opname', 'int', True, False),
            ('kode_sesi', 'varchar(50)', False, False),
            ('status', 'varchar(50)', False, False),
            ('dibekukan_oleh', 'int', False, True),
            ('dibekukan_pada', 'timestamp', False, False),
            ('dibuka_oleh', 'int', False, True),
            ('dibuka_pada', 'timestamp', False, False),
            ('catatan', 'text', False, False)
        ]
    },
    'detail_opname_batch': {
        'col': 4, 'y': 685,
        'title': 'detail_opname_batch',
        'fields': [
            ('id_detail_opname', 'int', True, False),
            ('id_sesi', 'int', False, True),
            ('id_batch', 'int', False, True),
            ('id_variasi', 'int', False, True),
            ('jenis_komoditas', 'varchar(50)', False, False),
            ('satuan_tipe', 'varchar(50)', False, False),
            ('nama_satuan', 'varchar(50)', False, False),
            ('stok_tersedia_sistem', 'int', False, False),
            ('stok_reserved', 'int', False, False),
            ('stok_sistem_fisik', 'int', False, False),
            ('hitung_fisik', 'int', False, False),
            ('selisih', 'int', False, False),
            ('status_mutasi', 'varchar(50)', False, False),
            ('alasan_mutasi', 'varchar(50)', False, False),
            ('id_kartu_stok', 'int', False, False),
            ('dihitung_oleh', 'int', False, True),
            ('waktu_hitung', 'timestamp', False, False),
            ('dimutasi_oleh', 'int', False, True)
        ]
    },
    'detail_opname_sn': {
        'col': 5, 'y': 685,
        'title': 'detail_opname_sn',
        'fields': [
            ('id_opname_sn', 'int', True, False),
            ('id_detail_opname', 'int', False, True),
            ('id_unit_serial', 'int', False, True),
            ('serial_number', 'varchar(100)', False, False),
            ('jenis_tindakan', 'varchar(50)', False, False),
            ('created_at', 'timestamp', False, False)
        ]
    },
    'riwayat_hitung_opname': {
        'col': 5, 'y': 895,
        'title': 'riwayat_hitung_opname',
        'fields': [
            ('id_riwayat_hitung', 'int', True, False),
            ('id_detail_opname', 'int', False, True),
            ('hitung_sebelumnya', 'int', False, False),
            ('hitung_baru', 'int', False, False),
            ('dihitung_oleh', 'int', False, True),
            ('alasan_koreksi', 'text', False, False),
            ('waktu_koreksi', 'timestamp', False, False)
        ]
    }
}

col_x = {
    1: 70,
    2: 470,
    3: 870,
    4: 1270,
    5: 1670
}
card_w = 260
row_h = 20
hdr_h = 28

# Calculate field port Y coordinates for each table
ports = {}
card_boxes = {}

for t_name, data in tables_data.items():
    cx = col_x[data['col']]
    cy = data['y']
    fields = data['fields']
    ch = hdr_h + len(fields) * row_h + 6
    card_boxes[t_name] = (cx, cy, card_w, ch, data['col'])
    
    ports[t_name] = {}
    for idx, (f_name, f_type, is_pk, is_fk) in enumerate(fields):
        py = cy + hdr_h + idx * row_h + row_h / 2
        ports[t_name][f_name] = {
            'left': (cx, py),
            'right': (cx + card_w, py),
            'y': py,
            'is_pk': is_pk,
            'is_fk': is_fk
        }

# Helper to build rounded orthogonal SVG path
def build_rounded_path(pts, r=8):
    if len(pts) < 2:
        return ''
    cleaned = [pts[0]]
    for p in pts[1:]:
        if abs(p[0] - cleaned[-1][0]) > 0.1 or abs(p[1] - cleaned[-1][1]) > 0.1:
            cleaned.append(p)
    pts = cleaned
    if len(pts) < 2:
        return ''
    if len(pts) == 2:
        return f"M {pts[0][0]:.1f} {pts[0][1]:.1f} L {pts[1][0]:.1f} {pts[1][1]:.1f}"
        
    d = [f'M {pts[0][0]:.1f} {pts[0][1]:.1f}']
    for i in range(1, len(pts) - 1):
        p_prev = pts[i-1]
        p_curr = pts[i]
        p_next = pts[i+1]
        
        dx_in = p_curr[0] - p_prev[0]
        dy_in = p_curr[1] - p_prev[1]
        len_in = abs(dx_in) + abs(dy_in)
        
        dx_out = p_next[0] - p_curr[0]
        dy_out = p_next[1] - p_curr[1]
        len_out = abs(dx_out) + abs(dy_out)
        
        actual_r = min(r, len_in / 2.0, len_out / 2.0)
        
        if dx_in != 0:
            bx = p_curr[0] - (1 if dx_in > 0 else -1) * actual_r
            by = p_curr[1]
        else:
            bx = p_curr[0]
            by = p_curr[1] - (1 if dy_in > 0 else -1) * actual_r
            
        if dx_out != 0:
            ax = p_curr[0] + (1 if dx_out > 0 else -1) * actual_r
            ay = p_curr[1]
        else:
            ax = p_curr[0]
            ay = p_curr[1] + (1 if dy_out > 0 else -1) * actual_r
            
        d.append(f'L {bx:.1f} {by:.1f}')
        d.append(f'Q {p_curr[0]:.1f} {p_curr[1]:.1f}, {ax:.1f} {ay:.1f}')
        
    d.append(f'L {pts[-1][0]:.1f} {pts[-1][1]:.1f}')
    return ' '.join(d)

# Build SVG
svg_parts = []
# ViewBox with clean padding
svg_parts.append('<svg viewBox="0 0 2050 1540" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="background: #ffffff; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif;">')
svg_parts.append('''  <defs>
    <filter id="card-shadow" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="0" dy="2" stdDeviation="4" flood-color="#0f172a" flood-opacity="0.06"/>
    </filter>
    <marker id="dot-start" viewBox="0 0 8 8" refX="4" refY="4" markerWidth="5" markerHeight="5">
      <circle cx="4" cy="4" r="2.8" fill="#64748b"/>
    </marker>
    <marker id="arrow-end" viewBox="0 0 8 8" refX="6" refY="4" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 1 1 L 7 4 L 1 7 z" fill="#475569"/>
    </marker>
    <pattern id="dot-grid" width="24" height="24" patternUnits="userSpaceOnUse">
      <circle cx="12" cy="12" r="0.8" fill="#e2e8f0"/>
    </pattern>
  </defs>''')

svg_parts.append('  <!-- Background Canvas Grid -->')
svg_parts.append('  <rect width="2050" height="1540" fill="url(#dot-grid)"/>')
svg_parts.append('  <rect x="15" y="15" width="2020" height="1510" fill="none" stroke="#e2e8f0" stroke-width="1.5" rx="8"/>')

# NOTE: The top banner box ("ZenCare Medical - Logical Data Model") has been REMOVED
# as requested by the user, leaving the top canvas clean and pristine for thesis screenshots.

lines_svg = []
lines_svg.append('  <!-- ================= RELATIONSHIP CONNECTIONS (DEDICATED NON-OVERLAPPING LANES) ================= -->')
lines_svg.append('  <g class="relationships" stroke="#64748b" stroke-width="1.2" fill="none" stroke-linecap="round" stroke-linejoin="round">')

def add_ortho(pts, r=8, marker_s=True, marker_e=True):
    path_d = build_rounded_path(pts, r)
    s_attr = ' marker-start="url(#dot-start)"' if marker_s else ''
    e_attr = ' marker-end="url(#arrow-end)"' if marker_e else ''
    lines_svg.append(f'    <path d="{path_d}"{s_attr}{e_attr}/>')

# =========================================================================
# 1. GUTTER 1-2 (DEDICATED PARALLEL NON-OVERLAPPING LANES)
# Connections to penjualan.id (Y = 828.0) - Each line has its own entry Y and lane X!
# =========================================================================
y_penj_id = ports['penjualan']['id_penjualan']['right'][1]      # 828.0
y_det_penj = ports['detail_penjualan']['id_penjualan']['left'][1] # 733.0
y_pesan_penj = ports['pesanan_batch_alokasi']['id_penjualan']['left'][1] # 933.0
y_unit_penj = ports['unit_serial']['id_penjualan']['left'][1] # 1193.0
y_anom_penj = ports['log_anomali_fefo']['id_penjualan']['left'][1] # 1233.0

# 1.1 detail_penjualan.id_penjualan -> penjualan.id (Lane X = 385, enters at Y = 824.0)
add_ortho([(470, y_det_penj), (385, y_det_penj), (385, y_penj_id - 4), (330, y_penj_id - 4)], r=8)

# 1.2 pesanan_batch_alokasi.id_penjualan -> penjualan.id (Lane X = 402, enters at Y = 828.0)
add_ortho([(470, y_pesan_penj), (402, y_pesan_penj), (402, y_penj_id), (330, y_penj_id)], r=8)

# 1.3 unit_serial.id_penjualan -> penjualan.id (Lane X = 418, enters at Y = 832.0)
add_ortho([(470, y_unit_penj), (418, y_unit_penj), (418, y_penj_id + 4), (330, y_penj_id + 4)], r=8)

# 1.4 kartu_stok.dibuat_oleh -> users.id (Lane X = 360, enters users from right at Y = 528.0)
y_user_id = ports['users']['id_user']['left'][1]          # 528.0
y_kartu_user = ports['kartu_stok']['dibuat_oleh']['left'][1] # 593.0
add_ortho([(470, y_kartu_user), (360, y_kartu_user), (360, y_user_id), (330, y_user_id)], r=8)

# 1.5 penerimaan_detail.id_penerimaan -> penerimaan_stok.id (Lane X = 435, enters at Y = 268.0)
y_terima_id = ports['penerimaan_stok']['id_penerimaan']['right'][1] # 268.0
y_det_terima = ports['penerimaan_detail']['id_penerimaan']['left'][1] # 98.0
add_ortho([(470, y_det_terima), (435, y_det_terima), (435, y_terima_id), (330, y_terima_id)], r=8)

# 1.6 pesanan_batch_alokasi.id_detail_penjualan -> detail_penjualan.id (Lane X = 452, Col 2 internal)
y_det_id = ports['detail_penjualan']['id_detail_penjualan']['left'][1]  # 713.0
y_pesan_det = ports['pesanan_batch_alokasi']['id_detail_penjualan']['left'][1] # 953.0
add_ortho([(470, y_pesan_det), (452, y_pesan_det), (452, y_det_id), (470, y_det_id)], r=8)

# 1.7 log_anomali_fefo.id_penjualan -> penjualan.id (via Bottom Corridor Y = 1460, enters at Y = 836.0)
add_ortho([(870, y_anom_penj), (815, y_anom_penj), (815, 1460), (370, 1460), (370, y_penj_id + 8), (330, y_penj_id + 8)], r=8)

# =========================================================================
# 2. LEFT MARGIN (X < 70) - DEDICATED LANES
# =========================================================================
# 2.1 supplier.id <- penerimaan_stok.id_supplier (Lane X = 22)
y_supp_id = ports['supplier']['id_supplier']['left'][1]        # 78.0
y_terima_supp = ports['penerimaan_stok']['id_supplier']['left'][1] # 328.0
add_ortho([(70, y_terima_supp), (22, y_terima_supp), (22, y_supp_id), (70, y_supp_id)], r=8)

# 2.2 users.id <- penerimaan_stok.dibuat_oleh (Lane X = 38, enters at Y = 525.0)
y_terima_user = ports['penerimaan_stok']['dibuat_oleh']['left'][1] # 408.0
add_ortho([(70, y_terima_user), (38, y_terima_user), (38, y_user_id - 3), (70, y_user_id - 3)], r=8)

# 2.3 users.id <- penjualan.id_user (Lane X = 52, enters at Y = 531.0)
y_penj_user = ports['penjualan']['id_user']['left'][1] # 868.0
add_ortho([(70, y_penj_user), (52, y_penj_user), (52, y_user_id + 3), (70, y_user_id + 3)], r=8)

# =========================================================================
# 3. GUTTER 2-3 (X in [730, 870]) - DEDICATED PARALLEL LANES
# Connections to produk_variasi.id (Y = 573.0) and stok_batch.id (Y = 373.0)
# =========================================================================
y_var_id = ports['produk_variasi']['id_variasi']['left'][1]   # 573.0
y_batch_id = ports['stok_batch']['id_stok_batch']['left'][1]     # 373.0

y_terima_var = ports['penerimaan_detail']['id_variasi']['right'][1] # 118.0
y_kartu_var = ports['kartu_stok']['id_variasi']['right'][1] # 393.0
y_det_var = ports['detail_penjualan']['id_variasi']['right'][1] # 753.0
y_unit_var = ports['unit_serial']['id_variasi']['right'][1] # 1133.0

y_kartu_batch = ports['kartu_stok']['id_batch']['right'][1] # 413.0
y_pesan_batch = ports['pesanan_batch_alokasi']['id_batch']['right'][1] # 973.0

# 3.1 Connections to stok_batch.id:
# kartu_stok.id_batch -> stok_batch.id (Lane X = 755, enters at Y = 370.0)
add_ortho([(730, y_kartu_batch), (755, y_kartu_batch), (755, y_batch_id - 3), (870, y_batch_id - 3)], r=8)

# pesanan_batch_alokasi.id_batch -> stok_batch.id (Lane X = 768, enters at Y = 376.0)
add_ortho([(730, y_pesan_batch), (768, y_pesan_batch), (768, y_batch_id + 3), (870, y_batch_id + 3)], r=8)

# 3.2 Connections from Col 2 to produk_variasi.id (each has its own Lane and entry Y!):
# penerimaan_detail.id_variasi -> Lane X = 822, enters at Y = 567.0
add_ortho([(730, y_terima_var), (822, y_terima_var), (822, y_var_id - 6), (870, y_var_id - 6)], r=8)

# kartu_stok.id_variasi -> Lane X = 806, enters at Y = 571.0
add_ortho([(730, y_kartu_var), (806, y_kartu_var), (806, y_var_id - 2), (870, y_var_id - 2)], r=8)

# detail_penjualan.id_variasi -> Lane X = 790, enters at Y = 575.0
add_ortho([(730, y_det_var), (790, y_det_var), (790, y_var_id + 2), (870, y_var_id + 2)], r=8)

# unit_serial.id_variasi -> Lane X = 778, enters at Y = 579.0
add_ortho([(730, y_unit_var), (778, y_unit_var), (778, y_var_id + 6), (870, y_var_id + 6)], r=8)

# 3.3 Col 3 internal relations to produk_variasi.id (from bottom left of Col 3):
y_konv_var = ports['histori_konversi']['id_variasi']['left'][1] # 973.0
y_anom_var = ports['log_anomali_fefo']['id_variasi']['left'][1] # 1273.0

# histori_konversi -> Lane X = 842, enters at Y = 583.0
add_ortho([(870, y_konv_var), (842, y_konv_var), (842, y_var_id + 10), (870, y_var_id + 10)], r=8)

# log_anomali_fefo -> Lane X = 856, enters at Y = 587.0
add_ortho([(870, y_anom_var), (856, y_anom_var), (856, y_var_id + 14), (870, y_var_id + 14)], r=8)

# =========================================================================
# 4. GUTTER 3-4 (X in [1130, 1270]) - DEDICATED PARALLEL LANES
# =========================================================================
# 4.1 produk_variasi.id_produk_induk -> produk_induk.id (Lane X = 1155)
y_induk_id = ports['produk_induk']['id_produk_induk']['right'][1]  # 78.0
y_var_induk = ports['produk_variasi']['id_produk_induk']['right'][1] # 593.0
add_ortho([(1130, y_var_induk), (1155, y_var_induk), (1155, y_induk_id), (1130, y_induk_id)], r=8)

# 4.2 stok_batch.id_variasi -> produk_variasi.id (Lane X = 1172, enters right at Y = 570.0)
y_batch_var = ports['stok_batch']['id_variasi']['right'][1] # 393.0
add_ortho([(1130, y_batch_var), (1172, y_batch_var), (1172, y_var_id - 3), (1130, y_var_id - 3)], r=8)

# 4.3 stok_toko.id_variasi -> produk_variasi.id (Lane X = 1195, enters right at Y = 574.0)
y_toko_var = ports['stok_toko']['id_variasi']['left'][1] # 98.0
add_ortho([(1270, y_toko_var), (1195, y_toko_var), (1195, y_var_id + 1), (1130, y_var_id + 1)], r=8)

# 4.4 detail_opname_batch.id_variasi -> produk_variasi.id (Lane X = 1215, enters right at Y = 578.0)
y_opname_var = ports['detail_opname_batch']['id_variasi']['left'][1] # 763.0
add_ortho([(1270, y_opname_var), (1215, y_opname_var), (1215, y_var_id + 5), (1130, y_var_id + 5)], r=8)

# 4.5 detail_opname_batch.id_batch -> stok_batch.id (Lane X = 1235, enters right at Y = 373.0)
y_opname_batch = ports['detail_opname_batch']['id_batch']['left'][1] # 743.0
add_ortho([(1270, y_opname_batch), (1235, y_opname_batch), (1235, y_batch_id), (1130, y_batch_id)], r=8)

# =========================================================================
# 5. GUTTER 4-5 (X in [1530, 1670]) - DEDICATED PARALLEL LANES
# =========================================================================
# 5.1 detail_opname_batch.id_sesi -> sesi_opname.id (Lane X = 1560)
y_sesi_id = ports['sesi_opname']['id_sesi_opname']['right'][1]    # 463.0
y_batch_sesi = ports['detail_opname_batch']['id_sesi']['right'][1] # 723.0
add_ortho([(1530, y_batch_sesi), (1560, y_batch_sesi), (1560, y_sesi_id), (1530, y_sesi_id)], r=8)

# 5.2 detail_opname_sn.id_detail_opname -> detail_opname_batch.id (Lane X = 1595, enters at Y = 700.0)
y_opname_id = ports['detail_opname_batch']['id_detail_opname']['right'][1] # 703.0
y_sn_opname = ports['detail_opname_sn']['id_detail_opname']['left'][1] # 723.0
add_ortho([(1670, y_sn_opname), (1595, y_sn_opname), (1595, y_opname_id - 3), (1530, y_opname_id - 3)], r=8)

# 5.3 riwayat_hitung_opname.id_detail_opname -> detail_opname_batch.id (Lane X = 1625, enters at Y = 706.0)
y_riwayat_opname = ports['riwayat_hitung_opname']['id_detail_opname']['left'][1] # 933.0
add_ortho([(1670, y_riwayat_opname), (1625, y_riwayat_opname), (1625, y_opname_id + 3), (1530, y_opname_id + 3)], r=8)

# =========================================================================
# 6. BOTTOM CORRIDOR (CROSS-CANVAS SPANNING RELATION)
# =========================================================================
# detail_opname_sn.id_unit_serial -> unit_serial.id (via Bottom Corridor Y = 1485)
y_sn_unit = ports['detail_opname_sn']['id_unit_serial']['right'][1] # 743.0
y_unit_id = ports['unit_serial']['id_unit_serial']['right'][1]    # 1113.0
add_ortho([(1930, y_sn_unit), (1965, y_sn_unit), (1965, 1485), (745, 1485), (745, y_unit_id), (730, y_unit_id)], r=8)

lines_svg.append('  </g>')
svg_parts.extend(lines_svg)

# Table Cards (Rendered AFTER lines, guaranteeing 100% sharp text with zero line overlap)
svg_parts.append('  <!-- ================= TABLE CARDS (20 ENTITIES - UNIFORM DEEP BLUE HEADER) ================= -->')
svg_parts.append('  <g class="tables">')

for t_name, data in tables_data.items():
    cx = col_x[data['col']]
    cy = data['y']
    fields = data['fields']
    ch = hdr_h + len(fields) * row_h + 6
    
    # Group for table
    svg_parts.append(f'    <!-- Table: {t_name} -->')
    svg_parts.append(f'    <g id="table_{t_name}" filter="url(#card-shadow)">')
    
    # Outer Card rect
    svg_parts.append(f'      <rect x="{cx}" y="{cy}" width="{card_w}" height="{ch}" rx="6" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.2"/>')
    
    # Table Header (Uniform Navy Blue #2b5a84 matching user\'s old image)
    hdr_path = f"M {cx} {cy+6} Q {cx} {cy} {cx+6} {cy} L {cx+card_w-6} {cy} Q {cx+card_w} {cy} {cx+card_w} {cy+6} L {cx+card_w} {cy+hdr_h} L {cx} {cy+hdr_h} Z"
    svg_parts.append(f'      <path d="{hdr_path}" fill="#2b5a84"/>')
    
    # Small header icon (table glyph)
    svg_parts.append(f'      <g transform="translate({cx+10}, {cy+8})" fill="#93c5fd">')
    svg_parts.append('        <rect width="11" height="11" rx="1.5" fill="none" stroke="#ffffff" stroke-width="1.2"/>')
    svg_parts.append('        <line x1="0" y1="4" x2="11" y2="4" stroke="#ffffff" stroke-width="1.2"/>')
    svg_parts.append('        <line x1="4.5" y1="4" x2="4.5" y2="11" stroke="#ffffff" stroke-width="1"/>')
    svg_parts.append('      </g>')
    
    # Table Name Text
    svg_parts.append(f'      <text x="{cx+28}" y="{cy+18}" font-size="11.5" font-weight="700" fill="#ffffff" font-family="monospace">{t_name}</text>')
    svg_parts.append(f'      <text x="{cx+card_w-10}" y="{cy+18}" font-size="9" font-weight="600" fill="#cbd5e1" text-anchor="end">{len(fields)} cols</text>')
    
    # Rows
    for idx, (f_name, f_type, is_pk, is_fk) in enumerate(fields):
        ry = cy + hdr_h + idx * row_h
        
        # Zebra striping for readability
        if idx % 2 == 1:
            svg_parts.append(f'      <rect x="{cx+1}" y="{ry}" width="{card_w-2}" height="{row_h}" fill="#f8fafc"/>')
            
        # Divider line
        svg_parts.append(f'      <line x1="{cx}" y1="{ry}" x2="{cx+card_w}" y2="{ry}" stroke="#f1f5f9" stroke-width="1"/>')
        
        # Key Indicator
        text_fill = '#0f172a'
        font_weight = '400'
        
        if is_pk:
            svg_parts.append(f'      <g transform="translate({cx+8}, {ry+4})">')
            svg_parts.append('        <rect width="14" height="11" rx="2" fill="#ef4444"/>')
            svg_parts.append('        <text x="7" y="8.5" font-size="7.5" font-weight="bold" fill="#ffffff" text-anchor="middle">PK</text>')
            svg_parts.append('      </g>')
            text_fill = '#0f172a'
            font_weight = '700'
        elif is_fk:
            svg_parts.append(f'      <g transform="translate({cx+8}, {ry+4})">')
            svg_parts.append('        <rect width="14" height="11" rx="2" fill="#3b82f6"/>')
            svg_parts.append('        <text x="7" y="8.5" font-size="7.5" font-weight="bold" fill="#ffffff" text-anchor="middle">FK</text>')
            svg_parts.append('      </g>')
            text_fill = '#1e293b'
            font_weight = '600'
        else:
            svg_parts.append(f'      <circle cx="{cx+15}" cy="{ry+10}" r="1.5" fill="#94a3b8"/>')
            
        # Field Name
        svg_parts.append(f'      <text x="{cx+27}" y="{ry+13.5}" font-size="9.5" font-weight="{font_weight}" fill="{text_fill}" font-family="monospace">{f_name}</text>')
        
        # Field Type
        svg_parts.append(f'      <text x="{cx+card_w-26}" y="{ry+13.5}" font-size="8.5" fill="#64748b" text-anchor="end" font-family="monospace">{f_type}</text>')
        
        # NN tag (Not null indicator)
        svg_parts.append(f'      <text x="{cx+card_w-8}" y="{ry+13.5}" font-size="7.5" font-weight="bold" fill="#94a3b8" text-anchor="end">NN</text>')
        
    svg_parts.append('    </g>')

svg_parts.append('  </g>')
svg_parts.append('</svg>')

final_svg = '\n'.join(svg_parts)

out_path = 'lain/Gambar_3.18_Logical_Database_Model.svg'
with open(out_path, 'w', encoding='utf-8') as f:
    f.write(final_svg)

print(f"Generated clean orthogonal SVG without overlaps or top banner to {out_path} ({len(final_svg)} bytes)")
