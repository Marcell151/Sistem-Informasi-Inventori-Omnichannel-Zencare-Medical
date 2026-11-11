# patch_opname_backend.py
import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace simpan_hitung_fisik through isi_cepat_demo
old_start = "    // --- 2. SIMPAN HITUNG FISIK (Hitung Buta: Ungkap Evaluasi & Selisih) ---"
old_end = "    // --- 2C. REKONSILIASI SEMUA SELISIH SEKALIGUS (Mass Reconciliation) ---"

pos1 = content.find(old_start)
pos2 = content.find(old_end)

if pos1 == -1 or pos2 == -1:
    print(f"ERROR: Markers not found! pos1={pos1}, pos2={pos2}")
    exit(1)

new_handlers = '''    // --- 2. SIMPAN HITUNG FISIK BERKONDISI (Hitung Buta: Baik, Rusak, Expired) ---
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

                // Catat audit trail jika ini adalah koreksi/hitung ulang
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
            $bulkBaik = $_POST['bulk_fisik_baik'] ?? [];
            $bulkRusak = $_POST['bulk_fisik_rusak'] ?? [];
            $bulkExpired = $_POST['bulk_fisik_expired'] ?? [];

            if (!is_array($bulkBaik) || empty($bulkBaik)) {
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

                    foreach ($bulkBaik as $idDet => $valBaik) {
                        if ($valBaik === '' || $valBaik === null) continue;
                        $idDet = intval($idDet);
                        $fBaik = max(0, intval($valBaik));
                        $fRusak = max(0, intval($bulkRusak[$idDet] ?? 0));
                        $fExp   = max(0, intval($bulkExpired[$idDet] ?? 0));

                        $stmtCheck->execute([$idDet, $idSesi]);
                        $rowItem = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                        if ($rowItem) {
                            // Aturan Expired
                            if ($rowItem['jenis_komoditas'] === 'Obat' && $rowItem['tgl_exp'] && strtotime($rowItem['tgl_exp']) < strtotime(date('Y-m-d'))) {
                                $fExp += $fBaik;
                                $fBaik = 0;
                            }
                            if ($rowItem['jenis_komoditas'] === 'Alkes') {
                                $fExp = 0;
                            }

                            $stokSistem = intval($rowItem['stok_sistem_fisik']);
                            $selisih = $fBaik - $stokSistem;
                            $statusMut = ($selisih === 0 && $fRusak === 0 && $fExp === 0) ? 'Sesuai' : 'Belum Dimutasi';

                            $stmtUpdate->execute([$fBaik, $fRusak, $fExp, $fBaik, $selisih, $statusMut, $userId, $idDet]);
                            $countSaved++;
                        }
                    }

                    $pdo->commit();
                    if ($isKasir) {
                        $msg = "Data hitung fisik untuk $countSaved item berhasil disimpan ke server. Tetap berada di halaman ini sampai sesi opname ditutup oleh Superadmin.";
                        $msgType = 'success';
                    } else {
                        $msg = "Berhasil menyimpan hasil hitung fisik untuk $countSaved item! Data siap ditutup untuk verifikasi.";
                        $msgType = 'success';
                    }
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    $msg = "Gagal menyimpan hitungan fisik masal: " . $e->getMessage();
                    $msgType = 'error';
                }
            }
        }
    }

    // --- 2A3. TUTUP PENGHITUNGAN & BUKA VERIFIKASI (Admin & Superadmin) ---
    elseif ($aksi === 'tutup_penghitungan') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang menutup tahap penghitungan.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $idSesi = $activeSession['id'];
                $pdo->prepare("
                    UPDATE sesi_opname
                    SET tahap = 'Verifikasi',
                        ditutup_hitung_oleh = ?,
                        ditutup_hitung_pada = NOW()
                    WHERE id = ?
                ")->execute([$userId, $idSesi]);

                header("Location: stok_opname.php?status_eval=berselisih&msg=" . urlencode("Tahap penghitungan fisik berhasil DITUTUP! Formulir pencacahan terkunci dan antarmuka verifikasi selisih dibuka. Barang yang sesuai otomatis disembunyikan.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal menutup penghitungan: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- 2B. AUTO-FILL SKENARIO DEMO UJI COBA (Khusus Superadmin, Pintasan Demo Bab 4) ---
    elseif ($aksi === 'isi_cepat_demo') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Fitur auto-generate skenario uji coba hanya diperuntukkan bagi Superadmin.";
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
                $doneBeta = false;

                foreach ($allRows as $uRow) {
                    $sistem = intval($uRow['stok_sistem_fisik']);
                    $fBaik = $sistem; // Default: Cocok (selisih 0)
                    $fRusak = 0;
                    $fExp = 0;

                    // 1. CONTOH DOSEN (U4): Cefadroxil 500mg Kapsul (Dus/Box) -> Sistem 10: Baik 5, Rusak 3, Expired 2
                    if (!$doneCef && stripos($uRow['nama_produk'], 'Cefadroxil') !== false && $uRow['satuan_tipe'] === 'besar') {
                        $fBaik = 5;
                        $fRusak = 3;
                        $fExp = 2;
                        $doneCef = true;
                    }
                    // 2. SKENARIO BATCH KEDALUWARSA (U6): Sanmol Sirup 60ml -> Sistem 15: Baik 0 (locked), Rusak 0, Expired 12 (Hilang 3)
                    elseif (!$doneSanmol && stripos($uRow['nama_produk'], 'Sanmol') !== false) {
                        $fBaik = 0;
                        $fRusak = 0;
                        $fExp = 12;
                        $doneSanmol = true;
                    }
                    // 3. SKENARIO BARANG HILANG (U2): Amoxicillin 500mg -> Sistem 25: Baik 23, Rusak 0, Expired 0 (Hilang 2)
                    elseif (!$doneAmox && stripos($uRow['nama_produk'], 'Amoxicillin') !== false && $uRow['satuan_tipe'] === 'kecil') {
                        $fBaik = max(0, $sistem - 2);
                        $doneAmox = true;
                    }
                    // 4. SKENARIO ALKES KURANG SN HILANG (U10): Tensimeter Omron -> Sistem 1: Baik 0 (Hilang 1 SN OMR-7120-109)
                    elseif (!$doneOmron && stripos($uRow['nama_produk'], 'Omron') !== false) {
                        $fBaik = 0;
                        $doneOmron = true;
                    }
                    // 5. SKENARIO KOREKSI SURPLUS (U3): Betadine Solution 60ml -> Sistem 18: Baik 20 (Surplus +2)
                    elseif (!$doneBeta && stripos($uRow['nama_produk'], 'Betadine') !== false) {
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

                header("Location: stok_opname.php?status_eval=berselisih&msg=" . urlencode("Skenario Uji Coba Berhasil Di-Generate! Sebanyak $updatedCount entitas fisik terisi ($diffCount anomali selisih: Cefadroxil 5/3/2, Sanmol Expired, Amoxicillin Hilang, Omron Hilang, Betadine Surplus). Sistem otomatis beralih ke tahap Verifikasi dan menyembunyikan 116 barang cocok.") . "&type=success");
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
'''

new_content = content[:pos1] + new_handlers + '\n' + content[pos2:]

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("✓ Successfully patched stok_opname.php backend handlers!")

