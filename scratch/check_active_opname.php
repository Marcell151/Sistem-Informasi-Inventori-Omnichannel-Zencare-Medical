<?php
require_once __DIR__ . '/../config/koneksi.php';

$s = $pdo->query("SELECT * FROM sesi_opname WHERE status = 'Aktif'")->fetch(PDO::FETCH_ASSOC);
echo "Active Session:\n";
print_r($s);

if ($s) {
    $rows = $pdo->query("SELECT id, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi FROM detail_opname_batch WHERE id_sesi = {$s['id']}")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nDetail rows count: " . count($rows) . "\n";
    foreach ($rows as $r) {
        $batchNo = '';
        if ($r['id_batch']) {
            $batchNo = $pdo->query("SELECT no_batch FROM stok_batch WHERE id = {$r['id_batch']}")->fetchColumn();
        }
        echo "ID: {$r['id']} | Batch: $batchNo (id_b: {$r['id_batch']}) | Sis: {$r['stok_sistem_fisik']} | Hit: " . ($r['hitung_fisik'] ?? 'NULL') . " | Sel: " . ($r['selisih'] ?? 'NULL') . " | Stat: {$r['status_mutasi']}\n";
    }
}
