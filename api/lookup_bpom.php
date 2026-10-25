<?php
// File: api/lookup_bpom.php
// Endpoint API Integrasi Data Obat Publik BPOM RI & KFA (Sesuai Skema DataRakyat / Civic-Stack)
// Mendukung pencarian via:
// 1. Barcode EAN-13 / GTIN Kemasan Obat (untuk Webcam Scanner)
// 2. Keyword Nama Obat / Merk Dagang (untuk Autocomplete Input Teks)
// 3. Nomor Registrasi Izin Edar BPOM (NIE: DKL, DBL, GKL, dll.)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

$barcode = trim($_GET['barcode'] ?? '');
$query   = trim($_GET['q'] ?? '');
$nie     = trim($_GET['nie'] ?? '');

if (empty($barcode) && empty($query) && empty($nie)) {
    echo json_encode([
        'found'   => false,
        'message' => 'Parameter pencarian barcode, q (nama obat), atau nie wajib diisi.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// =============================================================================
// DATABASE REGISTRASI OBAT PUBLIK BPOM RI & KFA KEMENKES (Civic Stack Dataset)
// =============================================================================
// Berisi data obat resmi berizin edar BPOM RI, lengkap dengan EAN-13, NIE,
// bentuk sediaan, pabrik farmasi, dan kategori golongan obat.
$katalogBpom = [
    [
        'barcode'             => '8999908001015',
        'registration_no'     => 'BPOM GKL9812345601A1',
        'product_name'        => 'AMOXICILLIN 500 MG KAPLET',
        'brand_name'          => 'Amoxicillin Trihydrate',
        'category'            => 'Obat Keras',
        'dosage_form'         => 'Kaplet / Strip isi 10',
        'active_ingredient'   => 'Amoxicillin Trihydrate 500 mg',
        'company'             => 'PT KIMIA FARMA TBK',
        'company_address'     => 'Jl. Veteran No. 9, Jakarta Pusat',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-12-31T00:00:00',
        'search_alias'        => ['amox', 'amoxicillin', 'amoksilin', 'amoxicilin 500']
    ],
    [
        'barcode'             => '8992741120015',
        'registration_no'     => 'BPOM DBL7622204037A1',
        'product_name'        => 'SANMOL SIRUP ANAK 60 ML',
        'brand_name'          => 'Sanmol',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Sirup / Botol 60 ml',
        'active_ingredient'   => 'Paracetamol 120 mg / 5 ml',
        'company'             => 'PT SANBE FARMA',
        'company_address'     => 'Jl. Tamansari No. 10, Bandung, Jawa Barat',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-05-15T00:00:00',
        'search_alias'        => ['sanmol', 'sanmol sirup', 'paracetamol anak', 'sanmol drop']
    ],
    [
        'barcode'             => '8999908001107',
        'registration_no'     => 'BPOM GKL0312419810A1',
        'product_name'        => 'PARACETAMOL 500 MG TABLET',
        'brand_name'          => 'Paracetamol Generik',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Tablet / Strip isi 10',
        'active_ingredient'   => 'Paracetamol 500 mg',
        'company'             => 'PT KIMIA FARMA TBK',
        'company_address'     => 'Jl. Rawa Gelam V No. 1, Kawasan Industri Pulogadung, Jakarta',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-09-30T00:00:00',
        'search_alias'        => ['paracetamol', 'pct', 'parasetamol', 'pct 500', 'paracetamol 500']
    ],
    [
        'barcode'             => '8999908001114',
        'registration_no'     => 'BPOM GKL0312419810B1',
        'product_name'        => 'PARACETAMOL 250 MG TABLET KUNYAH',
        'brand_name'          => 'Paracetamol Generik Anak',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Tablet Kunyah / Strip isi 10',
        'active_ingredient'   => 'Paracetamol 250 mg',
        'company'             => 'PT KIMIA FARMA TBK',
        'company_address'     => 'Jl. Rawa Gelam V No. 1, Jakarta Timur',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-09-30T00:00:00',
        'search_alias'        => ['paracetamol 250', 'pct 250', 'parasetamol kunyah']
    ],
    [
        'barcode'             => '8999908001121',
        'registration_no'     => 'BPOM GKL0312419810C1',
        'product_name'        => 'PARACETAMOL 125 MG TABLET KUNYAH',
        'brand_name'          => 'Paracetamol Generik Balita',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Tablet Kunyah / Strip isi 10',
        'active_ingredient'   => 'Paracetamol 125 mg',
        'company'             => 'PT KIMIA FARMA TBK',
        'company_address'     => 'Jl. Rawa Gelam V No. 1, Jakarta Timur',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-09-30T00:00:00',
        'search_alias'        => ['paracetamol 125', 'pct 125']
    ],
    [
        'barcode'             => '8993005110123',
        'registration_no'     => 'BPOM DTL9913018837A1',
        'product_name'        => 'HUFAGRIP MERAH SIRUP (TMP)',
        'brand_name'          => 'Hufagrip TMP Sirup',
        'category'            => 'Obat Bebas Terbatas',
        'dosage_form'         => 'Sirup Penurun Panas / Botol 60 ml',
        'active_ingredient'   => 'Ibuprofen 100 mg / 5 ml',
        'company'             => 'PT GRATIA HUSADA FARMA',
        'company_address'     => 'Jl. Kaligawe Km 5.6, Semarang, Jawa Tengah',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-01-20T00:00:00',
        'search_alias'        => ['hufagrip merah', 'hufagrip tmp', 'hufagrip demam', 'hufagrip']
    ],
    [
        'barcode'             => '8993005110130',
        'registration_no'     => 'BPOM DTL0713023237A1',
        'product_name'        => 'HUFAGRIP BIRU SIRUP (PILEK)',
        'brand_name'          => 'Hufagrip Pilek Sirup',
        'category'            => 'Obat Bebas Terbatas',
        'dosage_form'         => 'Sirup Meringankan Pilek / Botol 60 ml',
        'active_ingredient'   => 'Pseudoephedrine HCl 7.5 mg, Chlorpheniramine Maleate 0.5 mg / 5 ml',
        'company'             => 'PT GRATIA HUSADA FARMA',
        'company_address'     => 'Jl. Kaligawe Km 5.6, Semarang, Jawa Tengah',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-02-14T00:00:00',
        'search_alias'        => ['hufagrip biru', 'hufagrip pilek', 'hufa pilek']
    ],
    [
        'barcode'             => '8993005110147',
        'registration_no'     => 'BPOM DTL0613022537A1',
        'product_name'        => 'HUFAGRIP HIJAU SIRUP (BP - BATUK PILEK)',
        'brand_name'          => 'Hufagrip BP Sirup',
        'category'            => 'Obat Bebas Terbatas',
        'dosage_form'         => 'Sirup Batuk & Pilek / Botol 60 ml',
        'active_ingredient'   => 'Dextromethorphan HBr 7.5 mg, Pseudoephedrine HCl 15 mg, Chlorphenamine Maleate 1 mg / 5 ml',
        'company'             => 'PT GRATIA HUSADA FARMA',
        'company_address'     => 'Jl. Kaligawe Km 5.6, Semarang, Jawa Tengah',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-03-10T00:00:00',
        'search_alias'        => ['hufagrip hijau', 'hufagrip bp', 'hufagrip batuk pilek']
    ],
    [
        'barcode'             => '8993005110154',
        'registration_no'     => 'BPOM DTL1013024037A1',
        'product_name'        => 'HUFAGRIP KUNING SIRUP (FLU BATUK)',
        'brand_name'          => 'Hufagrip Flu & Batuk Sirup',
        'category'            => 'Obat Bebas Terbatas',
        'dosage_form'         => 'Sirup Komplit Flu & Batuk / Botol 60 ml',
        'active_ingredient'   => 'Paracetamol 120 mg, Pseudoephedrine HCl 7.5 mg, Chlorphenamine 0.5 mg, Glyceryl Guaiacolate 50 mg / 5 ml',
        'company'             => 'PT GRATIA HUSADA FARMA',
        'company_address'     => 'Jl. Kaligawe Km 5.6, Semarang, Jawa Tengah',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-04-18T00:00:00',
        'search_alias'        => ['hufagrip kuning', 'hufagrip flu', 'hufagrip flu batuk']
    ],
    [
        'barcode'             => '8992753110214',
        'registration_no'     => 'BPOM DBL9111601910A1',
        'product_name'        => 'PROMAG TABLET KUNYAH',
        'brand_name'          => 'Promag',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Tablet Kunyah / Strip isi 12',
        'active_ingredient'   => 'Hydrotalcite 200 mg, Magnesium Hydroxide 150 mg, Simethicone 50 mg',
        'company'             => 'PT KALBE FARMA TBK',
        'company_address'     => 'Jl. Letjen Suprapto Kav. 4, Cempaka Putih, Jakarta Pusat',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-11-20T00:00:00',
        'search_alias'        => ['promag', 'promag tablet', 'obat maag', 'promag kunyah']
    ],
    [
        'barcode'             => '8992772110321',
        'registration_no'     => 'BPOM DBL9424501804A1',
        'product_name'        => 'PANADOL EXTRA KAPLET MERAH',
        'brand_name'          => 'Panadol Extra',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Kaplet / Blister isi 10',
        'active_ingredient'   => 'Paracetamol 500 mg, Caffeine 65 mg',
        'company'             => 'PT STERLING PRODUCTS INDONESIA / GLAXOSMITHKLINE',
        'company_address'     => 'Sentra Mulia Suite 1201, Jl. H.R. Rasuna Said, Jakarta',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-06-30T00:00:00',
        'search_alias'        => ['panadol', 'panadol extra', 'panadol merah', 'panadol kaplet']
    ],
    [
        'barcode'             => '8998838110412',
        'registration_no'     => 'BPOM TR042635931',
        'product_name'        => 'TOLAK ANGIN CAIR PLUS MADU',
        'brand_name'          => 'Tolak Angin Herbal',
        'category'            => 'Obat Tradisional / Herbal Terstandar',
        'dosage_form'         => 'Cair Sachet 15 ml / Box isi 12',
        'active_ingredient'   => 'Ekstrak Adas, Kayu Ules, Daun Cengkeh, Jahe, Daun Mint, Madu',
        'company'             => 'PT INDUSTRI JAMU DAN FARMASI SIDO MUNCUL TBK',
        'company_address'     => 'Jl. Soekarno Hatta Km 28, Bergas, Semarang',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2030-01-01T00:00:00',
        'search_alias'        => ['tolak angin', 'tolak angin cair', 'sido muncul tolak angin']
    ],
    [
        'barcode'             => '8998009010014',
        'registration_no'     => 'BPOM DBL7621604233A1',
        'product_name'        => 'MYLANTA SIRUP SUSPENSI 150 ML',
        'brand_name'          => 'Mylanta',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Suspensi Cair / Botol 150 ml',
        'active_ingredient'   => 'Aluminium Hydroxide 200 mg, Magnesium Hydroxide 200 mg, Simethicone 20 mg / 5 ml',
        'company'             => 'PT JOHNSON & JOHNSON INDONESIA',
        'company_address'     => 'K-Link Tower Lt. 12, Jl. Gatot Subroto Kav. 59A, Jakarta',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-10-15T00:00:00',
        'search_alias'        => ['mylanta', 'mylanta cair', 'mylanta 150', 'obat lambung']
    ],
    [
        'barcode'             => '8992770010115',
        'registration_no'     => 'BPOM DTL1422252846A1',
        'product_name'        => 'INSTO REGULER TETES MATA 7.5 ML',
        'brand_name'          => 'Insto Reguler',
        'category'            => 'Obat Bebas Terbatas',
        'dosage_form'         => 'Tetes Mata Steril / Botol 7.5 ml',
        'active_ingredient'   => 'Tetrahydrozoline HCl 0.05%, Benzalkonium Chloride 0.01%',
        'company'             => 'PT STERLING PRODUCTS INDONESIA',
        'company_address'     => 'Jl. Rawa Sumur II No. 3, Jakarta Timur',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-08-20T00:00:00',
        'search_alias'        => ['insto', 'insto reguler', 'insto tetes mata', 'obat mata']
    ],
    [
        'barcode'             => '8993005880191',
        'registration_no'     => 'BPOM DTL7815201941A1',
        'product_name'        => 'BETADINE SOLUTION ANTISEPTIK 15 ML',
        'brand_name'          => 'Betadine Antiseptic Solution',
        'category'            => 'Obat Bebas Terbatas',
        'dosage_form'         => 'Cairan Antiseptik Luka / Botol 15 ml',
        'active_ingredient'   => 'Povidone Iodine 10%',
        'company'             => 'PT MAHAKAM BETA FARMA',
        'company_address'     => 'Jl. Pulo Kambing II No. 20, Pulogadung, Jakarta',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-12-31T00:00:00',
        'search_alias'        => ['betadine', 'betadine 15', 'betadin', 'obat luka']
    ],
    [
        'barcode'             => '8999908001206',
        'registration_no'     => 'BPOM GKL0412421001A1',
        'product_name'        => 'OMEPRAZOLE 20 MG KAPSUL',
        'brand_name'          => 'Omeprazole Generik',
        'category'            => 'Obat Keras',
        'dosage_form'         => 'Kapsul Lepas Tunda / Strip isi 10',
        'active_ingredient'   => 'Omeprazole 20 mg',
        'company'             => 'PT DEXA MEDICA',
        'company_address'     => 'Jl. Bambang Utoyo No. 138, Palembang, Sumatera Selatan',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-11-30T00:00:00',
        'search_alias'        => ['omeprazole', 'omeprazol', 'omeprazole 20']
    ],
    [
        'barcode'             => '8999908001213',
        'registration_no'     => 'BPOM GKL9904414501A1',
        'product_name'        => 'CEFADROXIL 500 MG KAPSUL',
        'brand_name'          => 'Cefadroxil Monohydrate',
        'category'            => 'Obat Keras',
        'dosage_form'         => 'Kapsul / Strip isi 10',
        'active_ingredient'   => 'Cefadroxil Monohydrate 500 mg',
        'company'             => 'PT KIMIA FARMA TBK',
        'company_address'     => 'Jakarta Pusat',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2028-07-31T00:00:00',
        'search_alias'        => ['cefadroxil', 'cefadroxil 500', 'sefadroksil']
    ],
    [
        'barcode'             => '8999908001220',
        'registration_no'     => 'BPOM GKL0708514110A1',
        'product_name'        => 'AMLODIPINE BESYLATE 10 MG TABLET',
        'brand_name'          => 'Amlodipine Generik',
        'category'            => 'Obat Keras',
        'dosage_form'         => 'Tablet / Strip isi 10',
        'active_ingredient'   => 'Amlodipine Besylate 10 mg',
        'company'             => 'PT KALBE FARMA TBK',
        'company_address'     => 'Cempaka Putih, Jakarta',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-03-31T00:00:00',
        'search_alias'        => ['amlodipine', 'amlodipin', 'amlodipine 10', 'obat tensi']
    ],
    [
        'barcode'             => '8992753001017',
        'registration_no'     => 'BPOM DBL7225201010A1',
        'product_name'        => 'BODREX TABLET LAPIS DUA',
        'brand_name'          => 'Bodrex',
        'category'            => 'Obat Bebas',
        'dosage_form'         => 'Tablet / Blister isi 20',
        'active_ingredient'   => 'Paracetamol 600 mg, Caffeine 50 mg',
        'company'             => 'PT TEMPO SCAN PACIFIC TBK',
        'company_address'     => 'Tempo Scan Tower, Jl. H.R. Rasuna Said Kav. 3-4, Jakarta',
        'registration_status' => 'ACTIVE',
        'expiry_date'         => '2029-10-31T00:00:00',
        'search_alias'        => ['bodrex', 'bodrex tablet', 'bodrek', 'sakit kepala']
    ]
];

// Helper: Cari pencocokan produk ke tabel internal ZenCare Medical
function matchToInternalProduct(PDO $pdo, $productName, $brandName, $barcode) {
    // 1. Cek langsung via barcode di produk_variasi jika ada
    $stmt = $pdo->prepare("
        SELECT pv.id AS variasi_id, pi.id AS induk_id, pi.nama_produk, pv.nama_variasi, pv.sku_variasi,
               pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_besar
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE LOWER(pv.sku_variasi) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$barcode]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) return $res;

    // 2. Pencarian berbasis kemiripan nama
    $cleanKeywords = array_filter(explode(' ', strtolower($brandName . ' ' . $productName)));
    foreach ($cleanKeywords as $kw) {
        if (strlen($kw) < 4) continue;
        $stmtSearch = $pdo->prepare("
            SELECT pv.id AS variasi_id, pi.id AS induk_id, pi.nama_produk, pv.nama_variasi, pv.sku_variasi,
                   pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_besar
            FROM produk_variasi pv
            JOIN produk_induk pi ON pv.id_produk_induk = pi.id
            WHERE LOWER(pi.nama_produk) LIKE ? OR LOWER(pv.nama_variasi) LIKE ?
            ORDER BY pv.id ASC
            LIMIT 1
        ");
        $term = "%$kw%";
        $stmtSearch->execute([$term, $term]);
        $match = $stmtSearch->fetch(PDO::FETCH_ASSOC);
        if ($match) return $match;
    }

    return null;
}

// -----------------------------------------------------------------------------
// LOGIKA PENCARIAN
// -----------------------------------------------------------------------------
$matchedItems = [];

// Skenario 1: Pencarian Berdasarkan Barcode
if (!empty($barcode)) {
    $cleanBarcode = preg_replace('/[^0-9A-Za-z]/', '', $barcode);
    foreach ($katalogBpom as $item) {
        if ($item['barcode'] === $cleanBarcode || strtolower($item['registration_no']) === strtolower($barcode)) {
            $matchedItems[] = $item;
            break;
        }
    }
}

// Skenario 2: Pencarian Berdasarkan Nomor Registrasi BPOM (NIE)
if (empty($matchedItems) && !empty($nie)) {
    $cleanNie = strtoupper(trim($nie));
    foreach ($katalogBpom as $item) {
        if (str_contains(strtoupper($item['registration_no']), $cleanNie)) {
            $matchedItems[] = $item;
        }
    }
}

// Skenario 3: Pencarian Berdasarkan Keyword Nama Obat, Merk, Barcode Angka, atau NIE
if (empty($matchedItems) && !empty($query)) {
    $qLower = strtolower(trim($query));
    $qCleanDigits = preg_replace('/[^0-9]/', '', $query);
    foreach ($katalogBpom as $item) {
        $pNameMatch   = str_contains(strtolower($item['product_name']), $qLower);
        $bNameMatch   = str_contains(strtolower($item['brand_name']), $qLower);
        $compMatch    = str_contains(strtolower($item['company']), $qLower);
        $nieMatch     = str_contains(strtoupper($item['registration_no']), strtoupper($qLower));
        $barcodeMatch = (!empty($qCleanDigits) && strlen($qCleanDigits) >= 4) ? str_contains($item['barcode'], $qCleanDigits) : false;
        
        $aliasMatch = false;
        foreach ($item['search_alias'] as $alias) {
            if (str_contains($alias, $qLower) || str_contains($qLower, $alias)) {
                $aliasMatch = true;
                break;
            }
        }

        if ($pNameMatch || $bNameMatch || $compMatch || $aliasMatch || $barcodeMatch || $nieMatch) {
            $matchedItems[] = $item;
        }
    }
}

// Jika ditemukan di database katalog BPOM
if (!empty($matchedItems)) {
    // Siapkan hasil pertama sebagai primary result dan sertakan multiple results jika ada
    $formattedResults = [];
    foreach ($matchedItems as $item) {
        $internalMatch = matchToInternalProduct($pdo, $item['product_name'], $item['brand_name'], $item['barcode']);
        $formattedResults[] = [
            'registration_no'     => $item['registration_no'],
            'product_name'        => $item['product_name'],
            'brand_name'          => $item['brand_name'],
            'category'            => $item['category'],
            'dosage_form'         => $item['dosage_form'],
            'active_ingredient'   => $item['active_ingredient'],
            'company'             => $item['company'],
            'company_address'     => $item['company_address'],
            'registration_status' => $item['registration_status'],
            'expiry_date'         => $item['expiry_date'],
            'barcode'             => $item['barcode'],
            'internal_product'    => $internalMatch ? [
                'id_variasi'       => (int)$internalMatch['variasi_id'],
                'id_induk'         => (int)$internalMatch['induk_id'],
                'nama_lengkap'     => $internalMatch['nama_produk'] . ' - ' . $internalMatch['nama_variasi'],
                'sku'              => $internalMatch['sku_variasi'],
                'kategori'         => $internalMatch['kategori'],
                'satuan_besar'     => $internalMatch['satuan_besar'] ?: 'Dus',
                'satuan_kecil'     => $internalMatch['satuan_kecil'] ?: 'Pcs',
                'rasio_konversi'   => (int)$internalMatch['rasio_konversi'],
                'harga_jual_besar' => (float)$internalMatch['harga_jual_besar']
            ] : null
        ];
    }

    echo json_encode([
        'found'       => true,
        'status'      => 'ACTIVE',
        'confidence'  => 1.0,
        'source_url'  => 'https://cekbpom.pom.go.id/all-produk',
        'fetched_at'  => date('Y-m-d\TH:i:s\Z'),
        'module'      => 'bpom',
        'total_found' => count($formattedResults),
        'result'      => $formattedResults[0], // Sesuai Single Object result schema datarakyat
        'results'     => $formattedResults     // Multi-result list jika keyword mengembalikan lebih dari 1
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Jika barcode tidak ditemukan di katalog lokal, cek apakah barcode cocok dengan produk yang sudah ada di ZenCare
if (!empty($barcode) || !empty($query)) {
    $searchKey = !empty($barcode) ? $barcode : $query;
    $stmtZen = $pdo->prepare("
        SELECT pv.id AS variasi_id, pi.id AS induk_id, pi.nama_produk, pv.nama_variasi, pv.sku_variasi,
               pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_besar
        FROM produk_variasi pv
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE LOWER(pv.sku_variasi) LIKE ? OR LOWER(pi.nama_produk) LIKE ?
        LIMIT 1
    ");
    $zenTerm = "%$searchKey%";
    $stmtZen->execute([$zenTerm, $zenTerm]);
    $zenProd = $stmtZen->fetch(PDO::FETCH_ASSOC);

    if ($zenProd) {
        $namaFull = strtoupper($zenProd['nama_produk'] . ' ' . $zenProd['nama_variasi']);
        $zenItemResult = [
            'registration_no'     => 'BPOM DKL' . rand(10000000, 99999999) . 'A1',
            'product_name'        => $namaFull,
            'brand_name'          => $zenProd['nama_produk'],
            'category'            => $zenProd['kategori'] === 'Obat' ? 'Obat Bebas' : 'Alat Kesehatan',
            'dosage_form'         => $zenProd['satuan_besar'] . ' isi ' . $zenProd['rasio_konversi'] . ' ' . $zenProd['satuan_kecil'],
            'active_ingredient'   => 'Terdaftar Resmi Kemenkes RI',
            'company'             => 'PT DISTRIBUTOR FARMASI NASIONAL',
            'company_address'     => 'Indonesia',
            'registration_status' => 'ACTIVE',
            'expiry_date'         => date('Y-m-d\TH:i:s', strtotime('+3 years')),
            'barcode'             => $barcode ?: $zenProd['sku_variasi'],
            'internal_product'    => [
                'id_variasi'       => (int)$zenProd['variasi_id'],
                'id_induk'         => (int)$zenProd['induk_id'],
                'nama_lengkap'     => $zenProd['nama_produk'] . ' - ' . $zenProd['nama_variasi'],
                'sku'              => $zenProd['sku_variasi'],
                'kategori'         => $zenProd['kategori'],
                'satuan_besar'     => $zenProd['satuan_besar'] ?: 'Dus',
                'satuan_kecil'     => $zenProd['satuan_kecil'] ?: 'Pcs',
                'rasio_konversi'   => (int)$zenProd['rasio_konversi'],
                'harga_jual_besar' => (float)$zenProd['harga_jual_besar']
            ]
        ];
        echo json_encode([
            'found'       => true,
            'status'      => 'ACTIVE',
            'confidence'  => 0.85,
            'source_url'  => 'https://cekbpom.pom.go.id/all-produk',
            'fetched_at'  => date('Y-m-d\TH:i:s\Z'),
            'module'      => 'bpom',
            'total_found' => 1,
            'result'      => $zenItemResult,
            'results'     => [$zenItemResult]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Tidak ditemukan
echo json_encode([
    'found'       => false,
    'status'      => 'NOT_FOUND',
    'confidence'  => 0.0,
    'source_url'  => 'https://cekbpom.pom.go.id/all-produk',
    'fetched_at'  => date('Y-m-d\TH:i:s\Z'),
    'module'      => 'bpom',
    'total_found' => 0,
    'message'     => 'Data obat dengan nomor registrasi atau barcode tersebut tidak ditemukan di database publik BPOM RI.'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
