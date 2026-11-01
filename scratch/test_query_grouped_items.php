<?php
require_once __DIR__ . '/../config/koneksi.php';

$stmt = $pdo->query("SELECT * FROM sesi_opname ORDER BY id DESC LIMIT 1");
$sesi = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Latest sesi: " . ($sesi['kode_sesi'] ?? 'NONE') . " (Status: " . ($sesi['status'] ?? '-') . ")\n";

if ($sesi) {
    $stmtItems = $pdo->prepare("
        SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
               pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp,
               u_hit.nama_lengkap AS nama_penghitung, u_mut.nama_lengkap AS nama_pemutasi
        FROM detail_opname_batch dob
        JOIN produk_variasi pv ON dob.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
        LEFT JOIN users u_hit ON dob.dihitung_oleh = u_hit.id
        LEFT JOIN users u_mut ON dob.dimutasi_oleh = u_mut.id
        WHERE dob.id_sesi = ?
        ORDER BY dob.jenis_komoditas ASC,
                 pi.nama_produk ASC, pv.nama_variasi ASC,
                 (dob.satuan_tipe = 'besar') DESC,
                 sb.tgl_exp ASC
    ");
    $stmtItems->execute([$sesi['id']]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    echo "Detail items count: " . count($items) . "\n";

    $grouped = [];
    foreach ($items as $it) {
        $vid = $it['id_variasi'];
        if (!isset($grouped[$vid])) {
            $grouped[$vid] = [
                'nama_produk' => $it['nama_produk'],
                'nama_variasi' => $it['nama_variasi'],
                'rasio' => $it['rasio_konversi'],
                'items' => []
            ];
        }
        $grouped[$vid]['items'][] = $it;
    }
    echo "Grouped products count: " . count($grouped) . "\n";
    $first5 = array_slice($grouped, 0, 5);
    foreach ($first5 as $g) {
        echo " - " . $g['nama_produk'] . " (" . $g['nama_variasi'] . ", Rasio: " . $g['rasio'] . ") -> " . count($g['items']) . " baris sub-item\n";
    }
}
