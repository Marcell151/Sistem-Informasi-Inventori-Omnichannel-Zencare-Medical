<?php
require 'config/koneksi.php';
$sessions = $pdo->query("SELECT * FROM sesi_opname ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "=== SESI OPNAME TERAKHIR ===\n";
foreach ($sessions as $s) {
    echo "ID: {$s['id']} | Kode: {$s['kode_sesi']} | Status: {$s['status']} | Catatan: {$s['catatan']}\n";
}

$lastSession = $sessions[0] ?? null;
if ($lastSession) {
    echo "\n=== DETAIL OPNAME SESI {$lastSession['kode_sesi']} (MUTATED ONLY) ===\n";
    $mutated = $pdo->query("
        SELECT dob.*, pv.nama_variasi, pi.nama_produk 
        FROM detail_opname_batch dob
        JOIN produk_variasi pv ON dob.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE dob.id_sesi = {$lastSession['id']} AND dob.status_mutasi = 'Sudah Dimutasi'
    ")->fetchAll(PDO::FETCH_ASSOC);
    print_r($mutated);

    echo "\n=== KARTU STOK TERKAIT SESI {$lastSession['kode_sesi']} ===\n";
    $ks = $pdo->query("
        SELECT * FROM kartu_stok 
        WHERE no_ref_dokumen = '{$lastSession['kode_sesi']}' OR keterangan LIKE '%{$lastSession['kode_sesi']}%'
    ")->fetchAll(PDO::FETCH_ASSOC);
    print_r($ks);
}
