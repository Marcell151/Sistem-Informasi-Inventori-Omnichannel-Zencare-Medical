<?php
// Script pembersihan istilah sesuai aturan Dosen Pembimbing:
// 1. ZenCare adalah toko alat kesehatan dan obat, BUKAN apotek.
// 2. Jangan tulis apotek, farmasi, apoteker, PBF, atau "audit" di teks UI, label cetak, banner, nama menu.
// 3. Stock Opname bukan "audit".

$replacements = [
    // UI text apotek / farmasi / pbf
    'api/register_bpom_product.php' => [
        'Pabrik/PBF:' => 'Produsen/Distributor:',
        'Master Produk Apotek' => 'Master Produk Toko',
    ],
    'ecommerce/cart.php' => [
        'Stok resmi farmasi terlindungi real-time' => 'Stok resmi toko obat & alkes terlindungi real-time',
    ],
    'ecommerce/index.php' => [
        'Farmasi &bull;' => 'Obat &bull;',
    ],
    'ecommerce/produk.php' => [
        'Referensi Eceran Apotek/POS:' => 'Referensi Eceran Toko Offline/POS:',
    ],
    'inventori/cetak_nota_penerimaan.php' => [
        'Izin Apotek: 445/SIA/2026 • SIPA: 19890425/SIPA/2026/01' => 'Izin Toko Alat Kesehatan dan Obat: 445/ITKO/2026',
    ],
    'inventori/konversi_uom.php' => [
        'siap pajang di etalase apotek.' => 'siap pajang di etalase toko.',
    ],
    'inventori/tambah_stok.php' => [
        "'Umum / PBF Mitra'" => "'Umum / Distributor Resmi'",
        'Izin Apotek: 445/SIA/2026 • SIPA: 19890425/SIPA/2026/01' => 'Izin Toko Alat Kesehatan dan Obat: 445/ITKO/2026',
        "company: 'PBF Mitra'" => "company: 'Distributor Resmi'",
        "doc.supplier || 'Umum / PBF Mitra'" => "doc.supplier || 'Umum / Distributor Resmi'",
    ],
    'laporan/logistik_medis.php' => [
        'Kategori: <strong>Farmasi (Obat-obatan)</strong>' => 'Kategori: <strong>Obat-obatan</strong>',
        'AUDIT JEJAK SERIAL NUMBER (SN)' => 'REKAM JEJAK SERIAL NUMBER (SN)',
    ],
    'laporan/mutasi_konversi.php' => [
        "'Petugas Farmasi'" => "'Petugas Toko'",
        'Audit dekomposisi kemasan' => 'Rekam jejak dekomposisi kemasan',
        'Pemisahan Audit Mutasi vs Konversi' => 'Pemisahan Mutasi vs Konversi',
    ],
    'laporan/penerimaan_barang.php' => [
        'Audit dokumen inbound' => 'Rekam dokumen inbound',
        'Audit Trail Inbound Logistik & Retur Parsial' => 'Riwayat Logistik Inbound & Retur Parsial',
        'Kulakan Apotek Lain' => 'Kulakan Toko Lain',
        'Kulakan (Apotek Lain)' => 'Kulakan (Toko Lain)',
        'AUDIT RETUR PARSIAL' => 'MONITORING RETUR PARSIAL',
        'Periode Audit:' => 'Periode Laporan:',
    ],
    'pos/pos.php' => [
        'PERINGATAN KETAT FARMASI:' => 'PERINGATAN KELAYAKAN OBAT:',
        'histori audit secara permanen' => 'histori log secara permanen',
    ],
    'index.php' => [
        'Log Audit Kartu Stok' => 'Log Kartu Stok',
        'Log Audit Mutasi' => 'Log Mutasi',
        'Audit Kartu Stok' => 'Log Kartu Stok',
        'Audit SOP: Anomali FEFO Hari Ini' => 'Monitoring SOP: Anomali FEFO Hari Ini',
        "title: 'Audit FEFO Tertib ✓'" => "title: 'Kepatuhan FEFO Tertib ✓'",
    ],
    'inventori/histori_konversi.php' => [
        'Laporan jejak audit (Audit Trail)' => 'Laporan rekam jejak histori log',
    ],
    'konsep/inventori/proses_mutasi.php' => [
        'dengan atomik & audit trail' => 'dengan atomik & riwayat mutasi',
    ],
    'laporan/anomali_fefo.php' => [
        'Laporan Audit Anomali FEFO' => 'Laporan Anomali FEFO',
        'Modul Audit' => 'Modul Monitoring',
        'Audit Trail Kepatuhan Standar FEFO' => 'Rekam Jejak Kepatuhan Standar FEFO',
        'LAPORAN AUDIT ANOMALI FEFO' => 'LAPORAN ANOMALI FEFO',
    ],
    'laporan/kartu_stok.php' => [
        'Audit log fisik keluar/masuk' => 'Log mutasi fisik keluar/masuk',
        'pergerakan audit stok khusus' => 'pergerakan log stok khusus',
        'BANNER AUDIT KHUSUS' => 'BANNER LOG KHUSUS',
    ],
];

foreach ($replacements as $file => $pairs) {
    if (!file_exists($file)) {
        echo "File $file not found, skipping.\n";
        continue;
    }
    $c = file_get_contents($file);
    $modified = false;
    foreach ($pairs as $target => $rep) {
        if (strpos($c, $target) !== false) {
            $c = str_replace($target, $rep, $c);
            $modified = true;
            echo "Replaced '$target' in $file\n";
        }
    }
    if ($modified) {
        file_put_contents($file, $c);
    }
}
echo "Sanitization complete.\n";

