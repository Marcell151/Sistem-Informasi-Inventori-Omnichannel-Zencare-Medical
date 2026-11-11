<?php
// Simulasi Pengujian Stok Opname Komprehensif (111 Entitas & 6 Skenario Anomali)
// Menjalankan siklus penuh dari awal (Mulai Sesi -> Hitung Fisik 3 Kondisi -> Verifikasi -> Mutasi 6 Anomali -> Selesaikan Sesi -> Berita Acara)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/opname_helper.php';
require_once __DIR__ . '/restore_baseline.php';

$stopAtVerifikasi = $stopAtVerifikasi ?? false;

echo "=== MEMULAI SIMULASI STOK OPNAME SELURUH PRODUK ===\n";

// 1. Pastikan database dalam kondisi baseline bersih
echo "1. Memulihkan database ke Baseline Bersih...\n";
restoreBaselineInventory($pdo);
cekSesiOpnameAktif($pdo, true);
echo "[OK] Database bersih.\n";

// 2. Superadmin memulai sesi opname baru
echo "\n2. Superadmin memulai Sesi Stok Opname Terpusat...\n";
$kodeSesi = 'OPN-' . date('Ymd') . '-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
$catatan = 'Stok Opname Periodik Gudang & Etalase Toko ZenCare Medical';
$userIdSuperadmin = 1;
$userIdAdmin = 2;
$userIdKasir = 4;

$pdo->beginTransaction();
$stmtSesi = $pdo->prepare("INSERT INTO sesi_opname (kode_sesi, status, tahap, dibekukan_oleh, dibekukan_pada, catatan) VALUES (?, 'Aktif', 'Penghitungan', ?, NOW(), ?)");
$stmtSesi->execute([$kodeSesi, $userIdSuperadmin, $catatan]);
$idSesi = $pdo->lastInsertId();

// Snapshot 1: Seluruh batch obat & BMHP
$stmtObat = $pdo->query("
    SELECT sb.id AS id_batch, sb.id_variasi, sb.no_batch, sb.stok_sisa,
           COALESCE((
               SELECT SUM(pba.qty_satuan_kecil) 
               FROM pesanan_batch_alokasi pba 
               JOIN penjualan p ON pba.id_penjualan = p.id 
               WHERE pba.id_batch = sb.id 
                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
           ), 0) AS stok_reserved_kecil,
           pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.nama_produk, pv.nama_variasi, pi.kategori
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

// Snapshot 2: Seluruh Alat Kesehatan ber-Serial Number
$stmtAlkes = $pdo->query("
    SELECT pv.id AS id_variasi, pv.satuan_besar, pv.satuan_kecil, pi.nama_produk, pv.nama_variasi,
           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_tersedia,
           (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')) as sn_reserved
    FROM produk_variasi pv
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    WHERE pv.is_active = 1
      AND ((SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') + 
           (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil'))) > 0
    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
");
$alkesItems = $stmtAlkes->fetchAll(PDO::FETCH_ASSOC);

foreach ($alkesItems as $ak) {
    $snTersedia = intval($ak['sn_tersedia']);
    $snReserved = intval($ak['sn_reserved']);
    $stokFisikAlkes = $snTersedia + $snReserved;
    $namaSatuan = $ak['satuan_besar'] ?: ($ak['satuan_kecil'] ?: 'Unit');

    $insDetail->execute([
        $idSesi,
        null,
        $ak['id_variasi'],
        'Alkes',
        'besar',
        $namaSatuan,
        $snTersedia,
        $snReserved,
        $stokFisikAlkes
    ]);
}

$pdo->commit();
cekSesiOpnameAktif($pdo, true);
$totalRowSnapshot = count($obatBatches) + count($alkesItems);
echo "[OK] Sesi $kodeSesi aktif dengan $totalRowSnapshot baris snapshot fisik.\n";

// 3. Kasir melakukan Blind Count di lapangan (3 Kondisi: Baik, Rusak, Expired)
echo "\n3. Kasir (User ID 4) menyimpan hasil hitung fisik 3 kondisi...\n";
$allRows = $pdo->query("
    SELECT dob.*, pi.nama_produk, pv.nama_variasi, pv.rasio_konversi, sb.no_batch, sb.tgl_exp
    FROM detail_opname_batch dob
    JOIN produk_variasi pv ON dob.id_variasi = pv.id
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
    WHERE dob.id_sesi = $idSesi
    ORDER BY dob.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$stmtUp = $pdo->prepare("
    UPDATE detail_opname_batch
    SET fisik_baik = ?,
        fisik_rusak = ?,
        fisik_expired = ?,
        hitung_fisik = ?,
        selisih = ?,
        status_mutasi = ?,
        status_hitung = 'Sudah',
        dihitung_oleh = ?,
        waktu_hitung = NOW()
    WHERE id = ?
");

$anomaliRows = [
    'cefadroxil' => null,
    'sanmol'     => null,
    'amox'       => null,
    'tensimeter' => null,
    'termometer' => null,
    'betadine'   => null,
];

foreach ($allRows as $uRow) {
    $sistem = intval($uRow['stok_sistem_fisik']);
    $fBaik = $sistem;
    $fRusak = 0;
    $fExp = 0;

    // 1. Cefadroxil 500mg (Satuan Kecil) -> Rusak 2 Strip
    if (!$anomaliRows['cefadroxil'] && stripos($uRow['nama_produk'], 'Cefadroxil') !== false && $uRow['satuan_tipe'] === 'kecil') {
        $fBaik = max(0, $sistem - 2);
        $fRusak = 2;
        $fExp = 0;
        $anomaliRows['cefadroxil'] = $uRow;
    }
    // 2. Sanmol Sirup Anak 60ml (Batch Expired) -> Kedaluwarsa
    elseif (!$anomaliRows['sanmol'] && stripos($uRow['nama_produk'], 'Sanmol') !== false && !empty($uRow['tgl_exp']) && strtotime($uRow['tgl_exp']) < strtotime(date('Y-m-d'))) {
        $fBaik = 0;
        $fRusak = 0;
        $fExp = $sistem;
        $anomaliRows['sanmol'] = $uRow;
    }
    // 3. Amoxicillin 500mg (Satuan Kecil) -> Hilang 2 Strip
    elseif (!$anomaliRows['amox'] && stripos($uRow['nama_produk'], 'Amoxicillin') !== false && $uRow['satuan_tipe'] === 'kecil') {
        $fBaik = max(0, $sistem - 2);
        $anomaliRows['amox'] = $uRow;
    }
    // 4. Tensimeter Digital Omron (Alkes SN) -> Hilang 1 Unit
    elseif (!$anomaliRows['tensimeter'] && stripos($uRow['nama_produk'], 'Tensimeter') !== false) {
        $fBaik = max(0, $sistem - 1);
        $anomaliRows['tensimeter'] = $uRow;
    }
    // 5. Termometer Digital Omron (Alkes SN) -> Rusak 1 Unit
    elseif (!$anomaliRows['termometer'] && stripos($uRow['nama_produk'], 'Termometer Digital') !== false) {
        $fBaik = max(0, $sistem - 1);
        $fRusak = 1;
        $anomaliRows['termometer'] = $uRow;
    }
    // 6. Betadine Solution 15ml (Satuan Kecil) -> Surplus +2 Botol
    elseif (!$anomaliRows['betadine'] && stripos($uRow['nama_produk'], 'Betadine') !== false && $uRow['satuan_tipe'] === 'kecil') {
        $fBaik = $sistem + 2;
        $anomaliRows['betadine'] = $uRow;
    }

    $selisih = $fBaik - $sistem;
    $statusMutasi = ($selisih === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';

    $stmtUp->execute([$fBaik, $fRusak, $fExp, $fBaik, $selisih, $statusMutasi, $userIdKasir, $uRow['id']]);
}

// 4. Buka Tahap Verifikasi Selisih
$pdo->prepare("
    UPDATE sesi_opname
    SET tahap = 'Verifikasi',
        ditutup_hitung_oleh = ?,
        ditutup_hitung_pada = NOW()
    WHERE id = ?
")->execute([$userIdAdmin, $idSesi]);

echo "[OK] Tahap Verifikasi dibuka: 105 barang sesuai disembunyikan otomatis, 6 barang anomali ditampilkan.\n";

if ($stopAtVerifikasi) {
    cekSesiOpnameAktif($pdo, true);
    return;
}

// 5. Admin Mengeksekusi Mutasi untuk 6 Skenario Anomali
echo "\n4. Admin (User ID 2) memverifikasi dan mengeksekusi mutasi untuk 6 barang anomali...\n";

$stmtKartu = $pdo->prepare("
    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
    VALUES (?, ?, ?, ?, 'Manual', ?, ?, ?, ?, ?, ?)
");
$stmtUpDetailMutasi = $pdo->prepare("
    UPDATE detail_opname_batch
    SET status_mutasi = 'Sudah Dimutasi',
        alasan_mutasi = ?,
        keterangan_mutasi = ?,
        dimutasi_oleh = ?,
        waktu_mutasi = NOW()
    WHERE id = ?
");

// 5.1 Mutasi 1: Cefadroxil 500mg -> Barang Rusak (-2 Strip)
if ($rCef = $anomaliRows['cefadroxil']) {
    $pdo->beginTransaction();
    $qtyDef = 2;
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyDef, $rCef['id_batch']]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$qtyDef, $rCef['id_variasi']]);
    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rCef['id_variasi']}")->fetchColumn();
    $ket = "Kemasan blister lembab dan rusak fisik di rak etalase";
    $ketKartu = "[Stok Opname: $kodeSesi] Batch: {$rCef['no_batch']} | Barang Rusak - $ket";
    $stmtKartu->execute([$rCef['id_variasi'], $rCef['id_batch'], $rCef['satuan_tipe'], 'Keluar', 'Barang Rusak', $kodeSesi, $qtyDef, $sisaStok, $ketKartu, $userIdAdmin]);
    $stmtUpDetailMutasi->execute(["Barang Rusak ($qtyDef {$rCef['nama_satuan']})", $ket, $userIdAdmin, $rCef['id']]);
    $pdo->commit();
    echo "  - 1. Cefadroxil 500mg dimutasi: Barang Rusak (-2 {$rCef['nama_satuan']})\n";
}

// 5.2 Mutasi 2: Sanmol Sirup Anak 60ml (Batch SNM-24K061) -> Barang Kedaluwarsa (-5 Karton)
if ($rSan = $anomaliRows['sanmol']) {
    $pdo->beginTransaction();
    $qtyDef = intval($rSan['stok_sistem_fisik']);
    $rasio = max(1, intval($rSan['rasio_konversi'] ?? 1));
    $qtyPcs = ($rSan['satuan_tipe'] === 'besar') ? ($qtyDef * $rasio) : $qtyDef;
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyPcs, $rSan['id_batch']]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$qtyPcs, $rSan['id_variasi']]);
    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rSan['id_variasi']}")->fetchColumn();
    $ket = "Batch {$rSan['no_batch']} telah melewati tanggal kedaluwarsa dan ditarik ke karantina";
    $ketKartu = "[Stok Opname: $kodeSesi] Batch: {$rSan['no_batch']} | Barang Kedaluwarsa ($qtyDef {$rSan['nama_satuan']} = $qtyPcs pcs) - $ket";
    $stmtKartu->execute([$rSan['id_variasi'], $rSan['id_batch'], $rSan['satuan_tipe'], 'Keluar', 'Barang Kedaluwarsa', $kodeSesi, $qtyDef, $sisaStok, $ketKartu, $userIdAdmin]);
    $stmtUpDetailMutasi->execute(["Barang Kedaluwarsa ($qtyDef {$rSan['nama_satuan']})", $ket, $userIdAdmin, $rSan['id']]);
    $pdo->commit();
    echo "  - 2. Sanmol Sirup Anak 60ml ({$rSan['no_batch']}) dimutasi: Barang Kedaluwarsa (-$qtyDef {$rSan['nama_satuan']})\n";
}

// 5.3 Mutasi 3: Amoxicillin 500mg -> Barang Hilang (-2 Strip)
if ($rAmx = $anomaliRows['amox']) {
    $pdo->beginTransaction();
    $qtyDef = 2;
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa - ? WHERE id = ?")->execute([$qtyDef, $rAmx['id_batch']]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$qtyDef, $rAmx['id_variasi']]);
    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rAmx['id_variasi']}")->fetchColumn();
    $ket = "Selisih kurang 2 strip tidak ditemukan saat pemeriksaan fisik rak etalase";
    $ketKartu = "[Stok Opname: $kodeSesi] Batch: {$rAmx['no_batch']} | Barang Hilang - $ket";
    $stmtKartu->execute([$rAmx['id_variasi'], $rAmx['id_batch'], $rAmx['satuan_tipe'], 'Keluar', 'Barang Hilang', $kodeSesi, $qtyDef, $sisaStok, $ketKartu, $userIdAdmin]);
    $stmtUpDetailMutasi->execute(["Barang Hilang ($qtyDef {$rAmx['nama_satuan']})", $ket, $userIdAdmin, $rAmx['id']]);
    $pdo->commit();
    echo "  - 3. Amoxicillin 500mg dimutasi: Barang Hilang (-2 {$rAmx['nama_satuan']})\n";
}

// 5.4 Mutasi 4: Tensimeter Digital Omron (Alkes SN) -> Barang Hilang (-1 Unit)
if ($rTns = $anomaliRows['tensimeter']) {
    $pdo->beginTransaction();
    $targetSn = $pdo->query("SELECT id, serial_number FROM unit_serial WHERE id_variasi = {$rTns['id_variasi']} AND status = 'Tersedia' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $ket = "1 unit nomor seri {$targetSn['serial_number']} tidak ditemukan di etalase alat kesehatan";
    $pdo->prepare("UPDATE unit_serial SET status = 'Hilang', catatan = ? WHERE id = ?")->execute(["[Opname $kodeSesi] Barang Hilang: $ket", $targetSn['id']]);
    $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, 'Kurang_Hilang')")->execute([$rTns['id'], $targetSn['id'], $targetSn['serial_number']]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok - 1 WHERE id_variasi = ?")->execute([$rTns['id_variasi']]);
    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rTns['id_variasi']}")->fetchColumn();
    $ketKartu = "[Stok Opname: $kodeSesi] SN: {$targetSn['serial_number']} (Barang Hilang) | Barang Hilang - $ket";
    $stmtKartu->execute([$rTns['id_variasi'], null, 'besar', 'Keluar', 'Barang Hilang', $kodeSesi, 1, $sisaStok, $ketKartu, $userIdAdmin]);
    $stmtUpDetailMutasi->execute(["Barang Hilang (1 Unit)", $ket, $userIdAdmin, $rTns['id']]);
    $pdo->commit();
    echo "  - 4. Tensimeter Digital Omron dimutasi: Barang Hilang (-1 Unit, SN: {$targetSn['serial_number']})\n";
}

// 5.5 Mutasi 5: Termometer Digital Omron (Alkes SN) -> Barang Rusak (-1 Unit)
if ($rTrm = $anomaliRows['termometer']) {
    $pdo->beginTransaction();
    $targetSn = $pdo->query("SELECT id, serial_number FROM unit_serial WHERE id_variasi = {$rTrm['id_variasi']} AND status = 'Tersedia' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $ket = "1 unit nomor seri {$targetSn['serial_number']} mengalami kerusakan layar LCD pecah";
    $pdo->prepare("UPDATE unit_serial SET status = 'Retur/Rusak', catatan = ? WHERE id = ?")->execute(["[Opname $kodeSesi] Barang Rusak: $ket", $targetSn['id']]);
    $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, 'Kurang_Rusak')")->execute([$rTrm['id'], $targetSn['id'], $targetSn['serial_number']]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok - 1 WHERE id_variasi = ?")->execute([$rTrm['id_variasi']]);
    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rTrm['id_variasi']}")->fetchColumn();
    $ketKartu = "[Stok Opname: $kodeSesi] SN: {$targetSn['serial_number']} (Barang Rusak) | Barang Rusak - $ket";
    $stmtKartu->execute([$rTrm['id_variasi'], null, 'besar', 'Keluar', 'Barang Rusak', $kodeSesi, 1, $sisaStok, $ketKartu, $userIdAdmin]);
    $stmtUpDetailMutasi->execute(["Barang Rusak (1 Unit)", $ket, $userIdAdmin, $rTrm['id']]);
    $pdo->commit();
    echo "  - 5. Termometer Digital Omron dimutasi: Barang Rusak (-1 Unit, SN: {$targetSn['serial_number']})\n";
}

// 5.6 Mutasi 6: Betadine Solution 15ml -> Kelebihan Kirim Supplier (+2 Botol)
if ($rBet = $anomaliRows['betadine']) {
    $pdo->beginTransaction();
    $qtySurplus = 2;
    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa + ? WHERE id = ?")->execute([$qtySurplus, $rBet['id_batch']]);
    $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$qtySurplus, $rBet['id_variasi']]);
    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rBet['id_variasi']}")->fetchColumn();
    $ket = "Kelebihan fisik +2 botol dari pengiriman supplier batch {$rBet['no_batch']}";
    $ketKartu = "[Stok Opname: $kodeSesi] Batch: {$rBet['no_batch']} | Kelebihan Kirim Supplier - $ket";
    $stmtKartu->execute([$rBet['id_variasi'], $rBet['id_batch'], $rBet['satuan_tipe'], 'Masuk', 'Koreksi Salah Catat / Ditemukan', $kodeSesi, $qtySurplus, $sisaStok, $ketKartu, $userIdAdmin]);
    $stmtUpDetailMutasi->execute(["Kelebihan Kirim Supplier ($qtySurplus {$rBet['nama_satuan']})", $ket, $userIdAdmin, $rBet['id']]);
    $pdo->commit();
    echo "  - 6. Betadine Solution 15ml dimutasi: Kelebihan Kirim Supplier (+2 {$rBet['nama_satuan']})\n";
}

// 6. Superadmin Menyelesaikan Sesi Stok Opname & Membuka Kunci Transaksi Server
echo "\n5. Superadmin (User ID 1) menyelesaikan sesi opname & menerbitkan Berita Acara...\n";
$pdo->prepare("
    UPDATE sesi_opname
    SET status = 'Selesai',
        dibuka_oleh = ?,
        dibuka_pada = NOW()
    WHERE id = ?
")->execute([$userIdSuperadmin, $idSesi]);

cekSesiOpnameAktif($pdo, true);
echo "[OK] Sesi $kodeSesi resmi DISELESAIKAN! Berita Acara siap dicetak (ID Sesi: $idSesi).\n";
