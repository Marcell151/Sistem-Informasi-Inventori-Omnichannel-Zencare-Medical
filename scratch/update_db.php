<?php
require "config/koneksi.php";
$pdo->exec("
CREATE TABLE IF NOT EXISTS histori_konversi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_variasi INT NOT NULL,
    batch_asal VARCHAR(100) NOT NULL,
    batch_hasil VARCHAR(100) NOT NULL,
    qty_box_buka INT NOT NULL,
    qty_pcs_hasil INT NOT NULL,
    dibuat_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_variasi) REFERENCES produk_variasi(id),
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
);
");
echo "DB Updated";
?>
