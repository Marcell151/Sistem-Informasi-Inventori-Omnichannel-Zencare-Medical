import re

with open('c:/xampp/htdocs/inventory_zencare/pos/pos.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Remove cabang query
content = re.sub(r'\$stmtCabang = \$pdo->prepare\("SELECT \* FROM cabang WHERE id=\? AND is_active=1"\);\n\$stmtCabang->execute\(\[\$idCabangKaryawan\]\);\n\$cabangKaryawan = \$stmtCabang->fetch\(\);\n', '', content)

# Remove id_cabang from insert penjualan
content = content.replace('INSERT INTO penjualan (no_invoice,id_cabang,id_user,tipe_transaksi,status_pesanan,total_harga,created_at)', 'INSERT INTO penjualan (no_invoice,id_user,tipe_transaksi,status_pesanan,total_harga,created_at)')
content = content.replace('->execute([$invoiceNo, $idCabangKaryawan, $_SESSION[\'user_id\'] ?? 2]);', '->execute([$invoiceNo, $_SESSION[\'user_id\'] ?? 2]);')

# chk execute
content = content.replace('execute([$idCabangKaryawan, $idVar])', 'execute([$idVar])')

# batch warning query
content = content.replace('AND id_cabang = ? ', '')
content = content.replace('execute([$idVar, $idCabangKaryawan])', 'execute([$idVar])')

# unit serial query
content = content.replace('AND id_cabang = ?', '')

# update stok toko
content = content.replace('UPDATE stok_toko SET stok=stok-? WHERE id_variasi=? AND 1=1', 'UPDATE stok_toko SET stok=stok-? WHERE id_variasi=?')
content = content.replace('execute([$qtyPotong,$idVar,$idCabangKaryawan])', 'execute([$qtyPotong,$idVar])')

# SELECT sisa stok
content = content.replace('AND id_cabang=?', '')

# insert kartu stok
content = content.replace('INSERT INTO kartu_stok (id_cabang,id_variasi,jenis_mutasi,qty,sisa_stok,keterangan)', 'INSERT INTO kartu_stok (id_variasi,jenis_mutasi,qty,sisa_stok,keterangan)')
content = content.replace('execute([$idCabangKaryawan,$idVar,$qtyPotong,$sisa,$invoiceNo])', 'execute([$idVar,\'Keluar\',$qtyPotong,$sisa,"Penjualan Luring $invoiceNo"])')

# skuQ execute
content = content.replace('AND sc.id_cabang=?', '')
content = content.replace('execute([$item[\'id\'], $idCabangKaryawan])', 'execute([$item[\'id\']])')

# katalog
content = content.replace('execute([$idCabangKaryawan])', 'execute()')

with open('c:/xampp/htdocs/inventory_zencare/pos/pos.php', 'w', encoding='utf-8') as f:
    f.write(content)
