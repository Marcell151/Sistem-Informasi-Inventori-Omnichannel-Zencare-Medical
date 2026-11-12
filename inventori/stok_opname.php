<?php
// File: inventori/stok_opname.php
// Modul Stok Opname Fisik Terpusat (Blind Count & Verifikasi Rekonsiliasi) - ZenCare Medical
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';
require_once __DIR__ . '/../config/opname_helper.php';

requireRole(['superadmin', 'admin', 'kasir']);

$role = $_SESSION['role'] ?? '';
$isSuperadmin = ($role === 'superadmin');
$isAdmin = ($role === 'admin');
$isKasir = ($role === 'kasir');
$canCountPhysical = ($isKasir || $isSuperadmin); // Kasir di lapangan & Superadmin (Owner IT)
$canVerify = ($isSuperadmin || $isAdmin); // Admin & Superadmin berwenang verifikasi & eksekusi mutasi
$userId = $_SESSION['user_id'] ?? 1;
$activeCabang = 1;

$msg = ''; $msgType = '';

// Ambil sesi aktif saat ini jika ada
$activeSession = cekSesiOpnameAktif($pdo);
$currentTahap = $activeSession['tahap'] ?? 'Penghitungan';

// --- AJAX: AMBIL DATA ANGKA HITUNG DEMO UNTUK PENGISIAN CEPAT (TANPA MEMBOCORKAN STOK DI HTML BLIND COUNT) ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_demo_counts') {
    header('Content-Type: application/json; charset=utf-8');
    if (!$activeSession) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada sesi opname aktif.']);
        exit;
    }
    
    $idSesi = $activeSession['id'];
    $stmtRows = $pdo->prepare("
        SELECT dob.id, dob.jenis_komoditas, dob.satuan_tipe, dob.stok_sistem_fisik,
               pi.nama_produk, pv.nama_variasi, sb.no_batch, sb.tgl_exp
        FROM detail_opname_batch dob
        JOIN produk_variasi pv ON dob.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
        WHERE dob.id_sesi = ?
        ORDER BY dob.id ASC
    ");
    $stmtRows->execute([$idSesi]);
    $allRows = $stmtRows->fetchAll(PDO::FETCH_ASSOC);

    $doneCef = false;
    $doneSanmol = false;
    $doneAmox = false;
    $doneOmron = false;
    $doneTermo = false;
    $doneBeta = false;

    $results = [];
    foreach ($allRows as $uRow) {
        $sistem = intval($uRow['stok_sistem_fisik']);
        $fBaik = $sistem;
        $fRusak = 0;
        $fExp = 0;
        $anomaliType = '';

        // 1. Cefadroxil 500mg (Satuan Kecil) -> Rusak 2 Strip
        if (!$doneCef && stripos($uRow['nama_produk'], 'Cefadroxil') !== false && $uRow['satuan_tipe'] === 'kecil') {
            $fBaik = max(0, $sistem - 2);
            $fRusak = 2;
            $fExp = 0;
            $doneCef = true;
            $anomaliType = 'rusak';
        }
        // 2. Sanmol Sirup 60ml -> Expired
        elseif (!$doneSanmol && stripos($uRow['nama_produk'], 'Sanmol') !== false && !empty($uRow['tgl_exp']) && strtotime($uRow['tgl_exp']) < strtotime(date('Y-m-d'))) {
            $fBaik = 0;
            $fRusak = 0;
            $fExp = $sistem;
            $doneSanmol = true;
            $anomaliType = 'expired';
        }
        // 3. Amoxicillin 500mg (Satuan Kecil) -> Hilang 2 Strip
        elseif (!$doneAmox && stripos($uRow['nama_produk'], 'Amoxicillin') !== false && $uRow['satuan_tipe'] === 'kecil') {
            $fBaik = max(0, $sistem - 2);
            $doneAmox = true;
            $anomaliType = 'hilang';
        }
        // 4. Tensimeter Digital Omron -> Hilang 1 Unit
        elseif (!$doneOmron && stripos($uRow['nama_produk'], 'Tensimeter') !== false) {
            $fBaik = max(0, $sistem - 1);
            $doneOmron = true;
            $anomaliType = 'hilang';
        }
        // 5. Termometer Digital Omron -> Rusak 1 Unit
        elseif (!$doneTermo && stripos($uRow['nama_produk'], 'Termometer Digital') !== false) {
            $fBaik = max(0, $sistem - 1);
            $fRusak = 1;
            $doneTermo = true;
            $anomaliType = 'rusak';
        }
        // 6. Betadine Solution 15ml (Satuan Kecil) -> Surplus +2 Botol
        elseif (!$doneBeta && stripos($uRow['nama_produk'], 'Betadine') !== false && $uRow['satuan_tipe'] === 'kecil') {
            $fBaik = $sistem + 2;
            $doneBeta = true;
            $anomaliType = 'surplus';
        }

        $results[] = [
            'id' => $uRow['id'],
            'baik' => $fBaik,
            'rusak' => $fRusak,
            'exp' => $fExp,
            'anomali' => ($anomaliType !== ''),
            'jenis' => $anomaliType
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $results]);
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// PROSES AKSI POST / REQUEST
// ─────────────────────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // --- RESET DATABASE KE BASELINE AWAL DENGAN CADANGAN OTOMATIS (UTILITAS DEMO) ---
    if ($aksi === 'reset_database_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset pangkalan data pengujian hanya dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } else {
            try {
                $backupDir = 'C:\\xampp\\backup_data';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0777, true);
                }
                
                $dbName = defined('DB_NAME') ? DB_NAME : 'db_inventory';
                $dbUser = defined('DB_USER') ? DB_USER : 'root';
                $passArg = (defined('DB_PASS') && DB_PASS !== '') ? "-p\"" . DB_PASS . "\"" : "";
                
                // 1. Cadangan otomatis sebelum menimpa data
                $backupFile = $backupDir . '\\backup_sebelum_reset_' . date('Ymd_His') . '.sql';
                $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
                if (file_exists($mysqldumpPath)) {
                    exec("\"$mysqldumpPath\" -u $dbUser $passArg $dbName > \"$backupFile\" 2>&1");
                }
                
                // 2. Pulihkan pangkalan data dari berkas dump bersih di luar folder web publik
                $cleanDump = $backupDir . '\\Demo_Bersih.sql';
                if (!file_exists($cleanDump)) {
                    $cleanDump = __DIR__ . '/../Demo_Bersih.sql';
                }
                
                $mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
                $restored = false;
                if (file_exists($mysqlPath) && file_exists($cleanDump)) {
                    $cleanDumpEscaped = str_replace('\\', '/', $cleanDump);
                    $cmd = "\"$mysqlPath\" -u $dbUser $passArg $dbName -e \"source $cleanDumpEscaped\" 2>&1";
                    $out = [];
                    $ret = 0;
                    exec($cmd, $out, $ret);
                    if ($ret === 0) {
                        $restored = true;
                    }
                }
                
                if (!$restored) {
                    require_once __DIR__ . '/../scratch/restore_baseline.php';
                    restoreBaselineInventory($pdo);
                }
                
                cekSesiOpnameAktif($pdo, true);
                header("Location: /inventory_zencare/inventori/stok_opname.php?msg=" . urlencode("Pangkalan data berhasil dipulihkan ke kondisi awal (Demo_Bersih.sql). Sesi simulasi telah dibersihkan dan cadangan tersimpan di backup_data.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal memulihkan data: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- JALANKAN SIMULASI DEMO PENGUJIAN OTOMATIS DARI AWAL HINGGA TUTUP SESI & BERITA ACARA ---
    elseif ($aksi === 'jalankan_simulasi_demo') {
        if (!$canVerify && !$isKasir) {
            $msg = "Akses ditolak!";
            $msgType = 'error';
        } else {
            try {
                $stopAtVerifikasi = false;
                ob_start();
                include __DIR__ . '/../scratch/run_full_opname_simulation.php';
                $simLog = ob_get_clean();
                cekSesiOpnameAktif($pdo, true);
                header("Location: /inventory_zencare/inventori/stok_opname.php?demo_selesai=" . intval($idSesi) . "&msg=" . urlencode("Uji coba testing penuh berhasil dijalankan dari awal hingga penutupan sesi opname! Seluruh 111 item telah dihitung, 6 item anomali selisih telah diverifikasi & dimutasi ke Kartu Stok, sesi resmi ditutup, dan Berita Acara telah diterbitkan di bawah ini.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal menjalankan simulasi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }
    elseif ($aksi === 'jalankan_simulasi_verifikasi') {
        if (!$canVerify && !$isKasir) {
            $msg = "Akses ditolak!";
            $msgType = 'error';
        } else {
            try {
                $stopAtVerifikasi = true;
                ob_start();
                include __DIR__ . '/../scratch/run_full_opname_simulation.php';
                $simLog = ob_get_clean();
                cekSesiOpnameAktif($pdo, true);
                header("Location: /inventory_zencare/inventori/stok_opname.php?status_eval=berselisih&msg=" . urlencode("Simulasi dibuka pada Tahap 2 (Verifikasi Selisih): 105 item klop disembunyikan otomatis dan 6 item anomali siap diverifikasi.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal menjalankan simulasi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 1. MULAI SESI STOK OPNAME (Khusus Superadmin) ---
    elseif ($aksi === 'mulai_sesi') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Hanya Superadmin (Pemilik/Manajer) yang berwenang memulai Sesi Stok Opname dan membekukan sistem.";
            $msgType = 'error';
        } elseif ($activeSession) {
            $msg = "Gagal! Masih ada sesi opname aktif ({$activeSession['kode_sesi']}). Selesaikan atau batalkan sesi tersebut terlebih dahulu.";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();

                $kodeSesi = 'OPN-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                $catatan = trim($_POST['catatan'] ?? '') ?: 'Sesi Stok Opname Berkala';

                $stmtSesi = $pdo->prepare("
                    INSERT INTO sesi_opname (kode_sesi, status, dibekukan_oleh, dibekukan_pada, catatan)
                    VALUES (?, 'Aktif', ?, NOW(), ?)
                ");
                $stmtSesi->execute([$kodeSesi, $userId, $catatan]);
                $idSesi = $pdo->lastInsertId();

                // Snapshot 1: Seluruh Batch Obat & BMHP yang memiliki stok fisik di toko (stok_sisa + reserved > 0)
                $stmtObat = $pdo->query("
                    SELECT sb.id AS id_batch, sb.id_variasi, sb.no_batch, sb.stok_sisa,
                           COALESCE((
                               SELECT SUM(pba.qty_satuan_kecil) 
                               FROM pesanan_batch_alokasi pba 
                               JOIN penjualan p ON pba.id_penjualan = p.id 
                               WHERE pba.id_batch = sb.id 
                                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
                           ), 0) AS stok_reserved_kecil,
                           pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.nama_produk, pi.kategori, pv.nama_variasi
                    FROM stok_batch sb
                    JOIN produk_variasi pv ON sb.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    WHERE pv.is_active = 1 
                      AND (sb.stok_sisa + COALESCE((
                               SELECT SUM(pba.qty_satuan_kecil) 
                               FROM pesanan_batch_alokasi pba 
                               JOIN penjualan p ON pba.id_penjualan = p.id 
                               WHERE pba.id_batch = sb.id 
                                 AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')
                           ), 0)) > 0
                    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC, sb.tgl_exp ASC
                ");
                $obatBatches = $stmtObat->fetchAll();

                $insDetail = $pdo->prepare("
                    INSERT INTO detail_opname_batch 
                    (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 'Belum Dimutasi')
                ");

                foreach ($obatBatches as $ob) {
                    $rasio = max(1, intval($ob['rasio_konversi']));
                    $isSubBatch = (strpos($ob['no_batch'], '.') !== false);
                    $stokFisikKecil = intval($ob['stok_sisa']) + intval($ob['stok_reserved_kecil']);

                    if ($rasio > 1 && !$isSubBatch) {
                        // BATCH INDUK (Kardus / Box / Dus Utuh di Gudang) -> Dihitung dalam Satuan Besar
                        $satuanTipe = 'besar';
                        $namaSatuan = $ob['satuan_besar'] ?: 'Dus';
                        $stokFisik = max(0, intval(floor($stokFisikKecil / $rasio)));
                        $stokTersedia = max(0, intval(floor(intval($ob['stok_sisa']) / $rasio)));
                        $stokReserved = max(0, intval(floor(intval($ob['stok_reserved_kecil']) / $rasio)));
                    } else {
                        // SUB-BATCH / ECERAN DI ETALASE / PRODUK SINGLE UOM -> Dihitung dalam Satuan Kecil
                        $satuanTipe = 'kecil';
                        $namaSatuan = $ob['satuan_kecil'] ?: 'Pcs';
                        $stokFisik = $stokFisikKecil;
                        $stokTersedia = intval($ob['stok_sisa']);
                        $stokReserved = intval($ob['stok_reserved_kecil']);
                    }

                    $jenisKomoditas = 'Obat'; // Batch-tracked (Obat & BMHP)

                    $insDetail->execute([
                        $idSesi,
                        $ob['id_batch'],
                        $ob['id_variasi'],
                        $jenisKomoditas,
                        $satuanTipe,
                        $namaSatuan,
                        $stokTersedia,
                        $stokReserved,
                        $stokFisik
                    ]);
                }

                // Snapshot 2: Seluruh Variasi Alat Kesehatan ber-Serial Number yang memiliki unit fisik di toko
                $stmtAlkes = $pdo->query("
                    SELECT pv.id AS id_variasi, pv.nama_variasi, pi.nama_produk, pv.satuan_besar, pv.satuan_kecil,
                           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_tersedia,
                           (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')) as sn_reserved
                    FROM produk_variasi pv
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    WHERE pv.is_active = 1 
                      AND ((SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') + 
                           (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil'))) > 0
                    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
                ");
                $alkesVars = $stmtAlkes->fetchAll();

                foreach ($alkesVars as $ak) {
                    $snTersedia = intval($ak['sn_tersedia']);
                    $snReserved = intval($ak['sn_reserved']);
                    $stokFisikAlkes = $snTersedia + $snReserved;
                    $namaSatuan = $ak['satuan_besar'] ?: ($ak['satuan_kecil'] ?: 'Unit');

                    $insDetail->execute([
                        $idSesi,
                        null, // Alkes tidak memiliki id_batch
                        $ak['id_variasi'],
                        'Alkes',
                        'besar', // Alkes seragam sebagai Satuan Besar (Kemasan Utuh)
                        $namaSatuan,
                        $snTersedia,
                        $snReserved,
                        $stokFisikAlkes
                    ]);
                }

                $pdo->commit();
                header("Location: stok_opname.php?msg=" . urlencode("Sesi Stok Opname $kodeSesi berhasil dimulai! Seluruh transaksi POS, checkout online, dan penerimaan telah DIBEKUKAN.") . "&type=success");
                exit;
            } catch (Exception $e) {
                if (isset($pdo)) $pdo->rollBack();
                $msg = "Gagal memulai sesi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2. SIMPAN HITUNG FISIK BERKONDISI (Hitung Buta: Baik, Rusak, Expired) ---
    elseif ($aksi === 'simpan_hitung_fisik') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $fisikBaikInput = $_POST['fisik_baik'] ?? '';
        $fisikRusakInput = intval($_POST['fisik_rusak'] ?? 0);
        $fisikExpInput   = intval($_POST['fisik_expired'] ?? 0);
        $isRecount = !empty($_POST['is_recount']);
        $alasanKoreksi = trim($_POST['alasan_koreksi'] ?? '');

        if (!$canCountPhysical) {
            $msg = "Akses ditolak! Sesuai SOP pengendalian internal gudang, staf gudang (Admin) dilarang melakukan penghitungan fisik untuk mencegah manipulasi stok. Penghitungan fisik wajib dilakukan oleh Kasir lapangan atau Superadmin.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif ($fisikBaikInput === '' || !is_numeric($fisikBaikInput) || intval($fisikBaikInput) < 0) {
            $msg = "Kuantitas kondisi Baik wajib diisi angka bulat non-negatif (>= 0)!";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();

                $stmtCheck = $pdo->prepare("
                    SELECT dob.*, so.kode_sesi, sb.tgl_exp
                    FROM detail_opname_batch dob
                    JOIN sesi_opname so ON dob.id_sesi = so.id
                    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                    WHERE dob.id = ? AND dob.id_sesi = ?
                    FOR UPDATE
                ");
                $stmtCheck->execute([$idDetail, $activeSession['id']]);
                $row = $stmtCheck->fetch();

                if (!$row) {
                    throw new Exception("Baris opname ID $idDetail tidak ditemukan pada sesi aktif.");
                }

                if ($row['status_mutasi'] === 'Sudah Dimutasi') {
                    throw new Exception("Baris ini sudah dimutasi secara permanen dan tidak dapat dihitung ulang.");
                }

                $fisikBaik = intval($fisikBaikInput);
                $fisikRusak = max(0, $fisikRusakInput);
                $fisikExpired = max(0, $fisikExpInput);

                // Aturan Bisnis Kedaluwarsa: Jika batch sudah melewati tanggal expired, opsi Baik = 0
                if ($row['jenis_komoditas'] === 'Obat' && $row['tgl_exp'] && strtotime($row['tgl_exp']) < strtotime(date('Y-m-d'))) {
                    $fisikExpired += $fisikBaik;
                    $fisikBaik = 0;
                }

                // Khusus Alkes: Alkes tidak memiliki tanggal expired
                if ($row['jenis_komoditas'] === 'Alkes') {
                    $fisikExpired = 0;
                }

                $hitungLama = ($row['hitung_fisik'] !== null) ? intval($row['hitung_fisik']) : null;

                // Evaluasi Reserved dinamis saat tombol Simpan ditekan
                if ($row['jenis_komoditas'] === 'Alkes') {
                    $reservedAktif = hitungReservedAlkesVar($pdo, $row['id_variasi']);
                } else {
                    $reservedAktif = hitungReservedBatch($pdo, $row['id_batch']);
                }

                $stokFisikSistem = intval($row['stok_sistem_fisik']);
                $stokTersediaSistem = $stokFisikSistem - $reservedAktif;
                $selisih = $fisikBaik - $stokFisikSistem;
                $statusMutasi = ($selisih === 0 && $fisikRusak === 0 && $fisikExpired === 0) ? 'Sesuai' : 'Belum Dimutasi';

                // Update baris opname dengan rincian kondisi fisik
                $stmtUp = $pdo->prepare("
                    UPDATE detail_opname_batch
                    SET fisik_baik = ?,
                        fisik_rusak = ?,
                        fisik_expired = ?,
                        hitung_fisik = ?,
                        selisih = ?,
                        stok_reserved = ?,
                        stok_tersedia_sistem = ?,
                        status_mutasi = ?,
                        status_hitung = 'Sudah',
                        dihitung_oleh = ?,
                        waktu_hitung = NOW()
                    WHERE id = ?
                ");
                $stmtUp->execute([
                    $fisikBaik,
                    $fisikRusak,
                    $fisikExpired,
                    $fisikBaik,
                    $selisih,
                    $reservedAktif,
                    $stokTersediaSistem,
                    $statusMutasi,
                    $userId,
                    $idDetail
                ]);

                // Catat riwayat perubahan jika ini adalah koreksi/hitung ulang
                if ($isRecount && $hitungLama !== null && $hitungLama !== $fisikBaik) {
                    $stmtLog = $pdo->prepare("
                        INSERT INTO riwayat_hitung_opname (id_detail_opname, hitung_sebelumnya, hitung_baru, dihitung_oleh, alasan_koreksi, waktu_koreksi)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtLog->execute([
                        $idDetail,
                        $hitungLama,
                        $fisikBaik,
                        $userId,
                        $alasanKoreksi ?: 'Koreksi hitung fisik di lapangan'
                    ]);
                }

                $pdo->commit();
                if ($isKasir) {
                    $msg = "Data hasil hitung fisik berhasil disimpan ke server. Tetap berada di halaman ini sampai sesi opname ditutup oleh Superadmin.";
                    $msgType = 'success';
                } else {
                    $msg = "Hasil fisik berhasil disimpan (Baik: $fisikBaik, Rusak: $fisikRusak, Expired: $fisikExpired).";
                    $msgType = 'success';
                }
            } catch (Exception $e) {
                if (isset($pdo)) $pdo->rollBack();
                $msg = "Gagal menyimpan hitung fisik: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2A2. SIMPAN SEMUA HASIL HITUNG FISIK SEKALIGUS (Bulk Save Fisik Kondisi) ---
    elseif ($aksi === 'simpan_semua_hitung_fisik') {
        if (!$canCountPhysical) {
            $msg = "Akses ditolak! Sesuai SOP pengendalian internal gudang, staf gudang (Admin) dilarang melakukan penghitungan fisik untuk mencegah manipulasi stok. Penghitungan fisik wajib dilakukan oleh Kasir lapangan atau Superadmin.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            $bulkItemIds = $_POST['bulk_item_ids'] ?? [];
            $bulkBaik = is_array($_POST['bulk_fisik_baik'] ?? null) ? $_POST['bulk_fisik_baik'] : [];
            $bulkRusak = is_array($_POST['bulk_fisik_rusak'] ?? null) ? $_POST['bulk_fisik_rusak'] : [];
            $bulkExpired = is_array($_POST['bulk_fisik_expired'] ?? null) ? $_POST['bulk_fisik_expired'] : [];

            $allIds = array_unique(array_filter(array_merge(
                is_array($bulkItemIds) ? array_map('intval', $bulkItemIds) : [],
                array_map('intval', array_keys($bulkBaik)),
                array_map('intval', array_keys($bulkRusak)),
                array_map('intval', array_keys($bulkExpired))
            )));

            if (empty($allIds)) {
                $msg = "Tidak ada data hitungan fisik yang dikirimkan.";
                $msgType = 'error';
            } else {
                try {
                    $pdo->beginTransaction();
                    $idSesi = $activeSession['id'];
                    $countSaved = 0;

                    $stmtCheck = $pdo->prepare("
                        SELECT dob.id, dob.stok_sistem_fisik, dob.jenis_komoditas, sb.tgl_exp
                        FROM detail_opname_batch dob
                        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                        WHERE dob.id = ? AND dob.id_sesi = ?
                    ");
                    $stmtUpdate = $pdo->prepare("
                        UPDATE detail_opname_batch
                        SET fisik_baik = ?,
                            fisik_rusak = ?,
                            fisik_expired = ?,
                            hitung_fisik = ?,
                            selisih = ?,
                            status_mutasi = ?,
                            status_hitung = 'Sudah',
                            dihitung_oleh = ?,
                            waktu_hitung = NOW()
                        WHERE id = ?
                    ");

                    // Cek apakah ini simpan parsial (sebagian baris normal diisi, sebagian masih kosong)
                    $totalNonExpRows = 0;
                    $filledNonExpRows = 0;
                    $cachedRows = [];
                    foreach ($allIds as $idDet) {
                        $idDet = intval($idDet);
                        if ($idDet <= 0) continue;
                        $stmtCheck->execute([$idDet, $idSesi]);
                        $rIt = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                        if (!$rIt) continue;
                        $cachedRows[$idDet] = $rIt;
                        $isExpRow = ($rIt['jenis_komoditas'] === 'Obat' && !empty($rIt['tgl_exp']) && strtotime($rIt['tgl_exp']) < strtotime(date('Y-m-d')));
                        if (!$isExpRow) {
                            $totalNonExpRows++;
                            $vb = isset($bulkBaik[$idDet]) ? trim((string)$bulkBaik[$idDet]) : '';
                            $vr = isset($bulkRusak[$idDet]) ? trim((string)$bulkRusak[$idDet]) : '';
                            $ve = isset($bulkExpired[$idDet]) ? trim((string)$bulkExpired[$idDet]) : '';
                            if ($vb !== '' || $vr !== '' || $ve !== '') {
                                $filledNonExpRows++;
                            }
                        }
                    }
                    $isPartialSave = ($totalNonExpRows > 0 && $filledNonExpRows > 0 && $filledNonExpRows < $totalNonExpRows);

                    foreach ($cachedRows as $idDet => $rowItem) {
                        $vBaik = isset($bulkBaik[$idDet]) ? trim((string)$bulkBaik[$idDet]) : '';
                        $vRusak = isset($bulkRusak[$idDet]) ? trim((string)$bulkRusak[$idDet]) : '';
                        $vExp = isset($bulkExpired[$idDet]) ? trim((string)$bulkExpired[$idDet]) : '';

                        $isItemExpired = ($rowItem['jenis_komoditas'] === 'Obat' && !empty($rowItem['tgl_exp']) && strtotime($rowItem['tgl_exp']) < strtotime(date('Y-m-d')));

                        if ($isItemExpired) {
                            // Logika Produk Kedaluwarsa (Expired Batch):
                            // Jika kasir mengisi rusak atau expired, gunakan nilai yang diinput kasir.
                            // Jika dikosongkan dan bukan simpan parsial baris lain, otomatis seluruh stok sistem dialokasikan ke kondisi expired.
                            $fBaik = 0;
                            if ($vExp !== '' || $vRusak !== '') {
                                $fRusak = ($vRusak !== '') ? max(0, intval($vRusak)) : 0;
                                $fExp = ($vExp !== '') ? max(0, intval($vExp)) : max(0, intval($rowItem['stok_sistem_fisik']) - $fRusak);
                            } elseif (!$isPartialSave && ($totalNonExpRows === 0 || $filledNonExpRows > 0)) {
                                $fRusak = 0;
                                $fExp = intval($rowItem['stok_sistem_fisik']);
                            } else {
                                continue;
                            }
                        } else {
                            // Produk Normal / Non-Expired:
                            // Jika seluruh kolom input kosong, lewati agar baris tetap berstatus Belum Dihitung (NULL)
                            if ($vBaik === '' && $vRusak === '' && $vExp === '') continue;

                            $fBaik = ($vBaik !== '') ? max(0, intval($vBaik)) : 0;
                            $fRusak = ($vRusak !== '') ? max(0, intval($vRusak)) : 0;
                            $fExp = ($vExp !== '') ? max(0, intval($vExp)) : 0;
                            if ($rowItem['jenis_komoditas'] === 'Alkes') {
                                $fExp = 0;
                            }
                        }

                        $stokSistem = intval($rowItem['stok_sistem_fisik']);
                        $selisih = $fBaik - $stokSistem;
                        $statusMut = ($selisih === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';

                        $stmtUpdate->execute([$fBaik, $fRusak, $fExp, $fBaik, $selisih, $statusMut, $userId, $idDet]);
                        $countSaved++;
                    }

                    if ($countSaved === 0) {
                        $pdo->rollBack();
                        $msg = "Belum ada angka fisik yang diinput! Masukkan hitungan fisik minimal pada 1 produk terlebih dahulu sebelum menyimpan.";
                        $msgType = 'error';
                    } else {
                        $pdo->commit();
                        if ($isKasir) {
                            $msg = "Data hitung fisik untuk $countSaved item berhasil disimpan ke server. Tetap berada di halaman ini sampai sesi opname ditutup oleh Superadmin.";
                            $msgType = 'success';
                        } else {
                            $msg = "Berhasil menyimpan hasil hitung fisik untuk $countSaved item! Data siap ditutup untuk verifikasi.";
                            $msgType = 'success';
                        }
                    }
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $msg = "Gagal menyimpan hitungan fisik masal: " . $e->getMessage();
                    $msgType = 'error';
                }
            }
        }
    }

    // --- 2A3. BUKA VERIFIKASI SELISIH (Admin & Superadmin) ---
    elseif ($aksi === 'tutup_penghitungan') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang membuka verifikasi selisih.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $idSesi = $activeSession['id'];

                // Validasi ketat: Kasir harus menyelesaikan perhitungan fisik rak terlebih dahulu
                $stmtCheckUncounted = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM detail_opname_batch 
                    WHERE id_sesi = ? AND hitung_fisik IS NULL
                ");
                $stmtCheckUncounted->execute([$idSesi]);
                $uncountedCount = (int)$stmtCheckUncounted->fetchColumn();

                if ($uncountedCount > 0) {
                    $msg = "Tahap verifikasi belum dapat dibuka! Masih ada $uncountedCount item yang belum dihitung fisiknya oleh kasir. Harap tunggu tim kasir menyelesaikan dan menyimpan seluruh hitungan fisik.";
                    $msgType = 'error';
                } else {
                    $pdo->prepare("
                        UPDATE sesi_opname
                        SET tahap = 'Verifikasi',
                            ditutup_hitung_oleh = ?,
                            ditutup_hitung_pada = NOW()
                        WHERE id = ?
                    ")->execute([$userId, $idSesi]);

                    header("Location: stok_opname.php?status_eval=berselisih&msg=" . urlencode("Tahap Verifikasi Selisih berhasil dibuka! Formulir pencacahan kasir terkunci dan antarmuka verifikasi aktif. Barang yang sesuai otomatis disembunyikan.") . "&type=success");
                    exit;
                }
            } catch (Exception $e) {
                $msg = "Gagal membuka verifikasi selisih: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2B. AUTO-FILL SKENARIO PENGUJIAN (Khusus Superadmin / Simulasi Uji Coba) ---
    elseif ($aksi === 'isi_cepat_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Fitur auto-generate skenario uji coba hanya diperuntukkan bagi Admin dan Superadmin.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $idSesi = $activeSession['id'];
                
                // Ambil seluruh baris pada sesi ini beserta informasi produk untuk skenario anomali
                $stmtRows = $pdo->prepare("
                    SELECT dob.id, dob.jenis_komoditas, dob.satuan_tipe, dob.stok_sistem_fisik,
                           pi.nama_produk, pv.nama_variasi, sb.no_batch, sb.tgl_exp
                    FROM detail_opname_batch dob
                    JOIN produk_variasi pv ON dob.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                    WHERE dob.id_sesi = ?
                    ORDER BY dob.id ASC
                ");
                $stmtRows->execute([$idSesi]);
                $allRows = $stmtRows->fetchAll(PDO::FETCH_ASSOC);

                $updatedCount = 0;
                $diffCount = 0;
                $stmtUp = $pdo->prepare("
                    UPDATE detail_opname_batch
                    SET fisik_baik = ?,
                        fisik_rusak = ?,
                        fisik_expired = ?,
                        hitung_fisik = ?,
                        selisih = ?,
                        status_mutasi = ?,
                        status_hitung = 'Sudah',
                        dihitung_oleh = ?,
                        waktu_hitung = NOW()
                    WHERE id = ?
                ");

                $doneCef = false;
                $doneSanmol = false;
                $doneAmox = false;
                $doneOmron = false;
                $doneTermo = false;
                $doneBeta = false;

                foreach ($allRows as $uRow) {
                    $sistem = intval($uRow['stok_sistem_fisik']);
                    $fBaik = $sistem; // Default: Cocok (selisih 0)
                    $fRusak = 0;
                    $fExp = 0;

                    // 1. SKENARIO BARANG RUSAK: Cefadroxil 500mg (Satuan Kecil) -> Rusak 2 Strip
                    if (!$doneCef && stripos($uRow['nama_produk'], 'Cefadroxil') !== false && $uRow['satuan_tipe'] === 'kecil') {
                        $fBaik = max(0, $sistem - 2);
                        $fRusak = 2;
                        $fExp = 0;
                        $doneCef = true;
                    }
                    // 2. SKENARIO BATCH KEDALUWARSA: Sanmol Sirup 60ml -> Kedaluwarsa
                    elseif (!$doneSanmol && stripos($uRow['nama_produk'], 'Sanmol') !== false && !empty($uRow['tgl_exp']) && strtotime($uRow['tgl_exp']) < strtotime(date('Y-m-d'))) {
                        $fBaik = 0;
                        $fRusak = 0;
                        $fExp = $sistem;
                        $doneSanmol = true;
                    }
                    // 3. SKENARIO BARANG HILANG: Amoxicillin 500mg (Satuan Kecil) -> Hilang 2 Strip
                    elseif (!$doneAmox && stripos($uRow['nama_produk'], 'Amoxicillin') !== false && $uRow['satuan_tipe'] === 'kecil') {
                        $fBaik = max(0, $sistem - 2);
                        $doneAmox = true;
                    }
                    // 4. SKENARIO ALKES KURANG SN HILANG: Tensimeter Omron -> Hilang 1 Unit
                    elseif (!$doneOmron && stripos($uRow['nama_produk'], 'Tensimeter') !== false) {
                        $fBaik = max(0, $sistem - 1);
                        $doneOmron = true;
                    }
                    // 5. SKENARIO ALKES RUSAK FISIK: Termometer Digital Omron -> Rusak 1 Unit
                    elseif (!$doneTermo && stripos($uRow['nama_produk'], 'Termometer Digital') !== false) {
                        $fBaik = max(0, $sistem - 1);
                        $fRusak = 1;
                        $doneTermo = true;
                    }
                    // 6. SKENARIO KOREKSI SURPLUS: Betadine Solution 15ml (Satuan Kecil) -> Surplus +2 Botol
                    elseif (!$doneBeta && stripos($uRow['nama_produk'], 'Betadine') !== false && $uRow['satuan_tipe'] === 'kecil') {
                        $fBaik = $sistem + 2;
                        $doneBeta = true;
                    }

                    $selisih = $fBaik - $sistem;
                    $statusMutasi = ($selisih === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';
                    if ($selisih !== 0 || $fRusak > 0 || $fExp > 0) $diffCount++;

                    $stmtUp->execute([$fBaik, $fRusak, $fExp, $fBaik, $selisih, $statusMutasi, $userId, $uRow['id']]);
                    $updatedCount++;
                }

                // Otomatis tutup penghitungan dan buka verifikasi agar siap dipamerkan
                $pdo->prepare("
                    UPDATE sesi_opname
                    SET tahap = 'Verifikasi',
                        ditutup_hitung_oleh = ?,
                        ditutup_hitung_pada = NOW()
                    WHERE id = ?
                ")->execute([$userId, $idSesi]);

                header("Location: stok_opname.php?status_eval=berselisih&msg=" . urlencode("Skenario Uji Coba Berhasil Di-Generate! Sebanyak $updatedCount entitas fisik terisi (6 anomali selisih: Cefadroxil Rusak, Sanmol Expired, Amoxicillin Hilang, Tensimeter Hilang, Termometer Rusak, Betadine Surplus). Sistem otomatis beralih ke tahap Verifikasi dan menyembunyikan 115 barang cocok.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal menjalankan auto-fill demo: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2B2. MUTASI 1-KLIK KONDISI (Rusak & Expired Sesuai Temuan Pencacah) ---
    elseif ($aksi === 'mutasi_1klik_rusak') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin dan Superadmin yang berwenang mengeksekusi mutasi.";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();
                $stmtD = $pdo->prepare("
                    SELECT dob.*, pv.nama_variasi, pv.rasio_konversi, pi.nama_produk, sb.no_batch
                    FROM detail_opname_batch dob
                    JOIN produk_variasi pv ON dob.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                    WHERE dob.id = ? AND dob.id_sesi = ? FOR UPDATE
                ");
                $stmtD->execute([$idDetail, $activeSession['id']]);
                $item = $stmtD->fetch();

                if (!$item || $item['fisik_rusak'] <= 0) {
                    throw new Exception("Tidak ada kuantitas barang rusak pada baris ini.");
                }

                $qtyRusak = intval($item['fisik_rusak']);
                $rasio = max(1, intval($item['rasio_konversi'] ?? 1));
                $qtyPcs = ($item['satuan_tipe'] === 'besar') ? ($qtyRusak * $rasio) : $qtyRusak;
                $kodeSesi = $activeSession['kode_sesi'];

                if ($item['jenis_komoditas'] === 'Obat') {
                    $pdo->prepare("UPDATE stok_batch SET stok_sisa = GREATEST(0, stok_sisa - ?) WHERE id = ?")->execute([$qtyPcs, $item['id_batch']]);
                    $pdo->prepare("UPDATE stok_toko SET stok = GREATEST(0, stok - ?) WHERE id_variasi = ?")->execute([$qtyPcs, $item['id_variasi']]);
                    $sisaToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$item['id_variasi']}")->fetchColumn();

                    $ket = "[Stok Opname: $kodeSesi] Temuan Pencacah: $qtyRusak {$item['nama_satuan']} Rusak Fisik di Rak/Gudang";
                    $pdo->prepare("
                        INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                        VALUES (?, ?, ?, 'Keluar', 'Manual', 'Barang Rusak', ?, ?, ?, ?, ?)
                    ")->execute([$item['id_variasi'], $item['id_batch'], $item['satuan_tipe'], $kodeSesi, $qtyRusak, $sisaToko, $ket, $userId]);
                } else {
                    $pdo->prepare("UPDATE stok_toko SET stok = GREATEST(0, stok - ?) WHERE id_variasi = ?")->execute([$qtyRusak, $item['id_variasi']]);
                }

                // Perbarui status detail
                $sisaDefisit = intval($item['stok_sistem_fisik']) - intval($item['fisik_baik']) - $qtyRusak - intval($item['fisik_expired']);
                $statusMut = ($item['fisik_expired'] == 0 && $sisaDefisit <= 0) ? 'Sudah Dimutasi' : 'Belum Dimutasi';

                $pdo->prepare("
                    UPDATE detail_opname_batch
                    SET alasan_mutasi = 'Mutasi Barang Rusak',
                        status_mutasi = ?,
                        dimutasi_oleh = ?,
                        waktu_mutasi = NOW()
                    WHERE id = ?
                ")->execute([$statusMut, $userId, $idDetail]);

                $pdo->commit();
                $msg = "Mutasi Barang Rusak ($qtyRusak {$item['nama_satuan']}) untuk {$item['nama_produk']} berhasil dieksekusi 1-klik dan dicatat di Kartu Stok!";
                $msgType = 'success';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $msg = "Gagal memproses mutasi rusak: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2B3. MUTASI 1-KLIK EXPIRED (Barang Kedaluwarsa Sesuai Temuan Pencacah) ---
    elseif ($aksi === 'mutasi_1klik_expired') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin dan Superadmin yang berwenang mengeksekusi mutasi.";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();
                $stmtD = $pdo->prepare("
                    SELECT dob.*, pv.nama_variasi, pv.rasio_konversi, pi.nama_produk, sb.no_batch
                    FROM detail_opname_batch dob
                    JOIN produk_variasi pv ON dob.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                    WHERE dob.id = ? AND dob.id_sesi = ? FOR UPDATE
                ");
                $stmtD->execute([$idDetail, $activeSession['id']]);
                $item = $stmtD->fetch();

                if (!$item || $item['fisik_expired'] <= 0) {
                    throw new Exception("Tidak ada kuantitas barang kedaluwarsa pada baris ini.");
                }

                $qtyExp = intval($item['fisik_expired']);
                $rasio = max(1, intval($item['rasio_konversi'] ?? 1));
                $qtyPcs = ($item['satuan_tipe'] === 'besar') ? ($qtyExp * $rasio) : $qtyExp;
                $kodeSesi = $activeSession['kode_sesi'];

                if ($item['jenis_komoditas'] === 'Obat') {
                    $pdo->prepare("UPDATE stok_batch SET stok_sisa = GREATEST(0, stok_sisa - ?) WHERE id = ?")->execute([$qtyPcs, $item['id_batch']]);
                    $pdo->prepare("UPDATE stok_toko SET stok = GREATEST(0, stok - ?) WHERE id_variasi = ?")->execute([$qtyPcs, $item['id_variasi']]);
                    $sisaToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$item['id_variasi']}")->fetchColumn();

                    $ket = "[Stok Opname: $kodeSesi] Temuan Pencacah: $qtyExp {$item['nama_satuan']} Kedaluwarsa (Expired) saat Opname";
                    $pdo->prepare("
                        INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                        VALUES (?, ?, ?, 'Keluar', 'Manual', 'Barang Kedaluwarsa', ?, ?, ?, ?, ?)
                    ")->execute([$item['id_variasi'], $item['id_batch'], $item['satuan_tipe'], $kodeSesi, $qtyExp, $sisaToko, $ket, $userId]);
                }

                $sisaDefisit = intval($item['stok_sistem_fisik']) - intval($item['fisik_baik']) - intval($item['fisik_rusak']) - $qtyExp;
                $statusMut = ($item['fisik_rusak'] == 0 && $sisaDefisit <= 0) ? 'Sudah Dimutasi' : 'Belum Dimutasi';

                $pdo->prepare("
                    UPDATE detail_opname_batch
                    SET alasan_mutasi = 'Mutasi Barang Kedaluwarsa',
                        status_mutasi = ?,
                        dimutasi_oleh = ?,
                        waktu_mutasi = NOW()
                    WHERE id = ?
                ")->execute([$statusMut, $userId, $idDetail]);

                $pdo->commit();
                $msg = "Mutasi Barang Kedaluwarsa ($qtyExp {$item['nama_satuan']}) untuk {$item['nama_produk']} berhasil dieksekusi 1-klik dan dicatat di Kartu Stok!";
                $msgType = 'success';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $msg = "Gagal memproses mutasi expired: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2C. REKONSILIASI SEMUA SELISIH SEKALIGUS (Mass Reconciliation) ---
    elseif ($aksi === 'rekonsiliasi_masal') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin dan Superadmin yang berwenang melakukan rekonsiliasi mutasi.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();
                $idSesi = $activeSession['id'];
                $kodeSesi = $activeSession['kode_sesi'];
                $alasanDefault = trim($_POST['alasan_mutasi_masal'] ?? 'Selisih Stok Opname');
                if (empty($alasanDefault)) $alasanDefault = 'Selisih Stok Opname';
                $ketMasal = trim($_POST['keterangan_masal'] ?? 'Rekonsiliasi masal hasil pemeriksaan fisik');

                $stmtPending = $pdo->prepare("
                    SELECT dob.*, pv.nama_variasi, pv.rasio_konversi, pi.nama_produk, sb.no_batch
                    FROM detail_opname_batch dob
                    JOIN produk_variasi pv ON dob.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                    WHERE dob.id_sesi = ? AND dob.hitung_fisik IS NOT NULL AND dob.selisih != 0 AND dob.status_mutasi != 'Sudah Dimutasi'
                    FOR UPDATE
                ");
                $stmtPending->execute([$idSesi]);
                $pendingItems = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

                if (empty($pendingItems)) {
                    throw new Exception("Tidak ada item berselisih yang memerlukan rekonsiliasi mutasi.");
                }

                $countMutated = 0;
                foreach ($pendingItems as $pIt) {
                    $selisih = intval($pIt['selisih']);
                    $absSelisih = abs($selisih);
                    $isMinus = ($selisih < 0);
                    $idVariasi = $pIt['id_variasi'];
                    $rasio = max(1, intval($pIt['rasio_konversi'] ?? 1));
                    $fRusakRow = intval($pIt['fisik_rusak'] ?? 0);
                    $fExpRow   = intval($pIt['fisik_expired'] ?? 0);

                    if (!$isMinus) {
                        $alasanRow = 'Kelebihan Kirim Supplier';
                    } elseif ($fRusakRow > 0) {
                        $alasanRow = 'Barang Rusak';
                    } elseif ($fExpRow > 0) {
                        $alasanRow = 'Barang Kedaluwarsa';
                    } else {
                        $alasanRow = 'Barang Hilang';
                    }

                    if ($pIt['jenis_komoditas'] === 'Obat') {
                        $idBatch = $pIt['id_batch'];
                        $qtyPcs = ($pIt['satuan_tipe'] === 'besar') ? ($absSelisih * $rasio) : $absSelisih;
                        $sqlOp = $isMinus ? '-' : '+';

                        $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa $sqlOp ? WHERE id = ?")
                            ->execute([$qtyPcs, $idBatch]);
                        $pdo->prepare("UPDATE stok_toko SET stok = stok $sqlOp ? WHERE id_variasi = ?")
                            ->execute([$qtyPcs, $idVariasi]);

                        $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();
                        $jMutasi = $isMinus ? 'Keluar' : 'Masuk';
                        $satuanLabel = ($pIt['satuan_tipe'] === 'besar') ? 'Satuan Besar' : 'Satuan Kecil';
                        $ketKartu = "[Stok Opname: $kodeSesi] Batch: {$pIt['no_batch']} ($satuanLabel) | $alasanRow" . ($ketMasal ? " - $ketMasal" : "");

                        $stmtKartu = $pdo->prepare("
                            INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                            VALUES (?, ?, ?, ?, 'Manual', ?, ?, ?, ?, ?, ?)
                        ");
                        $stmtKartu->execute([$idVariasi, $idBatch, $pIt['satuan_tipe'], $jMutasi, $alasanRow, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                    } else {
                        // Alkes (Serial Number)
                        if ($isMinus) {
                            $stmtSn = $pdo->prepare("SELECT id, serial_number FROM unit_serial WHERE id_variasi = ? AND status = 'Tersedia' LIMIT ?");
                            $stmtSn->bindValue(1, $idVariasi, PDO::PARAM_INT);
                            $stmtSn->bindValue(2, $absSelisih, PDO::PARAM_INT);
                            $stmtSn->execute();
                            $targetSnList = $stmtSn->fetchAll(PDO::FETCH_ASSOC);

                            $mutatedSnList = [];
                            $jTindakanSn = ($fRusakRow > 0) ? 'Kurang_Rusak' : 'Hapus_Hilang';
                            $statusSnBaru = ($fRusakRow > 0) ? 'Retur/Rusak' : 'Hilang';
                            $stmtInsDos = $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, ?)");
                            $stmtUpSn = $pdo->prepare("UPDATE unit_serial SET status = ?, catatan = CONCAT(COALESCE(catatan,''), ' | Opname $kodeSesi: $alasanRow') WHERE id = ?");

                            foreach ($targetSnList as $tsn) {
                                $stmtUpSn->execute([$statusSnBaru, $tsn['id']]);
                                $stmtInsDos->execute([$pIt['id'], $tsn['id'], $tsn['serial_number'], $jTindakanSn]);
                                $mutatedSnList[] = $tsn['serial_number'];
                            }

                            $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$absSelisih, $idVariasi]);
                            $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                            $ketKartu = "[Stok Opname: $kodeSesi] SN: " . implode(', ', $mutatedSnList) . " | $alasanRow" . ($ketMasal ? " - $ketMasal" : "");
                            $stmtKartu = $pdo->prepare("
                                INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                                VALUES (?, NULL, 'kecil', 'Keluar', 'Manual', ?, ?, ?, ?, ?, ?)
                            ");
                            $stmtKartu->execute([$idVariasi, $alasanRow, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                        } else {
                            $stmtInsDos = $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, 'Tambah_Ditemukan')");
                            $newSnArr = [];
                            for ($k = 1; $k <= $absSelisih; $k++) {
                                $autoSn = "SN-OPN-" . strtoupper(substr(md5(uniqid($pIt['id'] . $k, true)), 0, 8));
                                $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number, status, catatan) VALUES (?, ?, 'Tersedia', ?)")
                                    ->execute([$idVariasi, $autoSn, "Ditemukan saat Rekonsiliasi Opname $kodeSesi"]);
                                $newInsertedId = $pdo->lastInsertId();
                                $stmtInsDos->execute([$pIt['id'], $newInsertedId, $autoSn]);
                                $newSnArr[] = $autoSn;
                            }

                            $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$absSelisih, $idVariasi]);
                            $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                            $ketKartu = "[Stok Opname: $kodeSesi] SN Ditemukan: " . implode(', ', $newSnArr) . " | $alasanRow" . ($ketMasal ? " - $ketMasal" : "");
                            $stmtKartu = $pdo->prepare("
                                INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                                VALUES (?, NULL, 'kecil', 'Masuk', 'Manual', ?, ?, ?, ?, ?, ?)
                            ");
                            $stmtKartu->execute([$idVariasi, $alasanRow, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                        }
                    }

                    $pdo->prepare("
                        UPDATE detail_opname_batch
                        SET status_mutasi = 'Sudah Dimutasi',
                            alasan_mutasi = ?,
                            keterangan_mutasi = ?,
                            dimutasi_oleh = ?,
                            waktu_mutasi = NOW()
                        WHERE id = ?
                    ")->execute([$alasanRow, $ketMasal, $userId, $pIt['id']]);

                    $countMutated++;
                }

                $pdo->commit();
                $msg = "Rekonsiliasi Masal Berhasil! Sebanyak $countMutated baris selisih telah disesuaikan ke stok sistem dan dicatat di Kartu Stok. Sesi kini 100% klop dan siap diselesaikan.";
                $msgType = 'success';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $msg = "Gagal memproses rekonsiliasi masal: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2.5 KOREKSI HITUNGAN FISIK KHUSUS SUPERADMIN (Jalur Rekonsiliasi & Kesalahan Lapangan) ---
    elseif ($aksi === 'koreksi_hitung_superadmin') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $alasanKoreksi = trim($_POST['alasan_koreksi'] ?? '');

        if (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif (!$isSuperadmin) {
            $msg = "Akses ditolak! Koreksi data opname yang sudah sesuai hanya berhak dilakukan oleh Superadmin (Pemilik Toko).";
            $msgType = 'error';
        } elseif (!$idDetail) {
            $msg = "Data baris tidak valid.";
            $msgType = 'error';
        } else {
            try {
                $fBaik = max(0, intval($_POST['koreksi_fisik_baik'] ?? 0));
                $fRusak = max(0, intval($_POST['koreksi_fisik_rusak'] ?? 0));
                $fExp = max(0, intval($_POST['koreksi_fisik_expired'] ?? 0));
                $totalFisik = $fBaik + $fRusak + $fExp;

                // Ambil stok sistem fisik
                $stmtChk = $pdo->prepare("SELECT stok_sistem_fisik FROM detail_opname_batch WHERE id = ? AND id_sesi = ?");
                $stmtChk->execute([$idDetail, $activeSession['id']]);
                $rowChk = $stmtChk->fetch();

                if (!$rowChk) throw new Exception("Data baris opname tidak ditemukan.");

                $stokSistem = intval($rowChk['stok_sistem_fisik']);
                $selisihBaru = $totalFisik - $stokSistem;
                $statusMutasi = ($selisihBaru === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';

                $stmtUpd = $pdo->prepare("
                    UPDATE detail_opname_batch 
                    SET hitung_fisik = ?,
                        fisik_baik = ?,
                        fisik_rusak = ?,
                        fisik_expired = ?,
                        selisih = ?,
                        status_mutasi = ?,
                        catatan_kondisi = CONCAT(COALESCE(catatan_kondisi, ''), ' [Koreksi Superadmin: ', ?, ']')
                    WHERE id = ? AND id_sesi = ?
                ");
                $stmtUpd->execute([
                    $totalFisik,
                    $fBaik,
                    $fRusak,
                    $fExp,
                    $selisihBaru,
                    $statusMutasi,
                    $alasanKoreksi ?: 'Koreksi kesalahan hitung lapangan',
                    $idDetail,
                    $activeSession['id']
                ]);

                $msg = "Koreksi hitungan fisik berhasil disimpan oleh Superadmin! Data persediaan telah disesuaikan.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal menyimpan koreksi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- RESET DATABASE KE FRESH BASELINE (Admin & Superadmin untuk Keperluan Demo) ---
    elseif ($aksi === 'reset_database') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset database pengujian hanya dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } else {
            try {
                restoreBaselineInventory($pdo);
                cekSesiOpnameAktif($pdo, true);
                header("Location: stok_opname.php?msg=" . urlencode("Database inventori berhasil dipulihkan ke Baseline Bersih (Fresh State)! Seluruh data uji coba opname telah dibersihkan.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal mereset database: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 3. EKSEKUSI MUTASI SELISIH BARIS (Admin & Superadmin) ---
    elseif ($aksi === 'eksekusi_mutasi_baris') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $keterangan = trim($_POST['keterangan_mutasi'] ?? '');

        if (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif (!$canVerify) {
            $msg = "Akses ditolak! Kasir / Petugas Pencacah Lapangan hanya bertugas menghitung fisik (Blind Count). Mutasi selisih mutlak wewenang Admin atau Superadmin.";
            $msgType = 'error';
        } elseif (!$idDetail) {
            $msg = "Data baris opname tidak valid!";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();

                $stmtD = $pdo->prepare("
                    SELECT dob.*, pv.nama_variasi, pv.rasio_konversi, pi.nama_produk, pi.kategori, sb.no_batch, sb.stok_sisa, st.stok AS stok_toko_saat_ini
                    FROM detail_opname_batch dob
                    JOIN produk_variasi pv ON dob.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                    LEFT JOIN stok_toko st ON dob.id_variasi = st.id_variasi
                    WHERE dob.id = ? AND dob.id_sesi = ?
                    FOR UPDATE
                ");
                $stmtD->execute([$idDetail, $activeSession['id']]);
                $item = $stmtD->fetch();

                if (!$item) throw new Exception("Data detail opname tidak ditemukan.");
                if ($item['hitung_fisik'] === null) throw new Exception("Baris ini belum dihitung fisiknya!");
                if ($item['status_mutasi'] === 'Sudah Dimutasi') throw new Exception("Baris ini sudah dimutasi sebelumnya.");
                if (intval($item['selisih']) === 0) throw new Exception("Selisih 0 unit (Sesuai), tidak memerlukan mutasi.");

                $selisih = intval($item['selisih']);
                $absSelisih = abs($selisih);
                $isMinus = ($selisih < 0);
                $kodeSesi = $activeSession['kode_sesi'];

                // Evaluasi Distribusi / Alokasi Alasan Selisih
                $breakdown = [];
                if ($isMinus) {
                    $qtyRusak = max(0, intval($_POST['alokasi_rusak'] ?? 0));
                    $qtyHilang = max(0, intval($_POST['alokasi_hilang'] ?? 0));
                    $qtyExpired = max(0, intval($_POST['alokasi_expired'] ?? 0));
                    $qtySalahCatat = max(0, intval($_POST['alokasi_salah_catat'] ?? 0));
                    $qtyLainnya = max(0, intval($_POST['alokasi_lainnya'] ?? 0));
                    $teksLainnya = trim($_POST['teks_alasan_lainnya'] ?? '');
                    if ($qtyLainnya > 0 && empty($teksLainnya)) {
                        $teksLainnya = 'Alasan Lainnya (Kustom)';
                    }
                    $totalAlokasi = $qtyRusak + $qtyHilang + $qtyExpired + $qtySalahCatat + $qtyLainnya;

                    if ($totalAlokasi > 0) {
                        if ($totalAlokasi !== $absSelisih) {
                            throw new Exception("Total alokasi alasan ($totalAlokasi {$item['nama_satuan']}) tidak sesuai dengan selisih defisit ($absSelisih {$item['nama_satuan']})!");
                        }
                        if ($qtyRusak > 0) $breakdown['Barang Rusak'] = $qtyRusak;
                        if ($qtyHilang > 0) $breakdown['Barang Hilang'] = $qtyHilang;
                        if ($qtyExpired > 0) $breakdown['Barang Kedaluwarsa'] = $qtyExpired;
                        if ($qtySalahCatat > 0) $breakdown['Koreksi Salah Catat / Ditemukan'] = $qtySalahCatat;
                        if ($qtyLainnya > 0) $breakdown[$teksLainnya] = $qtyLainnya;
                    } else {
                        // Fallback single reason
                        $alasanSingle = trim($_POST['alasan_mutasi'] ?? 'Barang Rusak');
                        if (empty($alasanSingle)) $alasanSingle = 'Barang Rusak';
                        $breakdown[$alasanSingle] = $absSelisih;
                    }
                } else {
                    // Surplus (+): Selisih positif dialokasikan sesuai pilihan alasan kelebihan stok
                    $surplusReason = trim($_POST['alasan_mutasi_surplus'] ?? 'Koreksi Salah Catat / Ditemukan');
                    if ($surplusReason === 'Lainnya') {
                        $surplusCustom = trim($_POST['teks_surplus_lainnya'] ?? '');
                        $surplusReason = !empty($surplusCustom) ? $surplusCustom : 'Koreksi Fisik Lebih (Lainnya)';
                    }
                    $breakdown[$surplusReason] = $absSelisih;
                }

                // ── A. MUTASI BERBASIS BATCH (Obat & Alkes BMHP / Habis Pakai) ─────────────
                if (!empty($item['id_batch'])) {
                    // Batch selalu terkunci pada batch baris opname ini
                    $idBatch = $item['id_batch'];
                    $noBatchTerdampak = $item['no_batch'] ?? '-';

                    $idVariasi = $item['id_variasi'];
                    $rasio = max(1, intval($item['rasio_konversi'] ?? 1));
                    $isBesar = ($item['satuan_tipe'] === 'besar');
                    $qtyPcs = $isBesar ? ($absSelisih * $rasio) : $absSelisih;

                    // Update stok_batch & stok_toko
                    $operator = $isMinus ? '-' : '+';
                    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa $operator ? WHERE id = ?")
                        ->execute([$qtyPcs, $idBatch]);
                    $pdo->prepare("UPDATE stok_toko SET stok = stok $operator ? WHERE id_variasi = ?")
                        ->execute([$qtyPcs, $idVariasi]);

                    // Hitung saldo akhir untuk kartu stok
                    $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();
                    $jMutasi = $isMinus ? 'Keluar' : 'Masuk';

                    // Rekam Kartu Stok untuk setiap alasan yang memiliki kuantitas
                    $stmtKartu = $pdo->prepare("
                        INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                        VALUES (?, ?, ?, ?, 'Manual', ?, ?, ?, ?, ?, ?)
                    ");

                    $allowedAlasanKartu = [
                        'Penerimaan Barang', 'Penjualan POS', 'Penjualan E-Commerce', 'Retur Barang Rusak',
                        'Klaim Garansi SN', 'Selisih Stok Opname', 'Barang Rusak', 'Barang Kedaluwarsa',
                        'Barang Hilang', 'Koreksi Salah Catat / Ditemukan', 'Koreksi Manual'
                    ];

                    $summaryAlasanArr = [];
                    foreach ($breakdown as $reasonName => $rQty) {
                        $rQtyPcs = $isBesar ? ($rQty * $rasio) : $rQty;
                        $ketPcs = $isBesar ? " ($rQty {$item['nama_satuan']} = $rQtyPcs pcs)" : "";
                        $ketKartu = "[Stok Opname: $kodeSesi] Batch: $noBatchTerdampak | $reasonName$ketPcs" . ($keterangan ? " - $keterangan" : "");
                        $kartuEnum = in_array($reasonName, $allowedAlasanKartu) ? $reasonName : 'Koreksi Manual';

                        $stmtKartu->execute([
                            $idVariasi,
                            $idBatch,
                            $item['satuan_tipe'],
                            $jMutasi,
                            $kartuEnum,
                            $kodeSesi,
                            $rQty,
                            $sisaStokToko,
                            $ketKartu,
                            $userId
                        ]);
                        $summaryAlasanArr[] = "$reasonName ($rQty {$item['nama_satuan']})";
                    }
                    $finalAlasanMutasi = implode(', ', $summaryAlasanArr);
                }
                // ── B. MUTASI ALKES (Item-Based / Serial Number 1:1) ─────────
                else {
                    $idVariasi = $item['id_variasi'];

                    if ($isMinus) {
                        // Mutasi Minus Alkes: Wajib memilih nomor SN yang hilang/rusak
                        $selectedSns = $_POST['selected_sn_ids'] ?? [];
                        if (!is_array($selectedSns) || count($selectedSns) !== $absSelisih) {
                            throw new Exception("Mutasi minus Alkes defisit $absSelisih unit: Anda wajib mencentang tepat $absSelisih nomor Serial Number yang rusak/hilang!");
                        }

                        $mutatedSnList = [];
                        $alkesBreakdown = [];
                        $snReasonInputs = $_POST['sn_reason'] ?? [];
                        $snCustomInputs = $_POST['sn_custom_reason'] ?? [];

                        $stmtUpSn = $pdo->prepare("UPDATE unit_serial SET status = ?, catatan = ? WHERE id = ? AND id_variasi = ?");
                        $stmtInsDos = $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, ?)");

                        foreach ($selectedSns as $snId) {
                            $snId = intval($snId);
                            $qSn = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id = ? AND id_variasi = ?");
                            $qSn->execute([$snId, $idVariasi]);
                            $snVal = $qSn->fetchColumn();
                            if (!$snVal) throw new Exception("Serial Number ID $snId tidak valid.");

                            // Setiap SN memiliki alasan tersendiri yang dipilih pengguna
                            $thisReason = $snReasonInputs[$snId] ?? 'Barang Rusak';
                            if ($thisReason === 'Lainnya') {
                                $customVal = trim($snCustomInputs[$snId] ?? '');
                                $thisReason = !empty($customVal) ? $customVal : 'Alasan Lainnya (Kustom)';
                            } elseif (!in_array($thisReason, ['Barang Rusak', 'Barang Hilang', 'Koreksi Salah Catat / Ditemukan'])) {
                                $thisReason = 'Barang Rusak';
                            }
                            $alkesBreakdown[$thisReason] = ($alkesBreakdown[$thisReason] ?? 0) + 1;

                            $newSnStatus = (strpos($thisReason, 'Hilang') !== false) ? 'Hilang' : 'Retur/Rusak';
                            $catatanSn = "[Opname $kodeSesi] " . $thisReason . ($keterangan ? ": $keterangan" : "");
                            $stmtUpSn->execute([$newSnStatus, $catatanSn, $snId, $idVariasi]);
                            $stmtInsDos->execute([$idDetail, $snId, $snVal, ($newSnStatus === 'Retur/Rusak' ? 'Kurang_Rusak' : 'Kurang_Hilang')]);

                            $mutatedSnList[] = "$snVal ($thisReason)";
                        }

                        // Potong stok_toko
                        $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$absSelisih, $idVariasi]);
                        $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                        // Catat Kartu Stok untuk masing-masing alasan secara terpisah
                        $stmtKartu = $pdo->prepare("
                            INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                            VALUES (?, NULL, 'besar', 'Keluar', 'Manual', ?, ?, ?, ?, ?, ?)
                        ");

                        $allowedAlasanKartu = [
                            'Penerimaan Barang', 'Penjualan POS', 'Penjualan E-Commerce', 'Retur Barang Rusak',
                            'Klaim Garansi SN', 'Selisih Stok Opname', 'Barang Rusak', 'Barang Kedaluwarsa',
                            'Barang Hilang', 'Koreksi Salah Catat / Ditemukan', 'Koreksi Manual'
                        ];

                        $summaryAlasanArr = [];
                        foreach ($alkesBreakdown as $rName => $rCount) {
                            $snInThisReason = [];
                            foreach ($mutatedSnList as $ms) {
                                if (strpos($ms, $rName) !== false) $snInThisReason[] = $ms;
                            }
                            $ketKartu = "[Stok Opname: $kodeSesi] SN: " . implode(', ', $snInThisReason) . " | $rName" . ($keterangan ? " - $keterangan" : "");
                            $kartuEnum = in_array($rName, $allowedAlasanKartu) ? $rName : 'Koreksi Manual';
                            $stmtKartu->execute([$idVariasi, $kartuEnum, $kodeSesi, $rCount, $sisaStokToko, $ketKartu, $userId]);
                            $summaryAlasanArr[] = "$rName ($rCount Unit)";
                        }
                        $finalAlasanMutasi = implode(', ', $summaryAlasanArr);
                    } else {
                        // Mutasi Plus Alkes: Wajib input/scan SN baru yang ditemukan
                        $rawNewSns = trim($_POST['input_new_sns'] ?? '');
                        $newSnArr = array_filter(array_map('trim', explode(',', $rawNewSns)));
                        if (count($newSnArr) !== $absSelisih) {
                            throw new Exception("Mutasi kelebihan Alkes surplus $absSelisih unit: Harap masukkan tepat $absSelisih nomor Serial Number (pisahkan dengan koma jika lebih dari 1)!");
                        }

                        $stmtInsDos = $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, 'Tambah_Ditemukan')");
                        foreach ($newSnArr as $snStr) {
                            $chkSn = $pdo->prepare("SELECT id FROM unit_serial WHERE serial_number = ?");
                            $chkSn->execute([$snStr]);
                            $existSnId = $chkSn->fetchColumn();

                            if ($existSnId) {
                                $pdo->prepare("UPDATE unit_serial SET status = 'Tersedia', catatan = CONCAT(COALESCE(catatan,''), ' | Ditemukan kembali Opname $kodeSesi') WHERE id = ?")
                                    ->execute([$existSnId]);
                                $stmtInsDos->execute([$idDetail, $existSnId, $snStr]);
                            } else {
                                $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number, status, catatan) VALUES (?, ?, 'Tersedia', ?)")
                                    ->execute([$idVariasi, $snStr, "Ditemukan saat Opname $kodeSesi"]);
                                $newInsertedId = $pdo->lastInsertId();
                                $stmtInsDos->execute([$idDetail, $newInsertedId, $snStr]);
                            }
                        }

                        // Tambah stok_toko
                        $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$absSelisih, $idVariasi]);
                        $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                        // Catat Kartu Stok
                        $ketKartu = "[Stok Opname: $kodeSesi] SN Ditemukan: " . implode(', ', $newSnArr) . " | Koreksi Salah Catat / Ditemukan" . ($keterangan ? " - $keterangan" : "");
                        $stmtKartu = $pdo->prepare("
                            INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                            VALUES (?, NULL, 'besar', 'Masuk', 'Manual', 'Koreksi Salah Catat / Ditemukan', ?, ?, ?, ?, ?)
                        ");
                        $stmtKartu->execute([$idVariasi, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                        $finalAlasanMutasi = 'Koreksi Salah Catat / Ditemukan';
                    }
                }

                // Tandai baris opname sebagai 'Sudah Dimutasi' dengan ringkasan alasan yang presisi
                $stmtUpDetail = $pdo->prepare("
                    UPDATE detail_opname_batch
                    SET status_mutasi = 'Sudah Dimutasi',
                        alasan_mutasi = ?,
                        keterangan_mutasi = ?,
                        dimutasi_oleh = ?,
                        waktu_mutasi = NOW()
                    WHERE id = ?
                ");
                $stmtUpDetail->execute([$finalAlasanMutasi, $keterangan, $userId, $idDetail]);

                $pdo->commit();
                $msg = "Mutasi selisih untuk {$item['nama_produk']} berhasil dieksekusi ($finalAlasanMutasi) dan dicatat di Kartu Stok!";
                $msgType = 'success';
            } catch (Exception $e) {
                if (isset($pdo)) $pdo->rollBack();
                $msg = "Gagal memproses mutasi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 4. SELESAIKAN SESI OPNAME (Khusus Superadmin, Unfreeze) ---
    elseif ($aksi === 'selesaikan_sesi') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Hanya Superadmin yang berwenang menutup/menyelesaikan Sesi Stok Opname.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $idSesi = $activeSession['id'];

                // Syarat 1: Semua baris wajib sudah dihitung
                $sisaBelumHitung = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = $idSesi AND hitung_fisik IS NULL")->fetchColumn();
                if ($sisaBelumHitung > 0) {
                    throw new Exception("Sesi TIDAK BISA diselesaikan! Masih terdapat $sisaBelumHitung item yang belum dihitung fisiknya di lapangan.");
                }

                // Syarat 2: Semua selisih wajib sudah dimutasi
                $sisaBelumMutasi = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = $idSesi AND selisih != 0 AND status_mutasi != 'Sudah Dimutasi'")->fetchColumn();
                if ($sisaBelumMutasi > 0) {
                    throw new Exception("Sesi TIDAK BISA diselesaikan! Masih terdapat $sisaBelumMutasi item berselisih yang belum dimutasi selisihnya.");
                }

                // Eksekusi penyelesaian sesi
                $stmtClose = $pdo->prepare("
                    UPDATE sesi_opname
                    SET status = 'Selesai',
                        dibuka_oleh = ?,
                        dibuka_pada = NOW()
                    WHERE id = ?
                ");
                $stmtClose->execute([$userId, $idSesi]);

                header("Location: stok_opname.php?msg=" . urlencode("Sesi Stok Opname {$activeSession['kode_sesi']} resmi DISELESAIKAN! Seluruh pembekuan dicabut dan transaksi kasir POS, checkout online, serta logistik aktif kembali.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 5. BATALKAN SESI OPNAME (Jalur Darurat, Khusus Superadmin + Auto-Rollback Mutasi) ---
    elseif ($aksi === 'batalkan_sesi') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Hanya Superadmin yang berwenang membatalkan Sesi Stok Opname.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            $alasanBatal = trim($_POST['alasan_pembatalan'] ?? '');
            if (empty($alasanBatal)) {
                $msg = "Alasan pembatalan sesi wajib diisi!";
                $msgType = 'error';
            } else {
                try {
                    $pdo->beginTransaction();

                    $idSesi = $activeSession['id'];
                    $kodeSesi = $activeSession['kode_sesi'];

                    // 1. Cek apakah ada baris yang SUDAH DIMUTASI pada sesi ini (wajib di-rollback jika sesi dibatalkan)
                    $stmtMutated = $pdo->prepare("
                        SELECT dob.*, pv.nama_variasi, pv.rasio_konversi, pi.nama_produk, sb.no_batch
                        FROM detail_opname_batch dob
                        JOIN produk_variasi pv ON dob.id_variasi = pv.id
                        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                        WHERE dob.id_sesi = ? AND dob.status_mutasi = 'Sudah Dimutasi'
                        FOR UPDATE
                    ");
                    $stmtMutated->execute([$idSesi]);
                    $itemsToRollback = $stmtMutated->fetchAll(PDO::FETCH_ASSOC);
                    $countRollback = count($itemsToRollback);

                    // 2. Lakukan Rollback Otomatis untuk setiap item yang sempat dimutasi
                    foreach ($itemsToRollback as $mIt) {
                        $selisih = intval($mIt['selisih']);
                        $absSelisih = abs($selisih);
                        $isMinus = ($selisih < 0);
                        $idVariasi = $mIt['id_variasi'];

                        if ($mIt['jenis_komoditas'] === 'Obat') {
                            $idBatch = $mIt['id_batch'];
                            $rasio = max(1, intval($mIt['rasio_konversi'] ?? 1));
                            $qtyPcs = ($mIt['satuan_tipe'] === 'besar') ? ($absSelisih * $rasio) : $absSelisih;

                            // Jika sebelumnya berkurang (minus), saat rollback ditambah (+). Jika surplus (+), dikurangi (-).
                            $reverseOp = $isMinus ? '+' : '-';
                            $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa $reverseOp ? WHERE id = ?")
                                ->execute([$qtyPcs, $idBatch]);
                            $pdo->prepare("UPDATE stok_toko SET stok = stok $reverseOp ? WHERE id_variasi = ?")
                                ->execute([$qtyPcs, $idVariasi]);

                            $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();
                            $jMutasi = $isMinus ? 'Masuk' : 'Keluar';
                            $ketKartu = "[Rollback Sesi Dibatalkan: $kodeSesi] Batch: {$mIt['no_batch']} | Pembatalan Opname: $alasanBatal";

                            $stmtKartu = $pdo->prepare("
                                INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                                VALUES (?, ?, ?, ?, 'Manual', 'Koreksi Manual', ?, ?, ?, ?, ?)
                            ");
                            $stmtKartu->execute([$idVariasi, $idBatch, $mIt['satuan_tipe'], $jMutasi, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                        } else {
                            // Rollback Alkes (Item-Based / SN)
                            if ($isMinus) {
                                // Kembalikan status SN yang tadinya 'Retur/Rusak' atau 'Hilang' menjadi 'Tersedia'
                                $snRecords = $pdo->prepare("SELECT id_unit_serial FROM detail_opname_sn WHERE id_detail_opname = ?");
                                $snRecords->execute([$mIt['id']]);
                                $snIds = $snRecords->fetchAll(PDO::FETCH_COLUMN);

                                if (!empty($snIds)) {
                                    $inClause = implode(',', array_map('intval', $snIds));
                                    $pdo->query("UPDATE unit_serial SET status = 'Tersedia', catatan = CONCAT(IFNULL(catatan, ''), ' | [Rollback Batal Sesi $kodeSesi]') WHERE id IN ($inClause)");
                                }

                                // Kembalikan stok_toko
                                $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$absSelisih, $idVariasi]);
                                $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                                $ketKartu = "[Rollback Sesi Dibatalkan: $kodeSesi] SN dipulihkan ke Tersedia | Pembatalan Opname: $alasanBatal";
                                $stmtKartu = $pdo->prepare("
                                    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                                    VALUES (?, NULL, 'kecil', 'Masuk', 'Manual', 'Koreksi Manual', ?, ?, ?, ?, ?)
                                ");
                                $stmtKartu->execute([$idVariasi, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                            } else {
                                // Surplus Alkes: Hapus unit_serial baru yang sempat dibuat
                                $snRecords = $pdo->prepare("SELECT id_unit_serial FROM detail_opname_sn WHERE id_detail_opname = ?");
                                $snRecords->execute([$mIt['id']]);
                                $snIds = $snRecords->fetchAll(PDO::FETCH_COLUMN);
                                if (!empty($snIds)) {
                                    $inClause = implode(',', array_map('intval', $snIds));
                                    $pdo->query("DELETE FROM unit_serial WHERE id IN ($inClause)");
                                }

                                $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$absSelisih, $idVariasi]);
                                $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                                $ketKartu = "[Rollback Sesi Dibatalkan: $kodeSesi] Penghapusan SN surplus | Pembatalan Opname: $alasanBatal";
                                $stmtKartu = $pdo->prepare("
                                    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                                    VALUES (?, NULL, 'kecil', 'Keluar', 'Manual', 'Koreksi Manual', ?, ?, ?, ?, ?)
                                ");
                                $stmtKartu->execute([$idVariasi, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                            }

                            $pdo->prepare("DELETE FROM detail_opname_sn WHERE id_detail_opname = ?")->execute([$mIt['id']]);
                        }

                        // Reset status baris detail opname
                        $pdo->prepare("UPDATE detail_opname_batch SET status_mutasi = 'Belum Dimutasi', alasan_mutasi = NULL, keterangan_mutasi = NULL WHERE id = ?")
                            ->execute([$mIt['id']]);
                    }

                    $catatanBaru = ($activeSession['catatan'] ? $activeSession['catatan'] . ' | ' : '') . "Dibatalkan: " . $alasanBatal . ($countRollback > 0 ? " ($countRollback mutasi otomatis di-rollback)" : "");

                    $stmtCancel = $pdo->prepare("
                        UPDATE sesi_opname
                        SET status = 'Dibatalkan',
                            catatan = ?,
                            dibuka_oleh = ?,
                            dibuka_pada = NOW()
                        WHERE id = ?
                    ");
                    $stmtCancel->execute([$catatanBaru, $userId, $idSesi]);

                    $pdo->commit();

                    $rollbackNotif = ($countRollback > 0) ? " Seluruh ($countRollback) penyesuaian stok yang sempat dilakukan telah di-rollback otomatis kembali ke saldo semula." : "";
                    header("Location: stok_opname.php?msg=" . urlencode("Sesi Stok Opname {$activeSession['kode_sesi']} resmi DIBATALKAN. Pembekuan sistem dicabut.$rollbackNotif") . "&type=warning");
                    exit;
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $msg = "Gagal membatalkan sesi: " . $e->getMessage();
                    $msgType = 'error';
                }
            }
        }
    }
}

// Refresh sesi aktif setelah aksi
$activeSession = cekSesiOpnameAktif($pdo);

// ─────────────────────────────────────────────────────────────────────────────
// PERSIAPAN DATA TAMPILAN
// ─────────────────────────────────────────────────────────────────────────────
$searchQ   = trim($_GET['q'] ?? '');
$filterKom = $_GET['komoditas'] ?? 'semua';
$filterSts = $_GET['status_eval'] ?? '';

$detailItems = [];
$stats = [
    'total' => 0,
    'total_obat' => 0,
    'total_alkes' => 0,
    'belum_hitung' => 0,
    'sudah_hitung' => 0,
    'sesuai' => 0,
    'berselisih' => 0,
    'sudah_dimutasi' => 0,
    'defisit_reserved' => 0
];

if ($activeSession) {
    $idSesi = $activeSession['id'];

    // Ambil rekap statistik
    $qStat = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN jenis_komoditas = 'Obat' THEN 1 ELSE 0 END) as total_obat,
            SUM(CASE WHEN jenis_komoditas = 'Alkes' THEN 1 ELSE 0 END) as total_alkes,
            SUM(CASE WHEN hitung_fisik IS NULL THEN 1 ELSE 0 END) as belum_hitung,
            SUM(CASE WHEN hitung_fisik IS NOT NULL THEN 1 ELSE 0 END) as sudah_hitung,
            SUM(CASE WHEN hitung_fisik IS NOT NULL AND selisih = 0 THEN 1 ELSE 0 END) as sesuai,
            SUM(CASE WHEN hitung_fisik IS NOT NULL AND selisih != 0 THEN 1 ELSE 0 END) as berselisih,
            SUM(CASE WHEN status_mutasi = 'Sudah Dimutasi' THEN 1 ELSE 0 END) as sudah_dimutasi,
            SUM(CASE WHEN hitung_fisik IS NOT NULL AND hitung_fisik < stok_reserved THEN 1 ELSE 0 END) as defisit_reserved
        FROM detail_opname_batch
        WHERE id_sesi = $idSesi
    ")->fetch(PDO::FETCH_ASSOC);

    if ($qStat) {
        $stats = array_map('intval', $qStat);
    }

    // ── LOGIKA FILTER DEFAULT VERIFIKASI ANOMALI ─────────────
    // Untuk Admin / Superadmin dalam tahap verifikasi: secara default HANYA tampilkan barang berselisih ('berselisih')!
    // Barang yang jumlah fisik dan sistemnya cocok (0 selisih) disembunyikan secara otomatis agar langsung fokus pada anomali.
    // Untuk Kasir (pencacah): default adalah belum dihitung ('belum_hitung') atau semua data.
    // Penegakan Pemisahan Hak Akses Operasional (Internal Control & Segregation of Duties)
    // Admin Toko hanya berwenang menyelesaikan anomali selisih nyata yang dilaporkan dari lapangan.
    // Admin Toko terkunci secara mutlak pada daftar selisih ('berselisih') dan tidak dapat melihat/mengubah data yang sudah cocok (klop).
    if ($isAdmin && !$isSuperadmin) {
        $filterSts = 'berselisih';
    } else {
        if ($filterSts === '') {
            if ($isKasir) {
                $filterSts = ($stats['belum_hitung'] > 0) ? 'belum_hitung' : 'semua';
            } else {
                $filterSts = ($activeSession['tahap'] === 'Verifikasi') ? 'berselisih' : 'semua';
            }
        }
    }

    // Query daftar baris opname
    $where = ["dob.id_sesi = ?"];
    $params = [$idSesi];

    if ($searchQ !== '') {
        $where[] = "(pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR sb.no_batch LIKE ? OR pv.sku_variasi LIKE ?)";
        $params[] = "%$searchQ%";
        $params[] = "%$searchQ%";
        $params[] = "%$searchQ%";
        $params[] = "%$searchQ%";
    }
    if ($filterKom !== 'semua') {
        $where[] = "dob.jenis_komoditas = ?";
        $params[] = $filterKom;
    }
    if ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') {
        // HANYA barang yang berselisih dan belum selesai direkonsiliasi
        $where[] = "dob.hitung_fisik IS NOT NULL AND (dob.selisih != 0 OR COALESCE(dob.fisik_rusak, 0) > 0 OR COALESCE(dob.fisik_expired, 0) > 0) AND dob.status_mutasi != 'Sudah Dimutasi'";
    } elseif ($filterSts === 'belum_hitung') {
        $where[] = "dob.hitung_fisik IS NULL";
    } elseif ($filterSts === 'sudah_hitung') {
        $where[] = "dob.hitung_fisik IS NOT NULL";
    } elseif ($filterSts === 'sesuai') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih = 0 AND COALESCE(dob.fisik_rusak, 0) = 0 AND COALESCE(dob.fisik_expired, 0) = 0";
    } elseif ($filterSts === 'sudah_dimutasi') {
        $where[] = "dob.status_mutasi = 'Sudah Dimutasi'";
    }

    $whereSql = implode(' AND ', $where);
    $stmtItems = $pdo->prepare("
        SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar,
               (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli,
               pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp,
               u_hit.nama_lengkap AS nama_penghitung, u_mut.nama_lengkap AS nama_pemutasi
        FROM detail_opname_batch dob
        JOIN produk_variasi pv ON dob.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
        LEFT JOIN users u_hit ON dob.dihitung_oleh = u_hit.id
        LEFT JOIN users u_mut ON dob.dimutasi_oleh = u_mut.id
        WHERE $whereSql
        ORDER BY dob.jenis_komoditas ASC,
                 pi.nama_produk ASC, pv.nama_variasi ASC,
                 (dob.satuan_tipe = 'besar') DESC,
                 sb.tgl_exp ASC
    ");
    $stmtItems->execute($params);
    $detailItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // Pengelompokan Hierarki per Produk Variasi (Identik dengan standar Laporan Logistik Medis)
    $groupedDetailItems = [];
    foreach ($detailItems as $it) {
        $vid = $it['id_variasi'];
        if (!isset($groupedDetailItems[$vid])) {
            $groupedDetailItems[$vid] = [
                'id_variasi'     => $vid,
                'nama_produk'    => $it['nama_produk'],
                'nama_variasi'   => $it['nama_variasi'],
                'sku_variasi'    => $it['sku_variasi'] ?? '',
                'kategori'       => $it['kategori'] ?? '',
                'jenis_komoditas'=> $it['jenis_komoditas'],
                'satuan_besar'   => $it['satuan_besar'] ?? '',
                'satuan_kecil'   => $it['satuan_kecil'] ?? '',
                'rasio_konversi' => intval($it['rasio_konversi'] ?? 1),
                'items'          => []
            ];
        }
        $groupedDetailItems[$vid]['items'][] = $it;
    }

    // Ambil mapping seluruh Serial Number untuk variasi Alkes agar langsung tampil inline di tabel
    $alkesSnMap = [];
    $snRows = $pdo->query("
        SELECT id_variasi, serial_number, status 
        FROM unit_serial 
        ORDER BY id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($snRows as $sr) {
        $alkesSnMap[$sr['id_variasi']][] = $sr;
    }
} else {
    // Ambil histori sesi opname sebelumnya
    $historiSesi = $pdo->query("
        SELECT so.*, u_buka.nama_lengkap AS nama_pembuka, u_tutup.nama_lengkap AS nama_penutup,
               (SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = so.id) AS total_item,
               (SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = so.id AND selisih != 0) AS total_selisih
        FROM sesi_opname so
        LEFT JOIN users u_buka ON so.dibekukan_oleh = u_buka.id
        LEFT JOIN users u_tutup ON so.dibuka_oleh = u_tutup.id
        ORDER BY so.id DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($historiSesi as &$hsRow) {
        $stmtLoss = $pdo->prepare("
            SELECT dob.selisih, dob.satuan_tipe, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar,
                   (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli
            FROM detail_opname_batch dob
            JOIN produk_variasi pv ON dob.id_variasi = pv.id
            WHERE dob.id_sesi = ? AND dob.selisih < 0
        ");
        $stmtLoss->execute([$hsRow['id']]);
        $totLossSesi = 0;
        foreach ($stmtLoss->fetchAll(PDO::FETCH_ASSOC) as $lRow) {
            $rasioL = max(1, intval($lRow['rasio_konversi'] ?? 1));
            $hBeliL = floatval($lRow['last_harga_beli'] ?? 0);
            if ($hBeliL <= 0) $hBeliL = floatval($lRow['harga_jual_besar'] ?? 0) * 0.8;
            if ($hBeliL <= 0) $hBeliL = floatval($lRow['harga_jual_kecil'] ?? 0) * $rasioL * 0.8;
            if ($hBeliL <= 0) $hBeliL = 15000;
            $hargaSatuanL = ($lRow['satuan_tipe'] === 'besar') ? $hBeliL : round($hBeliL / $rasioL);
            $totLossSesi += abs(intval($lRow['selisih'])) * $hargaSatuanL;
        }
        $hsRow['estimasi_kerugian'] = $totLossSesi;
    }
    unset($hsRow);
}

// Tangkap pesan URL
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $msgType = $_GET['type'] ?? 'info';
}

layoutHead('Stok Opname Fisik');
layoutBodyOpen();

if ($isKasir) {
    // KHUSUS KASIR: Tampilan Layar Penuh Tanpa Sidebar (Selaras dengan Terminal POS)
    ?>
    <div class="w-full flex-1 min-h-screen bg-slate-50 flex flex-col min-w-0">
        <!-- Header Khusus Terminal Kasir (Identik dengan Terminal POS) -->
        <header class="w-full bg-white border-b border-slate-200 px-6 py-3.5 sticky top-0 z-30 shadow-2xs shrink-0">
            <div class="w-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/inventory_zencare/gambar/Logo.png" alt="Logo" class="h-9 w-auto object-contain" onerror="this.src='../gambar/Logo.png'">
                    <div>
                        <h1 class="font-extrabold text-sm text-slate-900 tracking-tight">ZENCARE MEDICAL</h1>
                        <p class="text-[11px] text-slate-500 font-medium">Input Fisik Stok Opname (Blind Count)</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 text-xs text-slate-600 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Pusat (Muharto)</span>
                        <span class="text-slate-300">|</span>
                        <span class="font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Kasir') ?></span>
                        <span class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded uppercase font-bold">Kasir</span>
                    </div>
                    <a href="/inventory_zencare/logout.php" class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-xl border border-slate-200 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Keluar Sistem</span>
                    </a>
                </div>
            </div>
        </header>
        <main class="flex-1 w-full px-4 sm:px-6 py-5">
    <?php
} else {
    layoutSidebar('stok_opname');
    layoutHeader('Stok Opname Persediaan', 'Pencacahan fisik stok (Blind Count), rekonsiliasi anomali, dan berita acara penyesuaian');
}
?>

<div class="space-y-5 w-full">

    <!-- Notifikasi Pesan -->
    <?php if (!empty($msg)): ?>
        <div class="p-4 rounded-xl border flex items-start gap-3 shadow-xs <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : ($msgType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-blue-50 border-blue-200 text-blue-900') ?>">
            <?php if ($msgType === 'success'): ?>
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <?php elseif ($msgType === 'error'): ?>
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php else: ?>
                <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <?php endif; ?>
            <div class="flex-1 text-xs font-medium leading-relaxed"><?= $msg ?></div>
        </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- KONDISI 1: TIDAK ADA SESI AKTIF (Halaman Landing / Riwayat Sesi)   -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <?php if (!$activeSession): ?>
        
        <div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-zcBrd pb-5 mb-5">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        <h2 class="text-base font-bold text-zcTxt">Status Sistem: Normal (Semua Transaksi Aktif)</h2>
                    </div>
                    <p class="text-xs text-zcMut mt-1 leading-relaxed">
                        Tidak ada sesi opname yang sedang berjalan. Kasir POS offline, toko online e-commerce, dan modul logistik beroperasi normal.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                    <?php if ($canVerify): ?>
                        <!-- Tombol [Utilitas Demo] Jalankan Uji Coba Testing -->
                        <form method="POST" onsubmit="return confirm('Jalankan skenario uji coba testing otomatis dari awal hingga selesai (Mulai Sesi 111 Item -> Hitung Fisik 3 Kondisi -> Verifikasi & Mutasi 6 Produk Anomali -> Tutup Sesi Opname -> Terbitkan Berita Acara)?');" class="inline">
                            <input type="hidden" name="aksi" value="jalankan_simulasi_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Khusus keperluan demo pengujian: Jalankan skenario uji coba otomatis dari awal hingga penutupan sesi dan penerbitan Berita Acara">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                <span>[Utilitas Demo] Jalankan Uji Coba Testing</span>
                            </button>
                        </form>

                        <!-- Tombol [Utilitas Demo] Reset Data Demo (Admin & Superadmin) -->
                        <form method="POST" action="/inventory_zencare/inventori/stok_opname.php" onsubmit="return confirm('Khusus keperluan demo: Apakah Anda yakin ingin memulihkan database inventori ke kondisi awal bersih (Demo_Bersih.sql)?');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Khusus keperluan demo pengujian: Memulihkan database ke kondisi awal bersih (Demo_Bersih.sql)">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>[Utilitas Demo] Reset Data Demo</span>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Tombol Panduan & Dokumentasi Pengujian Black-Box -->
                    <a href="dokumentasi_testing.php" target="_blank" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-2 shadow-2xs" title="Buka dokumentasi rekaman pengujian black-box modul stok opname">
                        <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        <span>Dokumentasi Pengujian Black-Box</span>
                    </a>

                    <?php if ($isSuperadmin): ?>
                        <button onclick="document.getElementById('modal_mulai_sesi').classList.remove('hidden')" class="h-9 px-4 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            <span>Mulai Sesi Stok Opname Baru (Bekukan Transaksi)</span>
                        </button>
                    <?php elseif ($isKasir): ?>
                        <a href="/inventory_zencare/pos/pos.php" class="h-9 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span>Buka Terminal Kasir POS</span>
                        </a>
                    <?php else: ?>
                        <div class="h-9 px-3 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-xs font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Akses Mulai Sesi: Khusus Superadmin</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Keterangan Penilaian Kerugian Persediaan -->
            <div class="mb-5 p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 leading-relaxed">
                <span class="font-bold text-slate-800">Catatan Perhitungan Kerugian:</span>
                Estimasi nilai kerugian dihitung dari kuantitas selisih kurang (barang rusak, expired, atau hilang) dikalikan harga modal perolehan (Harga Beli). Barang selisih lebih (surplus) tidak dihitung sebagai beban kerugian karena merupakan kelebihan fisik dari pengiriman supplier.
            </div>

            <?php
            $demoSesiId = intval($_GET['demo_selesai'] ?? 0);
            $demoSesiData = null;
            $demoAnomaliItems = [];
            $demoTotalLoss = 0;

            if ($demoSesiId > 0) {
                $stmtDemoSesi = $pdo->prepare("SELECT * FROM sesi_opname WHERE id = ?");
                $stmtDemoSesi->execute([$demoSesiId]);
                $demoSesiData = $stmtDemoSesi->fetch(PDO::FETCH_ASSOC);

                if ($demoSesiData) {
                    $stmtDemoItems = $pdo->prepare("
                        SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar,
                               (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli,
                               pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp
                        FROM detail_opname_batch dob
                        JOIN produk_variasi pv ON dob.id_variasi = pv.id
                        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
                        WHERE dob.id_sesi = ? AND (dob.selisih != 0 OR COALESCE(dob.fisik_rusak, 0) > 0 OR COALESCE(dob.fisik_expired, 0) > 0)
                        ORDER BY dob.id ASC
                    ");
                    $stmtDemoItems->execute([$demoSesiId]);
                    $demoAnomaliItems = $stmtDemoItems->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($demoAnomaliItems as &$dIt) {
                        $rasioD = max(1, intval($dIt['rasio_konversi'] ?? 1));
                        $hBeliD = floatval($dIt['last_harga_beli'] ?? 0);
                        if ($hBeliD <= 0) $hBeliD = floatval($dIt['harga_jual_besar'] ?? 0) * 0.8;
                        if ($hBeliD <= 0) $hBeliD = floatval($dIt['harga_jual_kecil'] ?? 0) * $rasioD * 0.8;
                        if ($hBeliD <= 0) $hBeliD = 15000;
                        $dIt['harga_satuan'] = ($dIt['satuan_tipe'] === 'besar') ? $hBeliD : round($hBeliD / $rasioD);
                        $selD = intval($dIt['selisih']);
                        $dIt['subtotal_kerugian'] = ($selD < 0) ? (abs($selD) * $dIt['harga_satuan']) : 0;
                        $demoTotalLoss += $dIt['subtotal_kerugian'];

                        $stmtSnMut = $pdo->prepare("SELECT serial_number, jenis_tindakan FROM detail_opname_sn WHERE id_detail_opname = ?");
                        $stmtSnMut->execute([$dIt['id']]);
                        $dIt['sn_mutated'] = $stmtSnMut->fetchAll(PDO::FETCH_ASSOC);
                    }
                    unset($dIt);
                }
            }
            ?>

            <?php if ($demoSesiData): ?>
                <!-- PANEL HASIL EKSEKUSI UJI COBA TESTING DARI AWAL HINGGA BERITA ACARA -->
                <div class="mb-6 border border-slate-200 rounded-2xl bg-white overflow-hidden shadow-xs">
                    <div class="px-5 py-4 bg-white border-b border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold uppercase tracking-wider">Selesai</span>
                                <h3 class="text-sm font-bold text-slate-900">Hasil Uji Coba Stok Opname &amp; Berita Acara (#<?= htmlspecialchars($demoSesiData['kode_sesi']) ?>)</h3>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Seluruh tahapan mulai dari Pembekuan Sesi (111 Item), Hitung Fisik 3 Kondisi, Verifikasi &amp; Mutasi 6 Produk Anomali, Penutupan Sesi, hingga Penerbitan Berita Acara telah selesai dijalankan.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            <a href="cetak_berita_acara_opname.php?id_sesi=<?= $demoSesiData['id'] ?>" target="_blank" class="h-8 px-3.5 rounded-lg bg-zc hover:bg-zcHv text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                <span>Buka Dokumen Berita Acara</span>
                            </a>
                            <button type="button" onclick="document.getElementById('iframe_berita_acara_demo').contentWindow.print();" class="h-8 px-3.5 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                <span>Cetak Sekarang</span>
                            </button>
                            <form method="POST" class="inline">
                                <input type="hidden" name="aksi" value="jalankan_simulasi_verifikasi">
                                <button type="submit" class="h-8 px-3 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-[11px] font-medium transition cursor-pointer" title="Buka simulasi pada Tahap 2 Verifikasi sebelum ditutup jika ingin mencoba klik pop-up mutasi secara manual">
                                    Simulasi Tahap Verifikasi
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="p-5 space-y-5 bg-slate-50/50">
                        <!-- 1. Ringkasan 6 Tahap Siklus Pengujian -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 text-xs">
                            <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                <div class="text-[10px] font-bold text-emerald-700 uppercase">Tahap 1 &bull; Selesai</div>
                                <div class="font-bold text-slate-800 mt-0.5">Mulai Sesi &amp; Freeze</div>
                                <div class="text-[10px] text-slate-500">111 Entitas Disnapshot</div>
                            </div>
                            <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                <div class="text-[10px] font-bold text-emerald-700 uppercase">Tahap 2 &bull; Selesai</div>
                                <div class="font-bold text-slate-800 mt-0.5">Hitung Fisik Kasir</div>
                                <div class="text-[10px] text-slate-500">105 Klop, 6 Anomali</div>
                            </div>
                            <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                <div class="text-[10px] font-bold text-emerald-700 uppercase">Tahap 3 &bull; Selesai</div>
                                <div class="font-bold text-slate-800 mt-0.5">Verifikasi Selisih</div>
                                <div class="text-[10px] text-slate-500">Filter Otomatis 6 Item</div>
                            </div>
                            <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                <div class="text-[10px] font-bold text-emerald-700 uppercase">Tahap 4 &bull; Selesai</div>
                                <div class="font-bold text-slate-800 mt-0.5">Mutasi &amp; Kartu Stok</div>
                                <div class="text-[10px] text-slate-500">6 / 6 Item Dimutasi</div>
                            </div>
                            <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                <div class="text-[10px] font-bold text-emerald-700 uppercase">Tahap 5 &bull; Selesai</div>
                                <div class="font-bold text-slate-800 mt-0.5">Tutup Sesi Opname</div>
                                <div class="text-[10px] text-slate-500">Kunci Transaksi Dibuka</div>
                            </div>
                            <div class="p-2.5 bg-white border border-slate-200 rounded-xl">
                                <div class="text-[10px] font-bold text-emerald-700 uppercase">Tahap 6 &bull; Terbit</div>
                                <div class="font-bold text-slate-800 mt-0.5">Berita Acara Resmi</div>
                                <div class="text-[10px] text-rose-600 font-semibold">Rugi: Rp <?= number_format($demoTotalLoss, 0, ',', '.') ?></div>
                            </div>
                        </div>

                        <!-- 2. Tabel Rincian 6 Produk Berselisih yang Telah Diverifikasi & Dimutasi -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    Rincian 6 Produk Berselisih yang Telah Diverifikasi &amp; Dimutasi (Dari Total 111 Entitas)
                                </h4>
                                <span class="text-[11px] font-semibold text-slate-600">
                                    105 Barang Klop (Sesuai = 0) Disembunyikan saat Verifikasi &amp; Ditampilkan Lengkap di Berita Acara
                                </span>
                            </div>
                            <div class="border border-slate-200 rounded-xl overflow-x-auto bg-white shadow-2xs">
                                <table class="w-full text-left text-xs border-collapse min-w-[950px]">
                                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold text-[11px]">
                                        <tr>
                                            <th class="py-2.5 px-3">No</th>
                                            <th class="py-2.5 px-3">Komoditas &amp; Produk</th>
                                            <th class="py-2.5 px-3">Batch / SN Disesuaikan</th>
                                            <th class="py-2.5 px-3 text-center">Satuan</th>
                                            <th class="py-2.5 px-3 text-center">Sistem</th>
                                            <th class="py-2.5 px-3 text-center">Fisik Baik</th>
                                            <th class="py-2.5 px-3 text-center">Fisik Rusak</th>
                                            <th class="py-2.5 px-3 text-center">Fisik Exp</th>
                                            <th class="py-2.5 px-3 text-center">Selisih</th>
                                            <th class="py-2.5 px-3 text-right">Estimasi Kerugian</th>
                                            <th class="py-2.5 px-3">Hasil Verifikasi &amp; Mutasi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        <?php foreach ($demoAnomaliItems as $idxD => $dIt): 
                                            $selD = intval($dIt['selisih']);
                                            $hasBatchD = (!empty($dIt['id_batch']) || !empty($dIt['no_batch']));
                                            $badgeKomD = ($dIt['kategori'] === 'Alat Kesehatan') ? ($hasBatchD ? 'Alkes (BMHP)' : 'Alkes (SN)') : 'Obat';
                                        ?>
                                            <tr class="hover:bg-slate-50/80">
                                                <td class="py-2.5 px-3 font-mono font-bold text-slate-600"><?= $idxD + 1 ?></td>
                                                <td class="py-2.5 px-3">
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300 mr-1"><?= $badgeKomD ?></span>
                                                    <strong class="text-slate-900"><?= htmlspecialchars($dIt['nama_produk']) ?></strong>
                                                    <div class="text-[11px] text-slate-500"><?= htmlspecialchars($dIt['nama_variasi']) ?></div>
                                                </td>
                                                <td class="py-2.5 px-3 font-mono text-[11px]">
                                                    <?php if ($hasBatchD): ?>
                                                        <span class="font-bold text-slate-800"><?= htmlspecialchars($dIt['no_batch']) ?></span>
                                                        <?php if (!empty($dIt['tgl_exp'])): ?>
                                                            <div class="text-[10px] text-slate-500 font-sans">Exp: <?= date('d/m/Y', strtotime($dIt['tgl_exp'])) ?></div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php if (!empty($dIt['sn_mutated'])): ?>
                                                            <?php foreach ($dIt['sn_mutated'] as $snm): ?>
                                                                <span class="inline-block px-1.5 py-0.5 rounded bg-rose-50 text-rose-800 border border-rose-200 font-bold"><?= htmlspecialchars($snm['serial_number']) ?></span>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span>Unit SN</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-semibold text-slate-700"><?= htmlspecialchars($dIt['nama_satuan']) ?></td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-800"><?= intval($dIt['stok_sistem_fisik']) ?></td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-emerald-700"><?= intval($dIt['fisik_baik']) ?></td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold <?= intval($dIt['fisik_rusak']) > 0 ? 'text-amber-700 bg-amber-50' : 'text-slate-400' ?>"><?= intval($dIt['fisik_rusak']) ?></td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold <?= intval($dIt['fisik_expired']) > 0 ? 'text-rose-700 bg-rose-50' : 'text-slate-400' ?>"><?= intval($dIt['fisik_expired']) ?></td>
                                                <td class="py-2.5 px-3 text-center font-mono font-bold <?= $selD < 0 ? 'text-rose-700' : 'text-blue-700' ?>">
                                                    <?= $selD > 0 ? '+' . $selD : $selD ?> <?= htmlspecialchars($dIt['nama_satuan']) ?>
                                                </td>
                                                <td class="py-2.5 px-3 text-right font-mono">
                                                    <?php if ($dIt['subtotal_kerugian'] > 0): ?>
                                                        <strong class="text-rose-700">Rp <?= number_format($dIt['subtotal_kerugian'], 0, ',', '.') ?></strong>
                                                        <div class="text-[10px] text-slate-500 font-sans">@ Rp <?= number_format($dIt['harga_satuan'], 0, ',', '.') ?></div>
                                                    <?php else: ?>
                                                        <span class="text-blue-700 font-bold">Rp 0 (Surplus)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-2.5 px-3">
                                                    <div class="font-bold text-emerald-700"><?= htmlspecialchars($dIt['alasan_mutasi'] ?: 'Telah Dimutasi') ?></div>
                                                    <div class="text-[11px] text-slate-600"><?= htmlspecialchars($dIt['keterangan_mutasi'] ?: '-') ?></div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-xs">
                                        <tr>
                                            <td colspan="9" class="py-2.5 px-3 text-right uppercase text-slate-700">Total Estimasi Nilai Acuan Kerugian Persediaan:</td>
                                            <td class="py-2.5 px-3 text-right font-mono text-rose-700 text-sm">Rp <?= number_format($demoTotalLoss, 0, ',', '.') ?></td>
                                            <td class="py-2.5 px-3 text-emerald-700">6 / 6 Selesai Dimutasi</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- 3. Dokumen Berita Acara Hasil Pemeriksaan Fisik (Tertanam Langsung) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    Dokumen Berita Acara Hasil Pemeriksaan Fisik (#<?= htmlspecialchars($demoSesiData['kode_sesi']) ?> — 111 Entitas &amp; Sorotan Merah 6 Baris Selisih)
                                </h4>
                                <a href="cetak_berita_acara_opname.php?id_sesi=<?= $demoSesiData['id'] ?>" target="_blank" class="text-xs font-bold text-zc hover:underline flex items-center gap-1">
                                    <span>Buka Berita Acara di Layar Penuh</span>
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            </div>
                            <iframe id="iframe_berita_acara_demo" src="cetak_berita_acara_opname.php?id_sesi=<?= $demoSesiData['id'] ?>" class="w-full h-[760px] border border-slate-300 rounded-xl bg-white shadow-inner"></iframe>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabel Riwayat Sesi Terdahulu -->
            <h3 class="text-xs font-bold text-zcTxt uppercase tracking-wider mb-3">Arsip Riwayat Sesi Stok Opname</h3>
            <div class="border border-zcBrd rounded-xl overflow-x-auto bg-white shadow-2xs">
                <table class="w-full text-left text-xs border-collapse min-w-[850px]">
                    <thead class="bg-slate-50 border-b border-zcBrd text-slate-600 font-semibold text-[11px]">
                        <tr>
                            <th class="py-2.5 px-3">Kode Sesi</th>
                            <th class="py-2.5 px-3">Waktu Mulai &amp; Selesai</th>
                            <th class="py-2.5 px-3">Pelaksana</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-center">Rekap Selisih</th>
                            <th class="py-2.5 px-3 text-right">Estimasi Kerugian</th>
                            <th class="py-2.5 px-3">Catatan</th>
                            <th class="py-2.5 px-3 text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($historiSesi)): ?>
                            <tr>
                                <td colspan="8" class="py-6 text-center text-zcMut italic">Belum ada riwayat sesi stok opname terdahulu.</td>
                            </tr>
                        <?php else: foreach ($historiSesi as $hs): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-2.5 px-3 font-mono font-bold text-zcTxt"><?= htmlspecialchars($hs['kode_sesi']) ?></td>
                                <td class="py-2.5 px-3 text-slate-600">
                                    <div>Mulai: <?= date('d/m/Y H:i', strtotime($hs['dibekukan_pada'])) ?></div>
                                    <?php if ($hs['dibuka_pada']): ?>
                                        <div class="text-[10px] text-zcMut">Selesai: <?= date('d/m/Y H:i', strtotime($hs['dibuka_pada'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 px-3 text-slate-700">
                                    <div>Oleh: <?= htmlspecialchars($hs['nama_pembuka'] ?? 'Superadmin') ?></div>
                                    <?php if ($hs['nama_penutup']): ?>
                                        <div class="text-[10px] text-zcMut">Ditutup: <?= htmlspecialchars($hs['nama_penutup']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $hs['status'] === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : ($hs['status'] === 'Dibatalkan' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') ?>">
                                        <?= htmlspecialchars($hs['status']) ?>
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="font-bold text-slate-800"><?= $hs['total_selisih'] ?></span> / <?= $hs['total_item'] ?> item
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <?php if (floatval($hs['estimasi_kerugian']) > 0): ?>
                                        <span class="font-bold text-rose-600 font-mono text-xs">Rp <?= number_format($hs['estimasi_kerugian'], 0, ',', '.') ?></span>
                                        <div class="text-[10px] text-rose-500 font-medium">Beban Kerugian</div>
                                    <?php else: ?>
                                        <span class="text-slate-400 font-mono text-xs">Rp 0</span>
                                        <div class="text-[10px] text-emerald-600 font-medium">Klop</div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-600 max-w-xs truncate"><?= htmlspecialchars($hs['catatan'] ?: '-') ?></td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="cetak_berita_acara_opname.php?id_sesi=<?= $hs['id'] ?>" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-zc hover:text-zc text-slate-700 transition text-[11px] font-semibold bg-white shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                        <span>Berita Acara</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Mulai Sesi Opname (Khusus Superadmin) -->
        <div id="modal_mulai_sesi" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-zcBrd">
                <div class="px-6 py-4 border-b border-zcBrd flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-zcTxt">Mulai Sesi Stok Opname</h3>
                            <p class="text-[11px] text-zcMut">Pembekuan transaksi operasional terpusat</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('modal_mulai_sesi').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <form method="POST" class="p-6 space-y-4 text-xs">
                    <input type="hidden" name="aksi" value="mulai_sesi">
                    <div class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl text-amber-900 text-[11px] leading-relaxed flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <strong>PENTING:</strong> Begitu sesi dimulai, server akan <strong>mengunci seluruh transaksi</strong> kasir POS, checkout e-commerce, konversi UOM, dan penerimaan logistik hingga sesi diselesaikan.
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-zcTxt mb-1.5">Catatan / Keterangan Sesi:</label>
                        <textarea name="catatan" rows="3" class="w-full border border-zcBrd rounded-xl p-3 text-xs focus:ring-1 focus:ring-zc" placeholder="Contoh: Stok opname triwulan penutupan bulan Oktober..."></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" onclick="document.getElementById('modal_mulai_sesi').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl font-semibold text-slate-600 hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-zc hover:bg-zcHv text-white rounded-xl font-bold transition shadow-sm">Ya, Mulai Sesi &amp; Bekukan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════════════ -->
        <!-- KONDISI 2: SESI OPNAME AKTIF (FREEZE & PENGHITUNGAN BERJALAN)       -->
        <!-- ══════════════════════════════════════════════════════════════════ -->
        <?php else: ?>

            <?php
            // Hitung estimasi acuan kerugian persediaan (estimasi acuan kerugian) dari seluruh baris sesi
            $stmtVal = $pdo->prepare("
                SELECT dob.*, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
                       pv.harga_jual_kecil, pv.harga_jual_besar,
                       (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli
                FROM detail_opname_batch dob
                JOIN produk_variasi pv ON dob.id_variasi = pv.id
                WHERE dob.id_sesi = ?
            ");
            $stmtVal->execute([$activeSession['id']]);
            $allSesiRows = $stmtVal->fetchAll(PDO::FETCH_ASSOC);

            $estLossRusak = 0;
            $estLossExp = 0;
            $estLossHilang = 0;
            $anomaliCount = 0;

            foreach ($allSesiRows as $r) {
                $rasio = max(1, intval($r['rasio_konversi'] ?? 1));
                $hBeli = floatval($r['last_harga_beli'] ?? 0);
                if ($hBeli <= 0) {
                    $hBeli = floatval($r['harga_jual_besar'] ?? 0) * 0.8;
                }
                if ($hBeli <= 0) {
                    $hBeli = floatval($r['harga_jual_kecil'] ?? 0) * $rasio * 0.8;
                }
                if ($hBeli <= 0) $hBeli = 15000;

                // Harga Beli per satuan baris (besar vs kecil)
                $hargaBeliSatuan = ($r['satuan_tipe'] === 'besar') ? $hBeli : round($hBeli / $rasio);

                $fRusak = intval($r['fisik_rusak'] ?? 0);
                $fExp   = intval($r['fisik_expired'] ?? 0);
                $sel    = intval($r['selisih'] ?? 0);

                if ($fRusak > 0 || $fExp > 0 || $sel != 0) {
                    $anomaliCount++;
                }

                $estLossRusak += ($fRusak * $hargaBeliSatuan);
                $estLossExp   += ($fExp * $hargaBeliSatuan);
                if ($sel < 0) {
                    $qtyHilang = max(0, abs($sel) - $fRusak - $fExp);
                    $estLossHilang += ($qtyHilang * $hargaBeliSatuan);
                }
                // Kelebihan stok (Surplus) = Kerugian Rp 0
            }
            $totalEstLoss = $estLossRusak + $estLossExp + $estLossHilang;
            ?>

            <!-- Banner Utama Sesi Aktif (2-Row Clean Stacked Layout - No Narrow Squishing) -->
            <div class="bg-white border border-zcBrd rounded-2xl p-5 shadow-sm space-y-4">
                <!-- Baris 1: Informasi Status Sesi & Kunci Server (Full Width) -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap text-xs text-slate-600">
                                <span class="font-bold text-slate-900 font-mono tracking-tight text-sm">
                                    Sesi: <?= htmlspecialchars($activeSession['kode_sesi']) ?>
                                </span>
                                <span class="text-slate-300">|</span>
                                <span class="font-semibold text-slate-800">
                                    <?= $currentTahap === 'Penghitungan' ? 'Tahap 1: Penghitungan Fisik (Blind Count)' : 'Tahap 2: Verifikasi &amp; Rekonsiliasi Selisih' ?>
                                </span>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-500">
                                    Dimulai: <?= date('d/m/Y H:i', strtotime($activeSession['dibekukan_pada'])) ?> WIB
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Status Server: Transaksi kasir POS, checkout toko daring, konversi UOM, dan penerimaan logistik sedang dibekukan sementara.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Transaksi Terkunci (423)</span>
                        </span>
                        <?php if ($isSuperadmin): ?>
                            <button type="button" onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="text-[11px] text-slate-400 hover:text-rose-600 font-semibold px-2 py-1 rounded transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                                Batalkan Sesi
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Baris 2: Toolbar Tindakan (Uniform Height h-9, Rapi, Lengkap) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-0.5">
                    <div class="text-xs text-slate-600">
                        <?php if ($currentTahap === 'Penghitungan'): ?>
                            <span>Progres Hitung: <strong class="text-slate-900 font-mono"><?= $stats['sudah_hitung'] ?></strong> / <?= $stats['total'] ?> item (<?= $stats['total'] > 0 ? round(($stats['sudah_hitung'] / $stats['total']) * 100) : 0 ?>%)</span>
                        <?php else: ?>
                            <span>Rekonsiliasi: <strong class="text-slate-900 font-mono"><?= $stats['sudah_dimutasi'] ?></strong> / <?= $stats['berselisih'] ?> anomali selesai dimutasi</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <?php if ($canVerify): ?>
                            <!-- Tombol [Utilitas Demo] Jalankan Uji Coba Testing -->
                            <form method="POST" onsubmit="return confirm('Jalankan skenario uji coba testing otomatis (111 item dengan 6 skenario anomali selisih: Rusak, Expired, Hilang, Surplus) untuk langsung melihat hasil verifikasi selisih, kuantitas, dan estimasi nilai kerugian?');" class="inline">
                                <input type="hidden" name="aksi" value="jalankan_simulasi_demo">
                                <button type="submit" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Khusus keperluan demo pengujian: Jalankan skenario uji coba otomatis untuk langsung menampilkan hasil selisih dan kuantitas">
                                    <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                    <span>[Utilitas Demo] Jalankan Uji Coba Testing</span>
                                </button>
                            </form>

                            <!-- Tombol [Utilitas Demo] Reset Data Demo (Admin & Superadmin) -->
                            <form method="POST" onsubmit="return confirm('Khusus keperluan demo: Apakah Anda yakin ingin memulihkan seluruh database inventori ke kondisi awal bersih (Demo_Bersih.sql)?');" class="inline">
                                <input type="hidden" name="aksi" value="reset_database_demo">
                                <button type="submit" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Khusus keperluan demo pengujian: Memulihkan database ke kondisi awal bersih (Demo_Bersih.sql)">
                                    <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                    <span>[Utilitas Demo] Reset Data Demo</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- 1. Dokumentasi Pengujian -->
                        <a href="dokumentasi_testing.php" target="_blank" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs" title="Buka rekaman dokumentasi pengujian black-box">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>Dokumentasi Pengujian</span>
                        </a>

                        <?php if ($currentTahap === 'Penghitungan'): ?>
                            <?php if ($canCountPhysical && $stats['belum_hitung'] > 0): ?>
                                <!-- 2. Tombol Cepat Isi Angka Demo (Client-Side JS) -->
                                <button type="button" onclick="isiOtomatisAngkaDemo()" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Uji Coba: Isi otomatis angka fisik di tabel sesuai skenario demo pengujian">
                                    <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    <span>Isi Cepat Angka Uji Coba</span>
                                </button>

                                <!-- 3. Tombol Simpan Hasil Hitung -->
                                <button type="button" onclick="submitBulkCountingForm()" id="btn_bulk_simpan" class="h-9 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan hasil hitung fisik yang telah diinput ke server">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    <span>Simpan Hasil Hitung</span>
                                </button>
                            <?php endif; ?>

                            <?php if ($canVerify): ?>
                                <!-- 4. Tombol Verifikasi Selisih (Hanya aktif jika kasir selesai menghitung seluruh item) -->
                                <?php if ($stats['belum_hitung'] > 0): ?>
                                    <button type="button" disabled class="h-9 px-3.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 text-xs font-semibold flex items-center gap-1.5 cursor-not-allowed opacity-80" title="Verifikasi belum aktif. Kasir masih menghitung fisik rak (sisa <?= $stats['belum_hitung'] ?> item belum dihitung)">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        <span>Verifikasi Selisih</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-200 text-slate-600 font-mono font-medium">Kasir: <?= $stats['belum_hitung'] ?> Item</span>
                                    </button>
                                <?php else: ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Buka tahap Verifikasi Selisih? Formulir pencacahan fisik kasir akan dikunci dan antarmuka beralih ke Verifikasi.');">
                                        <input type="hidden" name="aksi" value="tutup_penghitungan">
                                        <button type="submit" class="h-9 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Buka antarmuka verifikasi selisih">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                            <span>Verifikasi Selisih</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- TAHAP 2: VERIFIKASI -->
                            <?php 
                            $pendingMutasiCount = $stats['berselisih'] - $stats['sudah_dimutasi'];
                            if ($canVerify && $pendingMutasiCount > 0): 
                            ?>
                                <button type="button" onclick="document.getElementById('modal_rekonsiliasi_masal').classList.remove('hidden')" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Rekonsiliasi sekaligus seluruh <?= $pendingMutasiCount ?> selisih yang terdeteksi">
                                    <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                    <span>Rekonsiliasi Semua Selisih (<?= $pendingMutasiCount ?>)</span>
                                </button>
                            <?php endif; ?>

                            <?php if ($isSuperadmin): ?>
                                <?php $bisaSelesai = ($stats['belum_hitung'] === 0 && ($stats['berselisih'] - $stats['sudah_dimutasi']) === 0); ?>
                                <form method="POST" onsubmit="return confirm('Apakah Anda yakin seluruh hasil fisik sudah diverifikasi dan ingin menutup sesi opname ini? Pembekuan sistem akan dicabut.');">
                                    <input type="hidden" name="aksi" value="selesaikan_sesi">
                                    <button type="submit" <?= !$bisaSelesai ? 'disabled' : '' ?> class="h-9 px-4 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 <?= $bisaSelesai ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer' : 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed' ?>">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span>Selesaikan Sesi (Buka Kunci Server)</span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="h-9 px-3 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 text-xs font-medium flex items-center gap-1.5">
                                    Menunggu Superadmin membuka kunci
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Metrik Dashboard Opname -->
            <?php if ($isKasir): ?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Total Baris Barang</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['total'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Belum Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['belum_hitung'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sudah Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sudah_hitung'] ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Total Baris</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['total'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Belum Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['belum_hitung'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sudah Dihitung</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sudah_hitung'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sesuai (Klop)</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sesuai'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Ada Selisih</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['berselisih'] ?></span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Sudah Dimutasi</span>
                        <span class="text-xl font-bold font-mono text-slate-900 mt-1 block"><?= $stats['sudah_dimutasi'] ?> / <?= $stats['berselisih'] ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- JIKA TAHAP 2: VERIFIKASI -> TAMPILKAN ESTIMASI NILAI ACUAN KERUGIAN PERSEDIAAN -->
            <?php if ($currentTahap === 'Verifikasi'): ?>
                <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-2xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2.5 border-b border-slate-200 gap-2">
                        <div>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Estimasi Nilai Acuan Kerugian Persediaan</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">Kalkulasi nilai acuan persediaan dari kuantitas fisik yang tercatat Rusak, Expired, dan Hilang berdasarkan Harga Beli</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-mono font-semibold self-start sm:self-auto">
                            Rumus: (Jumlah Rusak + Jumlah Expired + Jumlah Hilang) &times; Harga Beli (Satuan Terkecil)
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Nilai Barang Rusak</span>
                            <span class="text-sm font-bold text-slate-900 font-mono mt-1 block">Rp <?= number_format($estLossRusak, 0, ',', '.') ?></span>
                            <span class="text-[10px] text-slate-500">Pecah / Cacat Fisik</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Nilai Barang Expired</span>
                            <span class="text-sm font-bold text-slate-900 font-mono mt-1 block">Rp <?= number_format($estLossExp, 0, ',', '.') ?></span>
                            <span class="text-[10px] text-slate-500">Lewat Masa Simpan</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Nilai Barang Hilang</span>
                            <span class="text-sm font-bold text-slate-900 font-mono mt-1 block">Rp <?= number_format($estLossHilang, 0, ',', '.') ?></span>
                            <span class="text-[10px] text-slate-500">Selisih Kurang (Defisit)</span>
                        </div>
                        <div class="p-3 rounded-lg bg-rose-50/60 border border-rose-200">
                            <span class="text-[10px] text-rose-900 font-bold uppercase tracking-wider block">Total Estimasi Nilai Kerugian</span>
                            <span class="text-base font-bold text-rose-700 font-mono mt-1 block">Rp <?= number_format($totalEstLoss, 0, ',', '.') ?></span>
                            <span class="text-[10px] text-rose-700 font-medium">Acuan Beban Persediaan</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- JIKA TAHAP 1: PENGHITUNGAN & USER ADALAH ADMIN -> MONITORING PROGRESS ONLY -->
            <?php if ($currentTahap === 'Penghitungan' && $isAdmin && !$isSuperadmin): ?>
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="text-xs text-slate-700 leading-relaxed">
                            <strong class="font-bold text-slate-900 block">SOP Pengendalian Internal: Pemisahan Tugas Pencacahan Fisik</strong>
                            Sesuai aturan tata kelola gudang, staf gudang (Admin) <strong>tidak diperkenankan melakukan penghitungan fisik</strong> di lapangan untuk mencegah manipulasi persediaan. Penghitungan fisik dilakukan secara mandiri oleh staf Kasir lapangan. Anda dapat memantau progres pencatatan kasir di bawah ini dan menekan tombol Verifikasi Selisih jika seluruh rak telah selesai dihitung.
                        </div>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Status Pencatatan Fisik oleh Kasir</h3>
                        <span class="text-xs font-medium text-slate-500">Progres: <strong><?= $stats['sudah_hitung'] ?> / <?= $stats['total'] ?></strong> baris (<?= round(($stats['total'] > 0 ? ($stats['sudah_hitung'] / $stats['total'] * 100) : 0)) ?>%)</span>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 font-semibold text-[11px] border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">#</th>
                                    <th class="py-2.5 px-3">Produk &amp; Variasi</th>
                                    <th class="py-2.5 px-3">Batch / SN Tracker</th>
                                    <th class="py-2.5 px-3 text-center">Satuan</th>
                                    <th class="py-2.5 px-3 text-center">Status Pencatatan Kasir</th>
                                    <th class="py-2.5 px-3">Waktu &amp; Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php 
                                $noProg = 1;
                                foreach ($detailItems as $di): 
                                ?>
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-2.5 px-3 text-center text-slate-400 font-mono"><?= $noProg++ ?></td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($di['nama_produk']) ?></div>
                                        <div class="text-[11px] text-slate-500"><?= htmlspecialchars($di['nama_variasi']) ?></div>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px] text-slate-700">
                                        <?= htmlspecialchars($di['no_batch'] ?: ($di['jenis_komoditas'] === 'Alkes' ? 'SN Tracking' : '-')) ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center text-slate-600"><?= htmlspecialchars($di['nama_satuan']) ?></td>
                                    <td class="py-2.5 px-3 text-center">
                                        <?php if ($di['status_hitung'] === 'Sudah'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Telah Dihitung</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Menunggu Kasir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                        <?php if ($di['status_hitung'] === 'Sudah'): ?>
                                            <?= htmlspecialchars($di['nama_penghitung'] ?? 'Kasir') ?> (<?= $di['waktu_hitung'] ? date('H:i:s', strtotime($di['waktu_hitung'])) : '-' ?>)
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <div class="text-xs text-slate-500">
                            <?php if ($stats['belum_hitung'] > 0): ?>
                                <span class="text-amber-600 font-medium">Menunggu tim kasir menyelesaikan pencacahan fisik rak (<?= $stats['belum_hitung'] ?> item lagi).</span>
                            <?php else: ?>
                                <span class="text-emerald-600 font-medium">Seluruh item fisik (<?= $stats['total'] ?>) telah selesai dihitung oleh tim kasir.</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($stats['belum_hitung'] > 0): ?>
                            <button type="button" disabled class="px-5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 text-xs font-bold flex items-center gap-2 cursor-not-allowed" title="Tahap verifikasi belum dapat dibuka karena kasir masih menghitung fisik rak">
                                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span>Verifikasi Selisih (Menunggu Kasir: <?= $stats['belum_hitung'] ?> Item)</span>
                            </button>
                        <?php else: ?>
                            <form method="POST" onsubmit="return confirm('Buka tahap Verifikasi Selisih? Formulir pencacahan kasir akan dikunci dan sistem beralih ke Verifikasi Selisih.');">
                                <input type="hidden" name="aksi" value="tutup_penghitungan">
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    <span>Verifikasi Selisih</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            <!-- JIKA TAHAP 1: PENGHITUNGAN & USER ADALAH KASIR ATAU SUPERADMIN -> FORM HITUNG BUTA KONDISI -->
            <?php elseif ($currentTahap === 'Penghitungan'): ?>

                <!-- Panduan Pencacahan Fisik (Blind Count) -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs flex items-start gap-3">
                    <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <div class="leading-relaxed">
                        <strong class="font-bold text-slate-900 block">Panduan Pencacahan Fisik (Blind Count)</strong>
                        Periksa fisik barang di rak etalase secara independen. Masukkan kuantitas fisik pada 3 kolom:
                        <strong>Kondisi Baik</strong> (layak jual), <strong>Kondisi Rusak</strong> (cacat fisik), dan <strong>Kondisi Expired</strong> (kedaluwarsa). Angka pembukuan sistem dirahasiakan selama tahap pencacahan fisik.
                    </div>
                </div>

                <!-- Bilah Pemindai Barcode / Fast SKU Locator -->
                <div class="p-3.5 bg-white border border-zcBrd rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                <span>Pemindai Barcode / Pencarian Cepat Rak</span>
                            </div>
                            <p class="text-[10px] text-slate-500">Scan barcode fisik / ketikkan SKU, Batch, atau SN untuk langsung menyorot baris input.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-1 max-w-md">
                        <input type="text" id="barcode_scan_input" placeholder="Scan Barcode / SKU / No. Batch / SN..." class="w-full border border-slate-300 focus:border-slate-800 rounded-xl px-3 py-1.5 text-xs font-mono font-bold bg-white text-slate-900 shadow-2xs outline-none">
                        <button type="button" onclick="handleBarcodeScan()" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shrink-0 cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Cari</span>
                        </button>
                    </div>
                </div>

                <?php if ($stats['belum_hitung'] === 0 && $isKasir): ?>
                    <!-- Banner Sukses Pencacahan Fisik Rak Kasir Selesai -->
                    <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-center space-y-2.5 shadow-2xs">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-sm">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-emerald-950">Pencacahan Fisik Rak Selesai (100% Terdata)</h3>
                        <p class="text-xs text-emerald-800 max-w-xl mx-auto leading-relaxed">
                            Seluruh <?= $stats['total'] ?> entitas barang fisik telah berhasil Anda input ke server.
                            Saat ini kasir POS <strong>masih dibekukan sementara</strong> demi integritas persediaan sampai Superadmin/Admin menyelesaikan verifikasi selisih dan membuka kunci server.
                        </p>
                        <div class="pt-1 flex items-center justify-center gap-3">
                            <a href="stok_opname.php" class="px-3.5 py-1.5 rounded-xl bg-white border border-emerald-300 hover:bg-emerald-100 text-emerald-900 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>Periksa Status Pembukaan Kunci POS</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tab Pemfilteran Progres Hitung Kasir (Belum vs Sudah Dihitung) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-1">
                    <div class="flex items-center gap-1.5">
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=belum_hitung&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 <?= $filterSts === 'belum_hitung' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                            <span>Belum Dihitung</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'belum_hitung' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600' ?>"><?= $stats['belum_hitung'] ?></span>
                        </a>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sudah_hitung&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 <?= $filterSts === 'sudah_hitung' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                            <span>Sudah Dihitung (Tersimpan)</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'sudah_hitung' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600' ?>"><?= $stats['sudah_hitung'] ?></span>
                        </a>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 <?= $filterSts === 'semua' ? 'bg-slate-800 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                            <span>Semua Item</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600' ?>"><?= $stats['total'] ?></span>
                        </a>
                    </div>
                    <div class="text-[11px] text-slate-500 hidden sm:block">
                        <?php if ($filterSts === 'belum_hitung'): ?>
                            <span>Menampilkan barang yang belum diinput. Barang yang disimpan akan otomatis pindah ke tab 'Sudah Dihitung'.</span>
                        <?php elseif ($filterSts === 'sudah_hitung'): ?>
                            <span>Menampilkan barang yang sudah tersimpan. Anda dapat merevisi angka jika terdapat koreksi.</span>
                        <?php else: ?>
                            <span>Menampilkan seluruh <?= $stats['total'] ?> baris persediaan.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Bulk Simpan Seluruh Hitungan -->
                <form id="form_bulk_simpan" method="POST" onsubmit="return validateBulkFormBeforeSubmit()">
                    <input type="hidden" name="aksi" value="simpan_semua_hitung_fisik">

                    <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-50 border-b border-zcBrd text-slate-600 font-semibold text-[11px]">
                                    <tr>
                                        <th class="py-3 px-3 w-8 text-center">#</th>
                                        <th class="py-3 px-3">Komoditas &amp; Produk</th>
                                        <th class="py-3 px-3">Batch / SN Tracker</th>
                                        <th class="py-3 px-3 text-center">Satuan</th>
                                        <th class="py-3 px-3 text-center w-28 border-x border-slate-200 bg-slate-50 text-slate-700">
                                            <div class="font-bold">Kondisi Baik</div>
                                            <div class="text-[9px] text-slate-500 font-normal">Layak Jual</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 border-r border-slate-200 bg-slate-50 text-slate-700">
                                            <div class="font-bold">Kondisi Rusak</div>
                                            <div class="text-[9px] text-slate-500 font-normal">Cacat Fisik</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 border-r border-slate-200 bg-slate-50 text-slate-700">
                                            <div class="font-bold">Kondisi Expired</div>
                                            <div class="text-[9px] text-slate-500 font-normal">Kedaluwarsa</div>
                                        </th>
                                        <th class="py-3 px-3 text-center whitespace-nowrap min-w-[110px]">Aksi Baris</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (empty($groupedDetailItems)): ?>
                                        <tr>
                                            <td colspan="8" class="py-8 text-center text-zcMut italic">Tidak ada item yang ditemukan.</td>
                                        </tr>
                                    <?php else: 
                                        $pNo = 1;
                                        foreach ($groupedDetailItems as $grp): 
                                    ?>
                                        <!-- Header Produk Induk & Variasi -->
                                        <tr class="bg-slate-100/80 border-t-2 border-slate-300 font-semibold text-slate-800 text-xs">
                                            <td class="py-2.5 px-3 text-center text-slate-500 font-bold"><?= $pNo++ ?></td>
                                            <td colspan="3" class="py-2.5 px-3">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <?php if ($grp['kategori'] === 'Alat Kesehatan'): ?>
                                                        <?php 
                                                        $firstItem = $grp['items'][0] ?? null;
                                                        $isVarBatchTracked = ($firstItem && (!empty($firstItem['id_batch']) || !empty($firstItem['no_batch'])));
                                                        ?>
                                                        <?php if ($isVarBatchTracked): ?>
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                                                                Alkes (BMHP)
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                Alkes (SN)
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200">
                                                            Obat
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="text-sm font-bold text-slate-900"><?= htmlspecialchars($grp['nama_produk']) ?></span>
                                                    <span class="text-slate-600 font-medium">— <?= htmlspecialchars($grp['nama_variasi']) ?></span>
                                                    <?php if (!empty($grp['sku_variasi'])): ?>
                                                        <span class="text-[10px] font-mono text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">[<?= htmlspecialchars($grp['sku_variasi']) ?>]</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td colspan="4" class="py-2.5 px-3 text-right">
                                                <?php if ($grp['jenis_komoditas'] === 'Obat' && $grp['rasio_konversi'] > 1): ?>
                                                    <span class="text-[11px] font-medium text-slate-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                                        Rasio: 1 <?= htmlspecialchars($grp['satuan_besar']) ?> = <?= $grp['rasio_konversi'] ?> <?= htmlspecialchars($grp['satuan_kecil']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Baris Sub-Item / Batch / Unit Fisik -->
                                        <?php foreach ($grp['items'] as $it): 
                                            $isCalculated = ($it['status_hitung'] === 'Sudah');
                                            $searchableStr = htmlspecialchars($it['nama_produk'] . ' ' . $it['nama_variasi'] . ' ' . ($it['sku_variasi'] ?? '') . ' ' . ($it['no_batch'] ?? ''));
                                            $isObatExp = ($it['jenis_komoditas'] === 'Obat' && !empty($it['tgl_exp']) && strtotime($it['tgl_exp']) < strtotime(date('Y-m-d')));
                                            $hasBatchInfo = (!empty($it['id_batch']) || !empty($it['no_batch']));
                                            $isAlkesSN = ($it['jenis_komoditas'] === 'Alkes' && !$hasBatchInfo);
                                        ?>
                                        <tr class="hover:bg-slate-50/70 transition" data-barcode-search="<?= $searchableStr ?>" data-id="<?= $it['id'] ?>" data-nama="<?= htmlspecialchars($it['nama_produk']) ?>" data-satuan-tipe="<?= $it['satuan_tipe'] ?>" data-is-exp="<?= $isObatExp ? '1' : '0' ?>" data-komoditas="<?= $it['jenis_komoditas'] ?>">
                                            <td class="py-3 px-3 text-center text-slate-400 font-mono text-xs">
                                                <input type="hidden" name="bulk_item_ids[]" value="<?= $it['id'] ?>">
                                                &gt;
                                            </td>
                                            
                                            <td class="py-3 px-3">
                                                <div class="text-slate-700 font-medium">
                                                    <?= htmlspecialchars($it['satuan_tipe'] === 'besar' ? 'Satuan Besar (Dus/Box)' : 'Satuan Kecil (Eceran)') ?>
                                                </div>
                                            </td>

                                            <td class="py-3 px-3">
                                                <?php if ($hasBatchInfo): ?>
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-mono font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($it['no_batch'] ?: '-') ?></span>
                                                        <?php if ($isObatExp): ?>
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-200">KEDALUWARSA</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-[10px] text-zcMut">Exp: <?= $it['tgl_exp'] ? date('d/m/Y', strtotime($it['tgl_exp'])) : '-' ?></div>
                                                <?php else: ?>
                                                    <div class="text-[10px] text-emerald-800 font-semibold">
                                                        Pelacakan Serial Number (SN Tracker)
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <td class="py-3 px-3 text-center font-semibold text-slate-700">
                                                <?= htmlspecialchars($it['nama_satuan']) ?>
                                            </td>

                                            <!-- INPUT FISIK BAIK -->
                                            <td class="py-2.5 px-3 text-center border-x border-slate-100">
                                                <?php if ($isObatExp): ?>
                                                    <input type="hidden" name="bulk_fisik_baik[<?= $it['id'] ?>]" value="0">
                                                    <span class="text-[10px] font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-200 block">Terkunci (0) Exp</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_baik[<?= $it['id'] ?>]" 
                                                           id="input_baik_<?= $it['id'] ?>"
                                                           value="<?= ($isCalculated && $it['fisik_baik'] !== null) ? $it['fisik_baik'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-slate-300 rounded-lg py-1 px-1.5 text-center font-mono text-xs focus:outline-none focus:border-slate-800 bg-white shadow-2xs">
                                                <?php endif; ?>
                                            </td>

                                            <!-- INPUT FISIK RUSAK -->
                                            <td class="py-2.5 px-3 text-center border-r border-slate-100">
                                                <input type="number" 
                                                       name="bulk_fisik_rusak[<?= $it['id'] ?>]" 
                                                       id="input_rusak_<?= $it['id'] ?>"
                                                       value="<?= ($isCalculated && $it['fisik_rusak'] !== null) ? $it['fisik_rusak'] : '' ?>"
                                                       min="0" 
                                                       placeholder="0"
                                                       class="w-16 border border-slate-300 rounded-lg py-1 px-1.5 text-center font-mono text-xs focus:outline-none focus:border-slate-800 bg-white shadow-2xs">
                                            </td>

                                            <!-- INPUT FISIK EXPIRED -->
                                            <td class="py-2.5 px-3 text-center border-r border-slate-100">
                                                <?php if ($isAlkesSN): ?>
                                                    <span class="text-[10px] text-slate-400 font-mono">-</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_expired[<?= $it['id'] ?>]" 
                                                           id="input_exp_<?= $it['id'] ?>"
                                                           value="<?= ($isCalculated && $it['fisik_expired'] !== null) ? $it['fisik_expired'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-slate-300 bg-white rounded-lg py-1 px-1.5 text-center font-mono text-xs focus:outline-none focus:border-slate-800 shadow-2xs">
                                                <?php endif; ?>
                                            </td>

                                            <!-- AKSI BARIS -->
                                            <td class="py-2.5 px-3 text-center">
                                                <?php if ($isCalculated): ?>
                                                    <div class="text-[10px] text-emerald-700 font-bold">Tersimpan</div>
                                                    <div class="text-[9px] text-slate-400"><?= htmlspecialchars($it['nama_penghitung'] ?? 'Kasir') ?></div>
                                                <?php else: ?>
                                                    <span class="text-[10px] text-amber-700 font-medium">Belum Simpan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>

            <!-- JIKA TAHAP 2: VERIFIKASI (ADMIN & SUPERADMIN) -> TABEL FOKUS ANOMALI & 1-KLIK MUTASI -->
            <?php else: ?>

                <!-- NAVIGASI FILTER TAB TAHAP 2 VERIFIKASI (ADMIN & SUPERADMIN) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3 mb-4">
                    <!-- Tab Filter: Perlu Mutasi, Sesuai, Sudah Dimutasi, Semua Item -->
                    <div class="flex items-center gap-1.5 overflow-x-auto text-xs">
                        <!-- Tab 1: Perlu Mutasi (Berselisih) -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=berselisih&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') ? 'bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Fokus verifikasi pada barang yang berselisih">
                            <span>Perlu Mutasi (Berselisih)</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= ($filterSts === 'berselisih' || $filterSts === 'berselisih_belum_mutasi') ? 'bg-rose-200 text-rose-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= max(0, $stats['berselisih'] - $stats['sudah_dimutasi']) ?>
                            </span>
                        </a>

                        <!-- Tab 2: Sesuai / Pas (Klop) -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sesuai&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= $filterSts === 'sesuai' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Daftar barang yang jumlah fisiknya cocok dengan sistem">
                            <span>Sesuai (Klop)</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'sesuai' ? 'bg-emerald-200 text-emerald-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $stats['sesuai'] ?>
                            </span>
                        </a>

                        <!-- Tab 3: Sudah Dimutasi -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=sudah_dimutasi&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= $filterSts === 'sudah_dimutasi' ? 'bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Barang yang sudah selesai direkonsiliasi dan dicatat ke kartu stok">
                            <span>Sudah Dimutasi</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'sudah_dimutasi' ? 'bg-blue-200 text-blue-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $stats['sudah_dimutasi'] ?>
                            </span>
                        </a>

                        <!-- Tab 4: Semua Item -->
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" 
                           class="px-3.5 py-1.5 rounded-lg font-semibold flex items-center gap-2 transition <?= $filterSts === 'semua' ? 'bg-slate-100 text-slate-800 border border-slate-300 shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>" title="Tampilkan seluruh entitas persediaan">
                            <span>Semua Item</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono <?= $filterSts === 'semua' ? 'bg-slate-200 text-slate-800 font-bold' : 'bg-slate-100 text-slate-600' ?>">
                                <?= $stats['total'] ?>
                            </span>
                        </a>
                    </div>

                    <!-- Keterangan Ringkas Jumlah Baris -->
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <strong><?= count($detailItems) ?></strong> dari <?= $stats['total'] ?> baris persediaan
                    </div>
                </div>

                <!-- Tabel Rekonsiliasi Anomali & Eksekusi Mutasi -->
                <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 border-b border-zcBrd text-slate-600 font-semibold text-[11px]">
                                <tr>
                                    <th class="py-3 px-3 w-8 text-center">#</th>
                                    <th class="py-3 px-3">Komoditas &amp; Produk</th>
                                    <th class="py-3 px-3">Batch / SN Tracker</th>
                                    <th class="py-3 px-3 text-center">Satuan</th>
                                    <th class="py-3 px-3 text-center">Fisik Baik</th>
                                    <th class="py-3 px-3 text-center">Fisik Rusak</th>
                                    <th class="py-3 px-3 text-center">Fisik Exp</th>
                                    <th class="py-3 px-3 text-center">Stok Sistem</th>
                                    <th class="py-3 px-3 text-center">Selisih Buku</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap min-w-[100px]">Status</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap min-w-[150px]">Tindakan Mutasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($groupedDetailItems)): ?>
                                    <tr>
                                        <td colspan="11" class="py-8 text-center text-zcMut italic">Tidak ada item anomali yang perlu disesuaikan. Seluruh stok cocok!</td>
                                    </tr>
                                <?php else: 
                                    $pNo = 1;
                                    foreach ($groupedDetailItems as $grp): 
                                ?>
                                    <!-- Header Produk -->
                                    <?php
                                    $isGrpBatchTracked = false;
                                    foreach ($grp['items'] as $_chkIt) {
                                        if (!empty($_chkIt['id_batch']) || !empty($_chkIt['no_batch'])) {
                                            $isGrpBatchTracked = true;
                                            break;
                                        }
                                    }
                                    ?>
                                    <tr class="bg-slate-100/80 border-t-2 border-slate-300 font-semibold text-slate-800 text-xs">
                                        <td class="py-2 px-3 text-center text-slate-500 font-bold"><?= $pNo++ ?></td>
                                        <td colspan="4" class="py-2 px-3">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <?php if ($grp['kategori'] === 'Alat Kesehatan'): ?>
                                                    <?php if ($isGrpBatchTracked || $grp['jenis_komoditas'] !== 'Alkes'): ?>
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-teal-50 text-teal-800 border border-teal-200">
                                                            Alkes (BMHP)
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                            Alkes (SN)
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-800 border border-blue-200">
                                                        Obat
                                                    </span>
                                                <?php endif; ?>
                                                <span class="text-sm font-bold text-slate-900"><?= htmlspecialchars($grp['nama_produk']) ?></span>
                                                <span class="text-slate-600 font-medium">— <?= htmlspecialchars($grp['nama_variasi']) ?></span>
                                            </div>
                                        </td>
                                        <td colspan="6" class="py-2 px-3 text-right">
                                            <span class="text-[10px] text-slate-500"><?= htmlspecialchars($grp['satuan_besar'] ? "1 {$grp['satuan_besar']} = {$grp['rasio_konversi']} {$grp['satuan_kecil']}" : 'Satuan Tunggal') ?></span>
                                        </td>
                                    </tr>

                                    <?php foreach ($grp['items'] as $it): 
                                        $fBaik = intval($it['fisik_baik'] ?? $it['hitung_fisik'] ?? 0);
                                        $fRusak = intval($it['fisik_rusak'] ?? 0);
                                        $fExp = intval($it['fisik_expired'] ?? 0);
                                        $sistem = intval($it['stok_sistem_fisik']);
                                        $selisih = intval($it['selisih'] ?? 0);
                                        $isMutated = ($it['status_mutasi'] === 'Sudah Dimutasi');
                                    ?>
                                    <tr class="hover:bg-slate-50/70 transition <?= ($selisih != 0 || $fRusak > 0 || $fExp > 0) && !$isMutated ? 'bg-rose-50/20' : '' ?>">
                                        <td class="py-3 px-3 text-center text-slate-400 font-mono text-xs">></td>
                                        <td class="py-3 px-3 font-medium text-slate-700">
                                            <?= htmlspecialchars($it['satuan_tipe'] === 'besar' ? 'Satuan Besar (Dus/Box)' : 'Satuan Kecil (Eceran)') ?>
                                        </td>
                                        <td class="py-3 px-3 font-mono text-[11px] text-slate-700">
                                            <?= htmlspecialchars($it['no_batch'] ?: ($it['jenis_komoditas'] === 'Alkes' && !$isGrpBatchTracked ? 'SN Tracking' : '-')) ?>
                                            <?php if ($it['tgl_exp']): ?>
                                                <div class="text-[10px] text-slate-400 font-sans">Exp: <?= date('d/m/Y', strtotime($it['tgl_exp'])) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center font-semibold text-slate-700"><?= htmlspecialchars($it['nama_satuan']) ?></td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-800 font-mono"><?= $fBaik ?></td>
                                        <td class="py-3 px-3 text-center font-bold font-mono <?= $fRusak > 0 ? 'text-amber-700 bg-amber-50/50' : 'text-slate-400' ?>"><?= $fRusak ?></td>
                                        <td class="py-3 px-3 text-center font-bold font-mono <?= $fExp > 0 ? 'text-rose-700 bg-rose-50/50' : 'text-slate-400' ?>"><?= $fExp ?></td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-700 font-mono"><?= $sistem ?></td>
                                        <td class="py-3 px-3 text-center font-mono">
                                            <div class="font-bold text-xs">
                                                <?php if ($selisih === 0): ?>
                                                    <span class="text-emerald-700">0</span>
                                                <?php elseif ($selisih > 0): ?>
                                                    <span class="text-blue-700">+<?= $selisih ?></span>
                                                <?php else: ?>
                                                    <span class="text-rose-700"><?= $selisih ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php
                                            // Tampilkan estimasi nilai acuan kerugian berbasis Harga Beli per baris
                                            $rasioIt = max(1, intval($it['rasio_konversi'] ?? 1));
                                            $hBeliIt = floatval($it['last_harga_beli'] ?? 0);
                                            if ($hBeliIt <= 0) $hBeliIt = floatval($it['harga_jual_besar'] ?? 0) * 0.8;
                                            if ($hBeliIt <= 0) $hBeliIt = floatval($it['harga_jual_kecil'] ?? 0) * $rasioIt * 0.8;
                                            if ($hBeliIt <= 0) $hBeliIt = 15000;
                                            $hargaBeliRow = ($it['satuan_tipe'] === 'besar') ? $hBeliIt : round($hBeliIt / $rasioIt);

                                            if ($selisih < 0) {
                                                $barisLoss = abs($selisih) * $hargaBeliRow;
                                                echo '<div class="text-[9px] text-rose-600 font-sans font-semibold mt-0.5">Beban: Rp ' . number_format($barisLoss, 0, ',', '.') . '</div>';
                                            } elseif ($selisih > 0) {
                                                echo '<div class="text-[9px] text-blue-600 font-sans font-semibold mt-0.5">Surplus: Rp 0</div>';
                                            }
                                            ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($isMutated): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Sudah Dimutasi</span>
                                                <div class="text-[9px] text-slate-400 mt-0.5"><?= htmlspecialchars($it['alasan_mutasi'] ?? '') ?></div>
                                            <?php elseif ($selisih === 0 && $fRusak === 0 && $fExp === 0): ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Sesuai / Pas</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">Perlu Mutasi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($isMutated): ?>
                                                <span class="text-[11px] text-emerald-700 font-semibold flex items-center justify-center gap-1">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Tuntas Direkonsiliasi
                                                </span>
                                            <?php elseif ($selisih === 0 && $fRusak === 0 && $fExp === 0): ?>
                                                <!-- BARANG SESUAI / KLOP (TIDAK ADA SELISIH) -->
                                                <div class="flex items-center justify-center gap-2">
                                                    <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                        Klop (Sesuai)
                                                    </span>
                                                    <?php if ($isSuperadmin): ?>
                                                        <!-- Khusus Superadmin: Jika ada salah hitung kasir/admin, Superadmin berwenang mengoreksi ulang -->
                                                        <button type="button" 
                                                                onclick="openModalKoreksiSuperadmin(<?= htmlspecialchars(json_encode($it)) ?>)" 
                                                                class="h-6 px-2 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold transition flex items-center gap-1 border border-slate-300 cursor-pointer" 
                                                                title="Khusus Pemilik/Superadmin: Koreksi jika terjadi kesalahan input kasir saat menghitung">
                                                            <svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                                            <span>Koreksi</span>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <!-- BARANG ANOMALI (PERLU MUTASI) -->
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button" 
                                                            onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" 
                                                            class="h-7 px-3 rounded <?= $selisih > 0 ? 'bg-blue-700 hover:bg-blue-800' : 'bg-slate-900 hover:bg-slate-800' ?> text-white text-[11px] font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Buka dialog konfirmasi untuk meninjau dan mengeksekusi mutasi">
                                                        <span>
                                                            <?php if ($selisih > 0): ?>
                                                                Koreksi Surplus (+<?= $selisih ?>)
                                                            <?php elseif ($fRusak > 0): ?>
                                                                Mutasi Rusak (-<?= $fRusak ?>)
                                                            <?php elseif ($fExp > 0): ?>
                                                                Mutasi Expired (-<?= $fExp ?>)
                                                            <?php else: ?>
                                                                Mutasi Hilang (<?= $selisih ?>)
                                                            <?php endif; ?>
                                                        </span>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>


<!-- Modal Koreksi Ulang Hitungan Fisik (Khusus Superadmin / Pemilik Toko) -->
            <div id="modal_koreksi_superadmin" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-zcBrd">
                    <div class="px-6 py-4 border-b border-zcBrd flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-zcTxt">Koreksi Hitungan Fisik</h3>
                                <p class="text-[11px] text-amber-700 font-semibold">Khusus Otoritas Pemilik (Superadmin)</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('modal_koreksi_superadmin').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form method="POST" class="p-6 space-y-4 text-xs">
                        <input type="hidden" name="aksi" value="koreksi_hitung_superadmin">
                        <input type="hidden" name="id_detail" id="koreksi_id_detail">

                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Produk yang Dikoreksi:</div>
                            <div class="font-bold text-slate-900 text-xs" id="koreksi_nama_produk">-</div>
                            <div class="text-[11px] text-slate-600 font-mono" id="koreksi_batch_info">-</div>
                        </div>

                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Fitur ini digunakan jika terjadi kekeliruan pencatatan lapangan oleh kasir atau admin. Mengubah angka fisik di sini akan memperbarui status selisih secara otomatis.
                        </p>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block font-bold text-emerald-800 text-[11px] mb-1">Fisik Baik:</label>
                                <input type="number" min="0" name="koreksi_fisik_baik" id="koreksi_fisik_baik" class="w-full border border-slate-300 rounded-lg p-2 text-center font-mono font-bold text-xs focus:ring-2 focus:ring-slate-900" required>
                            </div>
                            <div>
                                <label class="block font-bold text-amber-800 text-[11px] mb-1">Fisik Rusak:</label>
                                <input type="number" min="0" name="koreksi_fisik_rusak" id="koreksi_fisik_rusak" class="w-full border border-slate-300 rounded-lg p-2 text-center font-mono font-bold text-xs focus:ring-2 focus:ring-slate-900" required>
                            </div>
                            <div>
                                <label class="block font-bold text-rose-800 text-[11px] mb-1">Fisik Expired:</label>
                                <input type="number" min="0" name="koreksi_fisik_expired" id="koreksi_fisik_expired" class="w-full border border-slate-300 rounded-lg p-2 text-center font-mono font-bold text-xs focus:ring-2 focus:ring-slate-900" required>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-zcTxt mb-1">Alasan Koreksi oleh Superadmin (Wajib):</label>
                            <textarea name="alasan_koreksi" rows="2" class="w-full border border-zcBrd rounded-xl p-2.5 text-xs focus:ring-1 focus:ring-zc" placeholder="Contoh: Kesalahan salah hitung kasir di rak etalase..." required></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-zcBrd">
                            <button type="button" onclick="document.getElementById('modal_koreksi_superadmin').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition cursor-pointer">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-black text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Simpan Koreksi Fisik</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Eksekusi Mutasi Selisih (Obat vs Alkes & Multi-Alasan Defisit) -->
            <div id="modal_mutasi" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden border border-zcBrd max-h-[92vh] flex flex-col">
                    <div class="px-6 py-4 border-b border-zcBrd flex items-center justify-between bg-white shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-zcTxt" id="mutasi_modal_title">Verifikasi Mutasi Selisih Stok</h3>
                                <p class="text-[11px] text-zcMut">Rekonsiliasi saldo buku dan pencatatan alasan mutasi ke Kartu Stok</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('modal_mutasi').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form method="POST" id="form_mutasi_selisih" class="p-6 space-y-4 text-xs overflow-y-auto">
                        <input type="hidden" name="aksi" value="eksekusi_mutasi_baris">
                        <input type="hidden" name="id_detail" id="mutasi_id_detail">
                        <input type="hidden" name="id_batch_target" id="mutasi_id_batch_target">
                        
                        <!-- Rincian Produk & Selisih -->
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                            <div class="flex justify-between">
                                <span class="text-zcMut">Komoditas:</span>
                                <span id="mutasi_komoditas_badge" class="font-bold"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zcMut">Produk:</span>
                                <span id="mutasi_nama_produk" class="font-bold text-zcTxt text-right"></span>
                            </div>
                            <div class="flex justify-between font-mono">
                                <span class="text-zcMut">Selisih Fisik:</span>
                                <span id="mutasi_selisih_badge" class="font-extrabold text-sm"></span>
                            </div>
                        </div>

                        <!-- Form Khusus Obat & BMHP (Batch Terikat Langsung Sesuai Baris) -->
                        <div id="container_obat_batch" class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-800 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    Identitas Batch Terkait (Sesuai Baris)
                                </span>
                                <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 font-mono text-[10px] font-bold" id="mutasi_batch_satuan_label"></span>
                            </div>
                            <div class="p-2 bg-white border border-slate-200 rounded-lg flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-semibold uppercase">Nomor Batch</div>
                                    <div class="font-mono font-bold text-slate-900 text-xs" id="mutasi_batch_display_no">-</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] text-slate-400 font-semibold uppercase">Kedaluwarsa (Exp)</div>
                                    <div class="font-mono font-semibold text-slate-700 text-xs" id="mutasi_batch_display_exp">-</div>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-500 leading-tight">
                                Mutasi stok dialokasikan langsung ke identitas batch ini untuk akurasi kartu stok dan pelacakan FEFO.
                            </p>
                        </div>

                        <!-- SECTION ALOKASI ALASAN DEFISIT (Minus) -->
                        <div id="container_alokasi_defisit" class="space-y-3">
                            <div>
                                <label class="block font-bold text-slate-800 text-xs mb-1">Pilih Kategori Beban Selisih (Wajib):</label>
                                <p class="text-[11px] text-slate-500">Pilih satu alasan utama pembebanan selisih ke Kartu Stok toko:</p>
                            </div>

                            <!-- Pilihan Kategori Utama via Radio List Bersih & Profesional -->
                            <div class="space-y-2" id="kategori_radio_cards">
                                <!-- Option 1: Rusak -->
                                <label id="card_kat_rusak" onclick="selectKategoriCard('rusak')" class="relative flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="rusak" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="font-bold text-xs text-slate-900">1. Mutasi Barang Rusak</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Kemasan pecah, bocor, cacat fisik, atau rusak segel di etalase/gudang.</p>
                                    </div>
                                </label>

                                <!-- Option 2: Hilang -->
                                <label id="card_kat_hilang" onclick="selectKategoriCard('hilang')" class="relative flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="hilang" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="font-bold text-xs text-slate-900">2. Mutasi Barang Hilang</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Fisik tidak ditemukan di rak etalase maupun gudang setelah pencarian ulang.</p>
                                    </div>
                                </label>

                                <!-- Option 3: Kedaluwarsa (Khusus Batch dengan ED) -->
                                <label id="card_kat_expired" onclick="selectKategoriCard('expired')" class="relative flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="expired" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="font-bold text-xs text-slate-900">3. Mutasi Barang Kedaluwarsa</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Batch telah melewati batas tanggal kedaluwarsa dan harus dimusnahkan.</p>
                                    </div>
                                </label>

                                <!-- Option 4: Salah Catat -->
                                <label id="card_kat_salah_catat" onclick="selectKategoriCard('salah_catat')" class="relative flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="salah_catat" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="font-bold text-xs text-slate-900">4. Koreksi Salah Catat Administrasi</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Kekeliruan input saat penerimaan atau penjualan di masa lalu.</p>
                                    </div>
                                </label>

                                <!-- Option 5: Lainnya -->
                                <label id="card_kat_lainnya" onclick="selectKategoriCard('lainnya')" class="relative flex items-start gap-3 p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition select-none">
                                    <input type="radio" name="kategori_beban_radio" value="lainnya" class="mt-0.5 text-slate-900 focus:ring-slate-900">
                                    <div class="flex-1">
                                        <div class="font-bold text-xs text-slate-900">5. Alasan Lainnya (Kustom)</div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Penyebab khusus di luar opsi di atas (misal sampel uji laboratorium).</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Input Teks Alasan Lainnya -->
                            <div id="box_teks_alasan_lainnya" class="hidden pt-1">
                                <label class="block font-semibold text-slate-700 text-xs mb-1">Tuliskan Keterangan Alasan Khusus:</label>
                                <input type="text" name="teks_alasan_lainnya" id="teks_alasan_lainnya" placeholder="Contoh: Kerusakan Kemasan Akibat Lembap / Sampel Uji..." class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-white focus:ring-2 focus:ring-slate-900">
                            </div>

                            <!-- Banner Konfirmasi Alokasi Penuh Sesuai Kartu yang Dipilih -->
                            <div id="banner_alokasi_penuh" class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-500 block text-[10px] font-semibold uppercase tracking-wider">Pembebanan Defisit:</span>
                                    <span class="font-bold text-slate-800" id="teks_alokasi_penuh">100% dialokasikan ke kategori terpilih</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-white font-mono font-bold text-xs" id="badge_alokasi_penuh_qty">- Unit</span>
                            </div>

                            <!-- Opsi Lanjutan: Split Kuantitas Multi-Kategori (Accordion/Toggle Bersih) -->
                            <div class="pt-2 border-t border-slate-200">
                                <label class="flex items-center gap-2 cursor-pointer select-none text-xs font-semibold text-slate-700 hover:text-slate-900">
                                    <input type="checkbox" id="toggle_split_kategori" onchange="toggleSplitKategoriMode(this.checked)" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900 cursor-pointer">
                                    <span>Bagi kuantitas ke beberapa alasan berbeda (Split Kuantitas Multi-Kategori)</span>
                                </label>
                                <p class="text-[10px] text-slate-400 pl-6 mt-0.5">Aktifkan hanya jika dalam satu baris selisih terdapat kombinasi penyebab (contoh: 1 unit rusak dan 1 unit hilang).</p>

                                <!-- Box Input Breakdown Kuantitas (Hanya Muncul Jika Toggle Dicentang) -->
                                <div id="box_split_kuantitas" class="hidden mt-3 space-y-2 bg-slate-50 border border-slate-200 p-3 rounded-xl">
                                    <div class="flex items-center justify-between pb-1 border-b border-slate-200">
                                        <span class="text-[11px] font-bold text-slate-700">Rincian Pembagian Kuantitas:</span>
                                        <span id="badge_alokasi_counter" class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"></span>
                                    </div>

                                    <!-- 1. Rusak -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-rose-700">Barang Rusak</span>
                                            <span class="block text-[10px] text-slate-400">Kemasan pecah / cacat fisik</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_rusak" id="alokasi_rusak" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 2. Hilang -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-amber-700">Barang Hilang</span>
                                            <span class="block text-[10px] text-slate-400">Tidak ditemukan di rak</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_hilang" id="alokasi_hilang" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 3. Expired -->
                                    <div id="row_alokasi_expired" class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-purple-700">Barang Kedaluwarsa</span>
                                            <span class="block text-[10px] text-slate-400">Lewat tanggal ED</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_expired" id="alokasi_expired" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 4. Salah Catat -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-slate-700">Koreksi Salah Catat</span>
                                            <span class="block text-[10px] text-slate-400">Administrasi masa lalu</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_salah_catat" id="alokasi_salah_catat" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>

                                    <!-- 5. Lainnya -->
                                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                        <div>
                                            <span class="font-bold text-xs text-indigo-700">Alasan Lainnya</span>
                                            <span class="block text-[10px] text-slate-400">Penyebab khusus</span>
                                        </div>
                                        <input type="number" min="0" name="alokasi_lainnya" id="alokasi_lainnya" class="w-20 text-center font-mono font-bold text-xs border border-slate-300 rounded-lg p-1.5 focus:ring-2 focus:ring-slate-900" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION SURPLUS / KELEBIHAN FISIK (Plus) -->
                        <div id="container_alokasi_surplus" class="hidden p-3.5 bg-blue-50 border border-blue-200 rounded-xl space-y-2">
                            <div>
                                <span class="font-bold text-blue-900 block">Kelebihan Fisik (Surplus Stok)</span>
                                <p class="text-[11px] text-blue-800">
                                    Fisik lapangan melebihi catatan sistem. Tentukan penyebab kelebihan barang:
                                </p>
                            </div>
                            <div class="space-y-2">
                                <select name="alasan_mutasi_surplus" id="alasan_mutasi_surplus" class="w-full border border-blue-300 rounded-lg p-2 text-xs font-semibold bg-white text-slate-800" onchange="toggleSurplusCustomText(this.value)">
                                    <option value="Koreksi Salah Catat / Ditemukan">Koreksi Salah Catat / Ditemukan Kembali di Rak</option>
                                    <option value="Kelebihan Kirim Supplier">Kelebihan Kirim dari Supplier (Belum Tercatat)</option>
                                    <option value="Retur Pembatalan Pelanggan">Retur / Pembatalan Pelanggan Belum Terdata</option>
                                    <option value="Lainnya">Alasan Lainnya (Tulis Manual)...</option>
                                </select>
                                <div id="box_surplus_teks_lainnya" class="hidden">
                                    <input type="text" name="teks_surplus_lainnya" id="teks_surplus_lainnya" placeholder="Tuliskan penyebab kelebihan stok..." class="w-full border border-blue-300 rounded-lg p-2 text-xs bg-white">
                                </div>
                            </div>
                        </div>

                        <!-- Form Khusus Alkes (Pemilihan SN Spesifik) -->
                        <div id="container_alkes_sn" class="hidden space-y-3 p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl">
                            <div class="p-2.5 bg-white border border-emerald-200 rounded-lg text-xs text-emerald-950 leading-relaxed">
                                <strong>Pelacakan Unit Fisik (Serial Number Tracking):</strong>
                                Centang nomor seri (SN) spesifik dari unit fisik yang terdampak, lalu pilih apakah unit tersebut berstatus <strong>Barang Rusak</strong> (cacat fisik/fungsi) atau <strong>Barang Hilang</strong> (tidak ditemukan di rak).
                            </div>
                            <div id="alkes_minus_sn_box" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block font-bold text-emerald-900 text-xs">
                                        Pilih Serial Number Terkait (<span id="count_sn_needed" class="font-black text-rose-700"></span> unit):
                                    </label>
                                    <div id="sn_selection_status" class="text-[11px]"></div>
                                </div>
                                <div id="alkes_sn_checkbox_list" class="space-y-1 max-h-36 overflow-y-auto p-2 bg-white rounded-lg border border-emerald-200">
                                    <!-- Loaded via AJAX -->
                                </div>
                            </div>
                            <div id="alkes_plus_sn_box" class="hidden space-y-2">
                                <label class="block font-bold text-emerald-900 text-xs">
                                    Input / Scan Serial Number yang Baru Ditemukan:
                                </label>
                                <input type="text" name="input_new_sns" id="input_new_sns" placeholder="Contoh: SN-1234, SN-5678 (pisahkan koma jika > 1)" class="w-full border border-emerald-300 rounded-lg p-2 font-mono text-xs bg-white">
                            </div>
                        </div>

                        <div>
                            <label id="label_keterangan_mutasi" class="block font-bold text-zcTxt mb-1">Catatan / Keterangan Mutasi (Opsional):</label>
                            <textarea name="keterangan_mutasi" id="keterangan_mutasi" rows="2" class="w-full border border-zcBrd rounded-xl p-2 text-xs focus:ring-1 focus:ring-zc" placeholder="Keterangan kondisi fisik di lapangan..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-zcBrd">
                            <button type="button" onclick="document.getElementById('modal_mutasi').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" id="btn_submit_mutasi" class="px-5 py-2 bg-zc hover:bg-zcHv disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-sm transition">Simpan &amp; Eksekusi Mutasi</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Batalkan Sesi (Khusus Superadmin, Jalur Darurat) -->
            <div id="modal_batal_sesi" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-zcBrd">
                    <div class="px-6 py-4 border-b border-zcBrd flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-zcTxt">Batalkan Sesi Stok Opname</h3>
                                <p class="text-[11px] text-rose-600 font-semibold">Jalur Darurat Operasional</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('modal_batal_sesi').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form method="POST" class="p-6 space-y-4 text-xs">
                        <input type="hidden" name="aksi" value="batalkan_sesi">
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            Membatalkan sesi akan <strong>mencabut seluruh pembekuan transaksi</strong> secara seketika. Mutasi yang sudah terlanjur dieksekusi akan tetap tercatat di kartu stok demi integritas log rekonsiliasi.
                        </p>
                        <div>
                            <label class="block font-bold text-zcTxt mb-1.5">Alasan Pembatalan Sesi (Wajib):</label>
                            <textarea name="alasan_pembatalan" required rows="3" class="w-full border border-zcBrd rounded-xl p-3 text-xs focus:ring-1 focus:ring-zc" placeholder="Contoh: Terjadi kendala operasional darurat di toko..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-zcBrd">
                            <button type="button" onclick="document.getElementById('modal_batal_sesi').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs transition">Ya, Batalkan Sesi Opname</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Rekonsiliasi Semua Selisih Sekaligus (Mass Reconciliation) -->
            <div id="modal_rekonsiliasi_masal" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-zcBrd">
                    <div class="px-6 py-4 border-b border-zcBrd flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-zcTxt">Rekonsiliasi Semua Selisih</h3>
                                <p class="text-[11px] text-zcMut">Penyesuaian stok dan kartu stok otomatis</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('modal_rekonsiliasi_masal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form method="POST" class="p-6 space-y-4 text-xs">
                        <input type="hidden" name="aksi" value="rekonsiliasi_masal">
                        <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-[11px] leading-relaxed">
                            Terdapat <strong class="text-slate-900"><?= $stats['berselisih'] - $stats['sudah_dimutasi'] ?> baris item berselisih</strong> yang belum dimutasi. Sistem akan melakukan penyesuaian otomatis:
                            <ul class="list-disc pl-4 mt-1.5 space-y-1 text-slate-600">
                                <li>Barang Kurang (Minus): dipotong dari stok batch/toko &amp; dicatat mutasi keluar.</li>
                                <li>Barang Lebih (Surplus): ditambahkan ke stok batch/toko &amp; dicatat mutasi masuk.</li>
                                <li>Seluruh pergerakan dicatat resmi ke Kartu Stok dan status baris menjadi <em>Sudah Dimutasi</em>.</li>
                            </ul>
                        </div>
                        <div>
                            <label class="block font-bold text-zcTxt mb-1.5">Alasan Mutasi Bersama:</label>
                            <select name="alasan_mutasi_masal" class="w-full border border-zcBrd rounded-xl px-3 py-2 text-xs bg-white focus:ring-1 focus:ring-zc">
                                <option value="Selisih Stok Opname">Selisih Stok Opname Lapangan</option>
                                <option value="Koreksi Manual">Koreksi Manual Hasil Pemeriksaan Fisik</option>
                                <option value="Barang Rusak">Barang Rusak Saat Penyimpanan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-zcTxt mb-1.5">Keterangan / Catatan Tambahan (Opsional):</label>
                            <input type="text" name="keterangan_masal" class="w-full border border-zcBrd rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-zc" placeholder="Contoh: Rekonsiliasi masal sesi opname triwulan...">
                        </div>
                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-zcBrd">
                            <button type="button" onclick="document.getElementById('modal_rekonsiliasi_masal').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-xs transition">Ya, Rekonsiliasi Seluruh Selisih</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form Tersembunyi untuk Single Save -->
            <form method="POST" id="form_single_save" class="hidden">
                <input type="hidden" name="aksi" value="simpan_hitung_fisik">
                <input type="hidden" name="id_detail" id="single_save_id">
                <input type="hidden" name="hitung_fisik" id="single_save_val">
            </form>

        <?php endif; ?>

    </div>

<script>
// Alias untuk kompatibilitas
window.isiCepatAngkaDemo = function() { isiOtomatisAngkaDemo(); };

// Fungsi Simpan Satu Baris Fisik
function simpanSatuBaris(idDetail) {
    let inputEl = document.getElementById('input_fisik_' + idDetail);
    if (!inputEl || inputEl.value === '' || inputEl.value === null) {
        alert('Harap masukkan angka hitung fisik!');
        if (inputEl) inputEl.focus();
        return;
    }

    let valFisik = parseInt(inputEl.value, 10);
    let valSistem = parseInt(inputEl.dataset.sistem, 10);
    let isKasir = <?= json_encode($isKasir) ?>;

    // Untuk Kasir: Strict Blind Count (TIDAK BOLEH memunculkan popup selisih atau angka sistem!)
    if (isKasir || isNaN(valSistem)) {
        document.getElementById('single_save_id').value = idDetail;
        document.getElementById('single_save_val').value = valFisik;
        document.getElementById('form_single_save').submit();
        return;
    }

    // Untuk Admin / Superadmin: Jika terdeteksi selisih fisik vs sistem, berikan konfirmasi langsung
    if (valFisik !== valSistem) {
        let sel = valFisik - valSistem;
        let selText = (sel > 0 ? '+' + sel : sel);
        let defText = (sel < 0 ? 'Defisit / Berkurang' : 'Surplus / Bertambah');
        
        Swal.fire({
            title: 'Terdeteksi Selisih Fisik: ' + selText + ' Unit',
            html: `
                <div class="text-left text-xs text-slate-700 space-y-2">
                    <p>Hitung Fisik: <strong class="font-mono text-sm">${valFisik}</strong> | Stok Sistem: <strong class="font-mono text-sm">${valSistem}</strong></p>
                    <div class="p-2.5 rounded-xl ${sel < 0 ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-blue-50 border border-blue-200 text-blue-900'} font-bold">
                        Status: ${defText} (${selText} unit)
                    </div>
                    <p class="text-[11px] text-slate-500">
                        Hasil hitung fisik akan disimpan dan sistem akan langsung membuka formulir penyesuaian untuk memilih alasan selisih (Rusak, Hilang, Expired, atau Salah Catat) dan alokasi batch terkait.
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Simpan & Lanjut Penyesuaian',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#1a75d2',
            cancelButtonColor: '#64748b',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold text-xs px-4 py-2',
                cancelButton: 'rounded-xl font-bold text-xs px-4 py-2'
            }
        }).then((res) => {
            if (res.isConfirmed) {
                document.getElementById('single_save_id').value = idDetail;
                document.getElementById('single_save_val').value = valFisik;
                document.getElementById('form_single_save').submit();
            }
        });
        return;
    }

    document.getElementById('single_save_id').value = idDetail;
    document.getElementById('single_save_val').value = inputEl.value;
    document.getElementById('form_single_save').submit();
}

function openModalKoreksiSuperadmin(item) {
    document.getElementById('koreksi_id_detail').value = item.id;
    document.getElementById('koreksi_nama_produk').innerText = item.nama_produk + ' — ' + item.nama_variasi;
    document.getElementById('koreksi_batch_info').innerText = 'Batch: ' + (item.no_batch || 'SN Tracker') + ' | Satuan: ' + (item.nama_satuan || 'Unit');
    document.getElementById('koreksi_fisik_baik').value = item.fisik_baik || 0;
    document.getElementById('koreksi_fisik_rusak').value = item.fisik_rusak || 0;
    document.getElementById('koreksi_fisik_expired').value = item.fisik_expired || 0;
    document.getElementById('modal_koreksi_superadmin').classList.remove('hidden');
}

function openModalRecount(idDetail, hitungLama, namaProduk) {
    document.getElementById('recount_id_detail').value = idDetail;
    document.getElementById('recount_nama_produk').innerText = namaProduk;
    document.getElementById('recount_hitung_baru').value = hitungLama;
    document.getElementById('modal_recount').classList.remove('hidden');
}

let currentOpnameItem = null;
let currentAbsSelisih = 0;

function updateAllocationStatus() {
    let r = parseInt(document.getElementById('alokasi_rusak')?.value, 10) || 0;
    let h = parseInt(document.getElementById('alokasi_hilang')?.value, 10) || 0;
    let e = parseInt(document.getElementById('alokasi_expired')?.value, 10) || 0;
    let s = parseInt(document.getElementById('alokasi_salah_catat')?.value, 10) || 0;
    let l = parseInt(document.getElementById('alokasi_lainnya')?.value, 10) || 0;
    let sum = r + h + e + s + l;
    let target = currentAbsSelisih;
    let satuan = currentOpnameItem ? (currentOpnameItem.nama_satuan || 'Unit') : 'Unit';

    // Munculkan input teks alasan lainnya jika kuantitas lainnya diisi > 0
    let boxTeksLainnya = document.getElementById('box_teks_alasan_lainnya');
    if (boxTeksLainnya) {
        if (l > 0) {
            boxTeksLainnya.classList.remove('hidden');
        } else {
            boxTeksLainnya.classList.add('hidden');
        }
    }

    let counterBadge = document.getElementById('badge_alokasi_counter');
    let btnSubmit = document.getElementById('btn_submit_mutasi');

    if (counterBadge) {
        if (sum === target && target > 0) {
            counterBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
            counterBadge.innerText = `Sesuai: ${sum}/${target} ${satuan}`;
            if (btnSubmit) btnSubmit.disabled = false;
        } else if (sum < target) {
            let sisa = target - sum;
            counterBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-amber-100 text-amber-800 border border-amber-300';
            counterBadge.innerText = `Kurang: ${sum}/${target} (Sisa ${sisa})`;
            if (btnSubmit) btnSubmit.disabled = true;
        } else {
            let lebih = sum - target;
            counterBadge.className = 'px-2.5 py-1 rounded-full text-[11px] font-mono font-bold bg-rose-100 text-rose-800 border border-rose-300';
            counterBadge.innerText = `Lebih: ${sum}/${target} (+${lebih})`;
            if (btnSubmit) btnSubmit.disabled = true;
        }
    }
}

let currentSelectedKategori = 'rusak';

function selectKategoriCard(kat) {
    currentSelectedKategori = kat;
    
    // Update Radio Check
    let radios = document.querySelectorAll('input[name="kategori_beban_radio"]');
    radios.forEach(r => {
        r.checked = (r.value === kat);
    });

    // Update Card Styling
    let allCards = ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'];
    allCards.forEach(c => {
        let cardEl = document.getElementById('card_kat_' + c);
        if (cardEl) {
            if (c === kat) {
                cardEl.classList.add('border-slate-900', 'bg-slate-50', 'ring-1', 'ring-slate-900');
                cardEl.classList.remove('border-slate-200', 'bg-white');
            } else {
                cardEl.classList.remove('border-slate-900', 'bg-slate-50', 'ring-1', 'ring-slate-900');
                cardEl.classList.add('border-slate-200', 'bg-white');
            }
        }
    });

    // Handle Teks Alasan Lainnya & Kewajiban Keterangan pada Mutasi Hilang
    let boxTeks = document.getElementById('box_teks_alasan_lainnya');
    let inputTeks = document.getElementById('teks_alasan_lainnya');
    if (boxTeks) {
        if (kat === 'lainnya') {
            boxTeks.classList.remove('hidden');
            if (inputTeks) inputTeks.focus();
        } else {
            boxTeks.classList.add('hidden');
        }
    }

    let ketMutasi = document.getElementById('keterangan_mutasi');
    let lblKetMutasi = document.getElementById('label_keterangan_mutasi');
    if (ketMutasi && lblKetMutasi) {
        if (kat === 'hilang') {
            ketMutasi.required = true;
            lblKetMutasi.innerText = 'Catatan / Keterangan Mutasi (Wajib diisi untuk Mutasi Hilang):';
            lblKetMutasi.className = 'block font-bold text-rose-800 mb-1';
            if (!ketMutasi.value.trim()) {
                ketMutasi.value = 'Selisih kurang ditemukan saat pengecekan fisik rak etalase';
            }
        } else {
            ketMutasi.required = false;
            lblKetMutasi.innerText = 'Catatan / Keterangan Mutasi (Opsional):';
            lblKetMutasi.className = 'block font-bold text-zcTxt mb-1';
        }
    }

    let isSplit = document.getElementById('toggle_split_kategori')?.checked || false;
    let satuan = currentOpnameItem ? (currentOpnameItem.nama_satuan || 'Unit') : 'Unit';

    if (!isSplit) {
        // Alokasi 100% langsung ke kategori terpilih
        allCards.forEach(c => {
            let el = document.getElementById('alokasi_' + c);
            if (el) el.value = (c === kat) ? currentAbsSelisih : 0;
        });

        let names = {
            'rusak': 'Mutasi Barang Rusak',
            'hilang': 'Mutasi Barang Hilang',
            'expired': 'Mutasi Barang Kedaluwarsa',
            'salah_catat': 'Koreksi Salah Catat Administrasi',
            'lainnya': 'Alasan Lainnya (Kustom)'
        };

        let teksPenuh = document.getElementById('teks_alokasi_penuh');
        let badgeQty = document.getElementById('badge_alokasi_penuh_qty');
        if (teksPenuh) teksPenuh.innerText = '100% dialokasikan ke ' + (names[kat] || kat);
        if (badgeQty) badgeQty.innerText = currentAbsSelisih + ' ' + satuan;

        let btnSubmit = document.getElementById('btn_submit_mutasi');
        if (btnSubmit) btnSubmit.disabled = false;
    }

    updateAllocationStatus();
}

function toggleSplitKategoriMode(isSplit) {
    let boxSplit = document.getElementById('box_split_kuantitas');
    let bannerPenuh = document.getElementById('banner_alokasi_penuh');

    if (isSplit) {
        if (boxSplit) boxSplit.classList.remove('hidden');
        if (bannerPenuh) bannerPenuh.classList.add('hidden');
    } else {
        if (boxSplit) boxSplit.classList.add('hidden');
        if (bannerPenuh) bannerPenuh.classList.remove('hidden');
        // Reset kembali ke 100% pada kartu terpilih
        selectKategoriCard(currentSelectedKategori);
    }
    updateAllocationStatus();
}

function quickFillReason(type) {
    selectKategoriCard(type);
}

function applyKategoriBeban(kat) {
    selectKategoriCard(kat);
}

function toggleSurplusCustomText(val) {
    let box = document.getElementById('box_surplus_teks_lainnya');
    if (box) {
        if (val === 'Lainnya') {
            box.classList.remove('hidden');
            document.getElementById('teks_surplus_lainnya')?.focus();
        } else {
            box.classList.add('hidden');
        }
    }
}

function toggleSnCustomInput(selectEl, snId) {
    let customInput = document.getElementById('sn_custom_input_' + snId);
    if (customInput) {
        if (selectEl.value === 'Lainnya') {
            customInput.classList.remove('hidden');
            customInput.focus();
        } else {
            customInput.classList.add('hidden');
        }
    }
}

function openModalMutasi(item) {
    currentOpnameItem = item;
    let selisih = parseInt(item.selisih) || 0;
    let isMinus = (selisih < 0);
    let absSel = Math.abs(selisih);
    currentAbsSelisih = absSel;

    document.getElementById('mutasi_id_detail').value = item.id;
    document.getElementById('mutasi_id_batch_target').value = item.id_batch || '';
    document.getElementById('mutasi_nama_produk').innerText = item.nama_produk + ' — ' + item.nama_variasi;
    
    let katProduk = item.kategori || (item.jenis_komoditas === 'Obat' ? 'Obat' : 'Alat Kesehatan');
    let isAlkesSN = (item.jenis_komoditas === 'Alkes');
    let isAlkesBMHP = (katProduk === 'Alat Kesehatan' && !isAlkesSN);
    let badgeText = katProduk;
    if (isAlkesSN) badgeText += ' (Serial Number)';
    else if (isAlkesBMHP) badgeText += ' (BMHP / Batch)';
    let badgeEl = document.getElementById('mutasi_komoditas_badge');
    if (badgeEl) {
        badgeEl.innerText = badgeText;
        badgeEl.className = (katProduk === 'Obat') ? 'font-bold text-blue-700' : 'font-bold text-teal-700';
    }

    let selBadge = document.getElementById('mutasi_selisih_badge');
    selBadge.innerText = (selisih > 0 ? '+' + selisih : selisih) + ' ' + (item.nama_satuan || 'Unit');
    selBadge.className = isMinus ? 'font-extrabold text-sm text-rose-600' : 'font-extrabold text-sm text-blue-600';

    let containerObat = document.getElementById('container_obat_batch');
    let containerAlkes = document.getElementById('container_alkes_sn');
    let containerDefisit = document.getElementById('container_alokasi_defisit');
    let containerSurplus = document.getElementById('container_alokasi_surplus');
    let cardExpired = document.getElementById('card_kat_expired');
    let rowExpired = document.getElementById('row_alokasi_expired');
    let btnSubmit = document.getElementById('btn_submit_mutasi');

    let isBatchTracked = (item.id_batch !== null && item.id_batch !== undefined && item.id_batch !== '' && item.id_batch != 0);

    if (isBatchTracked) {
        containerObat.classList.remove('hidden');
        containerAlkes.classList.add('hidden');
        document.getElementById('mutasi_batch_display_no').innerText = item.no_batch || 'Batch Terdaftar';
        document.getElementById('mutasi_batch_display_exp').innerText = item.tgl_exp ? item.tgl_exp : '-';
        document.getElementById('mutasi_batch_satuan_label').innerText = item.nama_satuan || 'Satuan';
        if (cardExpired) {
            if (item.tgl_exp) cardExpired.classList.remove('hidden');
            else cardExpired.classList.add('hidden');
        }
        if (rowExpired) {
            if (item.tgl_exp) rowExpired.classList.remove('hidden');
            else rowExpired.classList.add('hidden');
        }
    } else {
        containerObat.classList.add('hidden');
        if (cardExpired) cardExpired.classList.add('hidden');
        if (rowExpired) rowExpired.classList.add('hidden');
    }

    if (isMinus) {
        if (isBatchTracked) {
            containerDefisit.classList.remove('hidden');
            containerAlkes.classList.add('hidden');
            containerSurplus.classList.add('hidden');

            // Reset Split toggle
            let toggleSplit = document.getElementById('toggle_split_kategori');
            if (toggleSplit) toggleSplit.checked = false;
            toggleSplitKategoriMode(false);

            // Evaluasi apakah tanggal kedaluwarsa sudah terlewati atau ada rusak tercatat
            let defaultKat = 'hilang';
            if (parseInt(item.fisik_rusak) > 0) {
                defaultKat = 'rusak';
            } else if (parseInt(item.fisik_expired) > 0) {
                defaultKat = 'expired';
            } else if (item.tgl_exp) {
                let expD = new Date(item.tgl_exp);
                let today = new Date();
                today.setHours(0,0,0,0);
                if (expD <= today) defaultKat = 'expired';
            }

            selectKategoriCard(defaultKat);

            ['rusak', 'hilang', 'expired', 'salah_catat', 'lainnya'].forEach(f => {
                let el = document.getElementById('alokasi_' + f);
                if (el) {
                    el.max = absSel;
                    el.oninput = updateAllocationStatus;
                }
            });

            updateAllocationStatus();
        } else {
            containerDefisit.classList.add('hidden');
            containerAlkes.classList.remove('hidden');
            containerSurplus.classList.add('hidden');
            let minusBox = document.getElementById('alkes_minus_sn_box');
            let plusBox = document.getElementById('alkes_plus_sn_box');
            minusBox.classList.remove('hidden');
            plusBox.classList.add('hidden');
            document.getElementById('count_sn_needed').innerText = absSel;
            if (btnSubmit) btnSubmit.disabled = true;

            let snListContainer = document.getElementById('alkes_sn_checkbox_list');
            snListContainer.innerHTML = '<div class="text-xs text-slate-400 py-2 text-center">Memuat Serial Number aktif...</div>';

            fetch('../api/get_batch_sn.php?action=get_available_sns&id_variasi=' + item.id_variasi)
                .then(r => r.json())
                .then(data => {
                    let sns = data.sns || data.items || [];
                    if (!sns.length) {
                        snListContainer.innerHTML = '<div class="text-xs text-rose-600 p-2">Tidak ada SN berstatus Tersedia di database.</div>';
                        return;
                    }
                    let html = '';
                    let defaultSnReason = (parseInt(item.fisik_rusak) > 0) ? 'Barang Rusak' : 'Barang Hilang';
                    sns.forEach(s => {
                        html += `
                            <div class="sn-item-card p-2.5 border border-slate-200 rounded-lg bg-white space-y-2 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <label class="sn-item-label flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" name="selected_sn_ids[]" value="${s.id}" class="rounded border-slate-300 text-zc focus:ring-zc sn-checkbox cursor-pointer" onchange="toggleSnItemReason(this, ${s.id})">
                                        <span class="font-mono text-xs font-bold text-slate-900">${s.serial_number}</span>
                                    </label>
                                    <span class="text-[10px] text-slate-400 font-medium">Status: ${s.status}</span>
                                </div>
                                <div id="sn_reason_box_${s.id}" class="hidden pt-2 border-t border-slate-100 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[11px] text-slate-700 font-semibold">Kategori Status SN:</span>
                                        <select name="sn_reason[${s.id}]" class="border border-slate-300 rounded-lg px-2.5 py-1 text-xs font-semibold bg-white focus:ring-1 focus:ring-zc text-slate-800" onchange="toggleSnCustomInput(this, ${s.id})">
                                            <option value="Barang Rusak" ${defaultSnReason === 'Barang Rusak' ? 'selected' : ''}>Barang Rusak (Cacat Fisik / Fungsi)</option>
                                            <option value="Barang Hilang" ${defaultSnReason === 'Barang Hilang' ? 'selected' : ''}>Barang Hilang (Tidak Ditemukan di Rak)</option>
                                            <option value="Koreksi Salah Catat / Ditemukan">Koreksi Salah Catat Administrasi</option>
                                            <option value="Lainnya">Alasan Lainnya (Kustom)...</option>
                                        </select>
                                    </div>
                                    <input type="text" name="sn_custom_reason[${s.id}]" id="sn_custom_input_${s.id}" placeholder="Tuliskan penyebab spesifik untuk SN ini..." class="hidden w-full border border-slate-300 rounded px-2 py-1 text-xs bg-slate-50 focus:bg-white text-slate-700">
                                </div>
                            </div>
                        `;
                    });
                    snListContainer.innerHTML = html;
                    updateAlkesSnCheckboxes();
                })
                .catch(() => {
                    snListContainer.innerHTML = '<div class="text-xs text-rose-600 p-2">Gagal memuat SN.</div>';
                });
        }
    } else {
        containerDefisit.classList.add('hidden');
        containerSurplus.classList.remove('hidden');
        let selSurplus = document.getElementById('alasan_mutasi_surplus');
        if (selSurplus) selSurplus.value = 'Koreksi Salah Catat / Ditemukan';
        toggleSurplusCustomText('Koreksi Salah Catat / Ditemukan');
        if (btnSubmit) btnSubmit.disabled = false;

        if (!isBatchTracked) {
            containerAlkes.classList.remove('hidden');
            let minusBox = document.getElementById('alkes_minus_sn_box');
            let plusBox = document.getElementById('alkes_plus_sn_box');
            minusBox.classList.add('hidden');
            plusBox.classList.remove('hidden');
        }
    }

    document.getElementById('modal_mutasi').classList.remove('hidden');
}

// Fungsi toggle dropdown alasan untuk masing-masing item Serial Number
function toggleSnItemReason(checkbox, snId) {
    let box = document.getElementById('sn_reason_box_' + snId);
    if (box) {
        if (checkbox.checked) {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }
    updateAlkesSnCheckboxes();
}

// Fungsi pembatasan pemilihan Serial Number agar tidak bisa memilih melebihi kuantitas selisih
function updateAlkesSnCheckboxes() {
    let needed = parseInt(document.getElementById('count_sn_needed')?.innerText, 10) || 0;
    let allCheckboxes = document.querySelectorAll('.sn-checkbox');
    let checkedBoxes = document.querySelectorAll('.sn-checkbox:checked');
    let countChecked = checkedBoxes.length;

    let infoStatus = document.getElementById('sn_selection_status');
    if (infoStatus) {
        if (countChecked >= needed && needed > 0) {
            infoStatus.innerHTML = `<span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded text-[10px]">${countChecked}/${needed} Dipilih (Lengkap)</span>`;
        } else {
            infoStatus.innerHTML = `<span class="inline-flex items-center gap-1 font-semibold text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded text-[10px]">${countChecked}/${needed} Dipilih</span>`;
        }
    }

    let btnSubmit = document.getElementById('btn_submit_mutasi');
    if (btnSubmit && currentOpnameItem && currentOpnameItem.jenis_komoditas !== 'Obat') {
        btnSubmit.disabled = (countChecked !== needed);
    }

    allCheckboxes.forEach(cb => {
        let card = cb.closest('.sn-item-card') || cb.closest('.sn-item-label');
        if (!cb.checked) {
            if (countChecked >= needed) {
                cb.disabled = true;
                cb.classList.remove('cursor-pointer');
                cb.classList.add('cursor-not-allowed');
                if (card) {
                    card.classList.add('opacity-40', 'cursor-not-allowed', 'bg-slate-100');
                    card.classList.remove('border-emerald-300', 'bg-emerald-50/50');
                }
            } else {
                cb.disabled = false;
                cb.classList.add('cursor-pointer');
                cb.classList.remove('cursor-not-allowed');
                if (card) {
                    card.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-100', 'bg-emerald-50/50');
                }
            }
        } else {
            cb.disabled = false;
            cb.classList.add('cursor-pointer');
            cb.classList.remove('cursor-not-allowed');
            if (card) {
                card.classList.remove('opacity-40', 'cursor-not-allowed', 'bg-slate-100');
                card.classList.add('bg-emerald-50/50', 'border-emerald-400');
            }
        }
    });
}

// Event delegation untuk perubahan checkbox SN
document.getElementById('alkes_sn_checkbox_list')?.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('sn-checkbox')) {
        updateAlkesSnCheckboxes();
    }
});

// Validasi checkbox SN saat form mutasi Alkes minus disubmit
document.getElementById('form_mutasi_selisih')?.addEventListener('submit', function(e) {
    let minusBox = document.getElementById('alkes_minus_sn_box');
    if (!minusBox.classList.contains('hidden') && !document.getElementById('container_alkes_sn').classList.contains('hidden')) {
        let needed = parseInt(document.getElementById('count_sn_needed').innerText, 10);
        let checked = document.querySelectorAll('.sn-checkbox:checked').length;
        if (checked !== needed) {
            e.preventDefault();
            alert('Harap pilih tepat ' + needed + ' nomor Serial Number yang rusak/hilang! (Saat ini dipilih: ' + checked + ')');
        }
    }
});

// Pemindai Barcode / Fast Locator untuk Pencacahan Fisik Rak
function handleBarcodeScan() {
    let input = document.getElementById('barcode_scan_input');
    let q = (input ? input.value : '').trim().toLowerCase();
    if (!q) return;

    let rows = document.querySelectorAll('tr[data-barcode-search]');
    let matchedRow = null;

    rows.forEach(r => {
        let str = (r.getAttribute('data-barcode-search') || '').toLowerCase();
        if (str.includes(q) && !matchedRow) {
            matchedRow = r;
        }
    });

    if (matchedRow) {
        matchedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        matchedRow.classList.add('bg-amber-100', 'ring-2', 'ring-amber-500');
        setTimeout(() => {
            matchedRow.classList.remove('bg-amber-100', 'ring-2', 'ring-amber-500');
        }, 3000);

        let inputFisik = matchedRow.querySelector('.input-hitung-fisik');
        if (inputFisik) {
            inputFisik.focus();
            inputFisik.select();
        }
        input.value = '';
    } else {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Barang Tidak Ditemukan',
                text: 'Barcode / SKU / Batch "' + q + '" tidak ditemukan dalam daftar opname ini.',
                icon: 'warning',
                timer: 2500,
                showConfirmButton: false
            });
        } else {
            alert('Barcode / SKU / Batch "' + q + '" tidak ditemukan!');
        }
    }
}

document.getElementById('barcode_scan_input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleBarcodeScan();
    }
});

// Fungsi Cepat Pengisian Angka Fisik Demo / Uji Coba Sesuai 5 Skenario
async function isiOtomatisAngkaDemo() {
    let btn = document.querySelector('button[onclick="isiOtomatisAngkaDemo()"]');
    let origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg><span>Mengisi Angka...</span>';
    }

    try {
        let res = await fetch('stok_opname.php?ajax=get_demo_counts');
        let json = await res.json();

        if (json.status !== 'success' || !json.data) {
            alert('Gagal mengambil data simulasi pengujian: ' + (json.message || 'Respons server tidak valid.'));
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            return;
        }

        let countFilled = 0;
        let anomaliCount = 0;

        json.data.forEach(item => {
            let tr = document.querySelector('tr[data-id="' + item.id + '"]');
            let inBaik = document.getElementById('input_baik_' + item.id);
            let inRusak = document.getElementById('input_rusak_' + item.id);
            let inExp = document.getElementById('input_exp_' + item.id);

            if (inBaik && inBaik.type !== 'hidden') {
                inBaik.value = item.baik;
            }
            if (inRusak) {
                inRusak.value = item.rusak;
            }
            if (inExp) {
                inExp.value = item.exp;
            }

            if (tr) {
                tr.classList.remove('bg-rose-50/70', 'bg-amber-50/70', 'bg-blue-50/70');
                if (item.jenis === 'rusak') {
                    tr.classList.add('bg-amber-50/70');
                    anomaliCount++;
                } else if (item.jenis === 'expired' || item.jenis === 'hilang') {
                    tr.classList.add('bg-rose-50/70');
                    anomaliCount++;
                } else if (item.jenis === 'surplus') {
                    tr.classList.add('bg-blue-50/70');
                    anomaliCount++;
                }
            }
            countFilled++;
        });

        if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }

        let msgAnomali = 'Berhasil mengisi ' + countFilled + ' baris kuantitas fisik di layar sesuai skenario pengujian:\n' +
            '• Cefadroxil (Obat): 2 Strip Rusak (di kolom Kondisi Rusak)\n' +
            '• Sanmol Sirup (Obat): Kedaluwarsa (di kolom Kondisi Expired)\n' +
            '• Amoxicillin (Obat): Hilang/Kurang 2 Strip (Fisik 6 dari Sistem 8)\n' +
            '• Tensimeter Omron (Alkes): Hilang 1 Unit (Fisik 7 dari Sistem 8)\n' +
            '• Termometer Omron (Alkes): 1 Unit Rusak Fisik (di kolom Kondisi Rusak)\n' +
            '• Betadine Solution (Obat): Surplus +2 Botol (Fisik 14 dari Sistem 12)\n' +
            '• 105 Item lainnya: Klop / Sesuai persis dengan data sistem.\n\n' +
            'Silakan tinjau angka di kolom fisik lalu tekan tombol "Simpan Hasil Hitung" untuk menyimpan data ke server.';
        alert(msgAnomali);

    } catch (err) {
        if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
        alert('Terjadi kendala koneksi saat mengisi angka uji coba: ' + err.message);
    }
}

// Fungsi Validasi & Submit Form Bulk Simpan
function validateBulkFormBeforeSubmit() {
    let form = document.getElementById('form_bulk_simpan');
    if (!form) return false;

    let inputs = form.querySelectorAll('input[type="number"]');
    let hasValue = false;
    inputs.forEach(inp => {
        let val = (inp.value || '').trim();
        if (val !== '') {
            hasValue = true;
        }
    });

    // Jika di halaman HANYA tersisa baris barang kedaluwarsa (semua baris non-expired sudah dihitung),
    // izinkan submit meskipun input dikosongkan agar otomatis dialokasikan ke kondisi Expired.
    let expRowsCount = form.querySelectorAll('tr[data-is-exp="1"]').length;
    let nonExpRowsCount = form.querySelectorAll('tr[data-is-exp="0"]').length;
    let onlyExpRowsRemaining = (expRowsCount > 0 && nonExpRowsCount === 0);

    if (!hasValue && !onlyExpRowsRemaining) {
        alert('Perhatian: Anda belum memasukkan angka hitungan fisik pada baris mana pun.\n\nSilakan ketik kuantitas fisik rak terlebih dahulu pada minimal 1 barang sebelum menekan tombol simpan.');
        return false;
    }
    return true;
}

function submitBulkCountingForm() {
    let form = document.getElementById('form_bulk_simpan');
    if (!form) return;

    if (!validateBulkFormBeforeSubmit()) return;

    if (confirm('Simpan hasil hitung fisik yang telah diinput ke server?')) {
        form.submit();
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
layoutFooter();
?>

