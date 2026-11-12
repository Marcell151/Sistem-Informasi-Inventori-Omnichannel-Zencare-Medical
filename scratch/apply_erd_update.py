with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8') as f:
    code = f.read()

# Update sesi_opname
old_sesi = """    'sesi_opname': {
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
    },"""

new_sesi = """    'sesi_opname': {
        'col': 4, 'y': 445,
        'title': 'sesi_opname',
        'fields': [
            ('id_sesi_opname', 'int', True, False),
            ('kode_sesi', 'varchar(50)', False, False),
            ('status', 'varchar(50)', False, False),
            ('tahap', 'varchar(50)', False, False),
            ('dibekukan_oleh', 'int', False, True),
            ('dibekukan_pada', 'timestamp', False, False),
            ('ditutup_hitung_oleh', 'int', False, True),
            ('ditutup_hitung_pada', 'timestamp', False, False),
            ('dibuka_oleh', 'int', False, True),
            ('dibuka_pada', 'timestamp', False, False),
            ('catatan', 'text', False, False)
        ]
    },"""

# Update detail_opname_batch
old_dob = """    'detail_opname_batch': {
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
    },"""

new_dob = """    'detail_opname_batch': {
        'col': 4, 'y': 745,
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
            ('fisik_baik', 'int', False, False),
            ('fisik_rusak', 'int', False, False),
            ('fisik_expired', 'int', False, False),
            ('status_hitung', 'varchar(50)', False, False),
            ('hitung_fisik', 'int', False, False),
            ('selisih', 'int', False, False),
            ('status_mutasi', 'varchar(50)', False, False),
            ('alasan_mutasi', 'varchar(255)', False, False),
            ('keterangan_mutasi', 'text', False, False),
            ('id_kartu_stok', 'int', False, True),
            ('dihitung_oleh', 'int', False, True),
            ('waktu_hitung', 'timestamp', False, False),
            ('dimutasi_oleh', 'int', False, True),
            ('waktu_mutasi', 'timestamp', False, False)
        ]
    },"""

# Update detail_opname_sn
old_sn = """    'detail_opname_sn': {
        'col': 5, 'y': 685,"""
new_sn = """    'detail_opname_sn': {
        'col': 5, 'y': 745,"""

# Update riwayat_hitung_opname
old_riwayat = """    'riwayat_hitung_opname': {
        'col': 5, 'y': 895,"""
new_riwayat = """    'riwayat_hitung_opname': {
        'col': 5, 'y': 945,"""

assert old_sesi in code, "old_sesi not found!"
code = code.replace(old_sesi, new_sesi)

assert old_dob in code, "old_dob not found!"
code = code.replace(old_dob, new_dob)

assert old_sn in code, "old_sn not found!"
code = code.replace(old_sn, new_sn)

assert old_riwayat in code, "old_riwayat not found!"
code = code.replace(old_riwayat, new_riwayat)

with open('scratch/generate_orthogonal_erd.py', 'w', encoding='utf-8') as f:
    f.write(code)

print("Updated scratch/generate_orthogonal_erd.py successfully!")

