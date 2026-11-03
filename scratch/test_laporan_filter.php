<?php
// Test filter matrix for laporan/penjualan.php

$scenarios = [
    ['kategori' => 'Alat Kesehatan', 'kanal' => 'semua', 'desc' => 'Alkes (Semua Kanal)'],
    ['kategori' => 'Alat Kesehatan', 'kanal' => 'pos', 'desc' => 'Alkes (Kanal POS)'],
    ['kategori' => 'Alat Kesehatan', 'kanal' => 'ecommerce', 'desc' => 'Alkes (Kanal E-Commerce)'],
    ['kategori' => 'Obat', 'kanal' => 'ecommerce', 'desc' => 'Obat (Kanal E-Commerce)'],
    ['kategori' => 'semua', 'kanal' => 'ecommerce', 'desc' => 'Semua Kategori (Kanal E-Commerce)'],
    ['kategori' => 'Obat', 'kanal' => 'pos', 'desc' => 'Obat (Kanal POS)'],
    ['kategori' => 'semua', 'kanal' => 'pos', 'desc' => 'Semua Kategori (Kanal POS)'],
    ['kategori' => 'semua', 'kanal' => 'semua', 'desc' => 'Default (Semua Kategori & Kanal)'],
];

echo "=== HASIL TEST MATRIKS FILTER LAPORAN PENJUALAN ===\n\n";

foreach ($scenarios as $s) {
    $kategori = $s['kategori'];
    $kanal = $s['kanal'];
    $hideEceran = ($kategori === 'Alat Kesehatan' || $kanal === 'ecommerce');
    $grid = $hideEceran ? 'md:grid-cols-3 (3 Kolom)' : 'md:grid-cols-4 (4 Kolom)';
    $statusCard = $hideEceran ? 'DISEMBUNYIKAN (Tidak Tampil)' : 'DITAMPILKAN';
    
    echo sprintf(
        "[%-30s] -> Hide Eceran: %-5s | Card: %-25s | Grid: %s\n",
        $s['desc'],
        $hideEceran ? 'YA' : 'TIDAK',
        $statusCard,
        $grid
    );
}

