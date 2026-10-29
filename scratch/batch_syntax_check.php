<?php
$files = [
    'index.php',
    'pos/pos.php',
    'admin/pesanan.php',
    'admin/cetak_label_pengiriman.php',
    'pos/cetak_invoice.php',
    'inventori/logistik_medis.php',
    'laporan/logistik_medis.php',
    'laporan/penerimaan_barang.php',
    'laporan/anomali_fefo.php',
    'laporan/kartu_stok.php',
    'laporan/mutasi_konversi.php',
    'inventori/cetak_nota_penerimaan.php',
    'inventori/tambah_stok.php',
    'inventori/konversi_uom.php',
    'api/register_bpom_product.php',
    'ecommerce/cart.php',
    'ecommerce/index.php',
    'ecommerce/produk.php',
];

$allPassed = true;
foreach ($files as $file) {
    exec("C:\\xampp\\php\\php.exe -l \"$file\"", $output, $code);
    if ($code !== 0) {
        echo "SYNTAX ERROR in $file:\n" . implode("\n", $output) . "\n";
        $allPassed = false;
    } else {
        echo "PASS: $file\n";
    }
    $output = [];
}

if ($allPassed) {
    echo "ALL 18 FILES PASSED SYNTAX CHECK!\n";
}

