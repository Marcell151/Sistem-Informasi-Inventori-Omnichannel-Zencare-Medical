<?php
// File: config/order_helper.php
// Helper Fungsi Pengelolaan Pesanan & Reservasi Stok Otomatis ZenCare Medical

if (!function_exists('batalkanSatuPesananInternal')) {
    /**
     * Membatalkan 1 pesanan dan mengembalikan seluruh reserved stok (stok_toko, stok_batch FEFO, unit_serial).
     */
    function batalkanSatuPesananInternal($pdo, $idPesanan) {
        // Ambil detail items untuk restorasi stok toko, batch, dan serial number
        $stmtItems = $pdo->prepare("
            SELECT d.id_variasi, d.qty, d.catatan_logistik, v.rasio_konversi, v.satuan_besar, pi.kategori 
            FROM detail_penjualan d 
            JOIN produk_variasi v ON d.id_variasi = v.id 
            JOIN produk_induk pi ON v.id_produk_induk = pi.id
            WHERE d.id_penjualan = ?
        ");
        $stmtItems->execute([$idPesanan]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $idVar = intval($item['id_variasi']);
            $qtyBox = intval($item['qty']);
            $rasio = max(1, intval($item['rasio_konversi'] ?: 1));
            $qtyPotong = $qtyBox * $rasio;

            // 1. Kembalikan ke stok_toko
            $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$qtyPotong, $idVar]);

            // 2. Restorasi FEFO stok_batch dan Serial Number berdasarkan catatan_logistik
            $catatan = trim($item['catatan_logistik'] ?? '');
            if (!empty($catatan)) {
                if ($item['kategori'] === 'Obat') {
                    // Format catatan_logistik: "BATCH-XXX (1 Karton = 50 Botol)" atau "BATCH-XXX (50x)"
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
                } elseif ($item['kategori'] === 'Alat Kesehatan') {
                    $sns = array_filter(array_map('trim', explode(',', $catatan)));
                    foreach ($sns as $snStr) {
                        $pdo->prepare("UPDATE unit_serial SET status = 'Tersedia', id_penjualan = NULL WHERE id_variasi = ? AND serial_number = ?")
                            ->execute([$idVar, $snStr]);
                    }
                }
            }
        }

        // Update status pesanan ke Dibatalkan
        $pdo->prepare("UPDATE penjualan SET status_pesanan = 'Dibatalkan' WHERE id = ?")->execute([$idPesanan]);
    }
}

if (!function_exists('batalkanPesananKadaluarsa')) {
    /**
     * Membatalkan semua pesanan dengan status 'Menunggu Pembayaran' yang telah melebihi batas waktu cooldown:
     * - POS Kasir Walk-In: 5 Menit (langsung di counter kasir)
     * - E-Commerce QRIS / E-Wallet: 15 Menit (standar instan Midtrans)
     * - E-Commerce Virtual Account / Bank Transfer: 120 Menit (2 Jam)
     * Mengembalikan reserved stok ke stok fisik dan stok batch FEFO.
     */
    function batalkanPesananKadaluarsa($pdo, $batasMenit = null) {
        try {
            if ($batasMenit !== null) {
                // Manual override durasi menit jika disuplai parameter
                $stmt = $pdo->prepare("
                    SELECT id, no_invoice, tipe_transaksi, payment_method_ecommerce 
                    FROM penjualan 
                    WHERE status_pesanan = 'Menunggu Pembayaran' 
                      AND created_at < (NOW() - INTERVAL ? MINUTE)
                ");
                $stmt->execute([intval($batasMenit)]);
            } else {
                // Durasi dinamis otomatis sesuai kanal & metode pembayaran Midtrans
                $posMenit   = defined('MIDTRANS_EXPIRY_POS_QRIS_MENIT') ? MIDTRANS_EXPIRY_POS_QRIS_MENIT : 5;
                $qrisMenit  = defined('MIDTRANS_EXPIRY_ECOMMERCE_QRIS_MENIT') ? MIDTRANS_EXPIRY_ECOMMERCE_QRIS_MENIT : 15;
                $vaMenit    = defined('MIDTRANS_EXPIRY_ECOMMERCE_VA_MENIT') ? MIDTRANS_EXPIRY_ECOMMERCE_VA_MENIT : 120;

                $stmt = $pdo->prepare("
                    SELECT id, no_invoice, tipe_transaksi, payment_method_ecommerce 
                    FROM penjualan 
                    WHERE status_pesanan = 'Menunggu Pembayaran' 
                      AND created_at < (NOW() - INTERVAL (
                          CASE 
                              WHEN tipe_transaksi = 'pos' THEN :pos_m
                              WHEN LOWER(COALESCE(payment_method_ecommerce, '')) IN ('qris', 'gopay', 'shopeepay') THEN :qris_m
                              ELSE :va_m
                          END
                      ) MINUTE)
                ");
                $stmt->execute([
                    ':pos_m'  => $posMenit,
                    ':qris_m' => $qrisMenit,
                    ':va_m'   => $vaMenit
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
                    batalkanSatuPesananInternal($pdo, $idPesanan);
                    $pdo->commit();
                    $countCancelled++;
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
