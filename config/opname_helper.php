<?php
// File: config/opname_helper.php
// Helper Resmi Manajemen Stok Opname Terpusat & Pembekuan Transaksi (Central Freeze) - ZenCare Medical

if (!function_exists('cekSesiOpnameAktif')) {
    /**
     * Memeriksa apakah saat ini ada Sesi Stok Opname yang berstatus 'Aktif'.
     * Hasil di-cache dalam static variable selama request berlangsung untuk performa tinggi.
     */
    function cekSesiOpnameAktif($pdo) {
        static $cachedSesi = null;
        if ($cachedSesi !== null) {
            return $cachedSesi;
        }

        try {
            $stmt = $pdo->prepare("
                SELECT so.*, u.nama_lengkap AS pembuat_sesi
                FROM sesi_opname so
                LEFT JOIN users u ON so.dibekukan_oleh = u.id
                WHERE so.status = 'Aktif'
                LIMIT 1
            ");
            $stmt->execute();
            $cachedSesi = $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
            return $cachedSesi;
        } catch (Exception $e) {
            error_log("Error in cekSesiOpnameAktif: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('assertTidakSedangOpname')) {
    /**
     * Memblokir eksekusi aksi yang memanipulasi stok fisik jika sesi opname sedang aktif.
     * Dapat mengembalikan Exception (untuk backend/API) atau me-render layar blokir jika dipanggil di halaman web.
     */
    function assertTidakSedangOpname($pdo, $namaAksi = 'melakukan transaksi') {
        $sesi = cekSesiOpnameAktif($pdo);
        if ($sesi) {
            $kodeSesi = htmlspecialchars($sesi['kode_sesi']);
            $waktuBeku = date('d/m/Y H:i', strtotime($sesi['dibekukan_pada']));
            $pembuat = htmlspecialchars($sesi['pembuat_sesi'] ?? 'Superadmin');

            $msg = "Aksi $namaAksi ditolak! Sistem sedang dalam pembekuan Stok Opname Fisik (Sesi: $kodeSesi sejak $waktuBeku oleh $pembuat). Transaksi fisik dikunci sementara demi integritas data.";

            // Jika dipanggil via API JSON
            if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                http_response_code(423); // 423 Locked
                echo json_encode(['status' => 'error', 'message' => $msg, 'opname_freeze' => true]);
                exit;
            }

            throw new Exception($msg);
        }
    }
}

if (!function_exists('hitungReservedBatch')) {
    /**
     * Menghitung total stok reserved (sedang dicadangkan untuk pesanan aktif yang belum dikirim) untuk suatu Batch Obat/BMHP.
     */
    function hitungReservedBatch($pdo, $idBatch) {
        if (!$idBatch) return 0;
        try {
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(pba.qty_satuan_kecil), 0) AS total_reserved
                FROM pesanan_batch_alokasi pba
                JOIN penjualan p ON pba.id_penjualan = p.id
                WHERE pba.id_batch = ?
                  AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
            ");
            $stmt->execute([$idBatch]);
            return intval($stmt->fetchColumn() ?: 0);
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('hitungReservedAlkesVar')) {
    /**
     * Menghitung total unit reserved untuk variasi Alkes ber-Serial Number.
     */
    function hitungReservedAlkesVar($pdo, $idVariasi) {
        if (!$idVariasi) return 0;
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(us.id) AS total_reserved
                FROM unit_serial us
                JOIN penjualan p ON us.id_penjualan = p.id
                WHERE us.id_variasi = ?
                  AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
            ");
            $stmt->execute([$idVariasi]);
            return intval($stmt->fetchColumn() ?: 0);
        } catch (Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('ambilDaftarPesananTerdampakBatch')) {
    /**
     * Mengambil daftar nomor invoice pesanan aktif yang menggunakan batch tertentu jika terjadi defisit fisik.
     */
    function ambilDaftarPesananTerdampakBatch($pdo, $idBatch) {
        if (!$idBatch) return [];
        try {
            $stmt = $pdo->prepare("
                SELECT p.no_invoice, p.status_pesanan, pba.qty_satuan_kecil AS qty_pesan, p.nama_penerima
                FROM pesanan_batch_alokasi pba
                JOIN penjualan p ON pba.id_penjualan = p.id
                WHERE pba.id_batch = ?
                  AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
                ORDER BY p.id ASC
            ");
            $stmt->execute([$idBatch]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('ambilDaftarPesananTerdampakAlkes')) {
    /**
     * Mengambil daftar nomor invoice pesanan aktif yang menggunakan variasi Alkes tertentu jika terjadi defisit fisik.
     */
    function ambilDaftarPesananTerdampakAlkes($pdo, $idVariasi) {
        if (!$idVariasi) return [];
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT p.no_invoice, p.status_pesanan, p.nama_penerima, us.serial_number
                FROM unit_serial us
                JOIN penjualan p ON us.id_penjualan = p.id
                WHERE us.id_variasi = ?
                  AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
                ORDER BY p.id ASC
            ");
            $stmt->execute([$idVariasi]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

