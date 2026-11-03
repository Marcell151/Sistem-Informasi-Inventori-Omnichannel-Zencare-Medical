<?php
// File: scratch/adjust_roles_actors.php
// Script untuk menyesuaikan aktor / user pelaksana agar lebih didominasi Admin Toko & Kasir
require_once __DIR__ . '/../config/koneksi.php';

echo "=== MEMULAI PENYESUAIAN ROLE & AKTOR OPERASIONAL ===\n";

try {
    $pdo->beginTransaction();

    // ── 1. TRANSAKSI PENJUALAN & PEMBATALAN ──
    // Pembatalan oleh Admin Toko (Bukan Superadmin)
    $pdo->prepare("
        UPDATE penjualan 
        SET dibatalkan_oleh = 'Admin Toko',
            alasan_batal = 'Permohonan Pelanggan Disetujui Admin: Salah memilih variasi kemasan obat'
        WHERE no_invoice = 'WEB-20261101-670'
    ")->execute();

    // Pastikan invoice batal lainnya juga rapi tercatat
    $pdo->prepare("
        UPDATE penjualan 
        SET dibatalkan_oleh = 'Admin Toko',
            waktu_batal = '2026-11-02 16:20:00',
            alasan_batal = 'Admin Toko: Konfirmasi Pelanggan Salah Memasukkan Alamat Pengiriman'
        WHERE no_invoice = 'WEB-20261102-818'
    ")->execute();

    echo "  ✓ Pembatalan pesanan disesuaikan: ditangani oleh 'Admin Toko'.\n";

    // ── 2. SESI STOK OPNAME & DETAIL PERHITUNGAN ──
    // Sesi 11: OPN-20261101-003 (Selesai Sempurna oleh Admin Toko)
    $pdo->prepare("
        UPDATE sesi_opname 
        SET dibekukan_oleh = 2,
            dibuka_oleh = 2,
            catatan = 'Stok Opname Rutin Awal Bulan oleh Admin Toko - Seluruh fisik obat dan unit alkes 100% cocok dengan data sistem (Selesai Sempurna).'
        WHERE id = 11
    ")->execute();

    $pdo->prepare("
        UPDATE detail_opname_batch 
        SET dihitung_oleh = 2
        WHERE id_sesi = 11
    ")->execute();

    // Sesi 3: OPN-20261028-002 (Dibatalkan oleh Admin Toko)
    $pdo->prepare("
        UPDATE sesi_opname 
        SET dibekukan_oleh = 2,
            dibuka_oleh = 2,
            catatan = 'Dibatalkan oleh Admin Toko karena gangguan pemadaman listrik PLN dan lonjakan antrean pasien di kasir.'
        WHERE id = 3
    ")->execute();

    // Sesi 6: Dibatalkan oleh Admin Toko
    $pdo->prepare("
        UPDATE sesi_opname 
        SET dibekukan_oleh = 2,
            dibuka_oleh = 2,
            catatan = 'Dibatalkan oleh Admin Toko karena penyesuaian jadwal operasional.'
        WHERE id = 6
    ")->execute();

    // Sesi 7: OPN-20261015-001 (Audit Kolaboratif: Dihitung oleh Admin Toko, Mutasi Selisih Disahkan Pemilik/SuperAdmin)
    $pdo->prepare("
        UPDATE sesi_opname 
        SET dibekukan_oleh = 1,
            dibuka_oleh = 1,
            catatan = 'Audit Fisik Triwulan III oleh Pemilik & Admin - Selesai dengan rekonsiliasi mutasi selisih barang rusak dan hilang.'
        WHERE id = 7
    ")->execute();

    $pdo->prepare("
        UPDATE detail_opname_batch 
        SET dihitung_oleh = 2,
            dimutasi_oleh = 1
        WHERE id_sesi = 7 AND status_mutasi = 'Sudah Dimutasi'
    ")->execute();

    $pdo->prepare("
        UPDATE detail_opname_batch 
        SET dihitung_oleh = 2
        WHERE id_sesi = 7 AND status_mutasi != 'Sudah Dimutasi'
    ")->execute();

    echo "  ✓ Stok Opname disesuaikan: Sesi rutin & pembatalan dikelola Admin Toko, Audit triwulan kolaboratif (Hitung: Admin Toko, Mutasi: Pemilik/SuperAdmin).\n";

    // ── 3. HISTORI KONVERSI & KARTU STOK ──
    // Sebagian besar konversi rak kasir dilakukan oleh Admin Toko (ID: 2) dan Kasir (ID: 4)
    $allKonversi = $pdo->query("SELECT id FROM histori_konversi ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
    $idx = 0;
    foreach ($allKonversi as $hId) {
        // Rotasi aktor: ID 2 (Admin Toko 65%), ID 4 (Kasir 25%), ID 1 (Superadmin 10%)
        if ($idx % 10 === 0) {
            $userAktor = 1; // Superadmin
        } elseif ($idx % 4 === 0) {
            $userAktor = 4; // Kasir
        } else {
            $userAktor = 2; // Admin Toko
        }
        $pdo->prepare("UPDATE histori_konversi SET dibuat_oleh = ? WHERE id = ?")->execute([$userAktor, $hId]);
        $idx++;
    }

    // Perbarui kartu_stok terkait konversi agar cocok dengan aktor histori_konversi
    $pdo->prepare("
        UPDATE kartu_stok 
        SET dibuat_oleh = 2 
        WHERE no_ref_dokumen = 'KNV-INIT' AND id % 4 != 0
    ")->execute();

    $pdo->prepare("
        UPDATE kartu_stok 
        SET dibuat_oleh = 4 
        WHERE no_ref_dokumen = 'KNV-INIT' AND id % 4 = 0
    ")->execute();

    echo "  ✓ Histori konversi dan kartu stok disesuaikan: didominasi oleh Admin Toko dan Kasir saat penyiapan etalase.\n";

    $pdo->commit();
    echo "=== PENYESUAIAN ROLE SELESAI DENGAN SUKSES ===\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

