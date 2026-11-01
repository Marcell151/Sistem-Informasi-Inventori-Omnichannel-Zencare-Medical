<?php
require 'config/koneksi.php';

try {
    $pdo->beginTransaction();

    $sesi7 = $pdo->query("SELECT id FROM sesi_opname WHERE kode_sesi = 'OPN-20261015-001'")->fetchColumn();
    if ($sesi7) {
        $items = $pdo->query("
            SELECT dob.id, dob.id_batch, dob.selisih, dob.status_mutasi, sb.no_batch, sb.stok_sisa, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi
            FROM detail_opname_batch dob
            JOIN stok_batch sb ON dob.id_batch = sb.id
            JOIN produk_variasi pv ON dob.id_variasi = pv.id
            WHERE dob.id_sesi = $sesi7 AND dob.jenis_komoditas = 'Obat'
        ")->fetchAll(PDO::FETCH_ASSOC);

        $stmtUp = $pdo->prepare("
            UPDATE detail_opname_batch
            SET satuan_tipe = ?,
                nama_satuan = ?,
                stok_tersedia_sistem = ?,
                stok_sistem_fisik = ?,
                hitung_fisik = ?
            WHERE id = ?
        ");

        foreach ($items as $it) {
            $rasio = max(1, intval($it['rasio_konversi']));
            $isSub = (strpos($it['no_batch'], '.') !== false);

            if ($rasio > 1 && !$isSub) {
                // Kardus Utuh / Satuan Besar
                $tipe = 'besar';
                $satuan = $it['satuan_besar'] ?: 'Dus';
                $qtyDus = max(0, intval(floor($it['stok_sisa'] / $rasio)));
                $sel = intval($it['selisih']);
                $hitung = max(0, $qtyDus + $sel);

                $stmtUp->execute([$tipe, $satuan, $qtyDus, $qtyDus, $hitung, $it['id']]);
            } else {
                // Satuan Kecil
                $tipe = 'kecil';
                $satuan = $it['satuan_kecil'] ?: 'Pcs';
                $qtyPcs = intval($it['stok_sisa']);
                $sel = intval($it['selisih']);
                $hitung = max(0, $qtyPcs + $sel);

                $stmtUp->execute([$tipe, $satuan, $qtyPcs, $qtyPcs, $hitung, $it['id']]);
            }
        }
        echo "[OK] Detail opname sesi 7 berhasil disesuaikan dengan UOM bertingkat (Besar vs Kecil)!\n";
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "[ERROR] " . $e->getMessage() . "\n";
}

