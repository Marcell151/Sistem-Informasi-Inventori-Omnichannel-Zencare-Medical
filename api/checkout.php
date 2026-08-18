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

    // 1. Validasi Stok dari stok_cabang
    foreach ($cart as $cartItem) {
        $idVariasi = intval($cartItem['id']);
        $qty = intval($cartItem['qty']);

        if ($qty <= 0) continue;

        $stmt = $pdo->prepare("SELECT pv.id, pv.nama_variasi, pv.harga, pi.nama_produk, sc.stok 
                               FROM produk_variasi pv 
                               JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
                               LEFT JOIN stok_cabang sc ON sc.id_variasi = pv.id AND sc.id_cabang = ?
                               WHERE pv.id = ? FOR UPDATE");
        $stmt->execute([$idCabang, $idVariasi]);
        $produk = $stmt->fetch();

        if (!$produk) {
            throw new Exception("Variasi produk ID $idVariasi tidak ditemukan.");
        }
        
        $stokAktif = intval($produk['stok'] ?? 0);

        if ($stokAktif < $qty) {
            throw new Exception("Stok '{$produk['nama_produk']} - {$produk['nama_variasi']}' tidak cukup! Sisa: $stokAktif");
        }

        $subtotal = $produk['harga'] * $qty;
        $totalHargaBarang += $subtotal;

        $itemDetails[] = [
            'id' => (string)$produk['id'],
            'price' => (int)$produk['harga'],
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
    $orderId = "ZNC-" . time() . "-" . rand(10, 99);

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

    // Simpan Detail
    $stmtDetail = $pdo->prepare("INSERT INTO detail_penjualan (id_penjualan, id_variasi, qty, harga_satuan) VALUES (?, ?, ?, ?)");
    foreach ($cart as $cartItem) {
        $idVariasi = intval($cartItem['id']);
        $qty = intval($cartItem['qty']);
        
        $stmtP = $pdo->prepare("SELECT harga FROM produk_variasi WHERE id = ?");
        $stmtP->execute([$idVariasi]);
        $harga = $stmtP->fetchColumn();

        $stmtDetail->execute([$idPenjualan, $idVariasi, $qty, $harga]);
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
