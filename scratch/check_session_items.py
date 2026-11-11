import subprocess

php_code = """<?php
require_once 'config/database.php';
$stmt = $pdo->query("
    SELECT dob.id, pi.nama_produk, dob.satuan_tipe, dob.nama_satuan, dob.stok_sistem_fisik, sb.no_batch, sb.tgl_exp
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id_sesi = 28 AND (
        pi.nama_produk LIKE '%Cefadroxil%' OR
        pi.nama_produk LIKE '%Sanmol%' OR
        pi.nama_produk LIKE '%Amoxicillin%' OR
        pi.nama_produk LIKE '%Tensimeter%' OR
        pi.nama_produk LIKE '%Termometer%' OR
        pi.nama_produk LIKE '%Betadine%'
    )
");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['id'] . ' | ' . $r['nama_produk'] . ' | ' . $r['satuan_tipe'] . ' | ' . $r['nama_satuan'] . ' | sistem=' . $r['stok_sistem_fisik'] . ' | ' . $r['no_batch'] . ' | ' . $r['tgl_exp'] . PHP_EOL;
}
"""

with open('scratch/temp_check.php', 'w', encoding='utf-8') as f:
    f.write(php_code)

res = subprocess.run(['C:/xampp/php/php.exe', 'scratch/temp_check.php'], capture_output=True, text=True)
print(res.stdout)

