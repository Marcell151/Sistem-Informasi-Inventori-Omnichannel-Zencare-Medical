<?php
// File: inventori/stok_opname.php
// Modul Stok Opname Fisik Terpusat (Blind Count & Audit-Proof) - ZenCare Medical
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';
require_once __DIR__ . '/../config/opname_helper.php';

requireRole(['superadmin', 'admin']);

$role = $_SESSION['role'] ?? '';
$isSuperadmin = ($role === 'superadmin');
$userId = $_SESSION['user_id'] ?? 1;
$activeCabang = 1;

$msg = ''; $msgType = '';

// Ambil sesi aktif saat ini jika ada
$activeSession = cekSesiOpnameAktif($pdo);

// ─────────────────────────────────────────────────────────────────────────────
// PROSES AKSI POST / REQUEST
// ─────────────────────────────────────────────────────────────────────────────
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // --- 1. MULAI SESI STOK OPNAME (Khusus Superadmin) ---
    if ($aksi === 'mulai_sesi') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Hanya Superadmin yang berwenang memulai Sesi Stok Opname.";
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

                // Snapshot 1: Seluruh Batch Obat & BMHP
                $stmtObat = $pdo->query("
                    SELECT sb.id AS id_batch, sb.id_variasi, sb.no_batch, sb.stok_sisa,
                           pv.satuan_kecil, pv.satuan_besar, pv.rasio_konversi, pi.nama_produk, pv.nama_variasi
                    FROM stok_batch sb
                    JOIN produk_variasi pv ON sb.id_variasi = pv.id
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    WHERE pv.is_active = 1
                    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC, sb.tgl_exp ASC
                ");
                $obatBatches = $stmtObat->fetchAll();

                $insDetail = $pdo->prepare("
                    INSERT INTO detail_opname_batch 
                    (id_sesi, id_batch, id_variasi, jenis_komoditas, satuan_tipe, nama_satuan, stok_tersedia_sistem, stok_reserved, stok_sistem_fisik, hitung_fisik, selisih, status_mutasi)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, 'Belum Dimutasi')
                ");

                foreach ($obatBatches as $ob) {
                    $stokFisik = max(0, intval($ob['stok_sisa']));
                    $insDetail->execute([
                        $idSesi,
                        $ob['id_batch'],
                        $ob['id_variasi'],
                        'Obat',
                        'kecil',
                        $ob['satuan_kecil'] ?: 'Pcs',
                        $stokFisik, // nilai awal snapshot
                        0,          // dihitung dinamis saat reveal
                        $stokFisik
                    ]);
                }

                // Snapshot 2: Seluruh Variasi Alat Kesehatan ber-Serial Number
                $stmtAlkes = $pdo->query("
                    SELECT pv.id AS id_variasi, pv.nama_variasi, pi.nama_produk, pv.satuan_kecil,
                           (SELECT COUNT(*) FROM unit_serial us WHERE us.id_variasi = pv.id AND us.status = 'Tersedia') as sn_tersedia,
                           (SELECT COUNT(*) FROM unit_serial us JOIN penjualan p ON us.id_penjualan = p.id WHERE us.id_variasi = pv.id AND p.status_pesanan IN ('Menunggu Pembayaran', 'Diproses', 'Siap Diambil')) as sn_reserved
                    FROM produk_variasi pv
                    JOIN produk_induk pi ON pv.id_produk_induk = pi.id
                    WHERE pv.is_active = 1 AND EXISTS (SELECT 1 FROM unit_serial us WHERE us.id_variasi = pv.id)
                    ORDER BY pi.nama_produk ASC, pv.nama_variasi ASC
                ");
                $alkesVars = $stmtAlkes->fetchAll();

                foreach ($alkesVars as $ak) {
                    $snTersedia = intval($ak['sn_tersedia']);
                    $snReserved = intval($ak['sn_reserved']);
                    $stokFisikAlkes = $snTersedia + $snReserved;

                    $insDetail->execute([
                        $idSesi,
                        null, // Alkes tidak memiliki id_batch
                        $ak['id_variasi'],
                        'Alkes',
                        'kecil',
                        $ak['satuan_kecil'] ?: 'Unit',
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

    // --- 2. SIMPAN HITUNG FISIK (Hitung Buta: Ungkap Evaluasi & Selisih) ---
    elseif ($aksi === 'simpan_hitung_fisik') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $hitungFisikInput = $_POST['hitung_fisik'] ?? '';
        $isRecount = !empty($_POST['is_recount']);
        $alasanKoreksi = trim($_POST['alasan_koreksi'] ?? '');

        if (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif ($hitungFisikInput === '' || !is_numeric($hitungFisikInput) || intval($hitungFisikInput) < 0) {
            $msg = "Jumlah hitung fisik wajib berupa angka bulat positif (>= 0)!";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();

                $stmtCheck = $pdo->prepare("
                    SELECT dob.*, so.kode_sesi
                    FROM detail_opname_batch dob
                    JOIN sesi_opname so ON dob.id_sesi = so.id
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

                $hitungBaru = intval($hitungFisikInput);
                $hitungLama = ($row['hitung_fisik'] !== null) ? intval($row['hitung_fisik']) : null;

                // Evaluasi Reserved dinamis saat tombol Simpan ditekan
                if ($row['jenis_komoditas'] === 'Alkes') {
                    $reservedAktif = hitungReservedAlkesVar($pdo, $row['id_variasi']);
                } else {
                    $reservedAktif = hitungReservedBatch($pdo, $row['id_batch']);
                }

                $stokFisikSistem = intval($row['stok_sistem_fisik']);
                $stokTersediaSistem = $stokFisikSistem - $reservedAktif;
                $selisih = $hitungBaru - $stokFisikSistem;
                $statusMutasi = ($selisih === 0) ? 'Sesuai' : 'Belum Dimutasi';

                // Update baris opname
                $stmtUp = $pdo->prepare("
                    UPDATE detail_opname_batch
                    SET hitung_fisik = ?,
                        selisih = ?,
                        stok_reserved = ?,
                        stok_tersedia_sistem = ?,
                        status_mutasi = ?,
                        dihitung_oleh = ?,
                        waktu_hitung = NOW()
                    WHERE id = ?
                ");
                $stmtUp->execute([
                    $hitungBaru,
                    $selisih,
                    $reservedAktif,
                    $stokTersediaSistem,
                    $statusMutasi,
                    $userId,
                    $idDetail
                ]);

                // Catat audit trail jika ini adalah koreksi/hitung ulang
                if ($isRecount && $hitungLama !== null && $hitungLama !== $hitungBaru) {
                    $stmtLog = $pdo->prepare("
                        INSERT INTO riwayat_hitung_opname (id_detail_opname, hitung_sebelumnya, hitung_baru, dihitung_oleh, alasan_koreksi, waktu_koreksi)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtLog->execute([
                        $idDetail,
                        $hitungLama,
                        $hitungBaru,
                        $userId,
                        $alasanKoreksi ?: 'Koreksi hitung fisik di lapangan'
                    ]);
                }

                $pdo->commit();
                $msg = "Hitung fisik berhasil disimpan! Selisih baris ini: " . ($selisih > 0 ? "+$selisih" : "$selisih") . " unit.";
                $msgType = ($selisih === 0) ? 'success' : 'info';
            } catch (Exception $e) {
                if (isset($pdo)) $pdo->rollBack();
                $msg = "Gagal menyimpan hitung fisik: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 3. EKSEKUSI MUTASI SELISIH BARIS (Admin & Superadmin) ---
    elseif ($aksi === 'eksekusi_mutasi_baris') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $alasanMutasi = trim($_POST['alasan_mutasi'] ?? '');
        $keterangan = trim($_POST['keterangan_mutasi'] ?? '');

        if (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif (!$idDetail || empty($alasanMutasi)) {
            $msg = "Harap tentukan alasan mutasi untuk baris berselisih!";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();

                $stmtD = $pdo->prepare("
                    SELECT dob.*, pv.nama_variasi, pi.nama_produk, pi.kategori, sb.no_batch, sb.stok_sisa, st.stok AS stok_toko_saat_ini
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

                // ── A. MUTASI OBAT (Quantity-Based) ──────────────────────────
                if ($item['jenis_komoditas'] === 'Obat') {
                    $idBatch = $item['id_batch'];
                    $idVariasi = $item['id_variasi'];

                    // Update stok_batch (Signed INT aman jika negatif)
                    $operator = $isMinus ? '-' : '+';
                    $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa $operator ? WHERE id = ?")
                        ->execute([$absSelisih, $idBatch]);

                    // Update stok_toko
                    $pdo->prepare("UPDATE stok_toko SET stok = stok $operator ? WHERE id_variasi = ?")
                        ->execute([$absSelisih, $idVariasi]);

                    // Hitung saldo akhir untuk kartu stok
                    $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                    $jMutasi = $isMinus ? 'Keluar' : 'Masuk';
                    $ketKartu = "[Stok Opname: $kodeSesi] Batch: {$item['no_batch']} | $alasanMutasi" . ($keterangan ? " - $keterangan" : "");

                    $stmtKartu = $pdo->prepare("
                        INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                        VALUES (?, ?, 'kecil', ?, 'Manual', 'Selisih Stok Opname', ?, ?, ?, ?, ?)
                    ");
                    $stmtKartu->execute([
                        $idVariasi,
                        $idBatch,
                        $jMutasi,
                        $kodeSesi,
                        $absSelisih,
                        $sisaStokToko,
                        $ketKartu,
                        $userId
                    ]);
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
                        $newSnStatus = ($alasanMutasi === 'Barang Rusak') ? 'Retur/Rusak' : 'Hilang';

                        $stmtUpSn = $pdo->prepare("UPDATE unit_serial SET status = ?, catatan = ? WHERE id = ? AND id_variasi = ?");
                        $stmtInsDos = $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, ?)");

                        foreach ($selectedSns as $snId) {
                            $snId = intval($snId);
                            $qSn = $pdo->prepare("SELECT serial_number FROM unit_serial WHERE id = ? AND id_variasi = ?");
                            $qSn->execute([$snId, $idVariasi]);
                            $snVal = $qSn->fetchColumn();
                            if (!$snVal) throw new Exception("Serial Number ID $snId tidak valid.");

                            $catatanSn = "[Opname $kodeSesi] " . $alasanMutasi . ($keterangan ? ": $keterangan" : "");
                            $stmtUpSn->execute([$newSnStatus, $catatanSn, $snId, $idVariasi]);
                            $stmtInsDos->execute([$idDetail, $snId, $snVal, ($alasanMutasi === 'Barang Rusak' ? 'Kurang_Rusak' : 'Kurang_Hilang')]);

                            $mutatedSnList[] = $snVal;
                        }

                        // Potong stok_toko
                        $pdo->prepare("UPDATE stok_toko SET stok = stok - ? WHERE id_variasi = ?")->execute([$absSelisih, $idVariasi]);
                        $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();

                        // Catat Kartu Stok
                        $ketKartu = "[Stok Opname: $kodeSesi] SN: " . implode(', ', $mutatedSnList) . " | $alasanMutasi" . ($keterangan ? " - $keterangan" : "");
                        $stmtKartu = $pdo->prepare("
                            INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                            VALUES (?, NULL, 'kecil', 'Keluar', 'Manual', 'Selisih Stok Opname', ?, ?, ?, ?, ?)
                        ");
                        $stmtKartu->execute([$idVariasi, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);

                    } else {
                        // Mutasi Plus Alkes: Wajib input/scan SN baru yang ditemukan
                        $rawNewSns = trim($_POST['input_new_sns'] ?? '');
                        $newSnArr = array_filter(array_map('trim', explode(',', $rawNewSns)));
                        if (count($newSnArr) !== $absSelisih) {
                            throw new Exception("Mutasi kelebihan Alkes surplus $absSelisih unit: Harap masukkan tepat $absSelisih nomor Serial Number (pisahkan dengan koma jika lebih dari 1)!");
                        }

                        $stmtInsDos = $pdo->prepare("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES (?, ?, ?, 'Tambah_Ditemukan')");
                        foreach ($newSnArr as $snStr) {
                            // Cek apakah SN sudah pernah ada (misal pernah ditandai Hilang/Rusak)
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
                        $ketKartu = "[Stok Opname: $kodeSesi] SN Ditemukan: " . implode(', ', $newSnArr) . " | $alasanMutasi" . ($keterangan ? " - $keterangan" : "");
                        $stmtKartu = $pdo->prepare("
                            INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                            VALUES (?, NULL, 'kecil', 'Masuk', 'Manual', 'Selisih Stok Opname', ?, ?, ?, ?, ?)
                        ");
                        $stmtKartu->execute([$idVariasi, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
                    }
                }

                // Tandai baris opname sebagai 'Sudah Dimutasi'
                $stmtUpDetail = $pdo->prepare("
                    UPDATE detail_opname_batch
                    SET status_mutasi = 'Sudah Dimutasi',
                        alasan_mutasi = ?,
                        keterangan_mutasi = ?,
                        dimutasi_oleh = ?,
                        waktu_mutasi = NOW()
                    WHERE id = ?
                ");
                $stmtUpDetail->execute([$alasanMutasi, $keterangan, $userId, $idDetail]);

                $pdo->commit();
                $msg = "Mutasi selisih untuk {$item['nama_produk']} berhasil dieksekusi dan dicatat di Kartu Stok!";
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
                        SELECT dob.*, pv.nama_variasi, pi.nama_produk, sb.no_batch
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
                            // Jika sebelumnya berkurang (minus), saat rollback ditambah (+). Jika surplus (+), dikurangi (-).
                            $reverseOp = $isMinus ? '+' : '-';
                            $pdo->prepare("UPDATE stok_batch SET stok_sisa = stok_sisa $reverseOp ? WHERE id = ?")
                                ->execute([$absSelisih, $idBatch]);
                            $pdo->prepare("UPDATE stok_toko SET stok = stok $reverseOp ? WHERE id_variasi = ?")
                                ->execute([$absSelisih, $idVariasi]);

                            $sisaStokToko = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idVariasi")->fetchColumn();
                            $jMutasi = $isMinus ? 'Masuk' : 'Keluar';
                            $ketKartu = "[Rollback Sesi Dibatalkan: $kodeSesi] Batch: {$mIt['no_batch']} | Pembatalan Opname: $alasanBatal";

                            $stmtKartu = $pdo->prepare("
                                INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                                VALUES (?, ?, 'kecil', ?, 'Manual', 'Koreksi Manual', ?, ?, ?, ?, ?)
                            ");
                            $stmtKartu->execute([$idVariasi, $idBatch, $jMutasi, $kodeSesi, $absSelisih, $sisaStokToko, $ketKartu, $userId]);
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
$filterSts = $_GET['status_eval'] ?? 'semua';

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

    // Query daftar baris opname
    $where = ["dob.id_sesi = ?"];
    $params = [$idSesi];

    if ($searchQ !== '') {
        $where[] = "(pi.nama_produk LIKE ? OR pv.nama_variasi LIKE ? OR sb.no_batch LIKE ?)";
        $params[] = "%$searchQ%";
        $params[] = "%$searchQ%";
        $params[] = "%$searchQ%";
    }
    if ($filterKom !== 'semua') {
        $where[] = "dob.jenis_komoditas = ?";
        $params[] = $filterKom;
    }
    if ($filterSts === 'belum_hitung') {
        $where[] = "dob.hitung_fisik IS NULL";
    } elseif ($filterSts === 'sesuai') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih = 0";
    } elseif ($filterSts === 'berselisih_belum_mutasi') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih != 0 AND dob.status_mutasi = 'Belum Dimutasi'";
    } elseif ($filterSts === 'sudah_dimutasi') {
        $where[] = "dob.status_mutasi = 'Sudah Dimutasi'";
    }

    $whereSql = implode(' AND ', $where);
    $stmtItems = $pdo->prepare("
        SELECT dob.*, pv.nama_variasi, pi.nama_produk, pi.kategori, sb.no_batch, sb.tgl_exp,
               u_hit.nama_lengkap AS nama_penghitung, u_mut.nama_lengkap AS nama_pemutasi
        FROM detail_opname_batch dob
        JOIN produk_variasi pv ON dob.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        LEFT JOIN stok_batch sb ON dob.id_batch = sb.id
        LEFT JOIN users u_hit ON dob.dihitung_oleh = u_hit.id
        LEFT JOIN users u_mut ON dob.dimutasi_oleh = u_mut.id
        WHERE $whereSql
        ORDER BY dob.jenis_komoditas ASC,
                 (dob.hitung_fisik IS NULL) DESC,
                 (dob.status_mutasi = 'Belum Dimutasi' AND dob.selisih != 0) DESC,
                 pi.nama_produk ASC, pv.nama_variasi ASC, sb.tgl_exp ASC
    ");
    $stmtItems->execute($params);
    $detailItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

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
}

// Tangkap pesan URL
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $msgType = $_GET['type'] ?? 'info';
}

layoutHead('Stok Opname Fisik');
layoutBodyOpen();
layoutSidebar('stok_opname');
?>

<div class="space-y-5 max-w-7xl mx-auto">

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
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zcBrd pb-5 mb-5">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <h2 class="text-base font-bold text-zcTxt">Status Sistem: Normal (Semua Transaksi Aktif)</h2>
                    </div>
                    <p class="text-xs text-zcMut mt-1 leading-relaxed">
                        Tidak ada sesi opname yang sedang berjalan. Kasir POS offline, toko online e-commerce, dan modul logistik beroperasi normal.
                    </p>
                </div>

                <?php if ($isSuperadmin): ?>
                    <button onclick="document.getElementById('modal_mulai_sesi').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-zc hover:bg-zcHv text-white text-xs font-bold transition shadow-md shadow-zc/20 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        <span>Mulai Sesi Stok Opname Baru (Bekukan Transaksi)</span>
                    </button>
                <?php else: ?>
                    <div class="text-[11px] bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-slate-500 font-medium">
                        Hanya Superadmin yang berwenang memulai Sesi Stok Opname.
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs mb-6">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 mb-3 shadow-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div class="font-bold text-zcTxt mb-1">1. Central Server Freeze</div>
                    <p class="text-[11px] text-zcMut leading-relaxed">
                        Saat sesi dimulai, POS kasir, checkout web, dan penerimaan dibekukan otomatis di server. Notifikasi Midtrans &amp; timer kedaluwarsa tetap aktif.
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 mb-3 shadow-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <div class="font-bold text-zcTxt mb-1">2. Blind Count (Hitung Buta)</div>
                    <p class="text-[11px] text-zcMut leading-relaxed">
                        Petugas tidak melihat stok sistem saat menghitung. Angka sistem &amp; selisih baru terbuka setelah tombol Simpan Fisik ditekan per baris.
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 mb-3 shadow-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                    </div>
                    <div class="font-bold text-zcTxt mb-1">3. Rekonsiliasi Terlacak</div>
                    <p class="text-[11px] text-zcMut leading-relaxed">
                        Obat dimutasi berbasis Batch; Alkes dimutasi dengan menunjuk Serial Number spesifik. Sesi hanya bisa ditutup jika 100% selisih telah dimutasi.
                    </p>
                </div>
            </div>

            <!-- Tabel Riwayat Sesi Terdahulu -->
            <h3 class="text-xs font-bold text-zcTxt uppercase tracking-wider mb-3">Arsip Riwayat Sesi Stok Opname</h3>
            <div class="border border-zcBrd rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 border-b border-zcBrd text-slate-600 font-semibold text-[11px]">
                        <tr>
                            <th class="py-2.5 px-3">Kode Sesi</th>
                            <th class="py-2.5 px-3">Waktu Mulai &amp; Selesai</th>
                            <th class="py-2.5 px-3">Pelaksana</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-center">Rekap Selisih</th>
                            <th class="py-2.5 px-3">Catatan</th>
                            <th class="py-2.5 px-3 text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($historiSesi)): ?>
                            <tr>
                                <td colspan="7" class="py-6 text-center text-zcMut italic">Belum ada riwayat sesi stok opname terdahulu.</td>
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
                                <td class="py-2.5 px-3 text-[11px] text-slate-600 max-w-xs truncate"><?= htmlspecialchars($hs['catatan'] ?: '-') ?></td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="cetak_berita_acara_opname.php?id_sesi=<?= $hs['id'] ?>" target="_blank" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border border-slate-200 hover:border-zc hover:text-zc text-slate-600 transition text-[11px] font-semibold bg-white shadow-2xs">
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

            <!-- Banner Utama Sesi Aktif (Clean White ZenCare Design) -->
            <div class="p-5 rounded-2xl bg-white border border-zcBrd shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs bg-rose-50 text-rose-700 border border-rose-200 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider font-mono">
                                Sesi Aktif: <?= htmlspecialchars($activeSession['kode_sesi']) ?>
                            </span>
                            <span class="text-[11px] text-zcMut">Dimulai: <?= date('d/m/Y H:i', strtotime($activeSession['dibekukan_pada'])) ?> WIB</span>
                        </div>
                        <p class="text-xs text-zcMut mt-1 leading-relaxed">
                            Seluruh transaksi kasir POS, checkout toko online, konversi UOM, dan penerimaan sedang <strong class="text-zcTxt font-bold">DIBEKUKAN</strong> di server. Lakukan penghitungan fisik lapangan di bawah ini.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 self-start md:self-auto shrink-0">
                    <?php if ($isSuperadmin): ?>
                        <button onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                            Batal Sesi
                        </button>

                        <?php 
                        $bisaSelesai = ($stats['belum_hitung'] === 0 && ($stats['berselisih'] - $stats['sudah_dimutasi']) === 0);
                        ?>
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin seluruh hasil fisik sudah diverifikasi dan ingin menutup sesi opname ini? Pembekuan sistem akan dicabut.');">
                            <input type="hidden" name="aksi" value="selesaikan_sesi">
                            <button type="submit" <?= !$bisaSelesai ? 'disabled' : '' ?> class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 <?= $bisaSelesai ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer' : 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed' ?>" title="<?= !$bisaSelesai ? 'Sesi hanya bisa diselesaikan jika semua item sudah dihitung dan semua selisih sudah dimutasi!' : 'Buka kembali sistem normal' ?>">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Selesaikan Sesi (Buka Kunci)</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Banner Peringatan Defisit Fisik (Jika Ada Hitung Fisik < Reserved) -->
            <?php if ($stats['defisit_reserved'] > 0): ?>
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-xs flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <div>
                        <strong class="font-bold text-xs text-amber-950 block">Peringatan Defisit Fisik Terdeteksi (<?= $stats['defisit_reserved'] ?> Baris Barang)</strong>
                        <p class="text-[11px] text-amber-800 mt-0.5 leading-relaxed">
                            Terdapat barang dengan jumlah hasil hitung fisik yang <strong>lebih sedikit daripada kuantitas yang sedang dicadangkan (reserved)</strong> untuk pesanan online aktif. Barang tersebut berpotensi tidak dapat memenuhi pesanan pembeli.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Ringkasan Metrik Dashboard Opname -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-zcMut font-bold uppercase tracking-wider block">Total Baris</span>
                    <span class="text-xl font-black text-zcTxt mt-1 block"><?= $stats['total'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-amber-700 font-bold uppercase tracking-wider block">Belum Dihitung</span>
                    <span class="text-xl font-black text-amber-600 mt-1 block"><?= $stats['belum_hitung'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider block">Sudah Dihitung</span>
                    <span class="text-xl font-black text-emerald-600 mt-1 block"><?= $stats['sudah_hitung'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-blue-700 font-bold uppercase tracking-wider block">Sesuai (Selisih 0)</span>
                    <span class="text-xl font-black text-blue-600 mt-1 block"><?= $stats['sesuai'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-rose-700 font-bold uppercase tracking-wider block">Ada Selisih</span>
                    <span class="text-xl font-black text-rose-600 mt-1 block"><?= $stats['berselisih'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-purple-700 font-bold uppercase tracking-wider block">Sudah Dimutasi</span>
                    <span class="text-xl font-black text-purple-600 mt-1 block"><?= $stats['sudah_dimutasi'] ?> / <?= $stats['berselisih'] ?></span>
                </div>
            </div>

            <!-- Segmented Tabs Komoditas (Obat vs Alkes) -->
            <div class="flex items-center gap-2 border-b border-zcBrd pb-1">
                <a href="?komoditas=semua&status_eval=<?= urlencode($filterSts) ?>&q=<?= urlencode($searchQ) ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 <?= $filterKom === 'semua' ? 'bg-zc text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                    <span>Semua Komoditas</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $filterKom === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['total'] ?></span>
                </a>
                <a href="?komoditas=Obat&status_eval=<?= urlencode($filterSts) ?>&q=<?= urlencode($searchQ) ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 <?= $filterKom === 'Obat' ? 'bg-zc text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                    <span>Obat (Batch)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $filterKom === 'Obat' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['total_obat'] ?></span>
                </a>
                <a href="?komoditas=Alkes&status_eval=<?= urlencode($filterSts) ?>&q=<?= urlencode($searchQ) ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 <?= $filterKom === 'Alkes' ? 'bg-zc text-white font-bold shadow-2xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                    <span>Alat Kesehatan (Serial Number)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $filterKom === 'Alkes' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' ?>"><?= $stats['total_alkes'] ?></span>
                </a>
            </div>

            <!-- Panel Filter & Navigasi Baris -->
            <div class="bg-white border border-zcBrd rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                <form method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="hidden" name="komoditas" value="<?= htmlspecialchars($filterKom) ?>">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Cari nama obat, alkes, atau batch..." class="border border-zcBrd rounded-xl px-3 py-1.5 text-xs focus:ring-1 focus:ring-zc flex-1 min-w-[200px]">
                    <select name="status_eval" onchange="this.form.submit()" class="border border-zcBrd rounded-xl px-2.5 py-1.5 text-xs bg-white">
                        <option value="semua" <?= $filterSts === 'semua' ? 'selected' : '' ?>>Semua Status Evaluasi</option>
                        <option value="belum_hitung" <?= $filterSts === 'belum_hitung' ? 'selected' : '' ?>>Belum Dihitung (<?= $stats['belum_hitung'] ?>)</option>
                        <option value="sesuai" <?= $filterSts === 'sesuai' ? 'selected' : '' ?>>Sesuai / 0 Selisih (<?= $stats['sesuai'] ?>)</option>
                        <option value="berselisih_belum_mutasi" <?= $filterSts === 'berselisih_belum_mutasi' ? 'selected' : '' ?>>Selisih Belum Dimutasi (<?= $stats['berselisih'] - $stats['sudah_dimutasi'] ?>)</option>
                        <option value="sudah_dimutasi" <?= $filterSts === 'sudah_dimutasi' ? 'selected' : '' ?>>Sudah Dimutasi (<?= $stats['sudah_dimutasi'] ?>)</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition cursor-pointer">Filter</button>
                    <?php if ($searchQ !== '' || $filterKom !== 'semua' || $filterSts !== 'semua'): ?>
                        <a href="stok_opname.php" class="text-rose-600 hover:underline font-semibold ml-1">Reset</a>
                    <?php endif; ?>
                </form>

                <div class="text-[11px] text-zcMut">
                    Menampilkan <strong><?= count($detailItems) ?></strong> dari total <?= $stats['total'] ?> baris opname
                </div>
            </div>

            <!-- Tabel Antarmuka Hitung Buta & Rekonsiliasi -->
            <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 border-b border-zcBrd text-slate-600 font-semibold text-[11px]">
                            <tr>
                                <th class="py-3 px-3 w-8 text-center">#</th>
                                <th class="py-3 px-3">Komoditas &amp; Produk</th>
                                <th class="py-3 px-3">Batch / SN Tracker</th>
                                <th class="py-3 px-3 text-center">Satuan</th>
                                <th class="py-3 px-3 text-center w-36 bg-amber-50/50 border-x border-amber-200">
                                    <div class="text-amber-900 font-bold">HITUNG FISIK</div>
                                    <div class="text-[9px] text-amber-700 font-normal">(Blind Count Input)</div>
                                </th>
                                <th class="py-3 px-3 text-center">Stok Fisik Sistem</th>
                                <th class="py-3 px-3 text-center">Reserved</th>
                                <th class="py-3 px-3 text-center">Selisih</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3 text-center w-48">Tindakan Mutasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($detailItems)): ?>
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-zcMut italic">Tidak ada item yang sesuai dengan filter pencarian.</td>
                                </tr>
                            <?php else: 
                                $no = 1;
                                foreach ($detailItems as $it): 
                                    $isCalculated = ($it['hitung_fisik'] !== null);
                                    $selisih = intval($it['selisih'] ?? 0);
                                    $isDefisit = ($isCalculated && intval($it['hitung_fisik']) < intval($it['stok_reserved']));
                            ?>
                                <tr class="hover:bg-slate-50/70 transition <?= $isDefisit ? 'bg-rose-50/40' : '' ?>">
                                    <td class="py-3 px-3 text-center text-slate-400 font-mono text-[10px]"><?= $no++ ?></td>
                                    
                                    <!-- Kolom Produk & Komoditas -->
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider <?= $it['jenis_komoditas'] === 'Obat' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                                <?= $it['jenis_komoditas'] === 'Obat' ? 'Obat' : 'Alkes (SN)' ?>
                                            </span>
                                            <strong class="text-zcTxt block leading-snug"><?= htmlspecialchars($it['nama_produk']) ?></strong>
                                        </div>
                                        <span class="text-zcMut text-[11px] block mt-0.5"><?= htmlspecialchars($it['nama_variasi']) ?></span>
                                    </td>

                                    <!-- Kolom Batch / SN -->
                                    <td class="py-3 px-3">
                                        <?php if ($it['jenis_komoditas'] === 'Obat'): ?>
                                            <div class="font-mono font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($it['no_batch'] ?: '-') ?></div>
                                            <div class="text-[10px] text-zcMut">Exp: <?= $it['tgl_exp'] ? date('d/m/Y', strtotime($it['tgl_exp'])) : '-' ?></div>
                                        <?php else: 
                                            $allSn = $alkesSnMap[$it['id_variasi']] ?? [];
                                            // HANYA tampilkan unit SN yang berstatus 'Tersedia' (unit aktif di rak toko)
                                            // Unit Retur/Rusak (di karantina) atau Terjual tidak dimunculkan di rak opname
                                            $snTersedia = array_values(array_filter($allSn, fn($s) => $s['status'] === 'Tersedia'));
                                        ?>
                                            <div class="space-y-1.5 min-w-[210px] max-w-[280px]">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                                        <span>Track SN (1:1)</span>
                                                    </span>
                                                    <span class="text-[10px] text-slate-500 font-medium">Unit di Rak: <strong class="text-emerald-700 font-bold"><?= count($snTersedia) ?> Unit</strong></span>
                                                </div>

                                                <!-- Daftar Unit SN Tersedia di Rak -->
                                                <div class="p-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                                                    <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                                        <?php if (empty($snTersedia)): ?>
                                                            <span class="text-[10px] text-slate-400 italic">Tidak ada SN aktif di rak</span>
                                                        <?php else: ?>
                                                            <?php foreach ($snTersedia as $snItem): ?>
                                                                <span class="px-1.5 py-0.5 rounded font-mono text-[10px] border bg-white border-slate-300 text-slate-800 font-semibold shadow-2xs" title="Unit SN Aktif: <?= htmlspecialchars($snItem['serial_number']) ?>">
                                                                    <?= htmlspecialchars($snItem['serial_number']) ?>
                                                                </span>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-3 px-3 text-center font-semibold text-slate-700">
                                        <?= htmlspecialchars($it['nama_satuan']) ?>
                                    </td>

                                    <!-- KOLOM HITUNG FISIK (BLIND COUNT) -->
                                    <td class="py-3 px-3 text-center bg-amber-50/30 border-x border-amber-100">
                                        <?php if (!$isCalculated): ?>
                                            <!-- Mode Input Hitung Buta Awal -->
                                            <form method="POST" class="flex items-center justify-center gap-1.5 m-0">
                                                <input type="hidden" name="aksi" value="simpan_hitung_fisik">
                                                <input type="hidden" name="id_detail" value="<?= $it['id'] ?>">
                                                <input type="number" name="hitung_fisik" min="0" required placeholder="" class="w-20 border-2 border-amber-300 rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-amber-600 bg-white shadow-2xs">
                                                <button type="submit" class="px-2 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[10px] font-bold shadow-2xs transition cursor-pointer" title="Simpan hitung fisik dan buka angka sistem">Simpan</button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Nilai Sudah Dihitung -->
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-sm font-black font-mono text-slate-900"><?= $it['hitung_fisik'] ?></span>
                                                <span class="text-[10px] text-slate-500"><?= htmlspecialchars($it['nama_satuan']) ?></span>
                                                <?php if ($it['status_mutasi'] !== 'Sudah Dimutasi'): ?>
                                                    <button onclick="openModalRecount(<?= $it['id'] ?>, <?= $it['hitung_fisik'] ?>, '<?= addslashes($it['nama_produk']) ?>')" class="text-slate-400 hover:text-zc p-1 rounded hover:bg-slate-100 transition inline-flex items-center cursor-pointer" title="Koreksi / Hitung Ulang">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">Oleh: <?= htmlspecialchars($it['nama_penghitung'] ?? 'User') ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM STOK FISIK SISTEM (Baru Terbuka Setelah Simpan Fisik) -->
                                    <td class="py-3 px-3 text-center font-mono">
                                        <?php if ($isCalculated): ?>
                                            <span class="font-bold text-slate-800"><?= $it['stok_sistem_fisik'] ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM RESERVED -->
                                    <td class="py-3 px-3 text-center font-mono">
                                        <?php if ($isCalculated): ?>
                                            <span class="text-slate-600 <?= $it['stok_reserved'] > 0 ? 'font-bold text-amber-700' : '' ?>"><?= $it['stok_reserved'] ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM SELISIH -->
                                    <td class="py-3 px-3 text-center font-mono font-bold">
                                        <?php if ($isCalculated): ?>
                                            <?php if ($selisih === 0): ?>
                                                <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full text-[11px]">0 (Pas)</span>
                                            <?php elseif ($selisih > 0): ?>
                                                <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full text-[11px]">+<?= $selisih ?></span>
                                            <?php else: ?>
                                                <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full text-[11px]"><?= $selisih ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded" title="Tersembunyi hingga fisik dihitung">
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>Tersembunyi</span>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM STATUS -->
                                    <td class="py-3 px-3 text-center">
                                        <?php if (!$isCalculated): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Belum Dihitung</span>
                                        <?php elseif ($it['status_mutasi'] === 'Sudah Dimutasi'): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span>Sudah Dimutasi</span>
                                            </span>
                                            <div class="text-[9px] text-slate-400 mt-0.5"><?= htmlspecialchars($it['alasan_mutasi']) ?></div>
                                        <?php elseif ($selisih === 0): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                <span>Sesuai</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Perlu Mutasi</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- KOLOM TINDAKAN MUTASI -->
                                    <td class="py-3 px-3 text-center">
                                        <?php if (!$isCalculated): ?>
                                            <span class="text-[11px] text-slate-400 italic">Isi hitung fisik dulu</span>
                                        <?php elseif ($selisih === 0): ?>
                                            <span class="text-[11px] text-emerald-600 font-semibold">Tidak ada selisih</span>
                                        <?php elseif ($it['status_mutasi'] === 'Sudah Dimutasi'): ?>
                                            <span class="text-[11px] text-purple-700 font-medium">Selesai (<?= htmlspecialchars($it['alasan_mutasi']) ?>)</span>
                                        <?php else: ?>
                                            <!-- Tombol Buka Modal Mutasi Baris -->
                                            <button onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" class="px-2.5 py-1.5 rounded-xl bg-zc hover:bg-zcHv text-white text-[11px] font-bold transition shadow-2xs inline-flex items-center justify-center gap-1.5 mx-auto cursor-pointer">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m10 8 4 4-4 4"/></svg>
                                                <span>Mutasi Selisih</span>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Koreksi / Hitung Ulang (Audit Trail) -->
            <div id="modal_recount" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-sm rounded-2xl shadow-xl overflow-hidden border border-zcBrd">
                    <div class="px-5 py-3.5 border-b border-zcBrd flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                            </div>
                            <h3 class="font-bold text-xs text-zcTxt">Hitung Ulang Fisik (Audit Trail)</h3>
                        </div>
                        <button onclick="document.getElementById('modal_recount').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form method="POST" class="p-5 space-y-3 text-xs">
                        <input type="hidden" name="aksi" value="simpan_hitung_fisik">
                        <input type="hidden" name="is_recount" value="1">
                        <input type="hidden" name="id_detail" id="recount_id_detail">
                        <div>
                            <span class="text-zcMut text-[11px] block">Produk:</span>
                            <strong id="recount_nama_produk" class="text-zcTxt block leading-snug"></strong>
                        </div>
                        <div>
                            <label class="block font-bold text-zcTxt mb-1">Angka Hitung Fisik Baru:</label>
                            <input type="number" min="0" required name="hitung_fisik" id="recount_hitung_baru" class="w-full border border-zcBrd rounded-xl p-2 font-mono font-bold text-xs focus:ring-1 focus:ring-zc">
                        </div>
                        <div>
                            <label class="block font-bold text-zcTxt mb-1">Alasan Koreksi / Hitung Ulang:</label>
                            <textarea name="alasan_koreksi" required rows="2" class="w-full border border-zcBrd rounded-xl p-2 text-xs focus:ring-1 focus:ring-zc" placeholder="Contoh: Salah hitung strip di rak bawah..."></textarea>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="document.getElementById('modal_recount').classList.add('hidden')" class="px-3.5 py-1.5 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" class="px-4 py-1.5 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-xs transition">Simpan Hitung Ulang</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Eksekusi Mutasi Selisih (Obat vs Alkes) -->
            <div id="modal_mutasi" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden border border-zcBrd">
                    <div class="px-6 py-4 border-b border-zcBrd flex items-center justify-between bg-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-zc flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5"/><path d="M4 20L21 3"/><path d="M21 16v5h-5"/><path d="M15 15l6 6"/><path d="M4 4l5 5"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-zcTxt" id="mutasi_modal_title">Eksekusi Mutasi Selisih</h3>
                                <p class="text-[11px] text-zcMut">Rekonsiliasi kartu stok dan penyesuaian fisik</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('modal_mutasi').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                    <form method="POST" id="form_mutasi_selisih" class="p-6 space-y-4 text-xs">
                        <input type="hidden" name="aksi" value="eksekusi_mutasi_baris">
                        <input type="hidden" name="id_detail" id="mutasi_id_detail">
                        
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

                        <!-- Pilihan Alasan Mutasi -->
                        <div>
                            <label class="block font-bold text-zcTxt mb-1">Alasan Penyesuaian / Mutasi:</label>
                            <select name="alasan_mutasi" id="mutasi_alasan_select" required class="w-full border border-zcBrd rounded-xl p-2.5 bg-white text-xs font-semibold focus:ring-1 focus:ring-zc">
                                <!-- Populated dynamically based on Minus vs Plus & Obat vs Alkes -->
                            </select>
                        </div>

                        <!-- Form Khusus Alkes (Pemilihan SN Spesifik) -->
                        <div id="container_alkes_sn" class="hidden space-y-3 p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl">
                            <div id="alkes_minus_sn_box" class="space-y-2">
                                <label class="block font-bold text-emerald-900 text-xs">
                                    Pilih Serial Number yang Rusak / Hilang (<span id="count_sn_needed" class="font-black text-rose-700"></span> unit):
                                </label>
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
                            <label class="block font-bold text-zcTxt mb-1">Catatan Tambahan (Opsional):</label>
                            <textarea name="keterangan_mutasi" rows="2" class="w-full border border-zcBrd rounded-xl p-2 text-xs focus:ring-1 focus:ring-zc" placeholder="Keterangan kondisi fisik di lapangan..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-zcBrd">
                            <button type="button" onclick="document.getElementById('modal_mutasi').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-sm transition">Simpan &amp; Eksekusi Mutasi</button>
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
                            Membatalkan sesi akan <strong>mencabut seluruh pembekuan transaksi</strong> secara seketika. Mutasi yang sudah terlanjur dieksekusi akan tetap tercatat di kartu stok demi integritas log audit.
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

        <?php endif; ?>

    </div>

<script>
function openModalRecount(idDetail, hitungLama, namaProduk) {
    document.getElementById('recount_id_detail').value = idDetail;
    document.getElementById('recount_nama_produk').innerText = namaProduk;
    document.getElementById('recount_hitung_baru').value = hitungLama;
    document.getElementById('modal_recount').classList.remove('hidden');
}

function openModalMutasi(item) {
    document.getElementById('mutasi_id_detail').value = item.id;
    document.getElementById('mutasi_nama_produk').innerText = item.nama_produk + ' — ' + item.nama_variasi;
    
    let isObat = (item.jenis_komoditas === 'Obat');
    let selisih = parseInt(item.selisih) || 0;
    let isMinus = (selisih < 0);
    let absSel = Math.abs(selisih);

    document.getElementById('mutasi_komoditas_badge').innerText = isObat ? 'Obat' : 'Alat Kesehatan';
    document.getElementById('mutasi_komoditas_badge').className = isObat ? 'font-bold text-blue-700' : 'font-bold text-emerald-700';

    let selBadge = document.getElementById('mutasi_selisih_badge');
    selBadge.innerText = (selisih > 0 ? '+' + selisih : selisih) + ' ' + (item.nama_satuan || 'Unit');
    selBadge.className = isMinus ? 'font-extrabold text-sm text-rose-600' : 'font-extrabold text-sm text-blue-600';

    let alasanSelect = document.getElementById('mutasi_alasan_select');
    alasanSelect.innerHTML = '';

    if (isMinus) {
        if (isObat) {
            alasanSelect.innerHTML = `
                <option value="Barang Rusak">Barang Rusak</option>
                <option value="Barang Kedaluwarsa">Barang Kedaluwarsa (Pemusnahan Batch)</option>
                <option value="Barang Hilang">Barang Hilang</option>
                <option value="Koreksi Salah Catat / Ditemukan">Koreksi Salah Catat</option>
            `;
        } else {
            // Alkes tidak punya kedaluwarsa
            alasanSelect.innerHTML = `
                <option value="Barang Rusak">Barang Rusak</option>
                <option value="Barang Hilang">Barang Hilang</option>
                <option value="Koreksi Salah Catat / Ditemukan">Koreksi Salah Catat</option>
            `;
        }
    } else {
        alasanSelect.innerHTML = `
            <option value="Koreksi Salah Catat / Ditemukan">Koreksi Salah Catat / Ditemukan Fisik</option>
        `;
    }

    // Penanganan khusus Alkes
    let containerAlkes = document.getElementById('container_alkes_sn');
    let minusBox = document.getElementById('alkes_minus_sn_box');
    let plusBox = document.getElementById('alkes_plus_sn_box');

    if (!isObat) {
        containerAlkes.classList.remove('hidden');
        if (isMinus) {
            minusBox.classList.remove('hidden');
            plusBox.classList.add('hidden');
            document.getElementById('count_sn_needed').innerText = absSel;

            // Fetch list SN aktif untuk variasi ini via AJAX
            let snListContainer = document.getElementById('alkes_sn_checkbox_list');
            snListContainer.innerHTML = '<div class="text-xs text-slate-400 py-2 text-center">Memuat Serial Number aktif...</div>';

            fetch('../api/get_batch_sn.php?action=get_available_sns&id_variasi=' + item.id_variasi)
                .then(r => r.json())
                .then(data => {
                    let sns = data.sns || [];
                    if (!sns.length) {
                        snListContainer.innerHTML = '<div class="text-xs text-rose-600 p-2">Tidak ada SN berstatus Tersedia di database.</div>';
                        return;
                    }
                    let html = '';
                    sns.forEach(s => {
                        html += `
                            <label class="flex items-center gap-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer border-b border-slate-100 last:border-none">
                                <input type="checkbox" name="selected_sn_ids[]" value="${s.id}" class="rounded border-slate-300 text-zc focus:ring-zc sn-checkbox">
                                <span class="font-mono text-xs font-semibold text-slate-800">${s.serial_number}</span>
                                <span class="text-[10px] text-slate-400 ml-auto">(${s.status})</span>
                            </label>
                        `;
                    });
                    snListContainer.innerHTML = html;
                })
                .catch(() => {
                    snListContainer.innerHTML = '<div class="text-xs text-rose-600 p-2">Gagal memuat SN.</div>';
                });

        } else {
            minusBox.classList.add('hidden');
            plusBox.classList.remove('hidden');
        }
    } else {
        containerAlkes.classList.add('hidden');
    }

    document.getElementById('modal_mutasi').classList.remove('hidden');
}

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
</script>

<?php
layoutFooter();
?>

