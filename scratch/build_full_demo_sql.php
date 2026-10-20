<?php
/**
 * build_full_demo_sql.php
 * Generates database/isi/db_inventory (demo).sql
 */

require_once __DIR__ . '/../config/koneksi.php';

$outputFile = __DIR__ . '/../database/isi/db_inventory (demo).sql';

echo "Building database/isi/db_inventory (demo).sql...\n";

function sqlVal($val) {
    if ($val === null) return 'NULL';
    if (is_int($val)) return (string)$val;
    if (is_float($val)) return sprintf('%.2f', $val);
    return "'" . addslashes((string)$val) . "'";
}

function insertSql($table, $rows) {
    if (empty($rows)) return "";
    $cols = array_keys($rows[0]);
    $colsStr = "`" . implode("`, `", $cols) . "`";
    $sql = "--\n-- Dumping data untuk tabel `$table`\n--\n\n";
    $sql .= "INSERT INTO `$table` ($colsStr) VALUES\n";
    $valLines = [];
    foreach ($rows as $r) {
        $vals = array_map(function($v) {
            return sqlVal($v);
        }, array_values($r));
        $valLines[] = "(" . implode(", ", $vals) . ")";
    }
    $sql .= implode(",\n", $valLines) . ";\n\n";
    return $sql;
}

// 1. Users
$users = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// 2. Suppliers
$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// 3. Pengaturan Web
$pengaturan = $pdo->query("SELECT * FROM pengaturan_web ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// 4. Produk Induk (Exclude dummy test 54, 56)
$produkInduk = $pdo->query("SELECT * FROM produk_induk WHERE id NOT IN (54, 56) ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// 5. Produk Variasi (Exclude dummy test 59)
$produkVariasi = $pdo->query("
    SELECT pv.*, pi.kategori, pi.nama_produk 
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
    WHERE pv.id != 59 
    ORDER BY pv.id
")->fetchAll(PDO::FETCH_ASSOC);

// 6. Penerimaan Stok & Penerimaan Detail
$penerimaanStok = [
    [
        'id' => 1,
        'no_referensi' => 'PO-20260915-01',
        'sumber' => 'PO',
        'id_supplier' => 1,
        'tanggal_terima' => '2026-09-15',
        'catatan' => 'Penerimaan stok awal obat dan multivitamin dari PT Kimia Farma Trading',
        'file_nota' => null,
        'dibuat_oleh' => 2,
        'created_at' => '2026-09-15 08:30:00'
    ],
    [
        'id' => 2,
        'no_referensi' => 'PO-20260925-02',
        'sumber' => 'PO',
        'id_supplier' => 2,
        'tanggal_terima' => '2026-09-25',
        'catatan' => 'Penerimaan restok sirup obat anak dan alat kesehatan dari PT Enseval',
        'file_nota' => null,
        'dibuat_oleh' => 2,
        'created_at' => '2026-09-25 09:15:00'
    ],
    [
        'id' => 3,
        'no_referensi' => 'PO-20261005-03',
        'sumber' => 'PO',
        'id_supplier' => 3,
        'tanggal_terima' => '2026-10-05',
        'catatan' => 'Penerimaan suplemen daya tahan tubuh dan peralatan diagnostik dari PT Kalbe Farma',
        'file_nota' => null,
        'dibuat_oleh' => 2,
        'created_at' => '2026-10-05 10:00:00'
    ]
];

$penerimaanDetail = [
    [
        'id' => 1,
        'id_penerimaan' => 1,
        'id_variasi' => 1,
        'qty_terima' => 15,
        'harga_beli' => 40000.00,
        'no_batch' => 'AMX-2026-01',
        'tgl_exp' => '2027-12-15'
    ],
    [
        'id' => 2,
        'id_penerimaan' => 1,
        'id_variasi' => 10,
        'qty_terima' => 25,
        'harga_beli' => 25000.00,
        'no_batch' => 'PCT-500-26',
        'tgl_exp' => '2027-11-20'
    ],
    [
        'id' => 3,
        'id_penerimaan' => 2,
        'id_variasi' => 2,
        'qty_terima' => 12,
        'harga_beli' => 300000.00,
        'no_batch' => 'SNM-2026-A',
        'tgl_exp' => '2028-05-10'
    ],
    [
        'id' => 4,
        'id_penerimaan' => 2,
        'id_variasi' => 6,
        'qty_terima' => 10,
        'harga_beli' => 750000.00,
        'no_batch' => 'HUF-TMP-27',
        'tgl_exp' => '2028-06-20'
    ],
    [
        'id' => 5,
        'id_penerimaan' => 3,
        'id_variasi' => 30,
        'qty_terima' => 20,
        'harga_beli' => 210000.00,
        'no_batch' => 'IMB-FRC-26',
        'tgl_exp' => '2027-09-15'
    ]
];

// 7. Penjualan & Detail Penjualan (6 Transaksi)
$penjualan = [
    [
        'id' => 1,
        'no_invoice' => 'POS-20261014-1001',
        'id_user' => 4,
        'tipe_transaksi' => 'pos',
        'metode_pengambilan' => 'Pick-up',
        'status_pesanan' => 'Selesai',
        'total_harga' => 95000.00,
        'metode_bayar_pos' => 'Tunai',
        'ongkir' => 0.00,
        'nama_penerima' => 'Pelanggan Langsung (Apotek)',
        'telepon_penerima' => '081299887766',
        'alamat_lengkap' => 'ZenCare Toko Fisik (Muharto)',
        'kota_tujuan' => 'KOTA MALANG',
        'kurir' => null,
        'layanan' => null,
        'snap_token' => null,
        'payment_method_ecommerce' => null,
        'paid_at' => '2026-10-14 10:15:20',
        'kode_pickup' => null,
        'created_at' => '2026-10-14 10:15:20',
        'updated_at' => '2026-10-14 10:15:20'
    ],
    [
        'id' => 2,
        'no_invoice' => 'POS-20261016-1002',
        'id_user' => 4,
        'tipe_transaksi' => 'pos',
        'metode_pengambilan' => 'Pick-up',
        'status_pesanan' => 'Selesai',
        'total_harga' => 17500.00,
        'metode_bayar_pos' => 'QRIS',
        'ongkir' => 0.00,
        'nama_penerima' => 'Pelanggan Langsung (Apotek)',
        'telepon_penerima' => '081344556677',
        'alamat_lengkap' => 'ZenCare Toko Fisik (Muharto)',
        'kota_tujuan' => 'KOTA MALANG',
        'kurir' => null,
        'layanan' => null,
        'snap_token' => null,
        'payment_method_ecommerce' => null,
        'paid_at' => '2026-10-16 14:22:10',
        'kode_pickup' => null,
        'created_at' => '2026-10-16 14:22:10',
        'updated_at' => '2026-10-16 14:22:10'
    ],
    [
        'id' => 3,
        'no_invoice' => 'POS-20261017-1003',
        'id_user' => 4,
        'tipe_transaksi' => 'pos',
        'metode_pengambilan' => 'Pick-up',
        'status_pesanan' => 'Selesai',
        'total_harga' => 940000.00,
        'metode_bayar_pos' => 'Tunai',
        'ongkir' => 0.00,
        'nama_penerima' => 'Klinik Sehat Bersama (Grosir & Eceran)',
        'telepon_penerima' => '081122334455',
        'alamat_lengkap' => 'ZenCare Toko Fisik (Muharto)',
        'kota_tujuan' => 'KOTA MALANG',
        'kurir' => null,
        'layanan' => null,
        'snap_token' => null,
        'payment_method_ecommerce' => null,
        'paid_at' => '2026-10-17 11:40:05',
        'kode_pickup' => null,
        'created_at' => '2026-10-17 11:40:05',
        'updated_at' => '2026-10-17 11:40:05'
    ],
    [
        'id' => 4,
        'no_invoice' => 'INV-20261018-041',
        'id_user' => 3,
        'tipe_transaksi' => 'ecommerce',
        'metode_pengambilan' => 'Kurir',
        'status_pesanan' => 'Selesai',
        'total_harga' => 188000.00,
        'metode_bayar_pos' => 'Midtrans Payment',
        'ongkir' => 18000.00,
        'nama_penerima' => 'Budi Santoso',
        'telepon_penerima' => '083333444555',
        'alamat_lengkap' => 'Jl. Soekarno Hatta No. 12, Lowokwaru',
        'kota_tujuan' => 'KOTA MALANG',
        'kurir' => 'jne',
        'layanan' => 'REG',
        'snap_token' => 'snap-token-inv-041-midtrans',
        'payment_method_ecommerce' => 'bank_transfer',
        'paid_at' => '2026-10-18 09:35:00',
        'kode_pickup' => null,
        'created_at' => '2026-10-18 09:30:00',
        'updated_at' => '2026-10-18 11:00:00'
    ],
    [
        'id' => 5,
        'no_invoice' => 'INV-20261019-052',
        'id_user' => 3,
        'tipe_transaksi' => 'ecommerce',
        'metode_pengambilan' => 'Kurir',
        'status_pesanan' => 'Selesai',
        'total_harga' => 945000.00,
        'metode_bayar_pos' => 'Midtrans Payment',
        'ongkir' => 45000.00,
        'nama_penerima' => 'Budi Santoso',
        'telepon_penerima' => '083333444555',
        'alamat_lengkap' => 'Jl. Soekarno Hatta No. 12, Lowokwaru',
        'kota_tujuan' => 'KOTA MALANG',
        'kurir' => 'sicepat',
        'layanan' => 'REG',
        'snap_token' => 'snap-token-inv-052-midtrans',
        'payment_method_ecommerce' => 'qris',
        'paid_at' => '2026-10-19 13:25:00',
        'kode_pickup' => null,
        'created_at' => '2026-10-19 13:20:00',
        'updated_at' => '2026-10-19 15:40:00'
    ],
    [
        'id' => 6,
        'no_invoice' => 'POS-20261012-0901',
        'id_user' => 4,
        'tipe_transaksi' => 'pos',
        'metode_pengambilan' => 'Pick-up',
        'status_pesanan' => 'Selesai',
        'total_harga' => 850000.00,
        'metode_bayar_pos' => 'Tunai',
        'ongkir' => 0.00,
        'nama_penerima' => 'dr. Hendra Setiawan',
        'telepon_penerima' => '081234567890',
        'alamat_lengkap' => 'Klinik Pratama Medika',
        'kota_tujuan' => 'KOTA MALANG',
        'kurir' => null,
        'layanan' => null,
        'snap_token' => null,
        'payment_method_ecommerce' => null,
        'paid_at' => '2026-10-12 16:10:00',
        'kode_pickup' => null,
        'created_at' => '2026-10-12 16:10:00',
        'updated_at' => '2026-10-12 16:10:00'
    ]
];

$detailPenjualan = [
    [
        'id' => 1,
        'id_penjualan' => 1,
        'id_variasi' => 1,
        'qty' => 2,
        'harga_satuan' => 5000.00,
        'catatan_logistik' => 'AMX-2026-01.A (2x)'
    ],
    [
        'id' => 2,
        'id_penjualan' => 1,
        'id_variasi' => 5,
        'qty' => 1,
        'harga_satuan' => 85000.00,
        'catatan_logistik' => 'OMR-TH-01'
    ],
    [
        'id' => 3,
        'id_penjualan' => 2,
        'id_variasi' => 10,
        'qty' => 5,
        'harga_satuan' => 3500.00,
        'catatan_logistik' => 'PCT-500-26.A (3x), PCT-500-26.B (2x)'
    ],
    [
        'id' => 4,
        'id_penjualan' => 3,
        'id_variasi' => 6,
        'qty' => 5,
        'harga_satuan' => 18000.00,
        'catatan_logistik' => 'HUF-TMP-26.A (2x), HUF-TMP-27.A (3x)'
    ],
    [
        'id' => 5,
        'id_penjualan' => 3,
        'id_variasi' => 6,
        'qty' => 1,
        'harga_satuan' => 850000.00,
        'catatan_logistik' => 'HUF-TMP-27 (1 Karton = 50 Botol)'
    ],
    [
        'id' => 6,
        'id_penjualan' => 4,
        'id_variasi' => 30,
        'qty' => 2,
        'harga_satuan' => 85000.00,
        'catatan_logistik' => 'IMB-FRC-26 (2x)'
    ],
    [
        'id' => 7,
        'id_penjualan' => 5,
        'id_variasi' => 2,
        'qty' => 1,
        'harga_satuan' => 350000.00,
        'catatan_logistik' => 'SNM-2026-A (1 Karton = 24 Botol)'
    ],
    [
        'id' => 8,
        'id_penjualan' => 5,
        'id_variasi' => 4,
        'qty' => 1,
        'harga_satuan' => 550000.00,
        'catatan_logistik' => 'OMR-TENS-01'
    ],
    [
        'id' => 9,
        'id_penjualan' => 6,
        'id_variasi' => 56,
        'qty' => 1,
        'harga_satuan' => 850000.00,
        'catatan_logistik' => 'OMR-NEB-99'
    ]
];

// 8. Histori Konversi (6 Aktivitas)
$historiKonversi = [
    [
        'id' => 1,
        'id_variasi' => 1,
        'batch_asal' => 'AMX-2026-01',
        'batch_hasil' => 'AMX-2026-01.A',
        'qty_box_buka' => 1,
        'qty_pcs_hasil' => 10,
        'sisa_dus_akhir' => 1,
        'dibuat_oleh' => 4,
        'created_at' => '2026-10-05 09:15:00'
    ],
    [
        'id' => 2,
        'id_variasi' => 2,
        'batch_asal' => 'SNM-2026-A',
        'batch_hasil' => 'SNM-2026-A.A',
        'qty_box_buka' => 1,
        'qty_pcs_hasil' => 24,
        'sisa_dus_akhir' => 9,
        'dibuat_oleh' => 2,
        'created_at' => '2026-10-07 10:30:00'
    ],
    [
        'id' => 3,
        'id_variasi' => 10,
        'batch_asal' => 'PCT-500-26',
        'batch_hasil' => 'PCT-500-26.A',
        'qty_box_buka' => 1,
        'qty_pcs_hasil' => 10,
        'sisa_dus_akhir' => 19,
        'dibuat_oleh' => 4,
        'created_at' => '2026-10-08 11:00:00'
    ],
    [
        'id' => 4,
        'id_variasi' => 10,
        'batch_asal' => 'PCT-500-26',
        'batch_hasil' => 'PCT-500-26.B',
        'qty_box_buka' => 1,
        'qty_pcs_hasil' => 10,
        'sisa_dus_akhir' => 18,
        'dibuat_oleh' => 4,
        'created_at' => '2026-10-10 14:20:00'
    ],
    [
        'id' => 5,
        'id_variasi' => 6,
        'batch_asal' => 'HUF-TMP-26',
        'batch_hasil' => 'HUF-TMP-26.A',
        'qty_box_buka' => 1,
        'qty_pcs_hasil' => 50,
        'sisa_dus_akhir' => 7,
        'dibuat_oleh' => 4,
        'created_at' => '2026-10-09 13:45:00'
    ],
    [
        'id' => 6,
        'id_variasi' => 6,
        'batch_asal' => 'HUF-TMP-27',
        'batch_hasil' => 'HUF-TMP-27.A',
        'qty_box_buka' => 1,
        'qty_pcs_hasil' => 50,
        'sisa_dus_akhir' => 9,
        'dibuat_oleh' => 2,
        'created_at' => '2026-10-11 15:30:00'
    ]
];

// 9. Generate Batches & Serial Numbers
$stokBatch = [];
$unitSerial = [];
$batchIdCounter = 1;
$snIdCounter = 1;

// Define specific configurations for key products
foreach ($produkVariasi as $pv) {
    $vId = $pv['id'];
    $kategori = $pv['kategori'];
    $rasio = $pv['rasio_konversi'];
    $sku = $pv['sku_variasi'];

    if ($kategori === 'Alat Kesehatan') {
        // Generate unit_serial
        if ($vId == 4) { // Tensimeter Omron
            $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => 'OMR-TENS-01', 'status' => 'Terjual', 'id_penjualan' => 5, 'catatan' => null, 'tgl_habis_garansi' => '2028-10-19', 'created_at' => '2026-09-25 09:15:00', 'updated_at' => '2026-10-19 13:20:00'];
            for ($i = 2; $i <= 5; $i++) {
                $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => sprintf('OMR-TENS-%02d', $i), 'status' => 'Tersedia', 'id_penjualan' => null, 'catatan' => null, 'tgl_habis_garansi' => '2028-10-19', 'created_at' => '2026-09-25 09:15:00', 'updated_at' => null];
            }
        } elseif ($vId == 5) { // Termometer MC-246
            $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => 'OMR-TH-01', 'status' => 'Terjual', 'id_penjualan' => 1, 'catatan' => null, 'tgl_habis_garansi' => '2027-10-14', 'created_at' => '2026-09-15 08:30:00', 'updated_at' => '2026-10-14 10:15:20'];
            for ($i = 2; $i <= 6; $i++) {
                $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => sprintf('OMR-TH-%02d', $i), 'status' => 'Tersedia', 'id_penjualan' => null, 'catatan' => null, 'tgl_habis_garansi' => '2027-10-14', 'created_at' => '2026-09-15 08:30:00', 'updated_at' => null];
            }
        } elseif ($vId == 56) { // Nebulizer Omron
            $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => 'OMR-NEB-99', 'status' => 'Retur/Rusak', 'id_penjualan' => 6, 'catatan' => 'Retur Pelanggan: Kompresor macet & uap tidak keluar (Klaim Garansi)', 'tgl_habis_garansi' => '2028-10-12', 'created_at' => '2026-09-20 10:00:00', 'updated_at' => '2026-10-15 11:20:00'];
            for ($i = 1; $i <= 4; $i++) {
                $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => sprintf('OMR-NEB-%02d', $i), 'status' => 'Tersedia', 'id_penjualan' => null, 'catatan' => null, 'tgl_habis_garansi' => '2028-10-12', 'created_at' => '2026-09-20 10:00:00', 'updated_at' => null];
            }
        } elseif ($vId == 53) { // Kursi Roda Standard Sella (Low Stock Alert 1)
            for ($i = 1; $i <= 2; $i++) {
                $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => sprintf('SLL-KRD-%02d', $i), 'status' => 'Tersedia', 'id_penjualan' => null, 'catatan' => null, 'tgl_habis_garansi' => '2028-09-01', 'created_at' => '2026-09-01 09:00:00', 'updated_at' => null];
            }
        } elseif ($vId == 55) { // Regulator Oksigen (Low Stock Alert 2)
            for ($i = 1; $i <= 3; $i++) {
                $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => sprintf('O2-REG-%02d', $i), 'status' => 'Tersedia', 'id_penjualan' => null, 'catatan' => null, 'tgl_habis_garansi' => '2027-09-01', 'created_at' => '2026-09-01 09:00:00', 'updated_at' => null];
            }
        } else {
            // General healthy alkes
            $count = 12;
            for ($i = 1; $i <= $count; $i++) {
                $cleanSku = preg_replace('/[^A-Za-z0-9]/', '', $sku);
                $sn = sprintf('%s-%03d', $cleanSku, $i);
                $unitSerial[] = ['id' => $snIdCounter++, 'id_variasi' => $vId, 'serial_number' => $sn, 'status' => 'Tersedia', 'id_penjualan' => null, 'catatan' => null, 'tgl_habis_garansi' => '2028-06-30', 'created_at' => '2026-09-10 10:00:00', 'updated_at' => null];
            }
        }
    } else {
        // Category Obat
        if ($vId == 1) { // Amoxicillin 500mg (Low Stock Alert 1)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'AMX-OLD-2025', 'tgl_exp' => '2025-08-15', 'stok_sisa' => 0, 'created_at' => '2025-08-15 08:00:00'];
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'AMX-2026-01', 'tgl_exp' => '2027-12-15', 'stok_sisa' => 10, 'created_at' => '2026-09-15 08:30:00']; // 1 Box sisa di dus (<= 2 Box)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'AMX-2026-01.A', 'tgl_exp' => '2027-12-15', 'stok_sisa' => 8, 'created_at' => '2026-10-05 09:15:00']; // 8 Strip (<= 10 Strip)
        } elseif ($vId == 2) { // Sanmol Sirup (Expiring Batch 1)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'SNM-2026-A', 'tgl_exp' => '2028-05-10', 'stok_sisa' => 192, 'created_at' => '2026-09-25 09:15:00']; // 8 Karton
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'SNM-2026-A.A', 'tgl_exp' => '2028-05-10', 'stok_sisa' => 24, 'created_at' => '2026-10-07 10:30:00']; // 24 Botol
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'SNM-EXP-22', 'tgl_exp' => '2026-10-22', 'stok_sisa' => 5, 'created_at' => '2026-08-10 10:00:00']; // Expiring Soon Alert 1
        } elseif ($vId == 6) { // Hufagrip TMP Merah
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'HUF-TMP-26', 'tgl_exp' => '2027-08-15', 'stok_sisa' => 350, 'created_at' => '2026-09-10 09:00:00']; // 7 Karton
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'HUF-TMP-26.A', 'tgl_exp' => '2027-08-15', 'stok_sisa' => 48, 'created_at' => '2026-10-09 13:45:00']; // 48 Botol
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'HUF-TMP-27', 'tgl_exp' => '2028-06-20', 'stok_sisa' => 400, 'created_at' => '2026-09-25 09:15:00']; // 8 Karton
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'HUF-TMP-27.A', 'tgl_exp' => '2028-06-20', 'stok_sisa' => 47, 'created_at' => '2026-10-11 15:30:00']; // 47 Botol
        } elseif ($vId == 10) { // Paracetamol 500mg
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'PCT-OLD-2025', 'tgl_exp' => '2025-10-01', 'stok_sisa' => 0, 'created_at' => '2025-10-01 08:00:00'];
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'PCT-500-26', 'tgl_exp' => '2027-11-20', 'stok_sisa' => 180, 'created_at' => '2026-09-15 08:30:00']; // 18 Box
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'PCT-500-26.A', 'tgl_exp' => '2027-11-20', 'stok_sisa' => 7, 'created_at' => '2026-10-08 11:00:00']; // 7 Strip
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'PCT-500-26.B', 'tgl_exp' => '2027-11-20', 'stok_sisa' => 8, 'created_at' => '2026-10-10 14:20:00']; // 8 Strip
        } elseif ($vId == 22) { // Diapet Kapsul (Low Stock Alert 2)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'DPT-2027', 'tgl_exp' => '2027-09-10', 'stok_sisa' => 25, 'created_at' => '2026-09-10 10:00:00']; // 1 Box (<= 2 Box)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'DPT-2027.A', 'tgl_exp' => '2027-09-10', 'stok_sisa' => 3, 'created_at' => '2026-10-06 10:00:00']; // 3 Strip (<= 5 Strip)
        } elseif ($vId == 30) { // Imboost Force
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'IMB-FRC-26', 'tgl_exp' => '2027-09-15', 'stok_sisa' => 58, 'created_at' => '2026-10-05 10:00:00'];
        } elseif ($vId == 31) { // Bodrex Migra (Expiring Batch 2)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'BDR-2028', 'tgl_exp' => '2028-03-12', 'stok_sisa' => 250, 'created_at' => '2026-09-10 10:00:00'];
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'BDR-EXP-23', 'tgl_exp' => '2026-10-23', 'stok_sisa' => 4, 'created_at' => '2026-08-15 10:00:00']; // Expiring Soon Alert 2
        } elseif ($vId == 33) { // Decolgen (Low Stock Alert 3)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'DCL-2027', 'tgl_exp' => '2027-10-15', 'stok_sisa' => 25, 'created_at' => '2026-09-10 10:00:00']; // 1 Box (<= 2 Box)
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => 'DCL-2027.A', 'tgl_exp' => '2027-10-15', 'stok_sisa' => 4, 'created_at' => '2026-10-06 10:00:00']; // 4 Strip (<= 5 Strip)
        } else {
            // All other regular Obat: UNCONVERTED (no sub-batch), safe expiry 2027/2028, healthy stock 15 boxes
            $cleanSku = preg_replace('/[^A-Za-z0-9]/', '', $sku);
            $batchCode = sprintf('%s-2027-01', $cleanSku);
            $stokPcs = 15 * $rasio;
            $stokBatch[] = ['id' => $batchIdCounter++, 'id_variasi' => $vId, 'no_batch' => $batchCode, 'tgl_exp' => '2027-12-30', 'stok_sisa' => $stokPcs, 'created_at' => '2026-09-10 10:00:00'];
        }
    }
}

// 10. Compute stok_toko
$stokToko = [];
$stokIdCounter = 1;
foreach ($produkVariasi as $pv) {
    $vId = $pv['id'];
    $kategori = $pv['kategori'];
    $stokTotal = 0;
    if ($kategori === 'Alat Kesehatan') {
        foreach ($unitSerial as $us) {
            if ($us['id_variasi'] == $vId && $us['status'] === 'Tersedia') {
                $stokTotal++;
            }
        }
    } else {
        foreach ($stokBatch as $sb) {
            if ($sb['id_variasi'] == $vId) {
                $stokTotal += $sb['stok_sisa'];
            }
        }
    }
    $stokToko[] = [
        'id' => $stokIdCounter++,
        'id_variasi' => $vId,
        'stok' => $stokTotal
    ];
}

// 11. Kartu Stok Audit Trail
$kartuStok = [
    // 1. Initial Expired mutations (Pemusnahan Batch 2025)
    [
        'id' => 1,
        'id_variasi' => 1,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Barang Kedaluwarsa',
        'no_ref_dokumen' => 'MUT-EXP-20260901-01',
        'qty' => 15,
        'sisa_stok' => 2,
        'keterangan' => 'Pemusnahan Batch Kadaluarsa [Batch: AMX-OLD-2025]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-09-01 10:00:00'
    ],
    [
        'id' => 2,
        'id_variasi' => 10,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Barang Kedaluwarsa',
        'no_ref_dokumen' => 'MUT-EXP-20260901-02',
        'qty' => 20,
        'sisa_stok' => 20,
        'keterangan' => 'Pemusnahan Batch Kadaluarsa [Batch: PCT-OLD-2025]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-09-01 10:15:00'
    ],

    // 2. Conversions (Histori Konversi)
    // Konversi 1: Variasi 1 (Amoxicillin)
    [
        'id' => 3,
        'id_variasi' => 1,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 1,
        'sisa_stok' => 1,
        'keterangan' => 'Bongkar 1 Box (Rp0 - Konversi) [Batch: AMX-2026-01]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-05 09:15:00'
    ],
    [
        'id' => 4,
        'id_variasi' => 1,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Masuk',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 10,
        'sisa_stok' => 20,
        'keterangan' => 'Lahir Sub-Batch AMX-2026-01.A (Masuk 10 Strip)',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-05 09:15:00'
    ],

    // Konversi 2: Variasi 2 (Sanmol)
    [
        'id' => 5,
        'id_variasi' => 2,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 1,
        'sisa_stok' => 9,
        'keterangan' => 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: SNM-2026-A]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-07 10:30:00'
    ],
    [
        'id' => 6,
        'id_variasi' => 2,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Masuk',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 24,
        'sisa_stok' => 245,
        'keterangan' => 'Lahir Sub-Batch SNM-2026-A.A (Masuk 24 Botol)',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-07 10:30:00'
    ],

    // Konversi 3 & 4: Variasi 10 (Paracetamol)
    [
        'id' => 7,
        'id_variasi' => 10,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 1,
        'sisa_stok' => 19,
        'keterangan' => 'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-08 11:00:00'
    ],
    [
        'id' => 8,
        'id_variasi' => 10,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Masuk',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 10,
        'sisa_stok' => 200,
        'keterangan' => 'Lahir Sub-Batch PCT-500-26.A (Masuk 10 Strip)',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-08 11:00:00'
    ],
    [
        'id' => 9,
        'id_variasi' => 10,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 1,
        'sisa_stok' => 18,
        'keterangan' => 'Bongkar 1 Box (Rp0 - Konversi) [Batch: PCT-500-26]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-10 14:20:00'
    ],
    [
        'id' => 10,
        'id_variasi' => 10,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Masuk',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 10,
        'sisa_stok' => 200,
        'keterangan' => 'Lahir Sub-Batch PCT-500-26.B (Masuk 10 Strip)',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-10 14:20:00'
    ],

    // Konversi 5 & 6: Variasi 6 (Hufagrip TMP)
    [
        'id' => 11,
        'id_variasi' => 6,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 1,
        'sisa_stok' => 7,
        'keterangan' => 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-26]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-09 13:45:00'
    ],
    [
        'id' => 12,
        'id_variasi' => 6,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Masuk',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 50,
        'sisa_stok' => 900,
        'keterangan' => 'Lahir Sub-Batch HUF-TMP-26.A (Masuk 50 Botol)',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-09 13:45:00'
    ],
    [
        'id' => 13,
        'id_variasi' => 6,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 1,
        'sisa_stok' => 9,
        'keterangan' => 'Bongkar 1 Karton (Rp0 - Konversi) [Batch: HUF-TMP-27]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-11 15:30:00'
    ],
    [
        'id' => 14,
        'id_variasi' => 6,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Masuk',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Koreksi Manual',
        'no_ref_dokumen' => null,
        'qty' => 50,
        'sisa_stok' => 900,
        'keterangan' => 'Lahir Sub-Batch HUF-TMP-27.A (Masuk 50 Botol)',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-11 15:30:00'
    ],

    // 3. Penjualan Transaksi 6 (Alkes POS yang nantinya di-retur)
    [
        'id' => 15,
        'id_variasi' => 56,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'POS',
        'alasan_mutasi' => 'Penjualan POS',
        'no_ref_dokumen' => 'POS-20261012-0901',
        'qty' => 1,
        'sisa_stok' => 4,
        'keterangan' => 'Penjualan Luring POS-20261012-0901 [SN: OMR-NEB-99]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-12 16:10:00'
    ],

    // 4. Penjualan Transaksi 1 (POS: Amoxicillin Sub-Batch + Termometer MC-246)
    [
        'id' => 16,
        'id_variasi' => 1,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'POS',
        'alasan_mutasi' => 'Penjualan POS',
        'no_ref_dokumen' => 'POS-20261014-1001',
        'qty' => 2,
        'sisa_stok' => 18,
        'keterangan' => 'Penjualan Luring POS-20261014-1001 [Batch: AMX-2026-01.A (2x)]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-14 10:15:20'
    ],
    [
        'id' => 17,
        'id_variasi' => 5,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'POS',
        'alasan_mutasi' => 'Penjualan POS',
        'no_ref_dokumen' => 'POS-20261014-1001',
        'qty' => 1,
        'sisa_stok' => 5,
        'keterangan' => 'Penjualan Luring POS-20261014-1001 [SN: OMR-TH-01]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-14 10:15:20'
    ],

    // 5. Retur Alkes Nebulizer (Klaim Garansi / Karantina)
    [
        'id' => 18,
        'id_variasi' => 56,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Masuk',
        'kanal' => 'Manual',
        'alasan_mutasi' => 'Retur Barang Rusak',
        'no_ref_dokumen' => 'POS-20261012-0901',
        'qty' => 1,
        'sisa_stok' => 4,
        'keterangan' => 'Klaim Garansi / Retur Rusak [SN: OMR-NEB-99]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-15 11:20:00'
    ],

    // 6. Penjualan Transaksi 2 (POS: Paracetamol 2 Sub-Batch berbeda dari batch induk yang sama)
    [
        'id' => 19,
        'id_variasi' => 10,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'POS',
        'alasan_mutasi' => 'Penjualan POS',
        'no_ref_dokumen' => 'POS-20261016-1002',
        'qty' => 5,
        'sisa_stok' => 195,
        'keterangan' => 'Penjualan Luring POS-20261016-1002 [Batch: PCT-500-26.A (3x), PCT-500-26.B (2x)]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-16 14:22:10'
    ],

    // 7. Penjualan Transaksi 3 (POS: Hufagrip 2 Sub-Batch berbeda dari 2 batch induk berbeda + 1 Karton)
    [
        'id' => 20,
        'id_variasi' => 6,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'POS',
        'alasan_mutasi' => 'Penjualan POS',
        'no_ref_dokumen' => 'POS-20261017-1003',
        'qty' => 5,
        'sisa_stok' => 845,
        'keterangan' => 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-26.A (2x), HUF-TMP-27.A (3x)]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-17 11:40:05'
    ],
    [
        'id' => 21,
        'id_variasi' => 6,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'POS',
        'alasan_mutasi' => 'Penjualan POS',
        'no_ref_dokumen' => 'POS-20261017-1003',
        'qty' => 1,
        'sisa_stok' => 8,
        'keterangan' => 'Penjualan Luring POS-20261017-1003 [Batch: HUF-TMP-27 (1 Karton = 50 Botol)]',
        'dibuat_oleh' => 4,
        'tanggal' => '2026-10-17 11:40:05'
    ],

    // 8. Penjualan Transaksi 4 (E-Commerce: 1 produk Imboost)
    [
        'id' => 22,
        'id_variasi' => 30,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'E-Commerce',
        'alasan_mutasi' => 'Penjualan E-Commerce',
        'no_ref_dokumen' => 'INV-20261018-041',
        'qty' => 2,
        'sisa_stok' => 58,
        'keterangan' => 'Pengiriman via Ekspedisi (Kurir) [Batch: IMB-FRC-26 (2x)]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-18 09:30:00'
    ],

    // 9. Penjualan Transaksi 5 (E-Commerce: 1 Obat Satuan Besar Sanmol + 1 Alkes Tensimeter)
    [
        'id' => 23,
        'id_variasi' => 2,
        'satuan_tipe' => 'besar',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'E-Commerce',
        'alasan_mutasi' => 'Penjualan E-Commerce',
        'no_ref_dokumen' => 'INV-20261019-052',
        'qty' => 1,
        'sisa_stok' => 8,
        'keterangan' => 'Pengiriman via Ekspedisi (Kurir) [Batch: SNM-2026-A (1 Karton = 24 Botol)]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-19 13:20:00'
    ],
    [
        'id' => 24,
        'id_variasi' => 4,
        'satuan_tipe' => 'kecil',
        'jenis_mutasi' => 'Keluar',
        'kanal' => 'E-Commerce',
        'alasan_mutasi' => 'Penjualan E-Commerce',
        'no_ref_dokumen' => 'INV-20261019-052',
        'qty' => 1,
        'sisa_stok' => 4,
        'keterangan' => 'Pengiriman via Ekspedisi (Kurir) [SN: OMR-TENS-01]',
        'dibuat_oleh' => 2,
        'tanggal' => '2026-10-19 13:20:00'
    ]
];

// 12. Log Anomali FEFO (empty table or 0 rows clean)
$logAnomaliFefo = [];

// Assemble full SQL file
$sqlContent = "-- phpMyAdmin SQL Dump\n";
$sqlContent .= "-- version 5.2.1\n";
$sqlContent .= "-- https://www.phpmyadmin.net/\n";
$sqlContent .= "--\n";
$sqlContent .= "-- Host: 127.0.0.1\n";
$sqlContent .= "-- Waktu pembuatan: " . date('d M Y \p\a\d\a H.i') . "\n";
$sqlContent .= "-- Versi server: 10.4.32-MariaDB\n";
$sqlContent .= "-- Versi PHP: 8.2.12\n\n";
$sqlContent .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$sqlContent .= "START TRANSACTION;\n";
$sqlContent .= "SET time_zone = \"+00:00\";\n\n";
$sqlContent .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
$sqlContent .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
$sqlContent .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
$sqlContent .= "/*!40101 SET NAMES utf8mb4 */;\n\n";
$sqlContent .= "--\n-- Database: `db_inventory`\n--\n\n";

// Table schemas from all_schemas.sql
$schemaSql = file_get_contents(__DIR__ . '/all_schemas.sql');
preg_match_all('/CREATE TABLE `(.*?)` \((.*?)\) ENGINE=InnoDB.*?;/s', $schemaSql, $matches, PREG_SET_ORDER);
$tableSchemas = [];
foreach ($matches as $m) {
    $tableSchemas[$m[1]] = $m[0];
}

$tablesData = [
    'detail_penjualan'   => $detailPenjualan,
    'histori_konversi'   => $historiKonversi,
    'kartu_stok'         => $kartuStok,
    'log_anomali_fefo'   => $logAnomaliFefo,
    'penerimaan_detail'  => $penerimaanDetail,
    'penerimaan_stok'    => $penerimaanStok,
    'pengaturan_web'     => $pengaturan,
    'penjualan'          => $penjualan,
    'produk_induk'       => $produkInduk,
    'produk_variasi'     => $produkVariasi,
    'stok_batch'         => $stokBatch,
    'stok_toko'          => $stokToko,
    'supplier'           => $suppliers,
    'unit_serial'        => $unitSerial,
    'users'              => $users
];

foreach ($tablesData as $tbl => $rows) {
    $sqlContent .= "-- --------------------------------------------------------\n\n";
    $sqlContent .= "--\n-- Struktur dari tabel `$tbl`\n--\n\n";
    if (isset($tableSchemas[$tbl])) {
        $sqlContent .= $tableSchemas[$tbl] . "\n\n";
    }
    if (!empty($rows)) {
        $sqlContent .= insertSql($tbl, $rows);
    }
}

$sqlContent .= "COMMIT;\n\n";
$sqlContent .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
$sqlContent .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
$sqlContent .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

file_put_contents($outputFile, $sqlContent);
echo "SUCCESS: Wrote " . strlen($sqlContent) . " bytes to " . $outputFile . "\n";
