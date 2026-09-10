<?php
// File: api/checkout.php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST method required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

$namaPembeli    = $input['nama_pembeli'] ?? '';
$phone          = $input['phone'] ?? '';
$alamatLengkap  = $input['alamat_lengkap'] ?? '';
$provinsi       = $input['provinsi'] ?? '';
$kota           = $input['kota'] ?? '';
$kecamatan      = $input['kecamatan'] ?? '';
$lat            = isset($input['lat']) ? floatval($input['lat']) : null;
$lng            = isset($input['lng']) ? floatval($input['lng']) : null;
$cart           = $input['cart'] ?? []; 
$kurir          = $input['kurir'] ?? ''; 
$layanan        = $input['layanan'] ?? ''; 
$idCabang       = 1; // Default ke Muharto untuk e-commerce

if (empty($namaPembeli) || empty($phone) || empty($alamatLengkap) || empty($cart) || empty($kurir) || empty($layanan)) {
    echo json_encode(['status' => 'error', 'message' => 'Lengkapi semua data penerima, keranjang, dan kurir!']);
    exit;
}

try {
    $pdo = DB::connect();
    $pdo->beginTransaction();

    $itemDetails = [];
    $totalHargaBarang = 0;

    // 1. Validasi Stok dari stok_toko
    foreach ($cart as $cartItem) {
        $idVariasi = intval($cartItem['id']);
        $qty = intval($cartItem['qty']);

        if ($qty <= 0) continue;

        $stmt = $pdo->prepare("SELECT pv.id, pv.nama_variasi, pv.harga_jual_kecil AS harga, pv.harga_jual_besar AS harga_grosir, pi.nama_produk, sc.stok, pv.rasio_konversi
                               FROM produk_variasi pv 
                               JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
                               LEFT JOIN stok_toko sc ON sc.id_variasi = pv.id 
                               WHERE pv.id = ? FOR UPDATE");
        $stmt->execute([$idCabang, $idVariasi]);
        $produk = $stmt->fetch();

        if (!$produk) {
            throw new Exception("Variasi produk ID $idVariasi tidak ditemukan.");
        }
        
        $stokAktif = intval($produk['stok'] ?? 0);
        $rasio = intval($produk['rasio_konversi']) ?: 1;
        $hargaGrosir = floatval($produk['harga_grosir'] ?: $produk['harga']);

        // Check stock considering conversion ratio since e-commerce buys 'Box'
        if ($stokAktif < ($qty * $rasio)) {
            throw new Exception("Stok '{$produk['nama_produk']} - {$produk['nama_variasi']}' tidak cukup!");
        }

        $subtotal = $hargaGrosir * $qty;
        $totalHargaBarang += $subtotal;

        $itemDetails[] = [
            'id' => (string)$produk['id'],
            'price' => (int)$hargaGrosir,
            'quantity' => $qty,
            'name' => substr($produk['nama_produk'], 0, 50)
        ];
    }

    if (empty($itemDetails)) {
        throw new Exception("Keranjang belanja kosong!");
    }

    // 2. Ongkir (Terima dari frontend, divalidasi minimalnya)
    $ongkir = intval($input['ongkir'] ?? 0);
    $grandTotal = $totalHargaBarang + $ongkir;

    if ($ongkir > 0) {
        $itemDetails[] = [
            'id' => 'SHIPPING_FEE',
            'price' => $ongkir,
            'quantity' => 1,
            'name' => substr('Ongkir: ' . strtoupper($kurir) . ' - ' . $layanan, 0, 50)
        ];
    }

    // 3. Simpan ke penjualan
    session_start();
    $idUser = $_SESSION['user_id'] ?? null;
    $orderId = "WEB-" . date('Ymd') . "-" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

    if ($idUser && !empty($input['save_profile'])) {
        $kotaId = intval($input['kota_id'] ?? 0);
        $pdo->prepare("UPDATE users SET telepon=?, alamat=?, kota_id=?, lat=?, lng=? WHERE id=?")
            ->execute([$phone, $alamatLengkap, $kotaId ?: null, $lat, $lng, $idUser]);
    }

    $stmtOrder = $pdo->prepare("INSERT INTO penjualan 
        (no_invoice, id_cabang, id_user, tipe_transaksi, status_pesanan, total_harga, ongkir, nama_penerima, telepon, alamat_lengkap, kurir, layanan) 
        VALUES (?, ?, ?, 'ecommerce', 'Menunggu', ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtOrder->execute([
        $orderId, $idCabang, $idUser, $grandTotal, $ongkir, $namaPembeli, $phone, $alamatLengkap, $kurir, $layanan
    ]);
    $idPenjualan = $pdo->lastInsertId();

    // Simpan Detail & Potong Stok dengan pencatatan Kartu Stok
    $stmtDetail = $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan) VALUES (?, ?, ?, ?)");
    foreach ($cart as $cartItem) {
        $idVariasi = intval($cartItem['id']);
        $qty = intval($cartItem['qty']);
        
        $stmtP = $pdo->prepare("SELECT pv.harga_jual_besar, pv.rasio_konversi, pi.kategori 
                                FROM produk_variasi pv 
                                JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
                                WHERE pv.id = ?");
        $stmtP->execute([$idVariasi]);
        $rowP = $stmtP->fetch();
        $harga = $rowP['harga_jual_besar'] ?? 0;
        $rasio = max(1, intval($rowP['rasio_konversi'] ?: 1));
        $kategori = $rowP['kategori'] ?? '';

        $stmtDetail->execute([$idPenjualan, $idVariasi, $qty, $harga]);

        // Potong stok fisik cabang (satuan kecil = qty Box * rasio)
        $qtyPotong = $qty * $rasio;
        
        // FEFO Logic untuk Obat
        if ($kategori === 'Obat') {
            $stmtBatch = $pdo->prepare("SELECT id, stok FROM stok_batch WHERE id_variasi = ? AND 1=1 AND stok > 0 AND is_active = 1 ORDER BY tgl_exp ASC FOR UPDATE");
            $stmtBatch->execute([$idVariasi, $idCabang]);
            $batches = $stmtBatch->fetchAll();
            
            $sisaPotong = $qtyPotong;
            foreach ($batches as $b) {
                if ($sisaPotong <= 0) break;
                
                $potongBatch = min($b['stok'], $sisaPotong);
                $pdo->prepare("UPDATE stok_batch SET stok = stok - ? WHERE id = ?")->execute([$potongBatch, $b['id']]);
                
                $sisaPotong -= $potongBatch;
            }
            if ($sisaPotong > 0) {
                throw new Exception("Stok Batch Obat tidak mencukupi untuk dipotong FEFO secara berurutan.");
            }
        }

        $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ? AND 1=1")
            ->execute([$qtyPotong, $idVariasi, $idCabang]);

        // Saldo akhir fisik
        $sisaQ = $pdo->prepare("SELECT stok FROM stok_toko WHERE id_variasi = ? AND 1=1");
        $sisaQ->execute([$idVariasi, $idCabang]);
        $sisa = $sisaQ->fetchColumn();

        // Catat ke kartu_stok dengan referensi nomor invoice WEB-
        $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Keluar', ?, ?, ?)")
            ->execute([$idCabang, $idVariasi, $qtyPotong, $sisa, $orderId]);
    }

    // 4. Request Midtrans Snap
    $midtransPayload = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => (int)$grandTotal,
        ],
        'customer_details' => [
            'first_name' => $namaPembeli,
            'phone' => $phone,
        ],
        'item_details' => $itemDetails,
    ];

    $ch = curl_init(MIDTRANS_API_URL . '/snap/v1/transactions');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($midtransPayload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
    ]);
    
    // Disable SSL verification for local sandbox only
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $midtransResponse = curl_exec($ch);
    $midtransError = curl_error($ch);
    curl_close($ch);

    if ($midtransError) {
        throw new Exception("Gagal terhubung ke Midtrans: " . $midtransError);
    }

    $midtransData = json_decode($midtransResponse, true);
    if (!isset($midtransData['token'])) {
        throw new Exception("Error dari Midtrans: " . json_encode($midtransData));
    }

    $snapToken = $midtransData['token'];

    // Update snap token
    $stmtUp = $pdo->prepare("UPDATE penjualan SET snap_token = ? WHERE no_invoice = ?");
    $stmtUp->execute([$snapToken, $orderId]);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'token' => $snapToken,
        'order_id' => $orderId,
        'grand_total' => $grandTotal
    ]);

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
