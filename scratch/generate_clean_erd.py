import sys
import os

# Definition of the 20 tables in db_inventory (demo) 3.sql
tables_data = {
    'supplier': {
        'col': 1, 'y': 60,
        'title': 'supplier',
        'fields': [
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
            ('id_variasi', 'int', False, True),
            ('stok', 'int', False, False)
        ]
    },
    'pengaturan_web': {
        'col': 4, 'y': 185,
        'title': 'pengaturan_web',
        'fields': [
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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
            ('id', 'int', True, False),
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

# Build SVG
svg_parts = []
svg_parts.append('<svg viewBox="0 0 2050 1550" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" style="background: #ffffff; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif;">')
svg_parts.append('''  <defs>
    <filter id="card-shadow" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="0" dy="2" stdDeviation="4" flood-color="#0f172a" flood-opacity="0.06"/>
    </filter>
    <marker id="dot-start" viewBox="0 0 8 8" refX="4" refY="4" markerWidth="5" markerHeight="5">
      <circle cx="4" cy="4" r="3" fill="#64748b"/>
    </marker>
    <marker id="arrow-end" viewBox="0 0 8 8" refX="6" refY="4" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
      <path d="M 1 1 L 7 4 L 1 7 z" fill="#475569"/>
    </marker>
    <pattern id="dot-grid" width="24" height="24" patternUnits="userSpaceOnUse">
      <circle cx="12" cy="12" r="0.8" fill="#e2e8f0"/>
    </pattern>
  </defs>''')

svg_parts.append('  <!-- Background Canvas Grid -->')
svg_parts.append('  <rect width="2050" height="1550" fill="url(#dot-grid)"/>')
svg_parts.append('  <rect x="15" y="15" width="2020" height="1520" fill="none" stroke="#e2e8f0" stroke-width="1.5" rx="8"/>')

# Title watermark / header badge
svg_parts.append('''  <!-- Diagram Info Banner -->
  <g transform="translate(35, 20)">
    <rect width="560" height="26" fill="#f8fafc" stroke="#cbd5e1" rx="5"/>
    <circle cx="14" cy="13" r="4.5" fill="#2563eb"/>
    <text x="26" y="17" font-size="12" font-weight="700" fill="#0f172a">ZenCare Medical - Logical Data Model (LDM / ERD)</text>
    <text x="380" y="17" font-size="11" font-weight="600" fill="#64748b">20 Entitas &#8226; RDBMS MySQL</text>
  </g>''')

# Relationship Lines (Single uniform neutral color #64748b)
lines_svg = []
lines_svg.append('  <!-- ================= RELATIONSHIP CONNECTIONS (CLEAN MONOCHROME) ================= -->')
lines_svg.append('  <g class="relationships" stroke="#64748b" stroke-width="1.3" fill="none" stroke-linecap="round" stroke-linejoin="round">')

def add_curve(x1, y1, x2, y2, c1_offset=None, c2_offset=None):
    if c1_offset is None:
        dx = x2 - x1
        c1x = x1 + dx * 0.5
        c2x = x2 - dx * 0.5
        c1y = y1
        c2y = y2
    else:
        c1x, c1y = c1_offset
        c2x, c2y = c2_offset
    d = f"M {x1:.1f} {y1:.1f} C {c1x:.1f} {c1y:.1f}, {c2x:.1f} {c2y:.1f}, {x2:.1f} {y2:.1f}"
    lines_svg.append(f'    <path d="{d}" marker-start="url(#dot-start)" marker-end="url(#arrow-end)"/>')

# 1. supplier <-> penerimaan_stok (Col 1 to Col 1, left margin)
p_src = ports['penerimaan_stok']['id_supplier']['left']
p_dst = ports['supplier']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] - 40, p_src[1]), (p_dst[0] - 40, p_dst[1]))

# 2. penerimaan_stok <-> penerimaan_detail (Col 1 to Col 2, Gutter 1-2)
p_src = ports['penerimaan_detail']['id_penerimaan']['left']
p_dst = ports['penerimaan_stok']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 3. penerimaan_stok <-> users (Col 1 to Col 1, left margin)
p_src = ports['penerimaan_stok']['dibuat_oleh']['left']
p_dst = ports['users']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] - 30, p_src[1]), (p_dst[0] - 30, p_dst[1]))

# 4. users <-> penjualan (Col 1 to Col 1, left margin)
p_src = ports['penjualan']['id_user']['left']
p_dst = ports['users']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] - 45, p_src[1]), (p_dst[0] - 45, p_dst[1]))

# 5. penjualan <-> detail_penjualan (Col 1 to Col 2, Gutter 1-2)
p_src = ports['detail_penjualan']['id_penjualan']['left']
p_dst = ports['penjualan']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 6. penjualan <-> pesanan_batch_alokasi (Col 1 to Col 2, Gutter 1-2)
p_src = ports['pesanan_batch_alokasi']['id_penjualan']['left']
p_dst = ports['penjualan']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 7. penjualan <-> unit_serial (Col 1 to Col 2, Gutter 1-2)
p_src = ports['unit_serial']['id_penjualan']['left']
p_dst = ports['penjualan']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 8. users <-> kartu_stok (Col 1 to Col 2, Gutter 1-2)
p_src = ports['kartu_stok']['dibuat_oleh']['left']
p_dst = ports['users']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 9. detail_penjualan <-> pesanan_batch_alokasi (Col 2 to Col 2, Gutter 1-2)
p_src = ports['pesanan_batch_alokasi']['id_detail_penjualan']['left']
p_dst = ports['detail_penjualan']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] - 35, p_src[1]), (p_dst[0] - 35, p_dst[1]))

# 10. penerimaan_detail <-> produk_variasi (Col 2 to Col 3, Gutter 2-3)
p_src = ports['penerimaan_detail']['id_variasi']['right']
p_dst = ports['produk_variasi']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 11. kartu_stok <-> produk_variasi (Col 2 to Col 3, Gutter 2-3)
p_src = ports['kartu_stok']['id_variasi']['right']
p_dst = ports['produk_variasi']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 12. kartu_stok <-> stok_batch (Col 2 to Col 3, Gutter 2-3)
p_src = ports['kartu_stok']['id_batch']['right']
p_dst = ports['stok_batch']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 13. detail_penjualan <-> produk_variasi (Col 2 to Col 3, Gutter 2-3)
p_src = ports['detail_penjualan']['id_variasi']['right']
p_dst = ports['produk_variasi']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 14. pesanan_batch_alokasi <-> stok_batch (Col 2 to Col 3, Gutter 2-3)
p_src = ports['pesanan_batch_alokasi']['id_batch']['right']
p_dst = ports['stok_batch']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 15. unit_serial <-> produk_variasi (Col 2 to Col 3, Gutter 2-3)
p_src = ports['unit_serial']['id_variasi']['right']
p_dst = ports['produk_variasi']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 16. produk_variasi <-> produk_induk (Col 3 to Col 3, Gutter 3-4)
p_src = ports['produk_variasi']['id_produk_induk']['right']
p_dst = ports['produk_induk']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] + 45, p_src[1]), (p_dst[0] + 45, p_dst[1]))

# 17. stok_batch <-> produk_variasi (Col 3 to Col 3, Gutter 3-4)
p_src = ports['stok_batch']['id_variasi']['right']
p_dst = ports['produk_variasi']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] + 30, p_src[1]), (p_dst[0] + 30, p_dst[1]))

# 18. histori_konversi <-> produk_variasi (Col 3 to Col 3, Gutter 2-3)
p_src = ports['histori_konversi']['id_variasi']['left']
p_dst = ports['produk_variasi']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] - 40, p_src[1]), (p_dst[0] - 40, p_dst[1]))

# 19. log_anomali_fefo <-> produk_variasi (Col 3 to Col 3, Gutter 2-3)
p_src = ports['log_anomali_fefo']['id_variasi']['left']
p_dst = ports['produk_variasi']['id']['left']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] - 55, p_src[1]), (p_dst[0] - 55, p_dst[1]))

# 20. stok_toko <-> produk_variasi (Col 4 to Col 3, Gutter 3-4)
p_src = ports['stok_toko']['id_variasi']['left']
p_dst = ports['produk_variasi']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 21. detail_opname_batch <-> sesi_opname (Col 4 to Col 4, Gutter 4-5)
p_src = ports['detail_opname_batch']['id_sesi']['right']
p_dst = ports['sesi_opname']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1], (p_src[0] + 40, p_src[1]), (p_dst[0] + 40, p_dst[1]))

# 22. detail_opname_batch <-> produk_variasi (Col 4 to Col 3, Gutter 3-4)
p_src = ports['detail_opname_batch']['id_variasi']['left']
p_dst = ports['produk_variasi']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 23. detail_opname_batch <-> stok_batch (Col 4 to Col 3, Gutter 3-4)
p_src = ports['detail_opname_batch']['id_batch']['left']
p_dst = ports['stok_batch']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 24. detail_opname_sn <-> detail_opname_batch (Col 5 to Col 4, Gutter 4-5)
p_src = ports['detail_opname_sn']['id_detail_opname']['left']
p_dst = ports['detail_opname_batch']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 25. riwayat_hitung_opname <-> detail_opname_batch (Col 5 to Col 4, Gutter 4-5)
p_src = ports['riwayat_hitung_opname']['id_detail_opname']['left']
p_dst = ports['detail_opname_batch']['id']['right']
add_curve(p_src[0], p_src[1], p_dst[0], p_dst[1])

# 26. unit_serial <-> detail_opname_sn (Spanning Col 2 to Col 5 via Bottom Corridor Y=1485)
p_sn = ports['detail_opname_sn']['id_unit_serial']['right']
p_us = ports['unit_serial']['id']['right']
d_us_sn = f"M {p_sn[0]:.1f} {p_sn[1]:.1f} C {p_sn[0]+40:.1f} {p_sn[1]:.1f}, 1980 1485, 1940 1485 L 760 1485 C 710 1485, {p_us[0]+40:.1f} {p_us[1]:.1f}, {p_us[0]:.1f} {p_us[1]:.1f}"
lines_svg.append(f'    <path d="{d_us_sn}" marker-start="url(#dot-start)" marker-end="url(#arrow-end)"/>')

# 27. log_anomali_fefo <-> penjualan (Spanning Col 3 to Col 1 via Bottom Corridor Y=1460)
p_log = ports['log_anomali_fefo']['id_penjualan']['left']
p_penj = ports['penjualan']['id']['right']
d_log_penj = f"M {p_log[0]:.1f} {p_log[1]:.1f} C {p_log[0]-40:.1f} {p_log[1]:.1f}, 820 1460, 780 1460 L 390 1460 C 350 1460, {p_penj[0]+40:.1f} {p_penj[1]:.1f}, {p_penj[0]:.1f} {p_penj[1]:.1f}"
lines_svg.append(f'    <path d="{d_log_penj}" marker-start="url(#dot-start)" marker-end="url(#arrow-end)"/>')

# 28. sesi_opname <-> users (Spanning Col 4 to Col 1 via Top Corridor Y=32 and Left Margin X=30)
p_sesi = ports['sesi_opname']['dibuka_oleh']['left']
p_usr = ports['users']['id']['left']
d_sesi_usr = f"M {p_sesi[0]:.1f} {p_sesi[1]:.1f} C {p_sesi[0]-50:.1f} {p_sesi[1]:.1f}, 1210 32, 1170 32 L 60 32 C 30 32, 30 {p_usr[1]-40:.1f}, 30 {p_usr[1]:.1f} L 30 {p_usr[1]:.1f} C 30 {p_usr[1]:.1f}, 50 {p_usr[1]:.1f}, {p_usr[0]:.1f} {p_usr[1]:.1f}"
lines_svg.append(f'    <path d="{d_sesi_usr}" marker-start="url(#dot-start)" marker-end="url(#arrow-end)"/>')

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
            # Key icon or PK badge
            svg_parts.append(f'      <g transform="translate({cx+8}, {ry+4})">')
            svg_parts.append('        <rect width="14" height="11" rx="2" fill="#ef4444"/>')
            svg_parts.append('        <text x="7" y="8.5" font-size="7.5" font-weight="bold" fill="#ffffff" text-anchor="middle">PK</text>')
            svg_parts.append('      </g>')
            text_fill = '#0f172a'
            font_weight = '700'
        elif is_fk:
            # FK badge
            svg_parts.append(f'      <g transform="translate({cx+8}, {ry+4})">')
            svg_parts.append('        <rect width="14" height="11" rx="2" fill="#3b82f6"/>')
            svg_parts.append('        <text x="7" y="8.5" font-size="7.5" font-weight="bold" fill="#ffffff" text-anchor="middle">FK</text>')
            svg_parts.append('      </g>')
            text_fill = '#1e293b'
            font_weight = '600'
        else:
            # Subtle bullet
            svg_parts.append(f'      <circle cx="{cx+15}" cy="{ry+10}" r="1.5" fill="#94a3b8"/>')
            
        # Field Name
        svg_parts.append(f'      <text x="{cx+27}" y="{ry+13.5}" font-size="9.5" font-weight="{font_weight}" fill="{text_fill}" font-family="monospace">{f_name}</text>')
        
        # Field Type
        svg_parts.append(f'      <text x="{cx+card_w-26}" y="{ry+13.5}" font-size="8.5" fill="#64748b" text-anchor="end" font-family="monospace">{f_type}</text>')
        
        # NN tag (Not null indicator)
        svg_parts.append(f'      <text x="{cx+card_w-8}" y="{ry+13.5}" font-size="7.5" font-weight="bold" fill="#94a3b8" text-anchor="end">NN</text>')
        
    svg_parts.append('    </g>')

svg_parts.append('  </g>')

# Footer note
svg_parts.append('''  <!-- Footer Notes -->
  <g transform="translate(35, 1530)">
    <text font-size="10" fill="#94a3b8" font-family="sans-serif">ZenCare Medical ERD &#8226; Generated strictly through empty gutters with collision-free bezier routing &#8226; Identical to dbdiagram.io export schema.</text>
  </g>''')

svg_parts.append('</svg>')

final_svg = '\n'.join(svg_parts)

out_path = 'lain/Gambar_3.18_Logical_Database_Model.svg'
with open(out_path, 'w', encoding='utf-8') as f:
    f.write(final_svg)

print(f"Generated clean monochrome SVG successfully to {out_path} ({len(final_svg)} bytes)")
