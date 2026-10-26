<?php
// File: inventori/tambah_stok.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['superadmin', 'admin']);

$msg = ''; $msgType = '';
$userId = $_SESSION['user_id'];
$idCabang = 1;

// Range tanggal penerimaan yang diizinkan (maksimal 30 hari ke belakang dan 30 hari ke depan)
$minTglTerima = date('Y-m-d', strtotime('-30 days'));
$maxTglTerima = date('Y-m-d', strtotime('+30 days'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // --- AKSI 1: TERIMA BARANG PENGGANTI RETUR (Tukar Guling Supplier) ---
    if ($aksi === 'terima_barang_pengganti') {
        $idDetail    = intval($_POST['id_detail'] ?? 0);
        $qtyGanti    = intval($_POST['qty_pengganti'] ?? 0);
        $noBatch     = trim($_POST['no_batch_pengganti'] ?? '');
        $tglExp      = trim($_POST['tgl_exp_pengganti'] ?? '') ?: null;
        $snPengganti = trim($_POST['sn_pengganti'] ?? '');
        $catatanRet  = trim($_POST['catatan_retur'] ?? '');

        try {
            $pdo->beginTransaction();
            $stmtD = $pdo->prepare("
                SELECT pd.*, ps.no_referensi, ps.sumber, ps.id_supplier, pv.id_produk_induk, pv.rasio_konversi,
                       pv.nama_variasi, pi.nama_produk, pi.kategori, pv.satuan_besar, pv.satuan_kecil
                FROM penerimaan_detail pd
                JOIN penerimaan_stok ps ON ps.id = pd.id_penerimaan
                JOIN produk_variasi pv ON pv.id = pd.id_variasi
                JOIN produk_induk pi ON pi.id = pv.id_produk_induk
                WHERE pd.id = ? FOR UPDATE
            ");
            $stmtD->execute([$idDetail]);
            $det = $stmtD->fetch();

            if (!$det) throw new Exception("Data klaim retur tidak ditemukan.");
            if ($det['status_retur'] !== 'menunggu_pengganti') {
                throw new Exception("Klaim retur ini sudah diselesaikan sebelumnya.");
            }
            if ($qtyGanti <= 0 || $qtyGanti > (int)$det['qty_rusak']) {
                throw new Exception("Jumlah barang pengganti tidak valid (maksimal {$det['qty_rusak']} {$det['satuan_besar']}).");
            }

            $kategori = $det['kategori'];
            $rasio = max(1, (int)$det['rasio_konversi']);
            $qtyPcsGanti = $qtyGanti * $rasio;
            $namaLengkap = $det['nama_produk'] . ' - ' . $det['nama_variasi'];
            $noRef = $det['no_referensi'];

            if ($kategori === 'Obat') {
                if (!$tglExp) throw new Exception("Barang pengganti Obat wajib mencantumkan Tanggal Kedaluwarsa.");
                if ($tglExp <= date('Y-m-d')) throw new Exception("Tanggal kedaluwarsa barang pengganti tidak boleh hari ini atau lampau.");
            } elseif ($kategori === 'Alat Kesehatan' && $qtyGanti > 0) {
                if (!$snPengganti) throw new Exception("Unit pengganti Alat Kesehatan wajib mencantumkan Nomor Seri (Serial Number).");
            }

            // Keterangan catatan retur
            if ($kategori === 'Alat Kesehatan' && $snPengganti) {
                $catatanRet = ($catatanRet ? $catatanRet . ' | ' : '') . "SN Pengganti: " . $snPengganti;
            }

            // 1. Update status di penerimaan_detail
            $stmtUpd = $pdo->prepare("
                UPDATE penerimaan_detail 
                SET status_retur = 'selesai_tukar_barang',
                    qty_pengganti_diterima = ?,
                    no_batch_pengganti = ?,
                    tgl_exp_pengganti = ?,
                    tgl_selesai_retur = NOW(),
                    catatan_retur = ?
                WHERE id = ?
            ");
            $stmtUpd->execute([$qtyGanti, $noBatch ?: null, $tglExp, $catatanRet, $idDetail]);

            // 2. Tambah stok fisik ke stok_toko
            $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ? AND 1=1")
                ->execute([$qtyPcsGanti, $det['id_variasi']]);

            // 3. Jika Obat, masukkan batch baru ke stok_batch
            if ($kategori === 'Obat' && $noBatch && $tglExp) {
                $pdo->prepare("
                    INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE stok_sisa = stok_sisa + ?
                ")->execute([$det['id_variasi'], $noBatch, $tglExp, $qtyPcsGanti, $qtyPcsGanti]);
            }

            // 4. Jika Alkes, masukkan SN pengganti ke unit_serial
            if ($kategori === 'Alat Kesehatan' && $snPengganti) {
                $snArr = array_filter(array_map('trim', explode(',', $snPengganti)));
                $stmtSN = $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number, status, catatan) VALUES (?, ?, 'Tersedia', ?)");
                foreach ($snArr as $sn) {
                    $stmtSN->execute([$det['id_variasi'], $sn, "Unit Pengganti Retur PO: $noRef"]);
                }
            }

            // 5. Catat di kartu_stok dengan mengacu ke No Referensi Dokumen Awal
            $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$det['id_variasi']} AND 1=1")->fetchColumn();
            $satuanTipe = ($kategori === 'Alat Kesehatan') ? 'kecil' : 'besar';
            $qtyKartu   = ($kategori === 'Alat Kesehatan') ? $qtyPcsGanti : $qtyGanti;
            $batchKet   = $noBatch ? " [Batch Baru: $noBatch, Exp: " . date('d/m/Y', strtotime($tglExp)) . "]" : "";
            $snKet      = ($kategori === 'Alat Kesehatan' && $snPengganti) ? " [SN Baru: $snPengganti]" : "";
            $ketKartu   = "Penerimaan Barang Pengganti Retur ($qtyGanti {$det['satuan_besar']})$batchKet$snKet — Mengacu ke PO Awal: $noRef";

            $pdo->prepare("
                INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
                VALUES (?, ?, 'Masuk', 'Penerimaan', 'Penerimaan Barang Pengganti Retur', ?, ?, ?, ?, ?)
            ")->execute([$det['id_variasi'], $satuanTipe, $noRef, $qtyKartu, $sisaStok, $ketKartu, $userId]);

            $pdo->commit();
            $msg = "Barang pengganti retur untuk $namaLengkap ($qtyGanti {$det['satuan_besar']}) berhasil diterima dan masuk ke stok aktif di bawah PO $noRef. Total tagihan/utang toko ke supplier tetap seimbang dan tidak bertambah.";
            $msgType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal memproses barang pengganti: " . $e->getMessage();
            $msgType = 'error';
        }
    }

    // --- AKSI 2: SELESAIKAN VIA POTONG FAKTUR / CREDIT NOTE ---
    elseif ($aksi === 'selesaikan_potong_faktur') {
        $idDetail   = intval($_POST['id_detail'] ?? 0);
        $catatanRet = trim($_POST['catatan_retur'] ?? '');

        try {
            $stmtD = $pdo->prepare("
                SELECT pd.*, ps.no_referensi, pv.nama_variasi, pi.nama_produk, pv.satuan_besar
                FROM penerimaan_detail pd
                JOIN penerimaan_stok ps ON ps.id = pd.id_penerimaan
                JOIN produk_variasi pv ON pv.id = pd.id_variasi
                JOIN produk_induk pi ON pi.id = pv.id_produk_induk
                WHERE pd.id = ?
            ");
            $stmtD->execute([$idDetail]);
            $det = $stmtD->fetch();

            if (!$det) throw new Exception("Data klaim retur tidak ditemukan.");
            if ($det['status_retur'] !== 'menunggu_pengganti') {
                throw new Exception("Klaim retur ini sudah diselesaikan sebelumnya.");
            }

            $nilaiKlaim = (int)$det['qty_rusak'] * (float)($det['harga_beli'] ?? 0);
            $namaLengkap = $det['nama_produk'] . ' - ' . $det['nama_variasi'];
            $noRef = $det['no_referensi'];

            $pdo->prepare("
                UPDATE penerimaan_detail 
                SET status_retur = 'selesai_potong_faktur',
                    tgl_selesai_retur = NOW(),
                    catatan_retur = ?
                WHERE id = ?
            ")->execute([$catatanRet ?: 'Diselesaikan via Credit Note / Potong Tagihan Invoice', $idDetail]);

            $msg = "Klaim retur $namaLengkap ({$det['qty_rusak']} {$det['satuan_besar']}) berhasil diselesaikan via Pemotongan Tagihan / Credit Note senilai Rp " . number_format($nilaiKlaim, 0, ',', '.') . " di bawah PO $noRef (Stok fisik etalase tidak bertambah).";
            $msgType = 'success';
        } catch (Exception $e) {
            $msg = "Gagal menyelesaikan klaim retur: " . $e->getMessage();
            $msgType = 'error';
        }
    }

    // --- AKSI 3: INPUT PENERIMAAN BARANG BARU ---
    else {
        $sumber      = $_POST['sumber'] ?? 'Pembelian Langsung';
        $noReferensi = trim($_POST['no_referensi'] ?? '');
        $idSupplier  = intval($_POST['id_supplier'] ?? 0) ?: null;
        $tglTerima   = $_POST['tanggal_terima'] ?? date('Y-m-d');
        $catatan     = trim($_POST['catatan'] ?? '');
        
        // Auto-generate No Referensi jika kosong & Pembelian Langsung
        if ($sumber === 'Pembelian Langsung' && empty($noReferensi)) {
            $prefix = 'KLK-' . date('ymd') . '-';
            $stmtSeq = $pdo->query("SELECT COUNT(*) FROM penerimaan_stok WHERE no_referensi LIKE '$prefix%'");
            $count = $stmtSeq->fetchColumn() + 1;
            $noReferensi = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
        }
        
        // Handle Upload File Nota (Opsional)
        $fileNotaName = null;
        if (isset($_FILES['file_nota']) && $_FILES['file_nota']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['file_nota']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['file_nota']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','pdf'];
            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/../uploads/nota/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $fileNotaName = 'NOTA_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($tmpName, $uploadDir . $fileNotaName);
            }
        }

        $idVariasis = $_POST['id_variasi'] ?? [];
        $qtyPesans  = $_POST['qty_pesan'] ?? [];
        $qtyTerimas = $_POST['qty_terima'] ?? [];
        $qtyRusaks  = $_POST['qty_rusak'] ?? [];
        $qtysLegacy = $_POST['qty'] ?? [];
        $hargaBelis = $_POST['harga_beli'] ?? [];
        $batchNos   = $_POST['no_batch'] ?? [];
        $tglExps    = $_POST['tgl_exp'] ?? [];
        $snList     = $_POST['serial_number'] ?? [];

        if (empty($idVariasis) || !$noReferensi) {
            $msg = "No Referensi dan minimal 1 produk wajib diisi."; $msgType = 'error';
        } elseif ($tglTerima < $minTglTerima || $tglTerima > $maxTglTerima) {
            $msg = "Tanggal penerimaan tidak valid. Batas jangkauan tanggal hanya diperbolehkan maksimal 1 bulan ke belakang (" . date('d/m/Y', strtotime($minTglTerima)) . ") hingga 1 bulan ke depan (" . date('d/m/Y', strtotime($maxTglTerima)) . ").";
            $msgType = 'error';
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Insert Header Penerimaan
                $stmtHeader = $pdo->prepare("INSERT INTO penerimaan_stok (no_referensi, sumber, id_supplier, tanggal_terima, catatan, file_nota, dibuat_oleh) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtHeader->execute([$noReferensi, $sumber, $idSupplier, $tglTerima, $catatan, $fileNotaName, $userId]);
                $idPenerimaan = $pdo->lastInsertId();

                // 2. Insert Detail Penerimaan dengan Retur Parsial & Status Retur
                $stmtDetail = $pdo->prepare("INSERT INTO penerimaan_detail (id_penerimaan, id_variasi, qty_pesan, qty_terima, qty_rusak, harga_beli, no_batch, tgl_exp, status_retur) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtStok   = $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ? AND 1=1");
                $stmtBatch  = $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE stok_sisa = stok_sisa + ?");
                $stmtSN     = $pdo->prepare("INSERT INTO unit_serial (id_variasi, serial_number) VALUES (?, ?)");
                $stmtKartu  = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Masuk', 'Penerimaan', 'Penerimaan Barang', ?, ?, ?, ?, ?)");

            $createdBatches = [];
            $createdSns = [];
            $minExpLimit = date('Y-m-d', strtotime('+1 day'));
            $totalBarangRusakAll = 0;

            foreach ($idVariasis as $i => $idV) {
                // Kuantiti PO / Parsial
                $qPesanRaw = isset($qtyPesans[$i]) ? intval($qtyPesans[$i]) : (isset($qtysLegacy[$i]) ? intval($qtysLegacy[$i]) : 1);
                $qtyPesan  = max(1, $qPesanRaw);
                $qtyRusak  = max(0, intval($qtyRusaks[$i] ?? 0));
                
                // Jika qty_terima tidak diinput eksplisit, default = qty_pesan - qty_rusak
                if (isset($qtyTerimas[$i]) && $qtyTerimas[$i] !== '') {
                    $qtyTerima = max(0, intval($qtyTerimas[$i]));
                } else {
                    $qtyTerima = max(0, $qtyPesan - $qtyRusak);
                }

                $totalBarangRusakAll += $qtyRusak;

                if ($idV && ($qtyTerima > 0 || $qtyRusak > 0)) {
                    $batch = trim($batchNos[$i] ?? '') ?: null;
                    $exp   = trim($tglExps[$i] ?? '') ?: null;
                    $hargaBeli = floatval(preg_replace('/[^0-9]/', '', (string)($hargaBelis[$i] ?? '0'))) ?: null;
                    
                    $stmtInfo = $pdo->query("SELECT pv.rasio_konversi, pi.kategori, pi.nama_produk, pv.nama_variasi, pv.satuan_besar, pv.satuan_kecil FROM produk_variasi pv JOIN produk_induk pi ON pv.id_produk_induk = pi.id WHERE pv.id = $idV")->fetch();
                    $rasio = max(1, (int)$stmtInfo['rasio_konversi']);
                    $kategori = $stmtInfo['kategori'];
                    $namaLengkap = $stmtInfo['nama_produk'] . ' - ' . $stmtInfo['nama_variasi'];
                    $qtyPcsTerima = $qtyTerima * $rasio;

                    // Validasi Expired Date untuk Obat: Minimal H+1 (Tidak boleh hari ini atau masa lalu)
                    if ($kategori === 'Obat') {
                        if (!$exp) {
                            throw new Exception("Produk Obat '$namaLengkap' wajib mencantumkan Tanggal Kedaluwarsa.");
                        }
                        if ($exp < $minExpLimit) {
                            $tglMinFmt = date('d/m/Y', strtotime($minExpLimit));
                            $tglExpFmt = date('d/m/Y', strtotime($exp));
                            throw new Exception("Tanggal kedaluwarsa Batch '$batch' ($tglExpFmt) ditolak! Tanggal kedaluwarsa tidak boleh hari ini atau tanggal lampau (minimal besok H+1 / $tglMinFmt).");
                        }
                    }
                    
                    // 1. Simpan detail PO lengkap (Pesan, Terima Baik, Rusak/Retur)
                    $statusRetur = ($qtyRusak > 0) ? 'menunggu_pengganti' : 'tidak_ada';
                    $stmtDetail->execute([$idPenerimaan, $idV, $qtyPesan, $qtyTerima, $qtyRusak, $hargaBeli, $batch, $exp, $statusRetur]);
                    
                    // 2. Update Stok Fisik: HANYA kuantiti yang diterima dalam kondisi baik yang masuk stok siap jual!
                    if ($qtyPcsTerima > 0) {
                        $stmtStok->execute([$qtyPcsTerima, $idV]);
                    }
                    
                    // 3. Jika Obat: stok_batch HANYA mencatat kuantiti yang diterima baik
                    if ($batch && $exp && $qtyPcsTerima > 0) {
                        $stmtBatch->execute([$idV, $batch, $exp, $qtyPcsTerima, $qtyPcsTerima]);
                        $batchRow = $pdo->query("SELECT id FROM stok_batch WHERE id_variasi = $idV AND no_batch = " . $pdo->quote($batch))->fetch();
                        if ($batchRow) {
                            $createdBatches[] = [
                                'id' => $batchRow['id'],
                                'batch' => $batch,
                                'nama' => $namaLengkap
                            ];
                        }
                    }
                    
                    // 4. Jika Alkes (Ada Serial Number): Hanya SN sejumlah barang diterima baik
                    $snStr = trim($snList[$i] ?? '');
                    if ($snStr && $qtyTerima > 0) {
                        $snArr = array_filter(array_map('trim', explode(',', $snStr)));
                        $snValid = array_slice($snArr, 0, $qtyTerima);
                        foreach ($snValid as $sn) {
                            $stmtSN->execute([$idV, $sn]);
                            $createdSns[] = [
                                'sn' => $sn,
                                'nama' => $namaLengkap
                            ];
                        }
                    }

                    // 5. Log Kartu Stok dengan nomor referensi dokumen dan rincian retur jika ada
                    $sisaStok = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = $idV AND 1=1")->fetchColumn();
                    $returKeterangan = ($qtyRusak > 0) ? " [Pesan: $qtyPesan, Diterima: $qtyTerima, Rusak: $qtyRusak - Diretur ke Supplier]" : "";
                    $identitasKet = $batch ? " [Batch: $batch]$returKeterangan" : ($snStr ? " [SN: $snStr]$returKeterangan" : $returKeterangan);
                    $deskripsi = "Penerimaan Barang [$sumber] - Ref: $noReferensi$identitasKet";
                    
                    $satuanTipe = ($kategori === 'Alat Kesehatan') ? 'kecil' : 'besar';
                    $qtyKartu   = ($kategori === 'Alat Kesehatan') ? $qtyPcsTerima : $qtyTerima;
                    
                    if ($qtyKartu > 0) {
                        $stmtKartu->execute([$idV, $satuanTipe, $noReferensi, $qtyKartu, $sisaStok, $deskripsi, $userId]);
                    }
                }
            }

            $pdo->commit();
            $returMsg = ($totalBarangRusakAll > 0) ? " Terdapat $totalBarangRusakAll unit barang rusak/cacat yang otomatis dicatat untuk klaim retur supplier." : "";
            $msg = "Penerimaan Barang (Ref: $noReferensi) berhasil disimpan ke dalam stok sistem.$returMsg"; 
            $msgType = 'success';
            $lastPenerimaanId = $idPenerimaan;
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal menyimpan penerimaan: ' . $e->getMessage(); $msgType = 'error';
        }
    }
}
}

// Data Produk untuk Form
$supplierList = $pdo->query("SELECT id, nama FROM supplier WHERE is_active = 1 ORDER BY nama")->fetchAll();
$produkList   = $pdo->query("
    SELECT pv.id, CONCAT(pi.nama_produk, ' - ', pv.nama_variasi) AS label, pi.nama_produk, pv.nama_variasi,
           pv.sku_variasi, pi.sku_induk, pi.kategori, pv.harga_jual_besar,
           pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi
    FROM produk_variasi pv 
    JOIN produk_induk pi ON pi.id = pv.id_produk_induk 
    WHERE pv.is_active = 1 ORDER BY pi.nama_produk, pv.nama_variasi
")->fetchAll();

// Ambil Histori Penerimaan Barang (Tersimpan Permanen & Dapat Difilter/Dicari)
$histDari    = $_GET['hist_dari'] ?? '';
$histSampai  = $_GET['hist_sampai'] ?? '';
$histStatus  = $_GET['hist_status'] ?? 'semua';
$histQ       = trim($_GET['hist_q'] ?? '');

$whereHist = ["1=1"];
$paramsHist = [];

if ($histDari !== '') {
    $whereHist[] = "ps.tanggal_terima >= ?";
    $paramsHist[] = $histDari;
}
if ($histSampai !== '') {
    $whereHist[] = "ps.tanggal_terima <= ?";
    $paramsHist[] = $histSampai;
}
if ($histQ !== '') {
    $whereHist[] = "(ps.no_referensi LIKE ? OR s.nama LIKE ? OR u.nama_lengkap LIKE ? OR ps.catatan LIKE ? OR EXISTS (
        SELECT 1 FROM penerimaan_detail pd_f
        JOIN produk_variasi pv_f ON pv_f.id = pd_f.id_variasi
        JOIN produk_induk pi_f ON pi_f.id = pv_f.id_produk_induk
        WHERE pd_f.id_penerimaan = ps.id
        AND (pi_f.nama_produk LIKE ? OR pv_f.nama_variasi LIKE ? OR pd_f.no_batch LIKE ?)
    ))";
    $histKw = "%$histQ%";
    for ($k = 0; $k < 7; $k++) $paramsHist[] = $histKw;
}

$whereHistClause = implode(' AND ', $whereHist);
$stmtHist = $pdo->prepare("
    SELECT ps.*, u.nama_lengkap AS pembuat, s.nama AS nama_supplier
    FROM penerimaan_stok ps
    LEFT JOIN users u ON u.id = ps.dibuat_oleh
    LEFT JOIN supplier s ON s.id = ps.id_supplier
    WHERE $whereHistClause
    ORDER BY ps.tanggal_terima DESC, ps.id DESC
");
$stmtHist->execute($paramsHist);
$rawHistori = $stmtHist->fetchAll();

$penerimaanIds = array_column($rawHistori, 'id');
$historiDetails = [];
if (!empty($penerimaanIds)) {
    $inIds = implode(',', array_map('intval', $penerimaanIds));
    $stmtDet = $pdo->query("
        SELECT pd.*, pv.nama_variasi, pi.nama_produk, pi.kategori, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
               sb.id AS id_stok_batch
        FROM penerimaan_detail pd
        JOIN produk_variasi pv ON pv.id = pd.id_variasi
        JOIN produk_induk pi ON pi.id = pv.id_produk_induk
        LEFT JOIN stok_batch sb ON sb.id_variasi = pd.id_variasi AND sb.no_batch = pd.no_batch
        WHERE pd.id_penerimaan IN ($inIds)
        ORDER BY pd.id ASC
    ");
    while ($row = $stmtDet->fetch()) {
        $historiDetails[$row['id_penerimaan']][] = $row;
    }
}

// Filter berdasarkan status retur jika dipilih
$historiPenerimaan = [];
foreach ($rawHistori as $p) {
    $items = $historiDetails[$p['id']] ?? [];
    $hasRetur = false;
    $hasPending = false;
    $hasResolved = false;
    foreach ($items as $it) {
        if ((int)($it['qty_rusak'] ?? 0) > 0) {
            $hasRetur = true;
            $st = $it['status_retur'] ?? 'menunggu_pengganti';
            if ($st === 'menunggu_pengganti') $hasPending = true;
            elseif (in_array($st, ['selesai_tukar_barang', 'selesai_potong_faktur'])) $hasResolved = true;
        }
    }
    if ($histStatus === 'menunggu' && !$hasPending) continue;
    if ($histStatus === 'selesai' && (!$hasResolved || $hasPending)) continue;
    if ($histStatus === 'sempurna' && $hasRetur) continue;
    if ($histStatus === 'retur' && !$hasRetur) continue;

    $historiPenerimaan[] = $p;
}

layoutHead('Penerimaan Barang');
layoutBodyOpen();
layoutSidebar('tambah_stok');
layoutHeader('Penerimaan Barang', 'Catat barang masuk fisik ke dalam sistem dari Supplier / PO');
?>

<style>
@media print {
    /* Sembunyikan seluruh elemen web induk (sidebar, navbar, form) */
    body * {
        visibility: hidden !important;
    }
    /* Tampilkan hanya area modal bukti penerimaan */
    #modal_nota_retur, #modal_nota_retur * {
        visibility: visible !important;
    }
    #modal_nota_retur {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        display: block !important;
        z-index: 999999 !important;
    }
    #modal_nota_retur > div {
        max-width: 100% !important;
        max-height: none !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        background: #ffffff !important;
        padding: 0 !important;
    }
    #printable_nota_area {
        padding: 0 !important;
        margin: 0 !important;
        font-size: 11pt !important;
        color: #000000 !important;
    }
    .print\:hidden {
        display: none !important;
    }
}
</style>

<?php if ($msg): ?>
<div class="mb-5 p-4 rounded-xl text-sm font-medium border <?= $msgType === 'error' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-800' ?>">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
            <span class="font-semibold"><?= htmlspecialchars($msg) ?></span>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($msgType === 'success' && !empty($lastPenerimaanId)): ?>
                <a href="cetak_batch_stiker.php?id_penerimaan=<?= $lastPenerimaanId ?>" target="_blank" 
                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition flex items-center gap-1.5 text-xs font-bold shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>Cetak Semua Label Stiker Dokumen Ini</span>
                </a>
            <?php endif; ?>
            <a href="#histori_section" class="px-3 py-1.5 bg-white border border-emerald-300 text-emerald-700 rounded-lg shadow-sm hover:bg-emerald-100 transition flex items-center gap-1.5 text-xs font-bold shrink-0">
                Lihat Histori ↓
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- FORM INPUT PENERIMAAN -->
<div class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm mb-8">
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- Header Info -->
        <div>
            <h3 class="text-sm font-bold text-zcTxt mb-4 border-b border-zcBrd pb-2">Informasi Dokumen Penerimaan</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Sumber *</label>
                    <select name="sumber" id="sumber_select" required class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-slate-50">
                        <option value="PO">Purchase Order (PO)</option>
                        <option value="Pembelian Langsung">Pembelian Langsung / Kulakan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5" id="label_referensi">No. Referensi (PO/Nota) *</label>
                    <input type="text" name="no_referensi" id="inp_referensi" placeholder="INV/PO/..." 
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                    <div id="hint_referensi" class="hidden text-[10px] text-zcMut mt-1 leading-tight">Kosongkan jika ingin digenerate otomatis oleh sistem (KLK-YYMMDD-XXX).</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Supplier (Opsional)</label>
                    <select name="id_supplier" class="select2 w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc">
                        <option value="">Umum / Tidak Terdaftar</option>
                        <?php foreach($supplierList as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Tanggal Terima *</label>
                    <input type="date" name="tanggal_terima" required value="<?= date('Y-m-d') ?>"
                        min="<?= $minTglTerima ?>" max="<?= $maxTglTerima ?>"
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2.5 focus:outline-none focus:border-zc bg-white">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Upload Bukti Nota Fisik (Opsional)</label>
                    <input type="file" name="file_nota" accept=".jpg,.jpeg,.png,.pdf" 
                        class="w-full text-xs border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc">
                    <div class="text-[10px] text-zcMut mt-1">Format: JPG, PNG, PDF (Maks. 2MB)</div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zcTxt mb-1.5">Keterangan / Catatan Tambahan</label>
                    <input type="text" name="catatan" placeholder="Kondisi barang saat diterima..." 
                        class="w-full text-sm border border-zcBrd rounded-xl px-3 py-2 focus:outline-none focus:border-zc">
                </div>
            </div>
        </div>

        <!-- Detail Produk -->
        <div>
            <div class="flex flex-wrap items-center justify-between border-b border-zcBrd pb-3 mb-4 gap-2">
                <div>
                    <h3 class="text-sm font-bold text-zcTxt flex items-center gap-2">
                        <svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        Daftar Barang Masuk Fisik
                    </h3>
                    <p class="text-[11px] text-zcMut mt-0.5">Catat kuantiti penerimaan fisik barang sesuai Purchase Order atau Faktur.</p>
                </div>
                <div class="flex items-center flex-wrap gap-2">
                    <button type="button" onclick="openBpomSearchModal()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <span>Cari Database Obat (API)</span>
                    </button>
                    <button type="button" onclick="addRow()" class="px-3.5 py-1.5 bg-zc hover:bg-zcHv text-white rounded-lg text-xs font-bold flex items-center gap-1.5 transition shadow-xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Tambah Baris</span>
                    </button>
                </div>
            </div>
            
            <div class="border border-zcBrd rounded-xl overflow-x-auto">
                <table class="w-full text-xs min-w-[1100px]">
                    <thead class="bg-slate-50 border-b border-zcBrd">
                        <tr>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[20%]">Produk * (Ketik untuk Cari)</th>
                            <th class="text-left px-2 py-2.5 font-bold text-zcMut w-[8%]">Pesan *</th>
                            <th class="text-left px-2 py-2.5 font-bold text-emerald-800 w-[8%] bg-emerald-50/60">Terima Baik*</th>
                            <th class="text-left px-2 py-2.5 font-bold text-rose-700 w-[8%] bg-rose-50/60">Rusak/Retur</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[11%]">Nilai Beli Satuan *</th>
                            <th class="text-left px-3 py-2.5 font-bold text-zcMut w-[11%]">Subtotal Diterima</th>
                            <th class="text-left px-2 py-2.5 font-bold text-zcMut w-[10%]">No. Batch</th>
                            <th class="text-left px-2 py-2.5 font-bold text-zcMut w-[9%]">Tgl Exp</th>
                            <th class="text-left px-2 py-2.5 font-bold text-zcMut w-[12%]">SN (Khusus Alkes)</th>
                            <th class="px-2 py-2.5 w-[3%]"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr id="row-0" class="border-b border-zcBrd/50">
                            <td class="px-2 py-2 align-top">
                                <select name="id_variasi[]" id="sel-0" data-row-idx="0" required onchange="checkProduk(this, 0); calcSubtotal(0);" class="select2-produk w-full border border-zcBrd rounded-lg px-2 py-1.5 text-xs">
                                    <option value="">-- Cari &amp; Pilih Produk --</option>
                                    <?php foreach ($produkList as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-kat="<?= htmlspecialchars($p['kategori']) ?>" data-satbesar="<?= htmlspecialchars($p['satuan_besar'] ?? 'Dus') ?>" data-satkecil="<?= htmlspecialchars($p['satuan_kecil'] ?? 'Pcs') ?>" data-rasio="<?= $p['rasio_konversi'] ?>" data-hargajual="<?= $p['harga_jual_besar'] ?>" data-sku="<?= htmlspecialchars($p['sku_variasi'] ?? '') ?>" data-skuinduk="<?= htmlspecialchars($p['sku_induk'] ?? '') ?>" data-namaproduk="<?= htmlspecialchars($p['nama_produk'] ?? '') ?>"><?= htmlspecialchars($p['label']) ?> (<?= htmlspecialchars($p['satuan_besar'] ?? 'Dus') ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                
                            </td>
                            <td class="px-2 py-2 align-top">
                                <div class="flex items-center gap-1">
                                    <input type="number" name="qty_pesan[]" id="qty_pesan-0" min="1" required placeholder="Pesan" oninput="syncQty(0, 'pesan')" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
                                    <span id="badge-uom-0" class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-1 rounded whitespace-nowrap border border-slate-200">Dus</span>
                                </div>
                            </td>
                            <td class="px-2 py-2 align-top bg-emerald-50/20">
                                <input type="number" name="qty_terima[]" id="qty_terima-0" min="0" required placeholder="Baik" oninput="syncQty(0, 'terima')" class="w-full border border-emerald-300 bg-white rounded-lg px-2 py-1.5 focus:outline-none focus:border-emerald-500 text-xs font-bold text-emerald-800">
                                <div class="text-[9px] text-emerald-600 mt-0.5">Masuk stok</div>
                            </td>
                            <td class="px-2 py-2 align-top bg-rose-50/20">
                                <input type="number" name="qty_rusak[]" id="qty_rusak-0" min="0" value="0" placeholder="0" oninput="syncQty(0, 'rusak')" class="w-full border border-rose-300 bg-white rounded-lg px-2 py-1.5 focus:outline-none focus:border-rose-500 text-xs font-bold text-rose-700">
                                <div class="text-[9px] text-rose-600 mt-0.5">Retur supplier</div>
                            </td>
                            <td class="px-2 py-2 align-top">
                                <div class="relative">
                                    <span class="absolute left-2.5 top-1.5 text-xs font-bold text-slate-400">Rp</span>
                                    <input type="text" inputmode="numeric" name="harga_beli[]" id="harga-0" placeholder="0" oninput="formatRupiahInput(this); calcSubtotal(0);" 
                                        class="w-full pl-8 border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
                                </div>
                                <div class="text-[9px] text-zcMut mt-0.5">Per 1 Satuan Besar</div>
                            </td>
                            <td class="px-2 py-2 align-top">
                                <div id="subtotal-0" class="text-xs font-bold text-slate-800 pt-1.5 whitespace-nowrap">Rp 0</div>
                                <div class="text-[9px] text-zcMut">Subtotal Diterima</div>
                            </td>
                            <td class="px-2 py-2 align-top"><input type="text" name="no_batch[]" id="b-0" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="Wajib obat"></td>
                            <td class="px-2 py-2 align-top"><input type="date" name="tgl_exp[]" id="e-0" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly></td>
                            <td class="px-2 py-2 align-top">
                                <div id="sn-container-0" class="min-w-[130px]">
                                    <input type="hidden" name="serial_number[]" id="s-0" value="">
                                    <div id="sn-placeholder-0" class="text-[10px] text-slate-400 italic py-1.5 px-1 bg-slate-50 border border-dashed border-slate-200 rounded text-center">
                                        — Khusus Alkes —
                                    </div>
                                    <div id="sn-list-0" class="space-y-1 hidden"></div>
                                </div>
                            </td>
                            <td class="px-2 py-2 align-top text-center"><button type="button" onclick="removeRow(0)" class="text-slate-300 hover:text-red-500 font-bold text-lg leading-none">&times;</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2.5 text-[11px] text-zcMut flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <span class="flex items-center gap-1.5 text-slate-600">
                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Pilih produk terlebih dahulu untuk mengaktifkan kolom Batch/Exp (khusus Obat) atau Serial Number (khusus Alkes). Qty diinput dalam <strong>Satuan Besar (Dus/Karton/Box)</strong>.
                </span>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-8 py-3 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-md shadow-blue-500/20 active:scale-95 transition flex items-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan &amp; Update Stok
            </button>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- SECTION HISTORI PENERIMAAN BARANG & CETAK STIKER (7 HARI TERAKHIR)         -->
<!-- ========================================================================= -->
<!-- ========================================================================= -->
<!-- SECTION HISTORI PENERIMAAN BARANG & BERITA ACARA RETUR                     -->
<!-- ========================================================================= -->
<div id="histori_section" class="bg-white border border-zcBrd rounded-2xl p-6 shadow-sm">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-zcBrd pb-4 mb-4">
        <div>
            <h3 class="text-base font-extrabold text-zcTxt flex items-center gap-2">
                <svg class="w-5 h-5 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Histori Penerimaan Barang &amp; Berita Acara Retur
            </h3>
            <p class="text-xs text-zcMut mt-0.5">Daftar dokumen PO/Kulakan barang masuk fisik lengkap dengan pelacakan retur, nomor batch/exp, dan cetak bukti penerimaan.</p>
        </div>

        <!-- Pagination Controls (< dan >) -->
        <?php if (!empty($historiPenerimaan)): ?>
        <div class="flex items-center gap-2">
            <span id="page_info" class="text-xs font-semibold text-zcMut">Halaman 1</span>
            <div class="flex items-center rounded-xl border border-zcBrd overflow-hidden shadow-xs">
                <button type="button" id="btn_prev" onclick="prevHistoryPage()" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed font-bold text-xs transition border-r border-zcBrd flex items-center gap-1">
                    &lt; Sebelumnya
                </button>
                <button type="button" id="btn_next" onclick="nextHistoryPage()" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 disabled:opacity-40 disabled:cursor-not-allowed font-bold text-xs transition flex items-center gap-1">
                    Berikutnya &gt;
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter & Pencarian Histori Penerimaan -->
    <form method="GET" action="tambah_stok.php#histori_section" class="bg-slate-50/80 border border-zcBrd rounded-2xl p-4 mb-5 flex flex-wrap items-end gap-3 text-xs">
        <div>
            <label class="block font-bold text-slate-700 mb-1">Dari Tanggal</label>
            <input type="date" name="hist_dari" value="<?= htmlspecialchars($histDari) ?>" class="bg-white border border-zcBrd rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-zc">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="hist_sampai" value="<?= htmlspecialchars($histSampai) ?>" class="bg-white border border-zcBrd rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-zc">
        </div>
        <div>
            <label class="block font-bold text-slate-700 mb-1">Status Retur</label>
            <select name="hist_status" class="bg-white border border-zcBrd rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-zc min-w-[150px]">
                <option value="semua" <?= $histStatus === 'semua' ? 'selected' : '' ?>>Semua Status</option>
                <option value="menunggu" <?= $histStatus === 'menunggu' ? 'selected' : '' ?>>Menunggu Pengganti</option>
                <option value="selesai" <?= $histStatus === 'selesai' ? 'selected' : '' ?>>Retur Selesai</option>
                <option value="sempurna" <?= $histStatus === 'sempurna' ? 'selected' : '' ?>>Lengkap Sempurna</option>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block font-bold text-slate-700 mb-1">Cari No. Faktur / Supplier / Obat / Batch</label>
            <div class="relative">
                <input type="text" name="hist_q" value="<?= htmlspecialchars($histQ) ?>" placeholder="Ketik nomor invoice (contoh: KUL-..., PO-...), produk, atau batch..." class="w-full bg-white border border-zcBrd rounded-xl pl-8 pr-3 py-2 text-xs focus:outline-none focus:border-zc">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 bg-zc hover:bg-zcHv text-white font-bold rounded-xl transition shadow-xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span>Cari</span>
            </button>
            <?php if ($histDari !== '' || $histSampai !== '' || $histStatus !== 'semua' || $histQ !== ''): ?>
                <a href="tambah_stok.php#histori_section" class="px-3 py-2 bg-white border border-slate-300 text-slate-600 hover:bg-slate-100 font-bold rounded-xl transition">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($historiPenerimaan)): ?>
        <div class="py-12 text-center text-zcMut">
            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <p class="text-sm font-semibold">Belum ada dokumen penerimaan barang yang sesuai kriteria pencarian.</p>
            <p class="text-xs text-slate-400 mt-1">Data tersimpan permanen dan dapat dicari sewaktu-waktu berdasarkan nomor invoice atau nama supplier.</p>
        </div>
    <?php else: ?>
        <div id="history_cards_container" class="space-y-5">
            <?php foreach ($historiPenerimaan as $idx => $p): 
                $items = $historiDetails[$p['id']] ?? [];
                $sumberCls = $p['sumber'] === 'PO' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200';
                $totalNilaiFaktur = 0;
                $totalNilaiFisik = 0;
                $totalNilaiRetur = 0;
                $totalQtyPesanPO = 0;
                $totalQtyTerimaPO = 0;
                $totalQtyRusakPO = 0;
                foreach ($items as $it) {
                    $qPesan = (int)($it['qty_pesan'] ?: ((int)$it['qty_terima'] + (int)($it['qty_rusak'] ?? 0)));
                    $qTerima = (int)$it['qty_terima'];
                    $qRusak = (int)($it['qty_rusak'] ?? 0);
                    $hBeli = (float)($it['harga_beli'] ?? 0);

                    $totalNilaiFaktur += ($qPesan * $hBeli);
                    $totalNilaiFisik  += ($qTerima * $hBeli);
                    $totalNilaiRetur  += ($qRusak * $hBeli);

                    $totalQtyPesanPO  += $qPesan;
                    $totalQtyTerimaPO += $qTerima;
                    $totalQtyRusakPO  += $qRusak;
                }
            ?>
            <div class="history-card bg-slate-50/60 border border-zcBrd rounded-2xl p-5 shadow-sm transition hover:shadow-md" data-idx="<?= $idx ?>">
                <!-- Top Info Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-zcBrd gap-2 mb-3">
                    <div class="flex items-center flex-wrap gap-2.5">
                        <span class="px-2.5 py-1 text-xs font-black rounded-lg border bg-white text-slate-800 font-mono shadow-xs">
                            <?= htmlspecialchars($p['no_referensi']) ?>
                        </span>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md border <?= $sumberCls ?>">
                            <?= htmlspecialchars($p['sumber']) ?>
                        </span>
                        <span class="text-xs font-semibold text-slate-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span><?= date('d M Y', strtotime($p['tanggal_terima'])) ?></span>
                        </span>
                        <?php if ($p['nama_supplier']): ?>
                            <span class="text-xs text-zcMut">
                                • Supplier: <strong class="text-slate-700"><?= htmlspecialchars($p['nama_supplier']) ?></strong>
                            </span>
                        <?php endif; ?>

                        <?php
                        $poHasPendingRetur = false;
                        $poHasResolvedRetur = false;
                        foreach ($items as $it) {
                            if (($it['status_retur'] ?? '') === 'menunggu_pengganti') $poHasPendingRetur = true;
                            if (in_array($it['status_retur'] ?? '', ['selesai_tukar_barang', 'selesai_potong_faktur'])) $poHasResolvedRetur = true;
                        }
                        ?>
                        <?php if ($poHasPendingRetur): ?>
                            <span class="px-2 py-0.5 text-[10px] font-black rounded-md border bg-amber-100 text-amber-800 border-amber-300">
                                Ada Retur Menunggu Pengganti
                            </span>
                        <?php elseif ($poHasResolvedRetur): ?>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md border bg-emerald-100 text-emerald-800 border-emerald-300">
                                ✓ Klaim Retur Selesai
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-[11px] text-zcMut flex items-center gap-2 flex-wrap">
                        <span>Diterima oleh: <strong class="text-slate-700"><?= htmlspecialchars($p['pembuat'] ?? 'Petugas Gudang') ?></strong></span>
                        <span>(<?= date('H:i', strtotime($p['created_at'])) ?> WIB)</span>
                        <button type="button" onclick="openNotaReturModal(<?= htmlspecialchars(json_encode([
                            'id' => $p['id'],
                            'no_referensi' => $p['no_referensi'],
                            'sumber' => $p['sumber'],
                            'tanggal_terima' => date('d F Y', strtotime($p['tanggal_terima'])),
                            'supplier' => $p['nama_supplier'] ?? 'Umum / PBF Mitra',
                            'pembuat' => $p['pembuat'] ?? 'Petugas Gudang',
                            'catatan' => $p['catatan'] ?? '-',
                            'items' => array_map(function($it) {
                                return [
                                    'id' => $it['id'],
                                    'nama' => $it['nama_produk'] . ' - ' . $it['nama_variasi'],
                                    'kategori' => $it['kategori'],
                                    'satuan' => $it['satuan_besar'] ?? 'Dus',
                                    'qty_pesan' => (int)($it['qty_pesan'] ?: $it['qty_terima']),
                                    'qty_terima' => (int)$it['qty_terima'],
                                    'qty_rusak' => (int)($it['qty_rusak'] ?? 0),
                                    'harga_beli' => (float)($it['harga_beli'] ?? 0),
                                    'no_batch' => $it['no_batch'] ?? '-',
                                    'tgl_exp' => $it['tgl_exp'] ? date('d/m/Y', strtotime($it['tgl_exp'])) : '-',
                                    'status_retur' => $it['status_retur'] ?? 'tidak_ada',
                                    'qty_pengganti_diterima' => (int)($it['qty_pengganti_diterima'] ?? 0),
                                    'no_batch_pengganti' => $it['no_batch_pengganti'] ?? '',
                                    'tgl_exp_pengganti' => $it['tgl_exp_pengganti'] ? date('d/m/Y', strtotime($it['tgl_exp_pengganti'])) : '',
                                    'tgl_selesai_retur' => $it['tgl_selesai_retur'] ? date('d/m/Y H:i', strtotime($it['tgl_selesai_retur'])) : '',
                                    'catatan_retur' => $it['catatan_retur'] ?? ''
                                ];
                            }, $items)
                        ]), ENT_QUOTES, 'UTF-8') ?>)" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-blue-300 text-blue-700 hover:bg-blue-50 text-[11px] font-bold rounded-lg transition shadow-2xs" title="Lihat & Cetak Bukti Penerimaan Barang">
                            <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <span>Bukti Penerimaan</span>
                        </button>
                        <a href="cetak_batch_stiker.php?id_penerimaan=<?= $p['id'] ?>" target="_blank" 
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 border border-emerald-300 text-emerald-700 text-[11px] font-bold rounded-lg hover:bg-emerald-100 transition shadow-2xs" 
                           title="Cetak seluruh stiker barcode produk dalam dokumen ini secara berkelompok (A4 Grid / Thermal)">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            <span>Cetak Semua Stiker Dokumen</span>
                        </a>
                        <?php if ($p['file_nota']): ?>
                            <a href="../uploads/nota/<?= htmlspecialchars($p['file_nota']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-zcBrd text-zc text-[10px] font-bold rounded-lg hover:bg-slate-50 transition shadow-2xs">
                                <svg class="w-3 h-3 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                <span>Bukti Nota</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($p['catatan']): ?>
                    <div class="text-xs text-slate-600 italic mb-3 bg-white p-2 rounded-lg border border-slate-200/70">
                        Catatan: <?= htmlspecialchars($p['catatan']) ?>
                    </div>
                <?php endif; ?>

                <!-- Table Item di dalam 1 PO yang sama -->
                <div class="overflow-x-auto bg-white rounded-xl border border-zcBrd">
                    <table class="w-full text-xs min-w-[780px]">
                        <thead class="bg-slate-100/70 border-b border-zcBrd text-zcMut font-bold">
                            <tr>
                                <th class="text-left px-3 py-2.5">Produk &amp; Variasi</th>
                                <th class="text-left px-3 py-2.5">Kategori</th>
                                <th class="text-left px-3 py-2.5">Kuantiti Masuk &amp; Retur</th>
                                <th class="text-left px-3 py-2.5">Nilai Beli Satuan</th>
                                <th class="text-left px-3 py-2.5">Subtotal Faktur &amp; Fisik</th>
                                <th class="text-left px-3 py-2.5">No. Batch / Exp (Obat)</th>
                                <th class="text-center px-3 py-2.5">Aksi Klaim &amp; Stiker Barcode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zcBrd/60">
                            <?php foreach ($items as $it): 
                                $isObat = ($it['kategori'] === 'Obat');
                                $qtyBesar = (int)$it['qty_terima'];
                                $hargaBeli = (float)($it['harga_beli'] ?? 0);
                                $totalPcs = $qtyBesar * (int)($it['rasio_konversi'] ?? 1);
                                $itQpesan = (int)($it['qty_pesan'] ?: ($qtyBesar + (int)($it['qty_rusak'] ?? 0)));
                                $itQrusak = (int)($it['qty_rusak'] ?? 0);
                                $itSubtotalFaktur = $itQpesan * $hargaBeli;
                                $itSubtotalFisik  = $qtyBesar * $hargaBeli;
                                $itSubtotalRetur  = $itQrusak * $hargaBeli;
                                $stRetur          = $it['status_retur'] ?? 'menunggu_pengganti';
                            ?>
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-3 py-2.5 font-bold text-slate-800">
                                    <?= htmlspecialchars($it['nama_produk'] . ' — ' . $it['nama_variasi']) ?>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $isObat ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' ?>">
                                        <?= htmlspecialchars($it['kategori']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <div class="font-bold text-slate-800">
                                        <?= $qtyBesar ?> <?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?>
                                    </div>
                                    <span class="block text-[10px] font-normal text-zcMut">(Total <?= number_format($totalPcs) ?> <?= htmlspecialchars($it['satuan_kecil'] ?? 'Pcs') ?> Masuk Stok)</span>

                                    <?php if ($itQrusak > 0): 
                                        $qtyPesan = $itQpesan;
                                        $qtyRusak = $itQrusak;
                                    ?>
                                        <div class="mt-1.5 pt-1.5 border-t border-slate-200 text-[10px] text-left bg-slate-50/90 p-1.5 rounded-lg space-y-1">
                                            <div class="flex items-center justify-between text-slate-500">
                                                <span>Pesan Faktur:</span>
                                                <strong class="text-slate-700"><?= $qtyPesan ?> <?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?></strong>
                                            </div>
                                            <div class="flex items-center justify-between text-rose-600 font-semibold">
                                                <span>Retur Fisik Cacat:</span>
                                                <span><?= $qtyRusak ?> <?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?></span>
                                            </div>

                                            <?php if ($stRetur === 'menunggu_pengganti'): ?>
                                                <div class="pt-0.5">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        <span>Menunggu Klaim Pengganti</span>
                                                    </span>
                                                </div>
                                            <?php elseif ($stRetur === 'selesai_tukar_barang'): ?>
                                                <div class="pt-0.5 p-1 bg-emerald-50 rounded border border-emerald-200 text-[10px]">
                                                    <span class="font-bold text-emerald-800">✓ Diganti Unit Baru (<?= (int)$it['qty_pengganti_diterima'] ?> <?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?>)</span>
                                                    <?php if ($it['no_batch_pengganti']): ?>
                                                        <div class="text-slate-700 font-mono text-[9px]">Batch: <strong><?= htmlspecialchars($it['no_batch_pengganti']) ?></strong></div>
                                                    <?php endif; ?>
                                                    <?php if ($it['tgl_exp_pengganti']): ?>
                                                        <div class="text-emerald-700 text-[9px]">Exp: <?= date('d/m/Y', strtotime($it['tgl_exp_pengganti'])) ?></div>
                                                    <?php endif; ?>
                                                    <div class="text-[9px] text-zcMut mt-0.5">Selesai: <?= $it['tgl_selesai_retur'] ? date('d/m/Y H:i', strtotime($it['tgl_selesai_retur'])) : '-' ?></div>
                                                </div>
                                            <?php elseif ($stRetur === 'selesai_potong_faktur'): ?>
                                                <div class="pt-0.5 p-1 bg-slate-100 rounded border border-slate-300 text-[10px]">
                                                    <span class="font-bold text-slate-800">Selesai Potong Tagihan</span>
                                                    <?php if (!empty($it['catatan_retur'])): ?>
                                                        <div class="text-slate-600 italic text-[9px]"><?= htmlspecialchars($it['catatan_retur']) ?></div>
                                                    <?php endif; ?>
                                                    <div class="text-[9px] text-zcMut mt-0.5">Selesai: <?= $it['tgl_selesai_retur'] ? date('d/m/Y H:i', strtotime($it['tgl_selesai_retur'])) : '-' ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 font-semibold text-slate-700">
                                    Rp <?= number_format($hargaBeli, 0, ',', '.') ?>
                                    <span class="block text-[10px] text-zcMut font-normal">/<?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?></span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="font-bold text-slate-800">Rp <?= number_format($itSubtotalFaktur, 0, ',', '.') ?></div>
                                    <div class="text-[10px] text-zcMut font-normal"><?= $itQpesan ?> <?= htmlspecialchars($it['satuan_besar'] ?? 'Dus') ?> &times; Rp <?= number_format($hargaBeli, 0, ',', '.') ?></div>
                                    <?php if ($itQrusak > 0): ?>
                                        <div class="mt-1 pt-1 border-t border-slate-100 text-[10px] space-y-0.5">
                                            <div class="text-emerald-700 font-semibold">• Fisik Masuk: Rp <?= number_format($itSubtotalFisik, 0, ',', '.') ?></div>
                                            <div class="text-rose-600 font-semibold">• Retur Pending: Rp <?= number_format($itSubtotalRetur, 0, ',', '.') ?></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5">
                                    <?php if ($it['no_batch']): ?>
                                        <div class="font-mono font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($it['no_batch']) ?></div>
                                        <div class="text-[10px] text-emerald-700 font-semibold">Exp: <?= date('d/m/Y', strtotime($it['tgl_exp'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic">— Non Obat —</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <?php if ($itQrusak > 0 && $stRetur === 'menunggu_pengganti'): ?>
                                        <div class="mb-2 p-1.5 bg-slate-50 border border-slate-200 rounded-lg flex flex-col gap-1 w-full max-w-[210px] mx-auto text-left shadow-2xs">
                                            <div class="text-[9px] font-bold text-slate-500 uppercase tracking-wider px-0.5">Tindakan Klaim Retur:</div>
                                            <button type="button" onclick="openTerimaPenggantiModal(<?= htmlspecialchars(json_encode([
                                                'id_detail' => $it['id'],
                                                'no_referensi' => $p['no_referensi'],
                                                'nama_produk' => $it['nama_produk'] . ' — ' . $it['nama_variasi'],
                                                'kategori' => $it['kategori'],
                                                'satuan' => $it['satuan_besar'] ?? 'Dus',
                                                'qty_rusak' => $qtyRusak,
                                                'harga_beli' => $hargaBeli
                                            ]), ENT_QUOTES, 'UTF-8') ?>)" class="w-full inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold transition shadow-2xs">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                                <span>Terima Unit Pengganti</span>
                                            </button>
                                            <button type="button" onclick="openPotongFakturModal(<?= htmlspecialchars(json_encode([
                                                'id_detail' => $it['id'],
                                                'no_referensi' => $p['no_referensi'],
                                                'nama_produk' => $it['nama_produk'] . ' — ' . $it['nama_variasi'],
                                                'satuan' => $it['satuan_besar'] ?? 'Dus',
                                                'qty_rusak' => $qtyRusak,
                                                'harga_beli' => $hargaBeli,
                                                'nilai_klaim' => $qtyRusak * $hargaBeli
                                            ]), ENT_QUOTES, 'UTF-8') ?>)" class="w-full inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded text-[11px] font-semibold transition">
                                                <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>
                                                <span>Potong Tagihan Faktur</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isObat && $it['no_batch'] && !empty($it['id_stok_batch'])): ?>
                                        <a href="cetak_batch_stiker.php?tipe=batch&id=<?= $it['id_stok_batch'] ?>&qty=<?= $qtyBesar ?>" target="_blank" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-xs font-bold transition shadow-2xs"
                                            title="Cetak <?= $qtyBesar ?> salinan stiker barcode batch ini">
                                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                            <span>Cetak (<?= $qtyBesar ?>x): <span class="font-mono"><?= htmlspecialchars($it['no_batch']) ?></span></span>
                                        </a>
                                    <?php elseif ($it['kategori'] === 'Alat Kesehatan'): ?>
                                        <?php if ($qtyBesar > 0): ?>
                                            <a href="cetak_batch_stiker.php?id_penerimaan=<?= $p['id'] ?>&id_variasi=<?= $it['id_variasi'] ?>" target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-xs font-bold transition shadow-2xs"
                                                title="Cetak <?= $qtyBesar ?> stiker barcode Serial Number (SN) unit alat kesehatan ini">
                                                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                                <span>Cetak (<?= $qtyBesar ?>x) Stiker SN</span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px] italic">Semua Diretur</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px]">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-slate-50/80 border-t border-zcBrd text-xs">
                            <tr>
                                <td colspan="4" class="px-3 py-2.5 text-right font-bold text-slate-700">
                                    Total Nilai Faktur Asli (<?= $totalQtyPesanPO ?> Satuan Besar):
                                </td>
                                <td class="px-3 py-2.5 font-black text-slate-900">
                                    Rp <?= number_format($totalNilaiFaktur, 0, ',', '.') ?>
                                </td>
                                <td colspan="2" class="px-3 py-2.5 text-[10px] text-zcMut">
                                    <?php if ($totalQtyRusakPO > 0): ?>
                                        <span class="text-emerald-700 font-bold block">• Fisik Diterima Baik: Rp <?= number_format($totalNilaiFisik, 0, ',', '.') ?> (<?= $totalQtyTerimaPO ?> Dus)</span>
                                        <span class="text-rose-600 font-bold block">• Klaim Retur Pending: Rp <?= number_format($totalNilaiRetur, 0, ',', '.') ?> (<?= $totalQtyRusakPO ?> Dus)</span>
                                    <?php else: ?>
                                        <span class="text-emerald-700 font-semibold italic">✓ 100% Sesuai Tagihan Faktur Fisik</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="px-3 py-2 bg-slate-50/50 border-t border-zcBrd/60 flex items-center justify-between text-[11px] text-zcMut">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span>Total harga dihitung dari <em>Qty Satuan Besar &times; Harga Beli</em> untuk verifikasi kesesuaian faktur/nota saat barang masuk.</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Viewport Scanner Kamera - Seragam persis dengan POS Kasir & Verifikasi */
#bpom_scanner_view { width: 100% !important; overflow: hidden !important; border-radius: 0.75rem !important; background: #0f172a !important; }
#bpom_scanner_view video {
    width: 100% !important;
    height: 100% !important;
    min-height: 280px !important;
    max-height: 360px !important;
    object-fit: cover !important;
    border-radius: 0.75rem !important;
    display: block !important;
    margin: 0 auto !important;
}
#bpom_scanner_view canvas { border-radius: 0.75rem !important; }
#bpom_scanner_view #qr-shaded-region { border-radius: 0.75rem !important; }
</style>

<!-- ========================================================================= -->
<!-- MODAL: PENCARIAN DATABASE OBAT RESMI (API BPOM / KFA & SCAN KAMERA)       -->
<!-- ========================================================================= -->
<div id="modal_bpom_search" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl border border-zcBrd w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between bg-slate-50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-800">Pencarian Database Obat (API Publik)</h4>
                    <p class="text-[11px] text-zcMut">Cari nama obat atau scan/ketik barcode kemasan (EAN-13)</p>
                </div>
            </div>
            <button type="button" onclick="closeBpomSearchModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>

        <div class="p-4 border-b border-zcBrd bg-white space-y-3">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-2.5 text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input type="text" id="bpom_search_input" oninput="debounceBpomSearch(this.value)" placeholder="Ketik nama obat atau barcode angka (contoh: 899..., Paracetamol, Sanmol)..." 
                        class="w-full pl-9 pr-3 py-2 border border-zcBrd rounded-xl text-sm focus:outline-none focus:border-zc">
                </div>
                <button type="button" onclick="debounceBpomSearch(document.getElementById('bpom_search_input').value, true)" class="px-4 py-2 bg-zc hover:bg-zcHv text-white rounded-xl text-xs font-bold transition shadow-xs">
                    Cari
                </button>
                <button type="button" id="btn_toggle_cam" onclick="toggleBpomInlineCamera()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span id="label_toggle_cam">Scan Kamera</span>
                </button>
            </div>

            <!-- Inline Camera Viewfinder (Seragam persis dengan POS Kasir) -->
            <div id="bpom_inline_camera_box" class="hidden p-4 bg-slate-950 rounded-2xl border-2 border-slate-700 space-y-3 shadow-xl transition-all">
                <div class="flex items-center justify-between text-xs text-slate-300 pb-2 border-b border-slate-800 flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="font-bold text-white text-xs">Arahkan Barcode Kemasan ke Kamera</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="bpom_camera_select" onchange="startBpomScanner(this.value)" class="text-xs bg-slate-800 text-slate-200 border border-slate-700 rounded-lg px-2.5 py-1 focus:outline-none font-medium">
                            <option value="">Deteksi kamera...</option>
                        </select>
                        <span id="bpom_scan_badge" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            🟢 Scanner Siap
                        </span>
                    </div>
                </div>

                <!-- Camera Viewport Box (Seragam seperti di POS dengan kotak panjang) -->
                <div class="relative w-full min-h-[280px] bg-slate-900 rounded-xl overflow-hidden shadow-inner border border-slate-800 flex items-center justify-center">
                    <div id="bpom_scanner_view" class="w-full"></div>
                    <div id="bpom_scanner_loading" class="absolute inset-0 bg-slate-900/95 flex flex-col items-center justify-center text-white gap-2 z-20">
                        <div class="w-8 h-8 border-4 border-emerald-500/20 border-t-emerald-500 rounded-full animate-spin"></div>
                        <span class="text-xs font-semibold">Memulai Scanner Kamera...</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 flex-wrap gap-2">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Mendukung: <strong>Barcode Angka (899...)</strong>, <strong>Barcode Huruf (Code 128/39)</strong>, &amp; <strong>QR Code Kotak</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="cursor-pointer text-blue-400 hover:text-blue-300 font-bold flex items-center gap-1 transition">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <span>Upload Foto Barcode</span>
                            <input type="file" id="bpom_file_scanner_input" accept="image/*" class="hidden" onchange="scanBarcodeFromFile(this)">
                        </label>
                    </div>
                </div>
            </div>

            <!-- Quick Suggestions -->
            <div class="flex items-center gap-2 text-[11px] text-zcMut flex-wrap">
                <span>Contoh Cepat:</span>
                <button type="button" onclick="setBpomSearch('Paracetamol')" class="text-blue-600 hover:underline">Paracetamol</button> •
                <button type="button" onclick="setBpomSearch('Sanmol')" class="text-blue-600 hover:underline">Sanmol</button> •
                <button type="button" onclick="setBpomSearch('Panadol')" class="text-blue-600 hover:underline">Panadol</button> •
                <button type="button" onclick="setBpomSearch('8992772110321')" class="font-mono text-emerald-700 hover:underline">8992772110321 (Barcode)</button> •
                <button type="button" onclick="setBpomSearch('Amoxicillin')" class="text-blue-600 hover:underline">Amoxicillin</button>
            </div>
        </div>

        <div id="bpom_search_results" class="p-4 flex-1 overflow-y-auto space-y-3 min-h-[260px]">
            <div class="text-center py-10 text-zcMut">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <p class="text-xs font-semibold">Silakan ketik nama obat atau barcode angka untuk mencari data obat</p>
            </div>
        </div>

        <div class="px-5 py-3 bg-slate-50 border-t border-zcBrd flex justify-between items-center text-xs text-zcMut">
            <span>Data Terhubung: Katalog BPOM RI / KFA Kemenkes</span>
            <button type="button" onclick="closeBpomSearchModal()" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- MODAL 4: BUKTI NOTA PENERIMAAN FISIK & BERITA ACARA RETUR                  -->
<!-- ========================================================================= -->
<div id="modal_nota_retur" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl border border-zcBrd w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden">
        <div class="px-5 py-3.5 border-b border-zcBrd flex items-center justify-between bg-slate-50 print:hidden">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <h4 class="text-sm font-bold text-slate-800">Bukti Penerimaan Barang</h4>
            </div>
            <div class="flex items-center gap-2">
                <a id="btn_open_full_nota" href="#" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg flex items-center gap-1.5 transition shadow-2xs">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>Buka Lembar A4 Cetak</span>
                </a>
                <button type="button" onclick="printNotaReturModal()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg flex items-center gap-1.5 transition shadow-sm">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>Cetak Bukti</span>
                </button>
                <button type="button" onclick="closeNotaReturModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>
        </div>

        <div id="printable_nota_area" class="p-6 overflow-y-auto flex-1 font-sans text-slate-800 bg-white">
            <!-- Kop Nota Resmi ZenCare Medical (Identik Dokumen Laporan Resmi) -->
            <div class="border-b-2 border-slate-900 pb-3 mb-4 flex justify-between items-center">
                <div class="flex items-center gap-3.5">
                    <img src="/inventory_zencare/gambar/Logo.png" alt="Logo ZenCare" onerror="this.src='../gambar/Logo.png'" class="h-12 w-auto object-contain">
                    <div>
                        <h2 class="text-base font-black tracking-tight text-[#0f2d5a] leading-tight">ZENCARE MEDICAL</h2>
                        <p class="text-xs text-slate-600 font-semibold">Distributor Alat Kesehatan dan Obat Omnichannel</p>
                        <p class="text-[10px] text-slate-500">Izin Apotek: 445/SIA/2026 • SIPA: 19890425/SIPA/2026/01 • Jl. Veteran No. 12, Kota Malang</p>
                    </div>
                </div>
                <div class="text-right text-[11px] text-slate-600">
                    <strong class="text-[#0f2d5a] block font-bold text-xs">BUKTI PENERIMAAN</strong>
                    <div id="nota_ref_no" class="font-mono font-bold text-slate-900 text-xs mt-0.5"></div>
                    <div class="text-[10px] text-slate-500" id="nota_tgl_kop"></div>
                </div>
            </div>

            <!-- Judul Dokumen Resmi -->
            <div class="text-center mb-3.5">
                <h3 class="text-sm font-black text-slate-900 tracking-wide uppercase">BUKTI PENERIMAAN BARANG</h3>
            </div>

            <!-- Metadata Info -->
            <table class="w-full text-xs mb-3 border-collapse">
                <tr>
                    <td class="text-slate-500 py-0.5 w-[16%]">No. Referensi:</td>
                    <td id="nota_ref_val" class="font-mono font-bold text-slate-900 py-0.5 w-[34%]"></td>
                    <td class="text-slate-500 py-0.5 w-[16%]">Supplier / Pengirim:</td>
                    <td id="nota_supplier" class="font-bold text-slate-900 py-0.5 w-[34%]"></td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">Tanggal Terima:</td>
                    <td id="nota_tgl" class="font-semibold text-slate-800 py-0.5"></td>
                    <td class="text-slate-500 py-0.5">Diinput Sistem:</td>
                    <td id="nota_petugas" class="font-semibold text-slate-800 py-0.5"></td>
                </tr>
                <tr>
                    <td class="text-slate-500 py-0.5">Sumber Dokumen:</td>
                    <td id="nota_sumber" class="font-semibold text-slate-800 py-0.5"></td>
                    <td class="text-slate-500 py-0.5">Status:</td>
                    <td id="nota_status_ringkas" class="font-semibold text-slate-800 py-0.5">Lengkap</td>
                </tr>
            </table>

            <!-- Tabel Rincian Barang (Standar Faktur Resmi) -->
            <div class="border border-slate-300 rounded-lg overflow-hidden mb-3">
                <table class="w-full text-xs">
                    <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300">
                        <tr>
                            <th class="px-2 py-2 text-center w-[4%]">No</th>
                            <th class="px-3 py-2 text-left w-[32%]">Nama Produk</th>
                            <th class="px-2 py-2 text-center w-[14%]">Batch / Exp / SN</th>
                            <th class="px-2 py-2 text-center w-[8%]">Pesan</th>
                            <th class="px-2 py-2 text-center w-[8%]">Diterima</th>
                            <th class="px-2 py-2 text-center w-[8%]">Rusak</th>
                            <th class="px-2 py-2 text-right w-[12%]">Harga Satuan</th>
                            <th class="px-2 py-2 text-right w-[14%]">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="nota_items_body" class="divide-y divide-slate-200">
                        <!-- Filled by JS -->
                    </tbody>
                    <tfoot id="nota_items_foot" class="bg-slate-50 font-bold border-t border-slate-300">
                        <!-- Filled by JS -->
                    </tfoot>
                </table>
            </div>

            <!-- Catatan & Ringkasan Nilai (Format Faktur Asli Tanpa Kotak AI Berlebih) -->
            <div class="flex justify-between items-start gap-6 mt-3 mb-4">
                <div class="w-[58%] text-xs text-slate-700">
                    <div class="font-bold text-slate-900 mb-1">Catatan:</div>
                    <div id="nota_catatan" class="text-slate-600">Barang telah diperiksa fisik dan diterima sesuai surat jalan.</div>
                    
                    <div id="nota_retur_klausul_box" class="hidden mt-2 p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700">
                        <strong class="text-slate-800">Keterangan Retur:</strong>
                        <div id="nota_retur_explicit_text" class="mt-1 text-slate-600 leading-relaxed"></div>
                    </div>
                </div>
                <div class="w-[38%]">
                    <table class="w-full text-xs">
                        <tr>
                            <td class="py-1 text-slate-500">Total Faktur Pesanan:</td>
                            <td id="nota_sum_faktur" class="py-1 text-right font-mono font-semibold text-slate-800">Rp 0</td>
                        </tr>
                        <tr id="nota_row_retur" class="hidden">
                            <td class="py-1 text-slate-500" id="nota_label_retur">Barang Rusak / Retur:</td>
                            <td id="nota_sum_retur" class="py-1 text-right font-mono text-slate-600">- Rp 0</td>
                        </tr>
                        <tr id="nota_row_pengganti" class="hidden">
                            <td class="py-1 text-[#0f2d5a] font-semibold" id="nota_label_pengganti">Barang Pengganti Masuk:</td>
                            <td id="nota_sum_pengganti" class="py-1 text-right font-mono font-semibold text-[#0f2d5a]">+ Rp 0</td>
                        </tr>
                        <tr class="border-t border-slate-900 font-bold text-sm">
                            <td class="py-2 text-slate-900" id="nota_label_total_akhir">Total Diterima Fisik:</td>
                            <td id="nota_sum_terima" class="py-2 text-right font-mono text-slate-900 font-extrabold">Rp 0</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Tanda Tangan Dua Pihak -->
            <div class="mt-8 pt-4 border-t border-slate-200 grid grid-cols-2 gap-8 text-center text-xs">
                <div>
                    <div class="text-slate-500 mb-0.5">Yang Menyerahkan,</div>
                    <div class="font-bold text-slate-800 mb-14">( Pihak Supplier / Pengirim )</div>
                    <div class="border-b border-slate-400 w-44 mx-auto"></div>
                    <div class="text-[11px] text-slate-500 mt-1">Tanda Tangan &amp; Nama Terang</div>
                </div>
                <div>
                    <div class="text-slate-500 mb-0.5">Yang Menerima,</div>
                    <div class="font-bold text-slate-800 mb-14">ZenCare Medical</div>
                    <div class="border-b border-slate-400 w-44 mx-auto"></div>
                    <div class="text-[11px] text-slate-500 mt-1">Petugas Gudang / Pemeriksa Fisik</div>
                </div>
            </div>
        </div>

        <div class="px-5 py-3 bg-slate-50 border-t border-zcBrd flex justify-end gap-2 print:hidden">
            <button type="button" onclick="closeNotaReturModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition">
                Tutup
            </button>
            <a id="btn_modal_full_nota_foot" href="#" target="_blank" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Format Dokumen A4</span>
            </a>
            <button type="button" onclick="printNotaReturModal()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak Bukti Penerimaan</span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: TERIMA BARANG PENGGANTI RETUR (TUKAR GULING SUPPLIER)            -->
<!-- ========================================================================= -->
<div id="modal_terima_pengganti" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl border border-zcBrd w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
        <form action="tambah_stok.php" method="POST" id="form_terima_pengganti">
            <input type="hidden" name="aksi" value="terima_barang_pengganti">
            <input type="hidden" name="id_detail" id="ganti_id_detail">

            <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Terima Barang Pengganti Retur</h4>
                        <p class="text-[11px] text-zcMut">Tukar guling fisik barang cacat dari supplier</p>
                    </div>
                </div>
                <button type="button" onclick="closeTerimaPenggantiModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>

            <div class="p-5 space-y-4 text-xs">
                <!-- Info Card Dokumen Awal -->
                <div class="p-3 bg-emerald-50/60 border border-emerald-200 rounded-xl space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-[11px]">No. Dokumen PO Awal:</span>
                        <span class="font-mono font-bold text-slate-800" id="ganti_no_ref">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-[11px]">Nama Produk:</span>
                        <span class="font-bold text-slate-800 text-right max-w-[240px] truncate" id="ganti_nama_produk">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-[11px]">Kategori:</span>
                        <span class="font-semibold text-slate-700" id="ganti_kategori">-</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-emerald-200/80">
                        <span class="text-slate-600 font-medium">Kuantiti Rusak/Klaim:</span>
                        <span class="font-bold text-rose-700" id="ganti_qty_klaim_text">-</span>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah Unit Pengganti Diterima *</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="qty_pengganti" id="ganti_qty_input" min="1" required 
                            class="w-full px-3 py-2 border border-zcBrd rounded-xl text-sm font-bold text-slate-800 focus:outline-none focus:border-emerald-600">
                        <span class="font-bold text-slate-600 text-xs px-2" id="ganti_satuan_label">Dus</span>
                    </div>
                    <p class="text-[10px] text-zcMut mt-1">Stok etalase akan bertambah sesuai kuantiti ini. Nilai utang/faktur toko tidak digandakan.</p>
                </div>

                <!-- Bagian Khusus Obat: Batch & Kedaluwarsa Baru -->
                <div id="ganti_obat_box" class="space-y-3 p-3 bg-slate-50 rounded-xl border border-zcBrd">
                    <div class="font-bold text-blue-900 flex items-center gap-1.5 text-xs">
                        <span>Data Batch &amp; Kedaluwarsa Barang Pengganti (Wajib Obat)</span>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Batch Baru *</label>
                        <input type="text" name="no_batch_pengganti" id="ganti_no_batch" placeholder="Contoh: BATCH-NEW-2026"
                            class="w-full px-3 py-2 border border-zcBrd rounded-xl text-xs font-mono uppercase focus:outline-none focus:border-blue-600">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Kedaluwarsa (Exp Date) Baru *</label>
                        <input type="date" name="tgl_exp_pengganti" id="ganti_tgl_exp" min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                            class="w-full px-3 py-2 border border-zcBrd rounded-xl text-xs focus:outline-none focus:border-blue-600">
                    </div>
                </div>

                <!-- Bagian Khusus Alkes: Serial Number Baru Dinamis (Tanpa Perlu Tanda Koma) -->
                <div id="ganti_alkes_box" class="space-y-3 p-3 bg-slate-50 rounded-xl border border-zcBrd hidden">
                    <div class="font-bold text-teal-900 flex items-center justify-between text-xs">
                        <span>Serial Number Unit Pengganti (Khusus Alkes)</span>
                        <span id="ganti_sn_count_badge" class="text-[10px] font-semibold text-teal-700 bg-teal-100 px-2 py-0.5 rounded">1 Unit</span>
                    </div>
                    <input type="hidden" name="sn_pengganti" id="ganti_sn_hidden" value="">
                    <div>
                        <div id="ganti_sn_list" class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                            <!-- Dihasilkan dinamis via JS per kuantiti unit pengganti -->
                        </div>
                        <p class="text-[10px] text-zcMut mt-1.5">Input tiap nomor seri pada kolom terpisah (tekan Enter untuk beralih baris tanpa perlu mengetik tanda koma).</p>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Serah Terima Pengganti (Opsional)</label>
                    <textarea name="catatan_retur" id="ganti_catatan" rows="2" placeholder="Contoh: Barang pengganti diantar kurir Bpk. Joko, kondisi segel aman 100%..."
                        class="w-full px-3 py-2 border border-zcBrd rounded-xl text-xs focus:outline-none focus:border-emerald-600"></textarea>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-zcBrd flex justify-end gap-2">
                <button type="button" onclick="closeTerimaPenggantiModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Konfirmasi Terima Pengganti</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 6: SELESAIKAN RETUR VIA POTONG FAKTUR (CREDIT NOTE)                 -->
<!-- ========================================================================= -->
<div id="modal_potong_faktur" class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4 hidden transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl border border-zcBrd w-full max-w-md max-h-[90vh] flex flex-col overflow-hidden">
        <form action="tambah_stok.php" method="POST" id="form_potong_faktur">
            <input type="hidden" name="aksi" value="selesaikan_potong_faktur">
            <input type="hidden" name="id_detail" id="potong_id_detail">

            <div class="px-5 py-4 border-b border-zcBrd flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Selesaikan via Potong Faktur</h4>
                        <p class="text-[11px] text-zcMut">Penyelesaian finansial / Credit Note supplier</p>
                    </div>
                </div>
                <button type="button" onclick="closePotongFakturModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>

            <div class="p-5 space-y-4 text-xs">
                <!-- Info Card Dokumen Awal -->
                <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-[11px]">No. Dokumen PO Awal:</span>
                        <span class="font-mono font-bold text-slate-800" id="potong_no_ref">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-[11px]">Nama Produk:</span>
                        <span class="font-bold text-slate-800 text-right max-w-[200px] truncate" id="potong_nama_produk">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 text-[11px]">Kuantiti Barang Diretur:</span>
                        <span class="font-bold text-rose-700" id="potong_qty_rusak_text">-</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-blue-200/80">
                        <span class="text-slate-600 font-bold">Nilai Potongan Tagihan:</span>
                        <span class="font-black text-blue-700 text-sm" id="potong_nilai_text">Rp 0</span>
                    </div>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-[11px] leading-relaxed">
                    <strong>Ketentuan Finansial:</strong> Pilihan ini digunakan jika pihak supplier tidak dapat mengganti fisik barang (kehabisan stok) dan menggantinya dengan potongan nilai tagihan pada faktur ini / faktur berikutnya (Credit Note). <strong>Stok fisik etalase tidak akan bertambah.</strong>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nomor Dokumen Credit Note / Catatan *</label>
                    <textarea name="catatan_retur" id="potong_catatan" rows="2" required placeholder="Contoh: Disepakati potong tagihan sesuai Credit Note No. CN-2026/08..."
                        class="w-full px-3 py-2 border border-zcBrd rounded-xl text-xs focus:outline-none focus:border-blue-600"></textarea>
                </div>
            </div>

            <div class="px-5 py-3 bg-slate-50 border-t border-zcBrd flex justify-end gap-2">
                <button type="button" onclick="closePotongFakturModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                    <span>Konfirmasi Potong Tagihan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/html5-qrcode.min.js"></script>
<script>
let rC = 1;
const rawProdukList = <?= json_encode($produkList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const minExpStr = '<?= date('Y-m-d', strtotime('+1 day')) ?>';

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function getOptionsHtml() {
    let html = '<option value="">-- Cari &amp; Pilih Produk --</option>';
    if (Array.isArray(rawProdukList)) {
        rawProdukList.forEach(p => {
            const satBesar = p.satuan_besar || 'Dus';
            const satKecil = p.satuan_kecil || 'Pcs';
            const rasio = p.rasio_konversi || 1;
            const hargaJual = p.harga_jual_besar || 0;
            const label = `${p.label} (${satBesar})`;
            const skuVar = p.sku_variasi || '';
            const skuInd = p.sku_induk || '';
            const namaP = p.nama_produk || '';
            html += `<option value="${p.id}" data-kat="${escapeHtml(p.kategori)}" data-satbesar="${escapeHtml(satBesar)}" data-satkecil="${escapeHtml(satKecil)}" data-rasio="${rasio}" data-hargajual="${hargaJual}" data-sku="${escapeHtml(skuVar)}" data-skuinduk="${escapeHtml(skuInd)}" data-namaproduk="${escapeHtml(namaP)}">${escapeHtml(label)}</option>`;
        });
    }
    return html;
}

document.getElementById('sumber_select').addEventListener('change', function() {
    const inp = document.getElementById('inp_referensi');
    const lbl = document.getElementById('label_referensi');
    const hint = document.getElementById('hint_referensi');
    if (this.value === 'Pembelian Langsung') {
        inp.required = false;
        lbl.innerText = 'No. Referensi (PO/Nota) (Opsional)';
        hint.classList.remove('hidden');
    } else {
        inp.required = true;
        lbl.innerText = 'No. Referensi (PO/Nota) *';
        hint.classList.add('hidden');
    }
});
document.getElementById('sumber_select').dispatchEvent(new Event('change'));

function formatRupiahInput(el) {
    if (!el) return;
    let clean = el.value.replace(/[^0-9]/g, '');
    if (clean === '') {
        el.value = '';
        return;
    }
    el.value = parseInt(clean, 10).toLocaleString('id-ID');
}

function syncQty(idx, source) {
    const pesanEl = document.getElementById('qty_pesan-' + idx);
    const terimaEl = document.getElementById('qty_terima-' + idx);
    const rusakEl = document.getElementById('qty_rusak-' + idx);
    if (!pesanEl || !terimaEl || !rusakEl) return;

    let pesan = parseInt(pesanEl.value, 10);
    if (isNaN(pesan) || pesan < 0) pesan = 0;
    
    let terima = parseInt(terimaEl.value, 10);
    if (isNaN(terima) || terima < 0) terima = 0;

    let rusak = parseInt(rusakEl.value, 10);
    if (isNaN(rusak) || rusak < 0) rusak = 0;

    if (source === 'pesan') {
        if (rusak === 0) {
            terimaEl.value = pesan;
        } else {
            terimaEl.value = Math.max(0, pesan - rusak);
        }
    } else if (source === 'rusak') {
        if (pesan > 0) {
            terimaEl.value = Math.max(0, pesan - rusak);
        }
    } else if (source === 'terima') {
        if (pesan > 0 && terima <= pesan) {
            rusakEl.value = Math.max(0, pesan - terima);
        }
    }
    calcSubtotal(idx);
    renderSnInputs(idx);
}

function calcSubtotal(idx) {
    const terimaEl = document.getElementById('qty_terima-' + idx);
    const legacyQtyEl = document.getElementById('qty-' + idx);
    const hargaEl = document.getElementById('harga-' + idx);
    const subtotalEl = document.getElementById('subtotal-' + idx);
    if (!subtotalEl) return;
    
    let qty = 0;
    if (terimaEl) {
        qty = parseFloat(terimaEl.value) || 0;
    } else if (legacyQtyEl) {
        qty = parseFloat(legacyQtyEl.value) || 0;
    }
    const rawHarga = hargaEl ? hargaEl.value.replace(/[^0-9]/g, '') : '0';
    const harga = parseFloat(rawHarga) || 0;
    const total = qty * harga;
    
    subtotalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
}

function addRow() {
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    tr.id = 'row-' + rC;
    tr.className = 'border-b border-zcBrd/50';
    tr.innerHTML = `
        <td class="px-2 py-2 align-top">
            <select name="id_variasi[]" id="sel-${rC}" data-row-idx="${rC}" required onchange="checkProduk(this, ${rC}); calcSubtotal(${rC});" class="select2-produk w-full border border-zcBrd rounded-lg px-2 py-1.5 text-xs">
                ${getOptionsHtml()}
            </select>
            
        </td>
        <td class="px-2 py-2 align-top">
            <div class="flex items-center gap-1">
                <input type="number" name="qty_pesan[]" id="qty_pesan-${rC}" min="1" required placeholder="Pesan" oninput="syncQty(${rC}, 'pesan')" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
                <span id="badge-uom-${rC}" class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-1 rounded whitespace-nowrap border border-slate-200">Dus</span>
            </div>
        </td>
        <td class="px-2 py-2 align-top bg-emerald-50/20">
            <input type="number" name="qty_terima[]" id="qty_terima-${rC}" min="0" required placeholder="Baik" oninput="syncQty(${rC}, 'terima')" class="w-full border border-emerald-300 bg-white rounded-lg px-2 py-1.5 focus:outline-none focus:border-emerald-500 text-xs font-bold text-emerald-800">
            <div class="text-[9px] text-emerald-600 mt-0.5">Masuk stok</div>
        </td>
        <td class="px-2 py-2 align-top bg-rose-50/20">
            <input type="number" name="qty_rusak[]" id="qty_rusak-${rC}" min="0" value="0" placeholder="0" oninput="syncQty(${rC}, 'rusak')" class="w-full border border-rose-300 bg-white rounded-lg px-2 py-1.5 focus:outline-none focus:border-rose-500 text-xs font-bold text-rose-700">
            <div class="text-[9px] text-rose-600 mt-0.5">Retur supplier</div>
        </td>
        <td class="px-2 py-2 align-top">
            <div class="relative">
                <span class="absolute left-2.5 top-1.5 text-xs font-bold text-slate-400">Rp</span>
                <input type="text" inputmode="numeric" name="harga_beli[]" id="harga-${rC}" placeholder="0" oninput="formatRupiahInput(this); calcSubtotal(${rC});" 
                    class="w-full pl-8 border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs font-semibold">
            </div>
            <div class="text-[9px] text-zcMut mt-0.5">Per 1 Satuan Besar</div>
        </td>
        <td class="px-2 py-2 align-top">
            <div id="subtotal-${rC}" class="text-xs font-bold text-slate-800 pt-1.5 whitespace-nowrap">Rp 0</div>
            <div class="text-[9px] text-zcMut">Subtotal Diterima</div>
        </td>
        <td class="px-2 py-2 align-top"><input type="text" name="no_batch[]" id="b-${rC}" class="w-full border border-zcBrd rounded-lg px-2 py-1.5 focus:outline-none focus:border-zc text-xs bg-slate-100" readonly placeholder="Wajib obat"></td>
        <td class="px-2 py-2 align-top">
            <div id="sn-container-${rC}" class="min-w-[130px]">
                <input type="hidden" name="serial_number[]" id="s-${rC}" value="">
                <div id="sn-placeholder-${rC}" class="text-[10px] text-slate-400 italic py-1.5 px-1 bg-slate-50 border border-dashed border-slate-200 rounded text-center">
                    — Khusus Alkes —
                </div>
                <div id="sn-list-${rC}" class="space-y-1 hidden"></div>
            </div>
        </td>
        <td class="px-2 py-2 align-top text-center"><button type="button" onclick="removeRow(${rC})" class="text-slate-300 hover:text-red-500 font-bold text-lg leading-none">&times;</button></td>
    `;
    tbody.appendChild(tr);
    initSelect2('sel-' + rC);
    rC++;
}

// --- UNIFIED BPOM / DRUG DATABASE SEARCH & INLINE SCANNER ---
let bpomScanner = null;
let isBpomScannerRunning = false;
let bpomSearchTimer = null;
let currentBpomResults = [];

function playAudioBeep() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, audioCtx.currentTime);
        gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.15);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.15);
    } catch(e) {}
}

function openBpomSearchModal(initKeyword = '') {
    const modal = document.getElementById('modal_bpom_search');
    if (!modal) return;
    modal.classList.remove('hidden');
    const inp = document.getElementById('bpom_search_input');
    if (inp) {
        if (initKeyword) inp.value = initKeyword;
        inp.focus();
        if (initKeyword) debounceBpomSearch(initKeyword, true);
    }
}

function closeBpomSearchModal() {
    const modal = document.getElementById('modal_bpom_search');
    if (modal) modal.classList.add('hidden');
    stopBpomScanner();
}

function setBpomSearch(term) {
    const inp = document.getElementById('bpom_search_input');
    if (inp) {
        inp.value = term;
        debounceBpomSearch(term, true);
    }
}

function toggleBpomInlineCamera() {
    const box = document.getElementById('bpom_inline_camera_box');
    const label = document.getElementById('label_toggle_cam');
    if (!box) return;

    if (box.classList.contains('hidden')) {
        box.classList.remove('hidden');
        if (label) label.innerText = 'Tutup Kamera';
        startBpomScanner();
    } else {
        box.classList.add('hidden');
        if (label) label.innerText = 'Scan Kamera';
        stopBpomScanner();
    }
}

async function startBpomScanner(cameraId = null) {
    const loading = document.getElementById('bpom_scanner_loading');
    const badge = document.getElementById('bpom_scan_badge');
    if (loading) loading.classList.remove('hidden');

    try {
        if (typeof Html5Qrcode === 'undefined') {
            throw new Error('Library Html5Qrcode belum dimuat');
        }

        // Hentikan scanner aktif sebelumnya jika ada
        if (bpomScanner && isBpomScannerRunning) {
            try { await bpomScanner.stop(); } catch(e) {}
            isBpomScannerRunning = false;
        }

        // Format lengkap mencakup:
        // 1. Barcode 1D Angka murni: EAN_13, EAN_8, UPC_A, UPC_E, ITF
        // 2. Barcode 1D Huruf/Alfanumerik: CODE_128, CODE_39, CODE_93, CODABAR
        // 3. Barcode 2D Kotak: QR_CODE, DATA_MATRIX, AZTEC
        let formatsToSupport = [];
        if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
            formatsToSupport = [
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.DATA_MATRIX,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.CODE_93,
                Html5QrcodeSupportedFormats.CODABAR,
                Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.AZTEC
            ];
        }

        bpomScanner = new Html5Qrcode("bpom_scanner_view", {
            formatsToSupport: formatsToSupport,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            },
            verbose: false
        });

        const devices = await Html5Qrcode.getCameras();
        const select = document.getElementById('bpom_camera_select');
        if (select && devices && devices.length) {
            select.innerHTML = '';
            devices.forEach((dev, idx) => {
                const opt = document.createElement('option');
                opt.value = dev.id;
                opt.text = dev.label || `Kamera ${idx + 1}`;
                if (cameraId && dev.id === cameraId) opt.selected = true;
                select.appendChild(opt);
            });
        }

        const chosenCamera = cameraId || (devices && devices.length > 0 ? devices[0].id : { facingMode: "environment" });
        const config = {
            fps: 20,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                let w = Math.min(Math.floor(viewfinderWidth * 0.75), 280);
                let h = Math.min(Math.floor(viewfinderHeight * 0.65), 180);
                return { width: Math.max(w, 200), height: Math.max(h, 120) };
            }
        };

        await bpomScanner.start(
            chosenCamera,
            config,
            (decodedText) => onBpomBarcodeScanSuccess(decodedText),
            (errorMessage) => {}
        );

        isBpomScannerRunning = true;
        if (loading) loading.classList.add('hidden');
        if (badge) {
            badge.innerText = '🟢 Scanner Aktif';
            badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
        }
    } catch(err) {
        if (loading) loading.classList.add('hidden');
        if (badge) {
            badge.innerText = '❌ Gagal Kamera';
            badge.className = 'px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30';
        }
        console.warn('Scanner camera error:', err);
    }
}

async function stopBpomScanner() {
    if (bpomScanner && isBpomScannerRunning) {
        try {
            await bpomScanner.stop();
        } catch(e) {}
        isBpomScannerRunning = false;
    }
}

// Opsi pemindaian cadangan dari upload foto jika webcam kurang tajam
async function scanBarcodeFromFile(input) {
    if (!input || !input.files || input.files.length === 0) return;
    const file = input.files[0];
    const badge = document.getElementById('bpom_scan_badge');
    if (badge) badge.innerText = 'Memindai File...';
    
    try {
        if (!bpomScanner) {
            let formats = (typeof Html5QrcodeSupportedFormats !== 'undefined') ? [
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.DATA_MATRIX,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A
            ] : [];
            bpomScanner = new Html5Qrcode("bpom_scanner_view", { formatsToSupport: formats, verbose: false });
        }
        const result = await bpomScanner.scanFileV2(file, true);
        if (result && result.decodedText) {
            onBpomBarcodeScanSuccess(result.decodedText);
        } else {
            alert('Tidak dapat mendeteksi barcode pada foto tersebut. Pastikan barcode terlihat jelas dan tidak buram.');
        }
    } catch(err) {
        alert('Gagal mendeteksi barcode dari foto: ' + (err.message || err || 'Barcode tidak terbaca'));
    } finally {
        input.value = '';
        if (badge) badge.innerText = '🟢 Scanner Siap';
    }
}

let lastScanCode = '';
let lastScanTime = 0;

async function onBpomBarcodeScanSuccess(rawCode) {
    if (!rawCode) return;
    let code = String(rawCode).trim();
    if (!code) return;

    let now = Date.now();
    // Cooldown 1.5 detik untuk barcode yang sama persis
    if (code.toLowerCase() === lastScanCode.toLowerCase() && (now - lastScanTime) < 1500) {
        return;
    }
    lastScanCode = code;
    lastScanTime = now;
    
    playAudioBeep();

    // 1. Efek Shutter Flash pada viewport kamera
    const scanView = document.getElementById('bpom_scanner_view');
    if (scanView) {
        scanView.classList.add('ring-4', 'ring-emerald-400');
        setTimeout(() => scanView.classList.remove('ring-4', 'ring-emerald-400'), 500);
    }

    // 2. Ekstraksi cerdas jika barcode berupa QR Code BPOM RI / URL / GS1
    let extractedCode = code;
    
    // Cek jika barcode berupa URL BPOM / web
    if (code.startsWith('http://') || code.startsWith('https://')) {
        try {
            const urlObj = new URL(code);
            const qParam = urlObj.searchParams.get('q') || urlObj.searchParams.get('nie') || urlObj.searchParams.get('barcode');
            if (qParam) {
                extractedCode = qParam;
            } else {
                const segments = urlObj.pathname.split('/').filter(Boolean);
                if (segments.length > 0) extractedCode = segments[segments.length - 1];
            }
        } catch(e) {}
    } 
    // Cek format GS1 (01)08992772110321...
    else if (code.includes('(01)')) {
        const matchGtin = code.match(/\(01\)(\d{13,14})/);
        if (matchGtin && matchGtin[1]) {
            extractedCode = matchGtin[1].replace(/^0+/, '');
        }
    }

    // 3. Pencocokan langsung (Direct Matching) ke variasi produk lokal apotek:
    // a. Cek mapping nomor barcode EAN-13 resmi
    // b. Cek sku_variasi atau sku_induk
    let directMatchedId = null;
    let directProductName = '';

    const cleanInputDigits = extractedCode.replace(/[^0-9]/g, '');
    const cleanLower = extractedCode.toLowerCase();

    // Mapping barcode EAN-13 produk umum
    const knownEanMap = {
        '8999908001015': 2, // Amoxicillin 500mg
        '8992741120015': 1, // Paracetamol Syrup Anak 60ml
        '8999908001107': 1, // Paracetamol 500mg
        '8992772110321': 1, // Panadol Extra / Paracetamol
        '8993005110123': 1  // Hufagrip Merah
    };

    if (cleanInputDigits && knownEanMap[cleanInputDigits]) {
        directMatchedId = knownEanMap[cleanInputDigits];
    }

    if (!directMatchedId && Array.isArray(rawProdukList)) {
        for (const p of rawProdukList) {
            const skuVar = (p.sku_variasi || '').toLowerCase();
            const skuInduk = (p.sku_induk || '').toLowerCase();
            const namaProd = (p.nama_produk || p.label || '').toLowerCase();
            
            if (skuVar && (skuVar === cleanLower || cleanLower.includes(skuVar))) {
                directMatchedId = p.id;
                directProductName = p.label;
                break;
            }
            if (skuInduk && (skuInduk === cleanLower)) {
                directMatchedId = p.id;
                directProductName = p.label;
                break;
            }
            if (cleanLower.length >= 4 && namaProd.includes(cleanLower)) {
                directMatchedId = p.id;
                directProductName = p.label;
                break;
            }
        }
    }

    // JIKA COCOK LANGSUNG DENGAN PRODUK DI APOTEK:
    if (directMatchedId) {
        stopBpomScanner();
        closeBpomSearchModal();
        selectBpomMedicineDirect(directMatchedId, directProductName);
        return;
    }

    // JIKA BELUM COCOK LANGSUNG DI DROPDOWN APOTEK:
    // Tampilkan di input pencarian teks dan otomatis panggil API BPOM
    const inp = document.getElementById('bpom_search_input');
    if (inp) {
        inp.value = extractedCode;
    }
    
    const box = document.getElementById('bpom_inline_camera_box');
    const label = document.getElementById('label_toggle_cam');
    if (box) box.classList.add('hidden');
    if (label) label.innerText = 'Scan Kamera';
    stopBpomScanner();

    debounceBpomSearch(extractedCode, true);
}

// --- Listener Barcode Scanner Fisik (USB Gun / Bluetooth Scanner) ---
let hwBarcodeBuffer = '';
let hwBarcodeTimer = null;

document.addEventListener('keydown', function(e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
    
    if (e.key === 'Enter') {
        if (hwBarcodeBuffer.trim().length >= 3) {
            onBpomBarcodeScanSuccess(hwBarcodeBuffer.trim());
        }
        hwBarcodeBuffer = '';
        clearTimeout(hwBarcodeTimer);
        return;
    }
    
    if (e.key && e.key.length === 1) {
        hwBarcodeBuffer += e.key;
        clearTimeout(hwBarcodeTimer);
        hwBarcodeTimer = setTimeout(() => {
            hwBarcodeBuffer = '';
        }, 300);
    }
});

function debounceBpomSearch(query, immediate = false) {
    clearTimeout(bpomSearchTimer);
    if (!query || query.trim().length < 2) {
        document.getElementById('bpom_search_results').innerHTML = `
            <div class="text-center py-10 text-zcMut">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <p class="text-xs font-semibold">Silakan ketik minimal 2 karakter untuk mencari database obat</p>
            </div>
        `;
        return;
    }

    const runSearch = async () => {
        const container = document.getElementById('bpom_search_results');
        container.innerHTML = `
            <div class="flex items-center justify-center py-10 gap-2 text-zcMut text-xs font-semibold">
                <svg class="w-5 h-5 animate-spin text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                <span>Mencari data obat...</span>
            </div>
        `;

        try {
            const res = await fetch('../api/lookup_bpom.php?q=' + encodeURIComponent(query.trim()));
            const data = await res.json();
            if (data.found && data.results && data.results.length > 0) {
                currentBpomResults = data.results;
                let html = '';
                data.results.forEach((item, itemIdx) => {
                    const hasInternal = item.internal_product && item.internal_product.id_variasi;
                    const idV = hasInternal ? item.internal_product.id_variasi : null;
                    const internalLabel = hasInternal ? item.internal_product.nama_lengkap : item.product_name;
                    
                    html += `
                        <div class="bg-slate-50 hover:bg-blue-50/50 border border-zcBrd rounded-xl p-3.5 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-200">
                                        ${escapeHtml(item.category || 'Obat')}
                                    </span>
                                    ${item.barcode ? `<span class="font-mono text-[10px] text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">Barcode: ${escapeHtml(item.barcode)}</span>` : ''}
                                    ${!hasInternal ? `<span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">Obat Baru (Auto Master)</span>` : ''}
                                </div>
                                <h4 class="text-sm font-bold text-slate-800">${escapeHtml(item.product_name)}</h4>
                                <div class="text-xs text-slate-600">${escapeHtml(item.dosage_form || '')} • <strong class="text-slate-700">${escapeHtml(item.company || '')}</strong></div>
                                ${hasInternal ? `
                                    <div class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                        <span>Tersedia di Katalog: ${escapeHtml(internalLabel)}</span>
                                    </div>
                                ` : `
                                    <div class="text-[11px] text-amber-700 font-medium flex items-center gap-1 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        <span>Belum di Master Produk. Klik tombol untuk otomatis didaftarkan &amp; langsung masuk penerimaan.</span>
                                    </div>
                                `}
                            </div>
                            <button type="button" id="btn_sel_bpom_${itemIdx}" onclick="handleSelectBpomItem(${itemIdx})" class="px-3.5 py-1.5 ${hasInternal ? 'bg-zc hover:bg-zcHv text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'} rounded-lg text-xs font-bold shrink-0 transition flex items-center gap-1 shadow-xs">
                                ${hasInternal ? `<span>Pilih Obat</span>` : `<span>+ Daftarkan &amp; Pilih</span>`}
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-6 text-zcMut space-y-3">
                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <p class="text-xs font-semibold text-slate-600">Obat dengan kata kunci/barcode "<strong>${escapeHtml(query)}</strong>" belum ada di katalog.</p>
                        <div class="p-4 bg-blue-50/80 border border-blue-200 rounded-xl max-w-md mx-auto text-center space-y-2">
                            <p class="text-xs text-blue-900 font-bold">Ingin mendaftarkan produk ini ke Master Produk secara instan?</p>
                            <p class="text-[11px] text-slate-600">Sistem akan otomatis membuat master produk dan memasukkannya ke baris penerimaan.</p>
                            <button type="button" id="btn_autoreg_custom" onclick="autoRegisterCustomProduct('${escapeHtml(query)}')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                <span>+ Daftarkan &amp; Masukkan ke Penerimaan</span>
                            </button>
                        </div>
                    </div>
                `;
            }
        } catch(e) {
            container.innerHTML = `<div class="text-center py-8 text-rose-600 text-xs font-bold">Gagal memuat API: ${e.message}</div>`;
        }
    };

    if (immediate) {
        runSearch();
    } else {
        bpomSearchTimer = setTimeout(runSearch, 300);
    }
}

// Menangani pemilihan obat dari modal BPOM: jika sudah ada langsung pilih, jika belum ada otomatis daftarkan ke master produk apotek
async function handleSelectBpomItem(itemIdx) {
    if (!currentBpomResults || !currentBpomResults[itemIdx]) return;
    const item = currentBpomResults[itemIdx];
    const btn = document.getElementById('btn_sel_bpom_' + itemIdx);

    // 1. Jika produk internal sudah ada di apotek
    if (item.internal_product && item.internal_product.id_variasi) {
        selectBpomMedicineDirect(item.internal_product.id_variasi, item.internal_product.nama_lengkap);
        return;
    }

    // 2. Jika belum ada di master produk apotek, daftarkan otomatis ke Master Produk via API
    let originalBtnHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg> <span>Mendaftarkan...</span>`;
    }

    try {
        const payload = {
            product_name: item.product_name,
            barcode: item.barcode || '',
            dosage_form: item.dosage_form || '',
            category: item.category || 'Obat',
            company: item.company || ''
        };

        const res = await fetch('../api/register_bpom_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success && data.id_variasi) {
            // Tambahkan produk baru ini ke rawProdukList apotek secara dinamis
            const newProd = {
                id: data.id_variasi,
                id_produk_induk: data.id_induk,
                label: data.label,
                nama_produk: data.nama_produk,
                nama_variasi: data.nama_variasi,
                sku_variasi: data.sku_variasi,
                sku_induk: '',
                kategori: data.kategori || 'Obat',
                satuan_besar: data.satuan_besar || 'Box',
                satuan_kecil: data.satuan_kecil || 'Pcs',
                rasio_konversi: data.rasio_konversi || 1,
                harga_jual_besar: data.harga_jual_besar || 0
            };
            rawProdukList.push(newProd);

            // Perbarui option di semua select yang ada di form
            const newOpt = new Option(newProd.label + ' (' + newProd.satuan_besar + ')', newProd.id, false, false);
            newOpt.setAttribute('data-kat', newProd.kategori);
            newOpt.setAttribute('data-satbesar', newProd.satuan_besar);
            newOpt.setAttribute('data-satkecil', newProd.satuan_kecil);
            newOpt.setAttribute('data-rasio', newProd.rasio_konversi);
            newOpt.setAttribute('data-hargajual', newProd.harga_jual_besar);
            newOpt.setAttribute('data-sku', newProd.sku_variasi);
            newOpt.setAttribute('data-skuinduk', '');
            newOpt.setAttribute('data-namaproduk', newProd.nama_produk);

            document.querySelectorAll('select.select2-produk').forEach(sel => {
                sel.appendChild(newOpt.cloneNode(true));
            });

            // Masukkan langsung ke baris penerimaan barang
            selectBpomMedicineDirect(data.id_variasi, data.label);
        } else {
            alert('Gagal mendaftarkan obat ke database: ' + (data.message || 'Terjadi kesalahan'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            }
        }
    } catch(err) {
        alert('Terjadi kendala jaringan: ' + err.message);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        }
    }
}

async function autoRegisterCustomProduct(query) {
    if (!query || !query.trim()) return;
    const btn = document.getElementById('btn_autoreg_custom');
    let orig = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-4 h-4 animate-spin inline mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg> Mendaftarkan...`;
    }

    try {
        const cleanDigits = query.replace(/[^0-9]/g, '');
        const isBarcodeOnly = cleanDigits.length >= 8 && query.trim() === cleanDigits;
        const prodName = isBarcodeOnly ? ('Produk Baru ' + cleanDigits) : query.trim();
        const barcodeVal = isBarcodeOnly ? cleanDigits : '';

        const res = await fetch('../api/register_bpom_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                product_name: prodName,
                barcode: barcodeVal,
                dosage_form: 'Box / Pcs',
                category: 'Obat',
                company: 'PBF Mitra'
            })
        });
        const data = await res.json();
        if (data.success && data.id_variasi) {
            const newProd = {
                id: data.id_variasi,
                id_produk_induk: data.id_induk,
                label: data.label,
                nama_produk: data.nama_produk,
                nama_variasi: data.nama_variasi,
                sku_variasi: data.sku_variasi,
                sku_induk: '',
                kategori: data.kategori || 'Obat',
                satuan_besar: data.satuan_besar || 'Box',
                satuan_kecil: data.satuan_kecil || 'Pcs',
                rasio_konversi: data.rasio_konversi || 1,
                harga_jual_besar: data.harga_jual_besar || 0
            };
            rawProdukList.push(newProd);

            const newOpt = new Option(newProd.label + ' (' + newProd.satuan_besar + ')', newProd.id, false, false);
            newOpt.setAttribute('data-kat', newProd.kategori);
            newOpt.setAttribute('data-satbesar', newProd.satuan_besar);
            newOpt.setAttribute('data-satkecil', newProd.satuan_kecil);
            newOpt.setAttribute('data-rasio', newProd.rasio_konversi);
            newOpt.setAttribute('data-hargajual', newProd.harga_jual_besar);
            newOpt.setAttribute('data-sku', newProd.sku_variasi);
            newOpt.setAttribute('data-skuinduk', '');
            newOpt.setAttribute('data-namaproduk', newProd.nama_produk);

            document.querySelectorAll('select.select2-produk').forEach(sel => {
                sel.appendChild(newOpt.cloneNode(true));
            });

            selectBpomMedicineDirect(data.id_variasi, data.label);
        } else {
            alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
            if (btn) { btn.disabled = false; btn.innerHTML = orig; }
        }
    } catch(e) {
        alert('Gagal menghubungi server: ' + e.message);
        if (btn) { btn.disabled = false; btn.innerHTML = orig; }
    }
}

// Langsung terapkan produk ke baris tabel tanpa konfirmasi popup & tanpa badge berlebih
function selectBpomMedicineDirect(idVariasi, productName) {
    closeBpomSearchModal();

    let targetIdx = 0;
    const firstSel = document.getElementById('sel-0');
    if (firstSel && (!firstSel.value || firstSel.value === '')) {
        targetIdx = 0;
    } else {
        addRow();
        targetIdx = rC - 1;
    }

    if (idVariasi) {
        const sel = $('#sel-' + targetIdx);
        if (sel.length) {
            sel.val(idVariasi).trigger('change');
        }
        setTimeout(() => {
            const qtyInput = document.getElementById('qty_pesan-' + targetIdx);
            if (qtyInput) qtyInput.focus();
        }, 150);
    }
}

// --- BUKTI PENERIMAAN BARANG & CATATAN RETUR ---
function openNotaReturModal(doc) {
    if (!doc) return;
    const modal = document.getElementById('modal_nota_retur');
    if (!modal) return;

    if (document.getElementById('nota_ref_no')) document.getElementById('nota_ref_no').innerText = doc.no_referensi || '-';
    if (document.getElementById('nota_ref_val')) document.getElementById('nota_ref_val').innerText = doc.no_referensi || '-';
    if (document.getElementById('nota_tgl_kop')) document.getElementById('nota_tgl_kop').innerText = 'Tgl: ' + (doc.tanggal_terima || '-');
    if (document.getElementById('nota_tgl')) document.getElementById('nota_tgl').innerText = doc.tanggal_terima || '-';
    if (document.getElementById('nota_sumber')) document.getElementById('nota_sumber').innerText = doc.sumber || 'PO';
    if (document.getElementById('nota_supplier')) document.getElementById('nota_supplier').innerText = doc.supplier || 'Umum / PBF Mitra';
    if (document.getElementById('nota_petugas')) document.getElementById('nota_petugas').innerText = doc.pembuat || 'Petugas Gudang';
    if (document.getElementById('nota_catatan')) document.getElementById('nota_catatan').innerText = doc.catatan || 'Barang telah diperiksa fisik dan diterima sesuai surat jalan.';

    const btnFull = document.getElementById('btn_open_full_nota');
    if (btnFull && doc.id) {
        btnFull.href = 'cetak_nota_penerimaan.php?id=' + doc.id;
    }
    const btnFullFoot = document.getElementById('btn_modal_full_nota_foot');
    if (btnFullFoot && doc.id) {
        btnFullFoot.href = 'cetak_nota_penerimaan.php?id=' + doc.id;
    }

    const tbody = document.getElementById('nota_items_body');
    const tfoot = document.getElementById('nota_items_foot');
    let html = '';
    let totalPesanAll = 0;
    let totalTerimaAwalAll = 0;
    let totalRusakAll = 0;
    let totalPenggantiAll = 0;

    let totalNilaiFakturAll = 0;
    let totalNilaiTerimaAwalAll = 0;
    let totalNilaiRusakAll = 0;
    let totalNilaiPenggantiAll = 0;
    let totalNilaiPotongFakturAll = 0;

    let hasPendingRetur = false;
    let hasResolvedTukar = false;
    let hasResolvedPotong = false;
    let returTextLines = [];

    (doc.items || []).forEach((item, idx) => {
        const qP = parseInt(item.qty_pesan || item.qty_terima || 0);
        const qT = parseInt(item.qty_terima || 0);
        const qR = parseInt(item.qty_rusak || 0);
        const qGanti = parseInt(item.qty_pengganti_diterima || 0);
        const hBeli = parseFloat(item.harga_beli || 0);
        const stRet = item.status_retur || 'tidak_ada';

        totalPesanAll += qP;
        totalTerimaAwalAll += qT;
        totalRusakAll += qR;

        const subFaktur = qP * hBeli;
        const subTerimaAwal = qT * hBeli;
        const subRusak = qR * hBeli;

        totalNilaiFakturAll += subFaktur;
        totalNilaiTerimaAwalAll += subTerimaAwal;
        totalNilaiRusakAll += subRusak;

        let subBaris = subTerimaAwal;
        let cellDiterima = `${qT} <span class="text-[10px] text-slate-500 font-normal">${escapeHtml(item.satuan)}</span>`;
        let cellRusak = `<span class="text-slate-400">0</span>`;

        if (qR > 0) {
            if (stRet === 'selesai_tukar_barang') {
                hasResolvedTukar = true;
                totalPenggantiAll += qGanti;
                totalNilaiPenggantiAll += (qGanti * hBeli);
                subBaris = (qT + qGanti) * hBeli;
                cellDiterima = `<div>${qT + qGanti} <span class="text-[10px] text-slate-500 font-normal">${escapeHtml(item.satuan)}</span></div><div class="text-[9px] text-[#0f2d5a] font-bold">(${qT} awal + ${qGanti} ganti)</div>`;
                cellRusak = `<div>${qR} <span class="text-[10px] text-slate-500 font-normal">${escapeHtml(item.satuan)}</span></div><div class="text-[9px] text-emerald-700 font-bold">✓ Diganti</div>`;
                returTextLines.push(`• ${escapeHtml(item.nama)}: ${qR} ${escapeHtml(item.satuan)} rusak telah diganti ${qGanti} ${escapeHtml(item.satuan)} baru${item.catatan_retur ? ' (' + escapeHtml(item.catatan_retur) + ')' : ''}.`);
            } else if (stRet === 'selesai_potong_faktur') {
                hasResolvedPotong = true;
                totalNilaiPotongFakturAll += subRusak;
                cellRusak = `<div>${qR} <span class="text-[10px] text-slate-500 font-normal">${escapeHtml(item.satuan)}</span></div><div class="text-[9px] text-blue-700 font-bold">✓ Potong Faktur</div>`;
                returTextLines.push(`• ${escapeHtml(item.nama)}: ${qR} ${escapeHtml(item.satuan)} diselesaikan via potong faktur.`);
            } else {
                hasPendingRetur = true;
                cellRusak = `<div>${qR} <span class="text-[10px] text-slate-500 font-normal">${escapeHtml(item.satuan)}</span></div><div class="text-[9px] text-amber-700 font-bold">Menunggu Ganti</div>`;
                returTextLines.push(`• ${escapeHtml(item.nama)}: ${qR} ${escapeHtml(item.satuan)} menunggu unit pengganti supplier.`);
            }
        }

        let identitasItem = '';
        if (item.kategori === 'Obat') {
            const b = item.no_batch ? `B: ${escapeHtml(item.no_batch)}` : '';
            const exp = item.tgl_exp ? `Exp: ${escapeHtml(item.tgl_exp)}` : '';
            identitasItem = [b, exp].filter(Boolean).join('<br>');
            if (stRet === 'selesai_tukar_barang' && item.no_batch_pengganti) {
                identitasItem += `<br><span class="text-[9px] text-[#0f2d5a] font-bold">Ganti B: ${escapeHtml(item.no_batch_pengganti)}</span>`;
            }
        } else {
            identitasItem = `<span class="text-slate-600 font-semibold">Alkes (SN)</span>`;
            if (stRet === 'selesai_tukar_barang' && item.catatan_retur) {
                identitasItem += `<br><span class="text-[9px] text-[#0f2d5a] font-semibold">${escapeHtml(item.catatan_retur)}</span>`;
            }
        }

        html += `
            <tr class="hover:bg-slate-50/50">
                <td class="px-2 py-2 text-center text-slate-500 font-mono">${idx + 1}</td>
                <td class="px-3 py-2">
                    <strong class="text-slate-800">${escapeHtml(item.nama)}</strong>
                    <div class="text-[10px] text-slate-500">${escapeHtml(item.nama_variasi || '')}</div>
                </td>
                <td class="px-2 py-2 text-center text-[11px] leading-tight text-slate-600">${identitasItem}</td>
                <td class="px-2 py-2 text-center">${qP} <span class="text-[10px] text-slate-500">${escapeHtml(item.satuan)}</span></td>
                <td class="px-2 py-2 text-center font-bold text-slate-800">${cellDiterima}</td>
                <td class="px-2 py-2 text-center font-semibold text-slate-700">${cellRusak}</td>
                <td class="px-2 py-2 text-right font-mono text-slate-700">Rp ${hBeli.toLocaleString('id-ID')}</td>
                <td class="px-2 py-2 text-right font-mono font-bold text-slate-900">Rp ${subBaris.toLocaleString('id-ID')}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    const totalFisikAkhirNilai = totalNilaiTerimaAwalAll + totalNilaiPenggantiAll;
    const totalFisikAkhirQty = totalTerimaAwalAll + totalPenggantiAll;

    if (tfoot) {
        tfoot.innerHTML = `
            <tr>
                <td colspan="3" class="px-3 py-2 text-right font-bold text-slate-700">Total Kuantiti:</td>
                <td class="px-2 py-2 text-center font-bold text-slate-800">${totalPesanAll}</td>
                <td class="px-2 py-2 text-center font-bold text-slate-800">${totalFisikAkhirQty}${totalPenggantiAll > 0 ? '<span class="text-[9px] block text-[#0f2d5a] font-normal">(Lengkap)</span>' : ''}</td>
                <td class="px-2 py-2 text-center font-bold ${totalRusakAll > 0 ? 'text-slate-700' : 'text-slate-400'}">${totalRusakAll}</td>
                <td class="px-2 py-2 text-right font-bold text-slate-700">Total Fisik:</td>
                <td class="px-2 py-2 text-right font-mono font-extrabold text-slate-900 text-xs">Rp ${totalFisikAkhirNilai.toLocaleString('id-ID')}</td>
            </tr>
        `;
    }

    // Set nilai ringkasan finansial
    document.getElementById('nota_sum_faktur').innerText = 'Rp ' + totalNilaiFakturAll.toLocaleString('id-ID');

    const rowRetur = document.getElementById('nota_row_retur');
    const elSumRetur = document.getElementById('nota_sum_retur');
    const labelRetur = document.getElementById('nota_label_retur');
    
    const rowPengganti = document.getElementById('nota_row_pengganti');
    const elSumPengganti = document.getElementById('nota_sum_pengganti');
    const labelTotalAkhir = document.getElementById('nota_label_total_akhir');

    if (totalRusakAll > 0) {
        if (rowRetur) rowRetur.classList.remove('hidden');
        if (hasPendingRetur) {
            if (labelRetur) labelRetur.innerText = `Barang Retur Rusak (${totalRusakAll} unit):`;
            if (elSumRetur) elSumRetur.innerText = '- Rp ' + totalNilaiRusakAll.toLocaleString('id-ID');
            if (rowPengganti) rowPengganti.classList.add('hidden');
            if (labelTotalAkhir) labelTotalAkhir.innerText = 'Total Diterima Fisik (Saat Ini):';
        } else if (hasResolvedTukar) {
            if (labelRetur) labelRetur.innerText = `Barang Retur Awal (${totalRusakAll} unit):`;
            if (elSumRetur) elSumRetur.innerText = '- Rp ' + totalNilaiRusakAll.toLocaleString('id-ID');
            if (rowPengganti) {
                rowPengganti.classList.remove('hidden');
                if (elSumPengganti) elSumPengganti.innerText = '+ Rp ' + totalNilaiPenggantiAll.toLocaleString('id-ID');
            }
            if (labelTotalAkhir) labelTotalAkhir.innerText = 'Total Diterima Fisik (Lengkap):';
        } else if (hasResolvedPotong) {
            if (labelRetur) labelRetur.innerText = `Potong Faktur Retur (${totalRusakAll} unit):`;
            if (elSumRetur) elSumRetur.innerText = '- Rp ' + totalNilaiPotongFakturAll.toLocaleString('id-ID');
            if (rowPengganti) rowPengganti.classList.add('hidden');
            if (labelTotalAkhir) labelTotalAkhir.innerText = 'Total Tagihan Akhir Faktur:';
        }
    } else {
        if (rowRetur) rowRetur.classList.add('hidden');
        if (rowPengganti) rowPengganti.classList.add('hidden');
        if (labelTotalAkhir) labelTotalAkhir.innerText = 'Total Diterima Fisik:';
    }

    document.getElementById('nota_sum_terima').innerText = 'Rp ' + totalFisikAkhirNilai.toLocaleString('id-ID');

    const statusRingkas = document.getElementById('nota_status_ringkas');
    if (statusRingkas) {
        if (totalRusakAll === 0) {
            statusRingkas.innerHTML = '<span class="text-emerald-700 font-semibold">Lengkap Sempurna</span>';
        } else if (hasResolvedTukar && !hasPendingRetur) {
            statusRingkas.innerHTML = '<span class="text-emerald-700 font-semibold">Lengkap (Retur Selesai Ditukar)</span>';
        } else if (hasResolvedPotong && !hasPendingRetur) {
            statusRingkas.innerHTML = '<span class="text-blue-700 font-semibold">Selesai (Potong Faktur)</span>';
        } else {
            statusRingkas.innerHTML = `<span class="text-amber-700 font-semibold">${totalRusakAll} unit menunggu pengganti</span>`;
        }
    }

    const klausulBox = document.getElementById('nota_retur_klausul_box');
    const explicitText = document.getElementById('nota_retur_explicit_text');
    if (klausulBox && explicitText) {
        if (totalRusakAll > 0) {
            klausulBox.classList.remove('hidden');
            if (hasResolvedTukar && !hasPendingRetur) {
                explicitText.innerHTML = `Terdapat ${totalRusakAll} unit barang rusak yang dikembalikan ke supplier.<br><span class="font-bold text-[#0f2d5a]">Selesai Ditukar:</span> ${totalPenggantiAll} unit barang pengganti telah diterima fisik dan masuk stok toko. Total persediaan fisik diterima telah lengkap 100% (Rp ${totalFisikAkhirNilai.toLocaleString('id-ID')}).<br>` + returTextLines.join('<br>');
            } else if (hasResolvedPotong && !hasPendingRetur) {
                explicitText.innerHTML = `Terdapat ${totalRusakAll} unit barang rusak yang dikembalikan ke supplier.<br><span class="font-bold text-blue-700">Selesai Potong Faktur:</span> Nilai tagihan invoice dikurangi Rp ${totalNilaiPotongFakturAll.toLocaleString('id-ID')} (Credit Note). Total tagihan disesuaikan menjadi Rp ${totalFisikAkhirNilai.toLocaleString('id-ID')}.<br>` + returTextLines.join('<br>');
            } else {
                explicitText.innerHTML = `Terdapat ${totalRusakAll} unit barang rusak yang dikembalikan ke supplier.<br><span class="font-bold text-amber-700">Menunggu Penggantian:</span> Sedang menunggu pengiriman unit pengganti dari supplier (Saldo klaim: Rp ${totalNilaiRusakAll.toLocaleString('id-ID')}).<br>` + returTextLines.join('<br>');
            }
        } else {
            klausulBox.classList.add('hidden');
        }
    }

    modal.classList.remove('hidden');
}

function closeNotaReturModal() {
    const modal = document.getElementById('modal_nota_retur');
    if (modal) modal.classList.add('hidden');
}

function printNotaReturModal() {
    window.print();
}

// Helper Modal Terima Barang Pengganti Retur
function openTerimaPenggantiModal(data) {
    if (!data) return;
    const modal = document.getElementById('modal_terima_pengganti');
    if (!modal) return;

    document.getElementById('ganti_id_detail').value = data.id_detail;
    document.getElementById('ganti_no_ref').innerText = data.no_referensi || '-';
    document.getElementById('ganti_nama_produk').innerText = data.nama_produk || '-';
    document.getElementById('ganti_kategori').innerText = data.kategori || '-';
    document.getElementById('ganti_satuan_label').innerText = data.satuan || 'Dus';
    document.getElementById('ganti_qty_klaim_text').innerText = (data.qty_rusak || 1) + ' ' + (data.satuan || 'Dus');
    
    const qtyInput = document.getElementById('ganti_qty_input');
    qtyInput.value = data.qty_rusak || 1;
    qtyInput.max = data.qty_rusak || 1;

    const obatBox = document.getElementById('ganti_obat_box');
    const batchInput = document.getElementById('ganti_no_batch');
    const expInput = document.getElementById('ganti_tgl_exp');
    const alkesBox = document.getElementById('ganti_alkes_box');
    const hiddenGantiSn = document.getElementById('ganti_sn_hidden');

    if (data.kategori === 'Obat') {
        if (obatBox) obatBox.classList.remove('hidden');
        if (batchInput) batchInput.required = true;
        if (expInput) expInput.required = true;

        if (alkesBox) alkesBox.classList.add('hidden');
        if (hiddenGantiSn) hiddenGantiSn.value = '';
    } else if (data.kategori === 'Alat Kesehatan') {
        if (obatBox) obatBox.classList.add('hidden');
        if (batchInput) { batchInput.required = false; batchInput.value = ''; }
        if (expInput) { expInput.required = false; expInput.value = ''; }

        if (alkesBox) alkesBox.classList.remove('hidden');
        renderGantiSnInputs();
    } else {
        if (obatBox) obatBox.classList.add('hidden');
        if (batchInput) batchInput.required = false;
        if (expInput) expInput.required = false;

        if (alkesBox) alkesBox.classList.add('hidden');
        if (hiddenGantiSn) hiddenGantiSn.value = '';
    }

    document.getElementById('ganti_catatan').value = '';
    modal.classList.remove('hidden');
}

function renderGantiSnInputs() {
    const container = document.getElementById('ganti_sn_list');
    const hidden = document.getElementById('ganti_sn_hidden');
    const qtyInput = document.getElementById('ganti_qty_input');
    const badge = document.getElementById('ganti_sn_count_badge');
    if (!container || !hidden || !qtyInput) return;

    const qty = Math.max(1, parseInt(qtyInput.value, 10) || 1);
    if (badge) badge.innerText = `${qty} Unit Pengganti`;

    const existingInputs = container.querySelectorAll('.ganti-sn-field');
    const existingVals = [];
    existingInputs.forEach(inp => existingVals.push(inp.value));

    let html = '';
    for (let i = 0; i < qty; i++) {
        const val = existingVals[i] || '';
        html += `
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold font-mono text-slate-500 w-14 shrink-0">Unit #${i + 1}:</span>
                <input type="text" class="ganti-sn-field w-full px-2.5 py-1.5 border border-zcBrd rounded-lg text-xs font-mono uppercase focus:outline-none focus:border-teal-600 bg-white" 
                    placeholder="Nomor Seri (SN) Baru Unit #${i + 1}" value="${escapeHtml(val)}" data-idx="${i}"
                    oninput="syncGantiSn()" onkeydown="handleGantiSnKey(event, ${i})">
            </div>
        `;
    }
    container.innerHTML = html;
    syncGantiSn();
}

function syncGantiSn() {
    const container = document.getElementById('ganti_sn_list');
    const hidden = document.getElementById('ganti_sn_hidden');
    if (!container || !hidden) return;

    const inputs = container.querySelectorAll('.ganti-sn-field');
    const vals = [];
    inputs.forEach(inp => {
        const v = inp.value.trim();
        if (v) vals.push(v);
    });
    hidden.value = vals.join(', ');
}

function handleGantiSnKey(e, idx) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const nextInput = document.querySelector(`.ganti-sn-field[data-idx="${idx + 1}"]`);
        if (nextInput) {
            nextInput.focus();
            nextInput.select();
        } else {
            const cat = document.getElementById('ganti_catatan');
            if (cat) cat.focus();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const gantiQtyInp = document.getElementById('ganti_qty_input');
    if (gantiQtyInp) {
        gantiQtyInp.addEventListener('input', renderGantiSnInputs);
    }
});

function closeTerimaPenggantiModal() {
    const modal = document.getElementById('modal_terima_pengganti');
    if (modal) modal.classList.add('hidden');
}

// Helper Modal Selesaikan via Potong Faktur / Credit Note
function openPotongFakturModal(data) {
    if (!data) return;
    const modal = document.getElementById('modal_potong_faktur');
    if (!modal) return;

    document.getElementById('potong_id_detail').value = data.id_detail;
    document.getElementById('potong_no_ref').innerText = data.no_referensi || '-';
    document.getElementById('potong_nama_produk').innerText = data.nama_produk || '-';
    document.getElementById('potong_qty_rusak_text').innerText = (data.qty_rusak || 1) + ' ' + (data.satuan || 'Dus');
    
    const nilai = parseFloat(data.nilai_klaim || 0);
    document.getElementById('potong_nilai_text').innerText = 'Rp ' + nilai.toLocaleString('id-ID');
    document.getElementById('potong_catatan').value = '';

    modal.classList.remove('hidden');
}

function closePotongFakturModal() {
    const modal = document.getElementById('modal_potong_faktur');
    if (modal) modal.classList.add('hidden');
}

function initSelect2(id) {
    if (window.jQuery && $.fn.select2) {
        const $el = $('#' + id);
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.select2({
            width: '100%',
            dropdownAutoWidth: true,
            language: {
                noResults: function() {
                    return "Produk tidak ditemukan";
                }
            }
        }).on('change', function() {
            const idx = this.getAttribute('data-row-idx');
            checkProduk(this, idx);
            calcSubtotal(idx);
        });
    }
}

// Inisialisasi Select2 pada baris pertama saat halaman selesai dimuat
$(document).ready(function() {
    initSelect2('sel-0');
});

function removeRow(n) {
    const row = document.getElementById('row-' + n);
    if (row) {
        const sel = $('#sel-' + n);
        if (sel.length && sel.data('select2')) {
            sel.select2('destroy');
        }
        row.remove();
    }
}

function checkProduk(sel, i) {
    const opt = sel.options[sel.selectedIndex];
    const kat = opt ? opt.getAttribute('data-kat') : '';
    const satBesar = opt ? (opt.getAttribute('data-satbesar') || 'Dus') : 'Dus';
    const b = document.getElementById('b-'+i);
    const e = document.getElementById('e-'+i);
    const badge = document.getElementById('badge-uom-'+i);
    
    if (badge) {
        badge.innerText = satBesar;
    }
    
    // Reset state Obat
    [b,e].forEach(el => { if (el) { el.readOnly = true; el.classList.add('bg-slate-100'); el.required = false; } });
    
    if (kat === 'Obat') {
        if (b) { b.readOnly = false; b.classList.remove('bg-slate-100'); b.required = true; }
        if (e) { e.readOnly = false; e.classList.remove('bg-slate-100'); e.required = true; }
    }
    
    renderSnInputs(i);
}

// Dynamic Serial Number Generator (Tanpa Perlu Tanda Koma)
function renderSnInputs(idx) {
    const sel = document.getElementById('sel-' + idx);
    const placeholder = document.getElementById('sn-placeholder-' + idx);
    const list = document.getElementById('sn-list-' + idx);
    const hiddenInput = document.getElementById('s-' + idx);
    const terimaEl = document.getElementById('qty_terima-' + idx);
    if (!sel || !placeholder || !list || !hiddenInput) return;

    const opt = sel.options[sel.selectedIndex];
    const kat = opt ? opt.getAttribute('data-kat') : '';

    if (kat !== 'Alat Kesehatan') {
        placeholder.innerText = '— Khusus Alkes —';
        placeholder.classList.remove('hidden');
        list.classList.add('hidden');
        list.innerHTML = '';
        hiddenInput.value = '';
        return;
    }

    placeholder.classList.add('hidden');
    list.classList.remove('hidden');

    const qty = Math.max(0, parseInt(terimaEl ? terimaEl.value : 0, 10) || 0);

    if (qty === 0) {
        list.innerHTML = '<div class="text-[10px] text-slate-400 italic py-1 px-1.5 bg-slate-50 rounded border border-slate-200 text-center">Qty terima: 0</div>';
        hiddenInput.value = '';
        return;
    }

    const existingInputs = list.querySelectorAll('.sn-single-field');
    const existingVals = [];
    existingInputs.forEach(inp => existingVals.push(inp.value));

    let html = `
        <div class="flex items-center justify-between text-[9px] font-bold text-slate-600 mb-1 px-0.5">
            <span>Scan/SN (${qty} unit):</span>
            <span class="text-teal-700 bg-teal-50 px-1 py-0.5 rounded text-[9px] border border-teal-200">Mandiri / Unit</span>
        </div>
    `;

    const isScroll = qty > 4;
    html += `<div class="space-y-1 ${isScroll ? 'max-h-36 overflow-y-auto pr-0.5' : ''}">`;

    for (let i = 0; i < qty; i++) {
        const val = existingVals[i] || '';
        html += `
            <div class="flex items-center gap-1">
                <span class="text-[9px] font-bold font-mono text-slate-400 w-5 shrink-0 text-right">#${i + 1}</span>
                <input type="text" class="sn-single-field sn-field-row-${idx} w-full border border-zcBrd rounded px-1.5 py-1 text-xs font-mono uppercase bg-white focus:outline-none focus:border-teal-600" 
                    placeholder="SN #${i + 1}" value="${escapeHtml(val)}" data-row="${idx}" data-idx="${i}" 
                    oninput="syncRowSn(${idx})" onkeydown="handleSnKey(event, ${idx}, ${i})">
            </div>
        `;
    }
    html += `</div>`;

    list.innerHTML = html;
    syncRowSn(idx);
}

function syncRowSn(idx) {
    const list = document.getElementById('sn-list-' + idx);
    const hidden = document.getElementById('s-' + idx);
    if (!list || !hidden) return;

    const inputs = list.querySelectorAll('.sn-single-field');
    const vals = [];
    inputs.forEach(inp => {
        const v = inp.value.trim();
        if (v) vals.push(v);
    });
    hidden.value = vals.join(', ');
}

function handleSnKey(e, rowIdx, snIdx) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const nextInput = document.querySelector(`.sn-field-row-${rowIdx}[data-idx="${snIdx + 1}"]`);
        if (nextInput) {
            nextInput.focus();
            nextInput.select();
        } else {
            const nextRowSel = document.getElementById('sel-' + (rowIdx + 1));
            if (nextRowSel && window.jQuery && $(nextRowSel).data('select2')) {
                $(nextRowSel).select2('open');
            }
        }
    }
}

// =========================================================================
// PAGINASI CLIENT-SIDE HISTORI PENERIMAAN (FORWARD / BACKWARD < & >)
// =========================================================================
let currentHistPage = 1;
const itemsPerPage = 3;
const allCards = document.querySelectorAll('.history-card');
const totalCards = allCards.length;
const totalPages = Math.ceil(totalCards / itemsPerPage) || 1;

function updateHistoryPagination() {
    allCards.forEach((card, index) => {
        const start = (currentHistPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        if (index >= start && index < end) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    const pageInfo = document.getElementById('page_info');
    if (pageInfo) {
        pageInfo.innerText = `Halaman ${currentHistPage} dari ${totalPages} (Total ${totalCards} Dokumen)`;
    }

    const btnPrev = document.getElementById('btn_prev');
    const btnNext = document.getElementById('btn_next');
    if (btnPrev) btnPrev.disabled = (currentHistPage <= 1);
    if (btnNext) btnNext.disabled = (currentHistPage >= totalPages);
}

function prevHistoryPage() {
    if (currentHistPage > 1) {
        currentHistPage--;
        updateHistoryPagination();
    }
}

function nextHistoryPage() {
    if (currentHistPage < totalPages) {
        currentHistPage++;
        updateHistoryPagination();
    }
}

if (totalCards > 0) {
    updateHistoryPagination();
}
</script>

<?php layoutFooter(); ?>
