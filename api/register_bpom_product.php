<?php
// File: api/register_bpom_product.php
// Endpoint untuk otomatis mendaftarkan obat dari API BPOM RI ke Master Produk ZenCare Medical
// Dipanggil saat obat baru dari API BPOM dipilih atau di-scan tetapi belum ada di katalog master produk.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode HTTP harus POST.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$productName = trim($data['product_name'] ?? '');
$barcode     = trim($data['barcode'] ?? '');
$dosageForm  = trim($data['dosage_form'] ?? '');
$category    = trim($data['category'] ?? 'Obat');
$company     = trim($data['company'] ?? '');
$brandName   = trim($data['brand_name'] ?? '');

if (empty($productName)) {
    echo json_encode(['success' => false, 'message' => 'Nama produk wajib diisi.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Cek apakah produk variasi dengan SKU/Barcode ini sudah ada
    if (!empty($barcode)) {
        $stmtCheckVar = $pdo->prepare("
            SELECT pv.id AS variasi_id, pi.id AS induk_id, pi.nama_produk, pv.nama_variasi, pv.sku_variasi,
                   pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_besar
            FROM produk_variasi pv
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE LOWER(pv.sku_variasi) = LOWER(?)
            LIMIT 1
        ");
        $stmtCheckVar->execute([$barcode]);
        $existingVar = $stmtCheckVar->fetch(PDO::FETCH_ASSOC);

        if ($existingVar) {
            $pdo->commit();
            echo json_encode([
                'success'        => true,
                'already_exists' => true,
                'id_variasi'     => (int)$existingVar['variasi_id'],
                'label'          => $existingVar['nama_produk'] . ' - ' . $existingVar['nama_variasi'],
                'kategori'       => $existingVar['kategori'],
                'satuan_besar'   => $existingVar['satuan_besar'] ?: 'Dus',
                'satuan_kecil'   => $existingVar['satuan_kecil'] ?: 'Pcs',
                'rasio_konversi' => (int)$existingVar['rasio_konversi'],
                'harga_jual_besar' => (float)$existingVar['harga_jual_besar'],
                'message'        => 'Produk sudah terdaftar di Master Produk.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // 2. Cek apakah produk induk dengan nama mirip sudah ada
    $stmtCheckInduk = $pdo->prepare("SELECT id FROM produk_induk WHERE LOWER(nama_produk) = LOWER(?) LIMIT 1");
    $stmtCheckInduk->execute([$productName]);
    $idInduk = $stmtCheckInduk->fetchColumn();

    if (!$idInduk) {
        // Tentukan kategori
        $katDb = (stripos($category, 'alat') !== false || stripos($productName, 'tensimeter') !== false || stripos($productName, 'kursi roda') !== false) 
                 ? 'Alat Kesehatan' : 'Obat';

        // Buat SKU Induk Unik
        $prefixInduk = ($katDb === 'Alat Kesehatan') ? 'ALK-' : 'OBT-';
        $randSeq = strtoupper(substr(md5(uniqid($productName, true)), 0, 4));
        $skuInduk = $prefixInduk . date('y') . '-' . $randSeq;

        $desc = "Obat resmi terdaftar BPOM RI.";
        if ($company) $desc .= " Produsen/Distributor: $company.";
        if ($dosageForm) $desc .= " Sediaan: $dosageForm.";

        $stmtInsertInduk = $pdo->prepare("
            INSERT INTO produk_induk (sku_induk, nama_produk, deskripsi, kategori, is_active)
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmtInsertInduk->execute([$skuInduk, $productName, $desc, $katDb]);
        $idInduk = $pdo->lastInsertId();
    }

    // 3. Tentukan nama variasi, satuan besar, satuan kecil, dan rasio konversi dari dosage_form
    $satuanBesar = 'Box';
    $satuanKecil = 'Pcs';
    $rasio = 10;
    $namaVariasi = 'Kemasan Box';

    if (stripos($dosageForm, 'sirup') !== false || stripos($dosageForm, 'botol') !== false) {
        $satuanBesar = 'Karton';
        $satuanKecil = 'Botol';
        $rasio = 24;
        $namaVariasi = 'Botol 60ml';
    } elseif (stripos($dosageForm, 'strip') !== false || stripos($dosageForm, 'kaplet') !== false || stripos($dosageForm, 'tablet') !== false) {
        $satuanBesar = 'Box';
        $satuanKecil = 'Strip';
        $rasio = 10;
        $namaVariasi = 'Tablet / Strip isi 10';
    } elseif (stripos($dosageForm, 'tube') !== false || stripos($dosageForm, 'salep') !== false) {
        $satuanBesar = 'Box';
        $satuanKecil = 'Tube';
        $rasio = 10;
        $namaVariasi = 'Tube 15g';
    } elseif (stripos($dosageForm, 'sachet') !== false) {
        $satuanBesar = 'Box';
        $satuanKecil = 'Sachet';
        $rasio = 12;
        $namaVariasi = 'Sachet Box isi 12';
    }

    // Buat SKU Variasi Unik
    $skuVar = !empty($barcode) ? $barcode : ('VAR-' . date('ym') . '-' . rand(100, 999));
    
    // Cek apakah SKU variasi sudah dipakai, jika ya tambahkan suffix unik
    $stmtCheckSku = $pdo->prepare("SELECT COUNT(*) FROM produk_variasi WHERE sku_variasi = ?");
    $stmtCheckSku->execute([$skuVar]);
    if ($stmtCheckSku->fetchColumn() > 0) {
        $skuVar .= '-' . rand(10, 99);
    }

    $stmtInsertVar = $pdo->prepare("
        INSERT INTO produk_variasi (id_produk_induk, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, harga_jual_kecil, harga_jual_besar, is_active)
        VALUES (?, ?, ?, ?, ?, ?, 0.00, 0.00, 1)
    ");
    $stmtInsertVar->execute([$idInduk, $skuVar, $namaVariasi, $satuanKecil, $satuanBesar, $rasio]);
    $idVariasi = $pdo->lastInsertId();

    // 4. Inisialisasi stok toko dengan saldo 0
    $stmtInitStok = $pdo->prepare("INSERT INTO stok_toko (id_variasi, stok) VALUES (?, 0) ON DUPLICATE KEY UPDATE id_variasi=id_variasi");
    $stmtInitStok->execute([$idVariasi]);

    $pdo->commit();

    $labelLengkap = $productName . ' - ' . $namaVariasi;

    echo json_encode([
        'success'        => true,
        'already_exists' => false,
        'id_variasi'     => (int)$idVariasi,
        'id_induk'       => (int)$idInduk,
        'label'          => $labelLengkap,
        'nama_produk'    => $productName,
        'nama_variasi'   => $namaVariasi,
        'sku_variasi'    => $skuVar,
        'kategori'       => 'Obat',
        'satuan_besar'   => $satuanBesar,
        'satuan_kecil'   => $satuanKecil,
        'rasio_konversi' => $rasio,
        'harga_jual_besar' => 0,
        'message'        => 'Obat berhasil didaftarkan ke Master Produk Toko & siap masuk ke baris penerimaan.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mendaftarkan produk: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
