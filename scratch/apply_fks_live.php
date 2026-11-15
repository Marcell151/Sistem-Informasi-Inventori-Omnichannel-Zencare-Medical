<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

echo "Testing ALTER TABLE on live DB:\n";

$queries = [
    // 1. log_anomali_fefo
    "ALTER TABLE log_anomali_fefo ADD CONSTRAINT fk_anomali_penjualan FOREIGN KEY (id_penjualan) REFERENCES penjualan(id) ON DELETE CASCADE",
    "ALTER TABLE log_anomali_fefo ADD CONSTRAINT fk_anomali_user FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE",
    "ALTER TABLE log_anomali_fefo ADD CONSTRAINT fk_anomali_variasi FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id) ON DELETE CASCADE",
    "ALTER TABLE log_anomali_fefo ADD CONSTRAINT fk_anomali_resolved FOREIGN KEY (resolved_in_penjualan) REFERENCES penjualan(id) ON DELETE SET NULL",
    
    // 2. kartu_stok
    "ALTER TABLE kartu_stok ADD CONSTRAINT fk_kartu_batch FOREIGN KEY (id_batch) REFERENCES stok_batch(id) ON DELETE SET NULL",
    
    // 3. unit_serial
    "ALTER TABLE unit_serial ADD CONSTRAINT fk_serial_penjualan FOREIGN KEY (id_penjualan) REFERENCES penjualan(id) ON DELETE SET NULL",
    
    // 4. detail_opname_batch
    "ALTER TABLE detail_opname_batch ADD CONSTRAINT fk_opname_kartu_stok FOREIGN KEY (id_kartu_stok) REFERENCES kartu_stok(id) ON DELETE SET NULL"
];

foreach ($queries as $q) {
    try {
        $pdo->exec($q);
        echo "SUCCESS: $q\n";
    } catch (Exception $e) {
        echo "NOTICE/ERROR: " . $e->getMessage() . "\n";
    }
}

