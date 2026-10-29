<?php
// File: config/order_helper.php
// Helper Fungsi Pengelolaan Pesanan & Reservasi Stok Otomatis ZenCare Medical
require_once __DIR__ . '/config.php';

if (!function_exists('batalkanSatuPesananInternal')) {
    /**
     * Membatalkan 1 pesanan dan mengembalikan seluruh reserved stok (stok_toko, stok_batch FEFO, unit_serial).
     * Dibuat idempoten: jika pesanan sudah dibatalkan atau bukan 'Menunggu Pembayaran', proses langsung berhenti tanpa pengembalian ganda.
     */
    function batalkanSatuPesananInternal($pdo, $idPesanan) {
        // 1. Update status pesanan ke Dibatalkan secara bersyarat (Idempotency Protection)
        $stmtCancel = $pdo->prepare("UPDATE penjualan SET status_pesanan = 'Dibatalkan' WHERE id = ? AND status_pesanan = 'Menunggu Pembayaran'");
        $stmtCancel->execute([$idPesanan]);
        if ($stmtCancel->rowCount() === 0) {
            // Status pesanan sudah bukan Menunggu Pembayaran. Tidak ada stok yang dikembalikan ganda.
            return false;
        }

        // 2. Ambil detail items untuk restorasi stok toko, batch, dan serial number
        $stmtItems = $pdo->prepare("
            SELECT d.id AS id_detail, d.id_variasi, d.qty, d.catatan_logistik, v.rasio_konversi, v.satuan_besar, pi.kategori 
            FROM detail_penjualan d 
            JOIN produk_variasi v ON d.id_variasi = v.id 
            JOIN produk_induk pi ON v.id_produk_induk = pi.id
            WHERE d.id_penjualan = ?
        ");
        $stmtItems->execute([$idPesanan]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $idDetail = $item['id_detail'];
            $idVar = intval($item['id_variasi']);
            $qtyBox = intval($item['qty']);
            $rasio = max(1, intval($item['rasio_konversi'] ?: 1));
            $qtyPotong = $qtyBox * $rasio;

            // Kembalikan ke stok_toko
            $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$qtyPotong, $idVar]);

            // Restorasi stok batch via tabel pesanan_batch_alokasi jika tersedia
            $stmtAlloc = $pdo->prepare("SELECT id_batch, qty_satuan_kecil FROM pesanan_batch_alokasi WHERE id_detail_penjualan = ?");
            $stmtAlloc->execute([$idDetail]);
            $allocs = $stmtAlloc->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($allocs)) {
                foreach ($allocs as $al) {
                    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa + ? WHERE id = ?")
                        ->execute([$al['qty_satuan_kecil'], $al['id_batch']]);
                }
            } else {
                // Fallback restorasi FEFO berdasarkan catatan_logistik untuk pesanan lama
                $catatan = trim($item['catatan_logistik'] ?? '');
                if (!empty($catatan)) {
                    preg_match_all('/([^\(,\s]+)\s*\((?:.*=\s*)?(\d+)(?:x|[^\)]*)?\)/i', $catatan, $matches, PREG_SET_ORDER);
                    if (empty($matches)) {
                        preg_match_all('/(.+?)\s*\((\d+)x\)/', $catatan, $matches, PREG_SET_ORDER);
                    }
                    foreach ($matches as $match) {
                        $batchNo = trim($match[1]);
                        $batchQty = intval($match[2]);
                        if ($batchQty > 0) {
                            $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa + ? WHERE id_variasi = ? AND no_batch = ?")
                                ->execute([$batchQty, $idVar, $batchNo]);
                        }
                    }
                }
            }

            // Restorasi Serial Number jika ada tercatat di catatan_logistik
            $catatan = trim($item['catatan_logistik'] ?? '');
            if (!empty($catatan)) {
                $sns = array_filter(array_map('trim', explode(',', $catatan)));
                foreach ($sns as $snStr) {
                    $pdo->prepare("UPDATE unit_serial SET status = 'Tersedia', id_penjualan = NULL WHERE id_variasi = ? AND serial_number = ?")
                        ->execute([$idVar, $snStr]);
                }
            }
        }

        // Hapus data alokasi setelah stok dikembalikan
        $pdo->prepare("DELETE FROM pesanan_batch_alokasi WHERE id_penjualan = ?")->execute([$idPesanan]);
        return true;
    }
}

if (!function_exists('batalkanPesananKadaluarsa')) {
    /**
     * Membatalkan semua pesanan dengan status 'Menunggu Pembayaran' yang telah melebihi batas waktu cooldown:
     * - POS Kasir Walk-In: 5 Menit (langsung di counter kasir)
     * - E-Commerce Online (Semua Metode Bayar): 120 Menit (2 Jam)
     * Mengembalikan reserved stok ke stok fisik dan stok batch FEFO secara idempoten.
     */
    function batalkanPesananKadaluarsa($pdo, $batasMenit = null) {
        try {
            if ($batasMenit !== null) {
                // Manual override durasi menit jika disuplai parameter khusus uji coba
                $stmt = $pdo->prepare("
                    SELECT id, no_invoice, tipe_transaksi, payment_method_ecommerce 
                    FROM penjualan 
                    WHERE status_pesanan = 'Menunggu Pembayaran' 
                      AND created_at < (NOW() - INTERVAL ? MINUTE)
                ");
                $stmt->execute([intval($batasMenit)]);
            } else {
                // Durasi resmi: POS Kasir 5 Menit, E-Commerce 120 Menit (2 Jam)
                $posMenit   = defined('MIDTRANS_EXPIRY_POS_QRIS_MENIT') ? MIDTRANS_EXPIRY_POS_QRIS_MENIT : 5;
                $ecomMenit  = defined('MIDTRANS_EXPIRY_ECOMMERCE_MENIT') ? MIDTRANS_EXPIRY_ECOMMERCE_MENIT : 120;

                $stmt = $pdo->prepare("
                    SELECT id, no_invoice, tipe_transaksi, payment_method_ecommerce 
                    FROM penjualan 
                    WHERE status_pesanan = 'Menunggu Pembayaran' 
                      AND created_at < (NOW() - INTERVAL (
                          CASE 
                              WHEN tipe_transaksi = 'pos' THEN :pos_m
                              ELSE :ecom_m
                          END
                      ) MINUTE)
                ");
                $stmt->execute([
                    ':pos_m'  => $posMenit,
                    ':ecom_m' => $ecomMenit
                ]);
            }
            $expiredOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($expiredOrders)) {
                return 0;
            }

            $countCancelled = 0;
            foreach ($expiredOrders as $exp) {
                $idPesanan = intval($exp['id']);
                $pdo->beginTransaction();
                try {
                    $cancelled = batalkanSatuPesananInternal($pdo, $idPesanan);
                    $pdo->commit();
                    if ($cancelled) {
                        $countCancelled++;
                    }
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Gagal auto-cancel pesanan kadaluarsa #{$exp['no_invoice']}: " . $e->getMessage());
                }
            }
            return $countCancelled;
        } catch (Exception $e) {
            error_log("Error in batalkanPesananKadaluarsa: " . $e->getMessage());
            return 0;
        }
    }
}
