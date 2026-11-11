import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Role check at top
role_old = """requireRole(['superadmin', 'admin']);

$role = $_SESSION['role'] ?? '';
$isSuperadmin = ($role === 'superadmin');
$userId = $_SESSION['user_id'] ?? 1;
$activeCabang = 1;"""

role_new = """requireRole(['superadmin', 'admin', 'kasir']);

$role = $_SESSION['role'] ?? '';
$isSuperadmin = ($role === 'superadmin');
$isAdmin = ($role === 'admin');
$isKasir = ($role === 'kasir');
$canVerify = ($isSuperadmin || $isAdmin); // Admin & Superadmin berwenang verifikasi & eksekusi mutasi
$userId = $_SESSION['user_id'] ?? 1;
$activeCabang = 1;"""

assert role_old in content, "role_old not found!"
content = content.replace(role_old, role_new, 1)

# 2. Update isi_cepat_demo guard
isi_cepat_old = """    // --- 2B. ISI CEPAT HASIL FISIK LAPANGAN (Khusus Superadmin, Pintasan Simulasi Demo) ---
    elseif ($aksi === 'isi_cepat_demo') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Fitur pintasan demo hanya untuk Superadmin.";"""

isi_cepat_new = """    // --- 2B. ISI CEPAT HASIL FISIK LAPANGAN (Khusus Superadmin & Admin, Pintasan Simulasi Demo) ---
    elseif ($aksi === 'isi_cepat_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Fitur pintasan simulasi verifikasi hanya untuk Admin atau Superadmin.";"""

assert isi_cepat_old in content, "isi_cepat_old not found!"
content = content.replace(isi_cepat_old, isi_cepat_new, 1)

# 3. Update rekonsiliasi_masal guard
rekon_old = """    // --- 2C. REKONSILIASI SEMUA SELISIH SEKALIGUS (Mass Reconciliation) ---
    elseif ($aksi === 'rekonsiliasi_masal') {
        if (!$isSuperadmin) {
            $msg = "Akses ditolak! Fitur rekonsiliasi masal hanya untuk Superadmin.";"""

rekon_new = """    // --- 2C. REKONSILIASI SEMUA SELISIH SEKALIGUS (Mass Reconciliation) ---
    elseif ($aksi === 'rekonsiliasi_masal') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin dan Superadmin yang berwenang melakukan rekonsiliasi mutasi.";"""

assert rekon_old in content, "rekon_old not found!"
content = content.replace(rekon_old, rekon_new, 1)

# 4. Update eksekusi_mutasi_baris guard
mutasi_old = """    // --- 3. EKSEKUSI MUTASI SELISIH BARIS (Admin & Superadmin) ---
    elseif ($aksi === 'eksekusi_mutasi_baris') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $keterangan = trim($_POST['keterangan_mutasi'] ?? '');

        if (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif (!$idDetail) {"""

mutasi_new = """    // --- 3. EKSEKUSI MUTASI SELISIH BARIS (Admin & Superadmin) ---
    elseif ($aksi === 'eksekusi_mutasi_baris') {
        $idDetail = intval($_POST['id_detail'] ?? 0);
        $keterangan = trim($_POST['keterangan_mutasi'] ?? '');

        if (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } elseif (!$canVerify) {
            $msg = "Akses ditolak! Kasir / Petugas Pencacah Lapangan hanya bertugas menghitung fisik (Blind Count). Mutasi selisih mutlak wewenang Admin atau Superadmin.";
            $msgType = 'error';
        } elseif (!$idDetail) {"""

assert mutasi_old in content, "mutasi_old not found!"
content = content.replace(mutasi_old, mutasi_new, 1)

# 5. Update data preparation & filter logic
prep_old = """$searchQ   = trim($_GET['q'] ?? '');
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
    }"""

prep_new = """$searchQ   = trim($_GET['q'] ?? '');
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

    // ── LOGIKA FILTER DEFAULT (REVISI BIMBINGAN DOSEN PEMBIMBING) ─────────────
    // Untuk Admin / Superadmin dalam tahap verifikasi: secara default HANYA tampilkan barang berselisih ('berselisih')!
    // Barang yang jumlah fisik dan sistemnya cocok (0 selisih) disembunyikan secara otomatis agar langsung fokus pada anomali.
    // Untuk Kasir (pencacah): default adalah belum dihitung ('belum_hitung') atau semua data.
    if ($filterSts === '') {
        if ($isKasir) {
            $filterSts = ($stats['belum_hitung'] > 0) ? 'belum_hitung' : 'semua';
        } else {
            if ($stats['sudah_hitung'] > 0 && $stats['berselisih'] > 0) {
                $filterSts = 'berselisih';
            } elseif ($stats['belum_hitung'] > 0) {
                $filterSts = 'semua';
            } else {
                $filterSts = 'semua';
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
    if ($filterSts === 'berselisih') {
        // HANYA barang yang berselisih (kurang atau lebih), menyembunyikan yang cocok (selisih = 0)
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih != 0";
    } elseif ($filterSts === 'belum_hitung') {
        $where[] = "dob.hitung_fisik IS NULL";
    } elseif ($filterSts === 'sesuai') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih = 0";
    } elseif ($filterSts === 'berselisih_belum_mutasi') {
        $where[] = "dob.hitung_fisik IS NOT NULL AND dob.selisih != 0 AND dob.status_mutasi = 'Belum Dimutasi'";
    } elseif ($filterSts === 'sudah_dimutasi') {
        $where[] = "dob.status_mutasi = 'Sudah Dimutasi'";
    }"""

assert prep_old in content, "prep_old not found!"
content = content.replace(prep_old, prep_new, 1)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)
print("Part 1 updated successfully.")
