<?php
require_once __DIR__ . '/../config/koneksi.php';

echo "=== penerimaan_stok dibuat_oleh ===\n";
print_r($pdo->query("SELECT dibuat_oleh, count(*) FROM penerimaan_stok GROUP BY dibuat_oleh")->fetchAll(PDO::FETCH_ASSOC));

echo "=== histori_konversi dibuat_oleh ===\n";
print_r($pdo->query("SELECT dibuat_oleh, count(*) FROM histori_konversi GROUP BY dibuat_oleh")->fetchAll(PDO::FETCH_ASSOC));

echo "=== kartu_stok dibuat_oleh ===\n";
print_r($pdo->query("SELECT dibuat_oleh, count(*) FROM kartu_stok GROUP BY dibuat_oleh")->fetchAll(PDO::FETCH_ASSOC));

echo "=== sesi_opname ===\n";
print_r($pdo->query("SELECT id, kode_sesi, status, dibekukan_oleh, dibuka_oleh, catatan FROM sesi_opname")->fetchAll(PDO::FETCH_ASSOC));

echo "=== detail_opname_batch dihitung/dimutasi ===\n";
print_r($pdo->query("SELECT id_sesi, dihitung_oleh, dimutasi_oleh, count(*) FROM detail_opname_batch GROUP BY id_sesi, dihitung_oleh, dimutasi_oleh")->fetchAll(PDO::FETCH_ASSOC));

echo "=== penjualan pembatalan ===\n";
print_r($pdo->query("SELECT id, no_invoice, status_pesanan, dibatalkan_oleh, alasan_batal FROM penjualan WHERE status_pesanan = 'Dibatalkan'")->fetchAll(PDO::FETCH_ASSOC));
