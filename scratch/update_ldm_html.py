with open('lain/logical_database_zencare.html', 'r', encoding='utf-8') as f:
    c = f.read()

# 1. Update DBML in <pre id="dbmlCode">
old_dbml_sesi = """Table sesi_opname {
  id_sesi_opname int [pk, increment]
  kode_sesi varchar(50) [not null, unique]
  status varchar(50) [not null, default: 'Aktif']
  dibekukan_oleh int [not null]
  dibekukan_pada timestamp [not null]
  dibuka_oleh int
  dibuka_pada timestamp
  catatan text
}"""

new_dbml_sesi = """Table sesi_opname {
  id_sesi_opname int [pk, increment]
  kode_sesi varchar(50) [not null, unique]
  status varchar(50) [not null, default: 'Aktif']
  tahap varchar(50) [not null, default: 'Penghitungan']
  dibekukan_oleh int [not null]
  dibekukan_pada timestamp [not null]
  ditutup_hitung_oleh int
  ditutup_hitung_pada timestamp
  dibuka_oleh int
  dibuka_pada timestamp
  catatan text
}"""

c = c.replace(old_dbml_sesi, new_dbml_sesi)

old_dbml_batch = """Table detail_opname_batch {
  id_detail_opname int [pk, increment]
  id_sesi int [not null]
  id_batch int
  id_variasi int [not null]
  jenis_komoditas varchar(50) [not null, default: 'Obat']
  satuan_tipe varchar(50) [not null, default: 'kecil']
  nama_satuan varchar(50) [not null, default: 'Unit']
  stok_tersedia_sistem int [not null]
  stok_reserved int [not null]
  stok_sistem_fisik int [not null]
  hitung_fisik int
  selisih int
  status_mutasi varchar(50) [not null, default: 'Belum Dimutasi']
  alasan_mutasi varchar(255)
  keterangan_mutasi text
  id_kartu_stok int
  dihitung_oleh int
  waktu_hitung timestamp
  dimutasi_oleh int
  waktu_mutasi timestamp
}"""

new_dbml_batch = """Table detail_opname_batch {
  id_detail_opname int [pk, increment]
  id_sesi int [not null]
  id_batch int
  id_variasi int [not null]
  jenis_komoditas varchar(50) [not null, default: 'Obat']
  satuan_tipe varchar(50) [not null, default: 'kecil']
  nama_satuan varchar(50) [not null, default: 'Unit']
  stok_tersedia_sistem int [not null]
  stok_reserved int [not null]
  stok_sistem_fisik int [not null]
  fisik_baik int
  fisik_rusak int
  fisik_expired int
  status_hitung varchar(50) [default: 'Belum']
  hitung_fisik int
  selisih int
  status_mutasi varchar(50) [not null, default: 'Belum Dimutasi']
  alasan_mutasi varchar(255)
  keterangan_mutasi text
  id_kartu_stok int
  dihitung_oleh int
  waktu_hitung timestamp
  dimutasi_oleh int
  waktu_mutasi timestamp
}"""

c = c.replace(old_dbml_batch, new_dbml_batch)

# 2. Update the second dbml occurrence (dbmlSource string in JS)
old_dbml2_sesi = """Table sesi_opname {\\n  id int [pk, increment]\\n  kode_sesi varchar(50) [not null, unique]\\n  status varchar(50) [not null, default: 'Aktif']\\n  dibekukan_oleh int [not null]\\n  dibekukan_pada timestamp [not null]\\n  dibuka_oleh int\\n  dibuka_pada timestamp\\n  catatan text\\n}"""
new_dbml2_sesi = """Table sesi_opname {\\n  id int [pk, increment]\\n  kode_sesi varchar(50) [not null, unique]\\n  status varchar(50) [not null, default: 'Aktif']\\n  tahap varchar(50) [not null, default: 'Penghitungan']\\n  dibekukan_oleh int [not null]\\n  dibekukan_pada timestamp [not null]\\n  ditutup_hitung_oleh int\\n  ditutup_hitung_pada timestamp\\n  dibuka_oleh int\\n  dibuka_pada timestamp\\n  catatan text\\n}"""
c = c.replace(old_dbml2_sesi, new_dbml2_sesi)

old_dbml2_batch = """Table detail_opname_batch {\\n  id int [pk, increment]\\n  id_sesi int [not null]\\n  id_batch int\\n  id_variasi int [not null]\\n  jenis_komoditas varchar(50) [not null, default: 'Obat']\\n  satuan_tipe varchar(50) [not null, default: 'kecil']\\n  nama_satuan varchar(50) [not null, default: 'Unit']\\n  stok_tersedia_sistem int [not null]\\n  stok_reserved int [not null]\\n  stok_sistem_fisik int [not null]\\n  hitung_fisik int\\n  selisih int\\n  status_mutasi varchar(50) [not null, default: 'Belum Dimutasi']\\n  alasan_mutasi varchar(255)\\n  keterangan_mutasi text\\n  id_kartu_stok int\\n  dihitung_oleh int\\n  waktu_hitung timestamp\\n  dimutasi_oleh int\\n  waktu_mutasi timestamp\\n}"""
new_dbml2_batch = """Table detail_opname_batch {\\n  id int [pk, increment]\\n  id_sesi int [not null]\\n  id_batch int\\n  id_variasi int [not null]\\n  jenis_komoditas varchar(50) [not null, default: 'Obat']\\n  satuan_tipe varchar(50) [not null, default: 'kecil']\\n  nama_satuan varchar(50) [not null, default: 'Unit']\\n  stok_tersedia_sistem int [not null]\\n  stok_reserved int [not null]\\n  stok_sistem_fisik int [not null]\\n  fisik_baik int\\n  fisik_rusak int\\n  fisik_expired int\\n  status_hitung varchar(50) [default: 'Belum']\\n  hitung_fisik int\\n  selisih int\\n  status_mutasi varchar(50) [not null, default: 'Belum Dimutasi']\\n  alasan_mutasi varchar(255)\\n  keterangan_mutasi text\\n  id_kartu_stok int\\n  dihitung_oleh int\\n  waktu_hitung timestamp\\n  dimutasi_oleh int\\n  waktu_mutasi timestamp\\n}"""
c = c.replace(old_dbml2_batch, new_dbml2_batch)

# 3. Update the JS tables array for visual cards
old_js_sesi = """                    { name: "id_sesi_opname", type: "int(11)", key: "PK", extra: "auto_inc" },

                    { name: "kode_sesi", type: "varchar(50)", key: "", extra: "unique" },

                    { name: "status", type: "enum(Aktif, Ditutup, Dibatalkan)", key: "", extra: "" },

                    { name: "dibekukan_oleh", type: "int(11)", key: "FK", extra: "-> users.id_user" },

                    { name: "dibekukan_pada", type: "timestamp", key: "", extra: "kunci freeze" },

                    { name: "dibuka_oleh", type: "int(11)", key: "FK", extra: "-> users.id_user" },

                    { name: "dibuka_pada", type: "timestamp", key: "", extra: "buka freeze" },

                    { name: "catatan", type: "text", key: "", extra: "" }"""

new_js_sesi = """                    { name: "id_sesi_opname", type: "int(11)", key: "PK", extra: "auto_inc" },

                    { name: "kode_sesi", type: "varchar(50)", key: "", extra: "unique" },

                    { name: "status", type: "enum(Aktif, Selesai, Dibatalkan)", key: "", extra: "" },

                    { name: "tahap", type: "enum(Penghitungan, Verifikasi, Selesai)", key: "", extra: "alur fase" },

                    { name: "dibekukan_oleh", type: "int(11)", key: "FK", extra: "-> users.id_user" },

                    { name: "dibekukan_pada", type: "timestamp", key: "", extra: "kunci freeze" },

                    { name: "ditutup_hitung_oleh", type: "int(11)", key: "FK", extra: "-> users.id_user" },

                    { name: "ditutup_hitung_pada", type: "timestamp", key: "", extra: "tutup hitung" },

                    { name: "dibuka_oleh", type: "int(11)", key: "FK", extra: "-> users.id_user" },

                    { name: "dibuka_pada", type: "timestamp", key: "", extra: "buka freeze" },

                    { name: "catatan", type: "text", key: "", extra: "" }"""

c = c.replace(old_js_sesi, new_js_sesi)

old_js_batch = """                    { name: "stok_sistem_fisik", type: "int(11)", key: "", extra: "stok buku" },

                    { name: "hitung_fisik", type: "int(11)", key: "", extra: "cacahan riil" },

                    { name: "selisih", type: "int(11)", key: "", extra: "varian" }"""

new_js_batch = """                    { name: "stok_sistem_fisik", type: "int(11)", key: "", extra: "stok buku" },

                    { name: "fisik_baik", type: "int(11)", key: "", extra: "layak jual" },

                    { name: "fisik_rusak", type: "int(11)", key: "", extra: "rusak/cacat" },

                    { name: "fisik_expired", type: "int(11)", key: "", extra: "kedaluwarsa" },

                    { name: "status_hitung", type: "enum(Belum, Sudah)", key: "", extra: "parsial" },

                    { name: "hitung_fisik", type: "int(11)", key: "", extra: "total riil" },

                    { name: "selisih", type: "int(11)", key: "", extra: "varian" }"""

c = c.replace(old_js_batch, new_js_batch)

# Clean audit badge
c = c.replace('badgeText: "Audit Opname"', 'badgeText: "Stok Opname"')
c = c.replace('// Domain 5: Audit Stok Opname', '// Domain 5: Stok Opname & Rekonsiliasi')

with open('lain/logical_database_zencare.html', 'w', encoding='utf-8') as f:
    f.write(c)

print("Updated lain/logical_database_zencare.html successfully!")

