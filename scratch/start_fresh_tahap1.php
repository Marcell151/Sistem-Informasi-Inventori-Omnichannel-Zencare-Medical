<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';
require_once __DIR__ . '/restore_baseline.php';

// Restore baseline cleanly
restoreBaselineInventory($pdo);
cekSesiOpnameAktif($pdo, true);

// Start fresh opname session by Superadmin
$kodeSesi = 'OPN-' . date('Ymd') . '-001';
$catatan = 'Sesi Stok Opname Pengujian Demo ZenCare Medical';
$userId = 1;

$pdo->beginTransaction();
$stmtSesi = $pdo->prepare("INSERT INTO sesi_opname (kode_sesi, status, dibekukan_oleh, dibekukan_pada, catatan) VALUES (?, 'Aktif', ?, NOW(), ?)");
$stmtSesi->execute([$kodeSesi, $userId, $catatan]);
$idSesi = $pdo->lastInsertId();

// Snapshot obat
$stmtObat = $pdo->query("
    SELECT sb.id AS id_batch, sb.id_variasi, sb.no_batch, sb.stok_sisa,
           COALESCE((
               SELECT SUM(pba.qty_satuan_kecil) 
               FROM pesanan_batch_alokasi pba 
               JOIN penjualan p ON pba.id_penjualan = p.id 
               WHERE pba.id_batch = sb.id 
                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
           ), 0) AS stok_reserved_kecil,
           pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.nama_produk, pv.nama_variasi
    FROM stok_batch sb
    JOIN produk_variasi pv ON sb.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1 
      AND (sb.stok_sisa + COALESCE((
               SELECT SUM(pba.qty_satuan_kecil) 
               FROM pesanan_batch_alokasi pba 
               JOIN penjualan p ON pba.id_penjualan = p.id 
               WHERE pba.id_batch = sb.id 
                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
           ), 0)) > 0
    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC, sb.tgl_exp ASC
");
$obatBatches = $stmtObat->fetchAll(PDO::FETCH_ASSOC);

$insDetail = $pdo->prepare("
    INSERT INTO detail_opname_batch 
    (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 'Belum Dimutasi')
");

foreach ($obatBatches as $ob) {
    $rasio = max(1, intval($ob['rasio_konversi']));
    $isSubBatch = (strpos($ob['no_batch'], '.') !== false);
    $stokFisikKecil = intval($ob['stok_sisa']) + intval($ob['stok_reserved_kecil']);

    if ($rasio > 1 && !$isSubBatch) {
        $satuanTipe = 'besar';
        $namaSatuan = $ob['satuan_besar'] ?: 'Dus';
        $stokFisik = max(0, intval(floor($stokFisikKecil / $rasio)));
        $stokTersedia = max(0, intval(floor(intval($ob['stok_sisa']) / $rasio)));
        $stokReserved = max(0, intval(floor(intval($ob['stok_reserved_kecil']) / $rasio)));
    } else {
        $satuanTipe = 'kecil';
        $namaSatuan = $ob['satuan_kecil'] ?: 'Pcs';
        $stokFisik = $stokFisikKecil;
        $stokTersedia = intval($ob['stok_sisa']);
        $stokReserved = intval($ob['stok_reserved_kecil']);
    }

    $insDetail->execute([
        $idSesi,
        $ob['id_batch'],
        $ob['id_variasi'],
        'Obat',
        $satuanTipe,
        $namaSatuan,
        $stokTersedia,
        $stokReserved,
        $stokFisik
    ]);
}

// Snapshot alkes
$stmtAlkes = $pdo->query("
    SELECT pv.id AS id_variasi, pv.satuan_kecil, pi.nama_produk, pv.nama_variasi,
           COALESCE(st.stok, 0) AS stok_toko,
           COALESCE((
               SELECT COUNT(us.id) 
               FROM unit_serial us 
               JOIN penjualan p ON us.id_penjualan = p.id 
               WHERE us.id_variasi = pv.id 
                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
           ), 0) AS stok_reserved_alkes
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_toko st ON pv.id = st.id_variasi
    WHERE pi.kategori = 'Alat Kesehatan' AND pv.is_active = 1
    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
");
$alkesItems = $stmtAlkes->fetchAll(PDO::FETCH_ASSOC);

foreach ($alkesItems as $ak) {
    $stokReservedAlkes = intval($ak['stok_reserved_alkes']);
    $stokFisikAlkes = intval($ak['stok_toko']) + $stokReservedAlkes;
    $stokTersediaAlkes = intval($ak['stok_toko']);

    $insDetail->execute([
        $idSesi,
        NULL,
        $ak['id_variasi'],
        'Alkes',
        'kecil',
        $ak['satuan_kecil'] ?: 'Unit',
        $stokTersediaAlkes,
        $stokReservedAlkes,
        $stokFisikAlkes
    ]);
}

$pdo->commit();
cekSesiOpnameAktif($pdo, true);
echo "SUCCESS: Fresh session $kodeSesi (ID: $idSesi) started in Tahap 1 with " . (count($obatBatches) + count($alkesItems)) . " items (All Belum Dihitung)!" . PHP_EOL;

