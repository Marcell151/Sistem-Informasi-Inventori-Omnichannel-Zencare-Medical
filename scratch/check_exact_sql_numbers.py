import pymysql

# Connect to local MySQL database inventory_zencare
conn = pymysql.connect(host='localhost', user='root', password='', database='inventory_zencare')
cur = conn.cursor(pymysql.cursors.DictCursor)

# 1. Cefadroxil
cur.execute("SELECT pi.nama_produk, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, sb.no_batch, sb.stok_sisa, sb.tgl_exp FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Cefadroxil%'")
print("Cefadroxil:", cur.fetchall())

# 2. Sanmol
cur.execute("SELECT pi.nama_produk, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, sb.no_batch, sb.stok_sisa, sb.tgl_exp FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Sanmol%'")
print("Sanmol:", cur.fetchall())

# 3. Amoxicillin
cur.execute("SELECT pi.nama_produk, pv.nama_variasi, pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, sb.no_batch, sb.stok_sisa, sb.tgl_exp FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Amoxicillin%'")
print("Amoxicillin:", cur.fetchall())

# 4. Tensimeter
cur.execute("SELECT pi.nama_produk, pv.nama_variasi, (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_count FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pi.nama_produk LIKE '%Tensimeter%'")
print("Tensimeter:", cur.fetchall())

# 5. Termometer
cur.execute("SELECT pi.nama_produk, pv.nama_variasi, (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_count FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pi.nama_produk LIKE '%Termometer%'")
print("Termometer:", cur.fetchall())

# 6. Betadine
cur.execute("SELECT pi.nama_produk, pv.nama_variasi, pv.satuan_kecil, sb.no_batch, sb.stok_sisa FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id JOIN stok_batch sb ON sb.id_variasi = pv.id WHERE pi.nama_produk LIKE '%Betadine%'")
print("Betadine:", cur.fetchall())

conn.close()

