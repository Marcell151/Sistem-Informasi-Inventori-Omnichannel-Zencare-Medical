<?php
function testRun($kategori, $kanal) {
    $cmd = 'C:\\xampp\\php\\php.exe c:\\xampp\\htdocs\\inventory_zencare\\scratch\\runner.php "' . $kategori . '" "' . $kanal . '"';
    return trim(shell_exec($cmd));
}

echo "Filter [Alkes + POS]: " . testRun('Alat Kesehatan', 'pos') . " (Ekspektasi: TIDAK)\n";
echo "Filter [Alkes + E-Commerce]: " . testRun('Alat Kesehatan', 'ecommerce') . " (Ekspektasi: TIDAK)\n";
echo "Filter [Obat + E-Commerce]: " . testRun('Obat', 'ecommerce') . " (Ekspektasi: TIDAK)\n";
echo "Filter [Semua + E-Commerce]: " . testRun('semua', 'ecommerce') . " (Ekspektasi: TIDAK)\n";
echo "Filter [Alkes + Semua Kanal]: " . testRun('Alat Kesehatan', 'semua') . " (Ekspektasi: TIDAK)\n";
echo "Filter [Obat + POS]: " . testRun('Obat', 'pos') . " (Ekspektasi: ADA)\n";
echo "Filter [Semua + POS]: " . testRun('semua', 'pos') . " (Ekspektasi: ADA)\n";
echo "Filter [Semua + Semua Kanal]: " . testRun('semua', 'semua') . " (Ekspektasi: ADA)\n";
