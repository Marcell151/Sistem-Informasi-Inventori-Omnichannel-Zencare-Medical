<?php
// File: api/check_sn.php
// Endpoint pemeriksa status & garansi Serial Number (SN) untuk Kasir POS & Admin
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

$snQuery = trim($_GET['sn'] ?? '');

if (empty($snQuery)) {
    echo json_encode(['found' => false, 'message' => 'Serial Number wajib diisi.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT us.*, 
               pi.nama_produk, pi.kategori,
               pv.nama_variasi, pv.sku_variasi,
               pj.no_invoice, pj.tipe_transaksi, pj.created_at AS tgl_terjual, pj.nama_penerima AS nama_pembeli,
               pj.total_harga
        FROM unit_serial us
        JOIN produk_variasi pv ON pv.id = us.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN penjualan pj ON pj.id = us.id_penjualan
        WHERE LOWER(us.serial_number) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$snQuery]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            'found' => false,
            'sn' => $snQuery,
            'message' => 'Serial Number tidak terdaftar di sistem ZenCare Medical. Unit ini bukan berasal dari toko ini.'
        ]);
        exit;
    }

    $status = $row['status']; // 'Tersedia', 'Terjual', 'Retur/Rusak'
    $tglTerjual = $row['tgl_terjual'];
    $durasiGaransiTokoHari = 30; // Garansi Toko 30 Hari

    $garansi = [
        'status' => 'BELUM_BERJALAN',
        'badge' => 'Belum Berjalan',
        'badge_cls' => 'bg-blue-100 text-blue-800 border-blue-200',
        'durasi_hari' => $durasiGaransiTokoHari,
        'hari_berlalu' => 0,
        'sisa_hari' => $durasiGaransiTokoHari,
        'tgl_berakhir' => null,
        'is_aktif' => false,
        'deskripsi' => 'Unit berada di gudang / etalase (Belum Terjual). Argo garansi toko belum berjalan dan akan aktif otomatis saat transaksi kasir.'
    ];

    if ($status === 'Terjual' && $tglTerjual) {
        $timeTerjual = strtotime($tglTerjual);
        $timeBerakhir = strtotime("+{$durasiGaransiTokoHari} days", $timeTerjual);
        $hariBerlalu = (int)floor((time() - $timeTerjual) / 86400);
        $sisaHari = $durasiGaransiTokoHari - $hariBerlalu;

        if ($sisaHari >= 0) {
            $garansi = [
                'status' => 'AKTIF',
                'badge' => "Garansi Aktif (Sisa {$sisaHari} Hari)",
                'badge_cls' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'durasi_hari' => $durasiGaransiTokoHari,
                'hari_berlalu' => $hariBerlalu,
                'sisa_hari' => $sisaHari,
                'tgl_berakhir' => date('d M Y', $timeBerakhir),
                'is_aktif' => true,
                'deskripsi' => "Garansi toko aktif hingga " . date('d F Y', $timeBerakhir) . ". Konsumen berhak klaim perbaikan / tukar baru jika ada cacat mesin."
            ];
        } else {
            $lewatHari = abs($sisaHari);
            $garansi = [
                'status' => 'HABIS',
                'badge' => "Garansi Toko Habis ({$lewatHari} Hari Lalu)",
                'badge_cls' => 'bg-slate-100 text-slate-600 border-slate-300',
                'durasi_hari' => $durasiGaransiTokoHari,
                'hari_berlalu' => $hariBerlalu,
                'sisa_hari' => $sisaHari,
                'tgl_berakhir' => date('d M Y', $timeBerakhir),
                'is_aktif' => false,
                'deskripsi' => "Masa garansi toko 30 hari telah berakhir pada " . date('d F Y', $timeBerakhir) . ". Arahkan konsumen ke Service Center resmi pabrikan untuk klaim garansi servis lanjutan."
            ];
        }
    } elseif ($status === 'Retur/Rusak') {
        $garansi = [
            'status' => 'RETUR',
            'badge' => 'Retur / Karantina',
            'badge_cls' => 'bg-rose-100 text-rose-800 border-rose-300',
            'durasi_hari' => $durasiGaransiTokoHari,
            'hari_berlalu' => 0,
            'sisa_hari' => 0,
            'tgl_berakhir' => null,
            'is_aktif' => false,
            'deskripsi' => 'Unit telah ditarik ke Gudang Karantina karena cacat fisik atau telah diretur sebelumnya.'
        ];
    }

    echo json_encode([
        'found' => true,
        'sn' => $row['serial_number'],
        'nama_produk' => $row['nama_produk'],
        'nama_variasi' => $row['nama_variasi'],
        'sku_variasi' => $row['sku_variasi'],
        'kategori' => $row['kategori'],
        'status' => $status,
        'tgl_terdaftar' => date('d M Y', strtotime($row['created_at'])),
        'transaksi' => [
            'no_invoice' => $row['no_invoice'] ?? null,
            'tipe' => $row['tipe_transaksi'] ? strtoupper($row['tipe_transaksi']) : null,
            'tgl_terjual' => $tglTerjual ? date('d M Y H:i', strtotime($tglTerjual)) : null,
            'nama_pembeli' => $row['nama_pembeli'] ?? 'Pelanggan Umum'
        ],
        'garansi' => $garansi
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['found' => false, 'error' => $e->getMessage()]);
}
