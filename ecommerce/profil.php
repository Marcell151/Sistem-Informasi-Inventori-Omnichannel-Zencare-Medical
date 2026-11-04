<?php
// File: ecommerce/profil.php
// Customer Profile Management & Order History
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = "Profil Akun & Riwayat Pesanan";
require_once __DIR__ . '/header.php';

// If user is not logged in, show guest gateway
if (!$isLoggedIn) {
    echo "<section class='pharmify-banner py-11 lg:py-14 text-center text-white'>
            <div class='max-w-7xl mx-auto px-4 lg:px-8 relative z-10'>
                <h1 class='text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5'>Akun Pelanggan</h1>
                <nav class='flex items-center justify-center gap-2 text-xs font-medium text-sky-200/90'>
                    <a href='index.php' class='hover:text-white transition'>Beranda</a>
                    <span>&rsaquo;</span>
                    <span class='text-white font-semibold'>Akun</span>
                </nav>
            </div>
          </section>
          <main class='max-w-lg mx-auto py-16 px-4 flex-1 w-full'>
            <div class='bg-[#eef6fb] p-8 sm:p-10 text-center'>
                <h2 class='text-xl font-extrabold text-[#0f2d5a] mb-2'>Masuk ke Akun Pelanggan</h2>
                <p class='text-xs text-slate-600 mb-6 leading-relaxed'>
                    Masuk ke akun pelanggan ZenCare Medical untuk mengelola alamat pengiriman, kontak, dan melacak riwayat pesanan online Anda.
                </p>
                <div class='flex flex-col gap-3'>
                    <a href='../login_customer.php?redirect=ecommerce/profil.php' class='w-full py-3 px-6 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold text-xs uppercase tracking-widest transition'>
                        MASUK KE AKUN
                    </a>
                    <a href='../register.php' class='w-full py-3 px-6 bg-white border border-slate-300 hover:border-[#0f2d5a] text-[#0f2d5a] font-bold text-xs uppercase tracking-widest transition'>
                        BUAT AKUN BARU
                    </a>
                </div>
            </div>
          </main>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg   = '';

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profil') {
    $nama    = trim($_POST['nama_lengkap'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if (empty($nama)) {
        $errorMsg = "Nama lengkap tidak boleh kosong.";
    } else {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE users SET nama_lengkap = ?, telepon = ?, email = ?, alamat = ? WHERE id = ?");
            $stmtUpdate->execute([$nama, $telepon, $email, $alamat, $userId]);
            
            $_SESSION['nama_lengkap'] = $nama;
            $userName = $nama;
            $successMsg = "Informasi profil dan alamat pengiriman berhasil diperbarui.";
        } catch (Exception $e) {
            $errorMsg = "Gagal memperbarui profil: " . $e->getMessage();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    $cancelInvoice = $_POST['no_invoice'] ?? '';
    $alasanKategori = trim($_POST['alasan_kategori'] ?? '');
    $alasanDetail   = trim($_POST['alasan_detail'] ?? '');
    
    if ($alasanKategori === 'Lainnya') {
        $finalReason = !empty($alasanDetail) ? 'Lainnya: ' . $alasanDetail : 'Lainnya';
    } elseif (!empty($alasanKategori)) {
        $finalReason = $alasanKategori . (!empty($alasanDetail) ? ' (' . $alasanDetail . ')' : '');
    } else {
        $finalReason = 'Dibatalkan oleh Pelanggan';
    }

    try {
        $pdo->beginTransaction();
        
        $stmtCek = $pdo->prepare("SELECT id, status_pesanan, metode_pengambilan FROM penjualan WHERE no_invoice = ? AND id_user = ? FOR UPDATE");
        $stmtCek->execute([$cancelInvoice, $userId]);
        $orderToCancel = $stmtCek->fetch();

        if (!$orderToCancel) {
            throw new Exception("Pesanan tidak ditemukan.");
        }
        if ($orderToCancel['status_pesanan'] !== 'Menunggu Pembayaran') {
            throw new Exception("Hanya pesanan berstatus Menunggu Pembayaran yang dapat dibatalkan.");
        }

        // Batalkan dan pulihkan seluruh stok toko, stok batch FEFO, dan unit serial beserta pencatatan alasan
        batalkanSatuPesananInternal($pdo, $orderToCancel['id'], $finalReason, 'Pelanggan');

        $pdo->commit();
        $successMsg = "Pesanan $cancelInvoice berhasil dibatalkan. Alasan: \"$finalReason\". Seluruh stok barang telah dikembalikan.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMsg = "Gagal membatalkan pesanan: " . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_cancel') {
    $reqInvoice = $_POST['no_invoice'] ?? '';
    $alasanKategori = trim($_POST['alasan_kategori'] ?? '');
    $alasanDetail   = trim($_POST['alasan_detail'] ?? '');
    
    if ($alasanKategori === 'Lainnya') {
        $finalReason = !empty($alasanDetail) ? 'Lainnya: ' . $alasanDetail : 'Lainnya';
    } elseif (!empty($alasanKategori)) {
        $finalReason = $alasanKategori . (!empty($alasanDetail) ? ' (' . $alasanDetail . ')' : '');
    } else {
        $finalReason = 'Pengajuan pembatalan oleh Pelanggan';
    }

    try {
        $stmtCek = $pdo->prepare("SELECT id, status_pesanan FROM penjualan WHERE no_invoice = ? AND id_user = ?");
        $stmtCek->execute([$reqInvoice, $userId]);
        $ord = $stmtCek->fetch();

        if (!$ord) throw new Exception("Pesanan tidak ditemukan.");
        if ($ord['status_pesanan'] !== 'Diproses') {
            throw new Exception("Pengajuan pembatalan hanya dapat dilakukan untuk pesanan yang sedang diproses.");
        }

        $pdo->prepare("UPDATE penjualan SET request_batal = 1, request_batal_alasan = ?, request_batal_waktu = NOW() WHERE id = ?")
            ->execute([$finalReason, $ord['id']]);

        $successMsg = "Permohonan pembatalan pesanan $reqInvoice berhasil dikirim! Menunggu konfirmasi peninjauan dari Admin toko.";
    } catch (Exception $e) {
        $errorMsg = "Gagal mengajukan pembatalan: " . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batal_request_cancel') {
    $reqInvoice = $_POST['no_invoice'] ?? '';
    try {
        $pdo->prepare("UPDATE penjualan SET request_batal = 0, request_batal_alasan = NULL, request_batal_waktu = NULL WHERE no_invoice = ? AND id_user = ?")
            ->execute([$reqInvoice, $userId]);
        $successMsg = "Permohonan pembatalan untuk pesanan $reqInvoice berhasil ditarik kembali (pesanan tetap diproses).";
    } catch (Exception $e) {
        $errorMsg = "Gagal membatalkan permohonan: " . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'selesai_order') {
    $selesaiInvoice = $_POST['no_invoice'];
    try {
        $stmtCek = $pdo->prepare("SELECT id, status_pesanan, metode_pengambilan FROM penjualan WHERE no_invoice = ? AND id_user = ?");
        $stmtCek->execute([$selesaiInvoice, $userId]);
        $orderToSelesai = $stmtCek->fetch();

        if ($orderToSelesai && in_array($orderToSelesai['status_pesanan'], ['Dikirim', 'Siap Diambil'])) {
            $pdo->prepare("UPDATE penjualan SET status_pesanan = 'Selesai' WHERE no_invoice = ?")->execute([$selesaiInvoice]);
            
            if ($orderToSelesai['metode_pengambilan'] === 'Pick-up') {
                $stmtItems = $pdo->prepare("SELECT d.id_variasi, d.qty, v.rasio_konversi, v.satuan_besar FROM detail_penjualan d JOIN produk_variasi v ON d.id_variasi = v.id WHERE d.id_penjualan = ?");
                $stmtItems->execute([$orderToSelesai['id']]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                foreach ($items as $item) {
                    $idVar = $item['id_variasi'];
                    $qtyBox = intval($item['qty']);
                    $rasio = intval($item['rasio_konversi']) ?: 1;
                    $qtyPotong = $qtyBox * $rasio;
                    
                    $stmtSisa = $pdo->prepare("SELECT stok FROM stok_toko WHERE id_variasi = ?");
                    $stmtSisa->execute([$idVar]);
                    $sisaStok = $stmtSisa->fetchColumn();

                    $satuanTipe = ($rasio > 1) ? 'besar' : 'kecil';
                    $qtyKartu = ($rasio > 1) ? $qtyBox : $qtyPotong;
                    $sisaStokTampil = ($rasio > 1) ? floor($sisaStok / $rasio) : $sisaStok;

                    $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, ?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)");
                    $stmtKartu->execute([$idVar, $satuanTipe, $selesaiInvoice, $qtyKartu, $sisaStokTampil, "Konfirmasi Pengambilan di Toko (Pick-up)", $userId]);
                }
            }
            
            $successMsg = "Terima kasih! Pesanan $selesaiInvoice telah diselesaikan.";
        } else {
            $errorMsg = "Gagal menyelesaikan pesanan.";
        }
    } catch (Exception $e) {
        $errorMsg = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Batalkan pesanan 'Menunggu Pembayaran' yang kadaluarsa & pulihkan stok
batalkanPesananKadaluarsa($pdo);

// Fetch current user data
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// Status filter & counts for this customer
$statusFilter = $_GET['status'] ?? 'semua';
$validStatuses = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Siap Diambil', 'Selesai', 'Dibatalkan'];

$countStatusStmt = $pdo->prepare("
    SELECT status_pesanan, COUNT(*) as cnt 
    FROM penjualan 
    WHERE id_user = ? 
    GROUP BY status_pesanan
");
$countStatusStmt->execute([$userId]);
$statusCounts = $countStatusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
$totalCustomerOrders = array_sum($statusCounts);

// Pagination setup (10 orders per page)
$perPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));

$whereSql = "WHERE id_user = ?";
$paramsSql = [$userId];
if (in_array($statusFilter, $validStatuses)) {
    $whereSql .= " AND status_pesanan = ?";
    $paramsSql[] = $statusFilter;
}

$countFilteredStmt = $pdo->prepare("SELECT COUNT(*) FROM penjualan $whereSql");
$countFilteredStmt->execute($paramsSql);
$filteredCount = (int) $countFilteredStmt->fetchColumn();

$totalPages = max(1, (int) ceil($filteredCount / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// Fetch paginated orders history for this customer (Latest first)
$stmtOrders = $pdo->prepare("
    SELECT p.*, 'Pusat' AS nama_cabang
    FROM penjualan p
    $whereSql
    ORDER BY p.id DESC
    LIMIT $perPage OFFSET $offset
");
$stmtOrders->execute($paramsSql);
$orders = $stmtOrders->fetchAll();

// Pre-fetch order details for all customer orders
$orderIds = array_column($orders, 'id');
$orderDetails = [];
if (!empty($orderIds)) {
    $inClause = implode(',', array_map('intval', $orderIds));
    $stmtItems = $pdo->query("
        SELECT dp.*, pv.nama_variasi, pi.nama_produk, pv.satuan_besar
        FROM detail_penjualan dp
        JOIN produk_variasi pv ON dp.id_variasi = pv.id
        JOIN produk_induk pi ON pv.id_produk_induk = pi.id
        WHERE dp.id_penjualan IN ($inClause)
    ");
    while ($row = $stmtItems->fetch()) {
        $orderDetails[$row['id_penjualan']][] = $row;
    }
}
?>

<section class="pharmify-banner py-11 lg:py-14 text-center text-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight mb-2.5">Akun &amp; Riwayat Pesanan</h1>
        <nav class="flex items-center justify-center gap-2 text-xs font-medium text-sky-200/90">
            <a href="index.php" class="hover:text-white transition">Beranda</a>
            <span>&rsaquo;</span>
            <span class="text-white font-semibold">Akun Pelanggan</span>
        </nav>
    </div>
</section>

<main class="max-w-7xl mx-auto px-4 lg:px-8 py-8 sm:py-10 pb-28 md:pb-12 flex-1 w-full">

    <!-- ── Alerts ── -->
    <?php if ($successMsg): ?>
        <div class="p-4 mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($successMsg) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="p-4 mb-6 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2.5">
            <svg class="w-4 h-4 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            <span><?= htmlspecialchars($errorMsg) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <!-- ── LEFT: Edit Profile Card (4 cols - Pharmify #eef6fb Account Container) ── -->
        <div class="lg:col-span-4 bg-[#eef6fb] p-4 sm:p-6 sm:p-7">
            
            <div class="flex items-center gap-3.5 pb-5 border-b border-[#cbe4f5] mb-5">
                <div class="w-11 h-11 sm:w-12 sm:h-12 bg-[#0f2d5a] text-white font-extrabold text-base sm:text-lg flex items-center justify-center">
                    <?= strtoupper(substr($user['nama_lengkap'] ?? 'P', 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm font-extrabold text-[#0f2d5a] leading-tight truncate"><?= htmlspecialchars($user['nama_lengkap'] ?? '') ?></h2>
                    <span class="text-xs text-slate-500 block truncate">@<?= htmlspecialchars($user['username'] ?? '') ?></span>
                    <span class="inline-block mt-0.5 px-2 py-0.5 bg-white text-[#0f2d5a] border border-[#cbe4f5] font-bold text-[9px] uppercase tracking-wider">
                        <?= htmlspecialchars($user['role'] ?? 'pelanggan') ?>
                    </span>
                </div>
            </div>

            <form method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="update_profil">

                <div>
                    <label class="block font-bold text-[#0f2d5a] mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" required value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>"
                           class="w-full px-3.5 py-2.5 border border-slate-200 bg-white focus:outline-none focus:border-[#0f2d5a] transition">
                </div>

                <div>
                    <label class="block font-bold text-[#0f2d5a] mb-1.5">Alamat Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="nama@email.com"
                           class="w-full px-3.5 py-2.5 border border-slate-200 bg-white focus:outline-none focus:border-[#0f2d5a] transition">
                </div>

                <div>
                    <label class="block font-bold text-[#0f2d5a] mb-1.5">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>" placeholder="08123456789"
                           class="w-full px-3.5 py-2.5 border border-slate-200 bg-white focus:outline-none focus:border-[#0f2d5a] transition">
                </div>

                <div>
                    <label class="block font-bold text-[#0f2d5a] mb-1.5">Alamat Pengiriman Lengkap</label>
                    <textarea name="alamat" rows="3" placeholder="Jl. Raya No. 123, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Provinsi, Kode Pos"
                              class="w-full px-3.5 py-2.5 border border-slate-200 bg-white focus:outline-none focus:border-[#0f2d5a] transition text-xs"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Tulis alamat lengkap termasuk nama kota, kecamatan, dan kode pos agar pengiriman lebih akurat.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 px-4 bg-[#0f2d5a] hover:bg-[#1a75d2] text-white font-bold uppercase tracking-widest transition cursor-pointer text-xs">
                        SIMPAN PERUBAHAN
                    </button>
                </div>
            </form>

        </div>

        <!-- ── RIGHT: Order History (8 cols) ── -->
        <div class="lg:col-span-8 bg-white border border-slate-200 p-4 sm:p-6 sm:p-7">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-200 mb-5">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-[#1a75d2] block mb-0.5">TRANSAKSI ONLINE</span>
                    <h2 class="text-base sm:text-lg font-extrabold text-[#0f2d5a] tracking-tight">Riwayat Pesanan Saya</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Lihat status dan rincian transaksi pesanan online Anda</p>
                </div>
                <span class="self-start sm:self-auto text-[11px] font-extrabold uppercase tracking-wider bg-[#eef6fb] text-[#0f2d5a] border border-[#dcebf7] px-3 py-1 sm:px-3.5 sm:py-1.5">
                    <?= $filteredCount ?> Pesanan
                </span>
            </div>

            <!-- Filter Status Tabs (Scrollable on Mobile) -->
            <div class="flex items-center gap-1.5 mb-6 text-xs border-b border-slate-200 pb-4 overflow-x-auto no-scrollbar whitespace-nowrap">
                <a href="?status=semua" class="px-3.5 py-2 font-bold whitespace-nowrap transition border <?= $statusFilter === 'semua' ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0f2d5a] hover:text-[#0f2d5a]' ?>">
                    Semua (<?= $totalCustomerOrders ?>)
                </a>
                <a href="?status=Menunggu+Pembayaran" class="px-3.5 py-2 font-bold whitespace-nowrap transition border <?= $statusFilter === 'Menunggu Pembayaran' ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0f2d5a] hover:text-[#0f2d5a]' ?>">
                    Menunggu Bayar (<?= $statusCounts['Menunggu Pembayaran'] ?? 0 ?>)
                </a>
                <a href="?status=Diproses" class="px-3.5 py-2 font-bold whitespace-nowrap transition border <?= $statusFilter === 'Diproses' ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0f2d5a] hover:text-[#0f2d5a]' ?>">
                    Diproses (<?= $statusCounts['Diproses'] ?? 0 ?>)
                </a>
                <a href="?status=Dikirim" class="px-3.5 py-2 font-bold whitespace-nowrap transition border <?= $statusFilter === 'Dikirim' ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0f2d5a] hover:text-[#0f2d5a]' ?>">
                    Dikirim (<?= $statusCounts['Dikirim'] ?? 0 ?>)
                </a>
                <a href="?status=Siap+Diambil" class="px-3.5 py-2 font-bold whitespace-nowrap transition border <?= $statusFilter === 'Siap+Diambil' || $statusFilter === 'Siap Diambil' ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0f2d5a] hover:text-[#0f2d5a]' ?>">
                    Siap Diambil (<?= $statusCounts['Siap Diambil'] ?? 0 ?>)
                </a>
                <a href="?status=Selesai" class="px-3.5 py-2 font-bold whitespace-nowrap transition border <?= $statusFilter === 'Selesai' ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0f2d5a] hover:text-[#0f2d5a]' ?>">
                    Selesai (<?= $statusCounts['Selesai'] ?? 0 ?>)
                </a>
                <a href="?status=Dibatalkan" class="px-3.5 py-2 font-bold whitespace-nowrap transition border <?= $statusFilter === 'Dibatalkan' ? 'bg-[#0f2d5a] text-white border-[#0f2d5a]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#0f2d5a] hover:text-[#0f2d5a]' ?>">
                    Dibatalkan (<?= $statusCounts['Dibatalkan'] ?? 0 ?>)
                </a>
            </div>

            <?php if (!empty($orders)): ?>
                <div class="space-y-4">
                    <?php foreach ($orders as $ord): 
                        $status = $ord['status_pesanan'];
                        if ($status === 'Selesai') {
                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        } elseif ($status === 'Diproses' || $status === 'Dikirim' || $status === 'Siap Diambil') {
                            $badgeClass = 'bg-[#eef6fb] text-[#0f2d5a] border-[#cde3f5]';
                        } elseif ($status === 'Dibatalkan') {
                            $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                        } else {
                            $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
                        }
                        $items = $orderDetails[$ord['id']] ?? [];
                    ?>
                    <div class="border border-slate-200 bg-white hover:border-[#1a75d2]/50 transition-colors">
                        
                        <!-- Order Header Row -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-4 py-3 bg-[#f8fafc] border-b border-slate-200 text-xs">
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono font-extrabold text-[#0f2d5a] text-[13px] tracking-tight"><?= htmlspecialchars($ord['no_invoice']) ?></span>
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-500 text-[11px] font-medium"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?> WIB</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                                <span class="px-2.5 py-1 border text-[10px] font-extrabold uppercase tracking-wider <?= $badgeClass ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                                
                                <?php if ($status === 'Menunggu Pembayaran'): ?>
                                    <button onclick="lanjutkanPembayaran('<?= htmlspecialchars($ord['snap_token']) ?>', '<?= htmlspecialchars($ord['no_invoice']) ?>')" class="px-3 py-1 bg-[#0f2d5a] border border-[#0f2d5a] hover:bg-[#1a75d2] hover:border-[#1a75d2] text-white text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                        Bayar
                                    </button>
                                    <button type="button" onclick="bukaModalBatalCustomer('<?= htmlspecialchars($ord['no_invoice']) ?>')" class="px-3 py-1 bg-white border border-rose-300 hover:bg-rose-50 hover:border-rose-500 text-rose-600 text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                        Batal
                                    </button>
                                <?php elseif (in_array($status, ['Dikirim', 'Siap Diambil'])): ?>
                                    <form method="POST" class="m-0 p-0" onsubmit="return confirm('Apakah Anda yakin telah menerima pesanan ini dengan baik?');">
                                        <input type="hidden" name="action" value="selesai_order">
                                        <input type="hidden" name="no_invoice" value="<?= htmlspecialchars($ord['no_invoice']) ?>">
                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 border border-emerald-600 text-white text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                            Pesanan Diterima
                                        </button>
                                    </form>
                                    <a href="../pos/cetak_invoice.php?no_invoice=<?= urlencode($ord['no_invoice']) ?>" target="_blank"
                                       class="px-3 py-1 bg-white border border-slate-300 hover:border-[#0f2d5a] text-[#0f2d5a] text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                        Struk
                                    </a>
                                <?php elseif ($status === 'Diproses'): ?>
                                    <?php if (!empty($ord['request_batal'])): ?>
                                        <form method="POST" class="m-0 p-0" onsubmit="return confirm('Tarik kembali permohonan pembatalan?');">
                                            <input type="hidden" name="action" value="batal_request_cancel">
                                            <input type="hidden" name="no_invoice" value="<?= htmlspecialchars($ord['no_invoice']) ?>">
                                            <button type="submit" title="Tarik pengajuan pembatalan" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-900 text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                                Tarik Batal
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" onclick="bukaModalRequestBatal('<?= htmlspecialchars($ord['no_invoice']) ?>')" class="px-3 py-1 bg-white border border-slate-300 hover:border-rose-400 hover:text-rose-600 text-slate-600 text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                            Ajukan Batal
                                        </button>
                                    <?php endif; ?>
                                    <a href="../pos/cetak_invoice.php?no_invoice=<?= urlencode($ord['no_invoice']) ?>" target="_blank"
                                       class="px-3 py-1 bg-white border border-slate-300 hover:border-[#0f2d5a] text-[#0f2d5a] text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                        Struk
                                    </a>
                                <?php else: ?>
                                    <a href="../pos/cetak_invoice.php?no_invoice=<?= urlencode($ord['no_invoice']) ?>" target="_blank"
                                       class="px-3 py-1 bg-white border border-slate-300 hover:border-[#0f2d5a] text-[#0f2d5a] text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1.5">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                        Struk
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-4">
                            <?php if ($status === 'Menunggu Pembayaran'): 
                                $payMethod = strtolower($ord['payment_method_ecommerce'] ?? '');
                                $isQris = in_array($payMethod, ['qris', 'gopay', 'shopeepay']);
                                $cooldownMenit = defined('MIDTRANS_EXPIRY_ECOMMERCE_MENIT') ? MIDTRANS_EXPIRY_ECOMMERCE_MENIT : 120;
                                $cooldownLabel = $isQris ? '2 Jam (QRIS / E-Wallet)' : '2 Jam (Transfer Bank / Virtual Account)';
                                $cooldownDetik = $cooldownMenit * 60;
                                $expireTime = strtotime($ord['created_at']) + $cooldownDetik;
                                $sisaDetik = max(0, $expireTime - time());
                            ?>
                            <div class="mb-3 p-3 bg-amber-50/80 border border-amber-200 text-amber-900 text-[11px] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-center gap-2 font-medium">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <span>Stok barang telah dicadangkan (reserved). Batas waktu pembayaran (<?= $cooldownLabel ?>):</span>
                                </div>
                                <div class="flex items-center gap-1 self-start sm:self-auto font-mono font-black text-xs px-2.5 py-1 bg-white border border-amber-300 text-amber-950 countdown-timer shrink-0" data-seconds="<?= $sisaDetik ?>">
                                    <span class="countdown-text">--:--:--</span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($status === 'Diproses' && !empty($ord['request_batal'])): ?>
                            <div class="mb-3 p-3 bg-amber-50/80 border border-amber-300 text-amber-900 text-[11px] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <span class="font-bold">Permohonan Pembatalan Sedang Ditinjau Admin:</span> "<?= htmlspecialchars($ord['request_batal_alasan']) ?>"
                                        <span class="text-amber-700 block text-[10px] mt-0.5">Admin toko sedang memeriksa status fisik paket Anda di meja pengemasan.</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 self-start sm:self-auto shrink-0">
                                    <a href="https://wa.me/6281234567890?text=<?= urlencode('Halo Admin ZenCare, saya ingin konfirmasi permohonan pembatalan pesanan #' . $ord['no_invoice'] . ' karena: ' . $ord['request_batal_alasan']) ?>" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-extrabold uppercase tracking-wider transition flex items-center gap-1">
                                        WhatsApp Admin
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($status === 'Dibatalkan'): ?>
                            <div class="mb-3 p-3 bg-rose-50/80 border border-rose-200 text-rose-900 text-[11px] flex items-start gap-2">
                                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                <div class="leading-snug">
                                    <span class="font-bold">Pesanan Dibatalkan:</span> <?= htmlspecialchars($ord['alasan_batal'] ?: 'Pesanan dibatalkan.') ?>
                                    <span class="text-rose-600 block text-[10px] mt-0.5">Dibatalkan oleh: <strong><?= htmlspecialchars($ord['dibatalkan_oleh'] ?: 'Pelanggan/Sistem') ?></strong><?= !empty($ord['waktu_batal']) ? ' pada ' . date('d/m/Y H:i', strtotime($ord['waktu_batal'])) . ' WIB' : '' ?></span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Purchased Items List -->
                            <div class="divide-y divide-slate-100 text-xs">
                                <?php foreach ($items as $it): ?>
                                <div class="py-2 first:pt-0 last:pb-0 flex items-center justify-between text-slate-700 gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-1.5 h-1.5 bg-[#1a75d2] shrink-0"></span>
                                        <span class="font-bold text-[#0f2d5a] truncate"><?= htmlspecialchars($it['nama_produk']) ?> &mdash; <?= htmlspecialchars($it['nama_variasi']) ?></span>
                                        <span class="text-slate-500 text-[11px] font-semibold bg-slate-100 px-1.5 py-0.5 border border-slate-200 shrink-0">x<?= intval($it['qty']) ?> <?= htmlspecialchars($it['satuan_besar'] ?: 'Box') ?></span>
                                    </div>
                                    <span class="font-bold text-[#0f2d5a] text-xs shrink-0">
                                        Rp <?= number_format($it['harga_satuan'] * $it['qty'], 0, ',', '.') ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Order Footer Summary -->
                            <div class="mt-3 pt-3 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="text-[11px] text-slate-500">
                                    <span>Kurir: <strong class="text-[#0f2d5a]"><?= strtoupper(htmlspecialchars($ord['kurir'] ?: 'REGULER')) ?> (<?= htmlspecialchars($ord['layanan'] ?: '-') ?>)</strong></span>
                                    <span class="mx-1.5 text-slate-300">|</span>
                                    <span>Ongkir: <strong class="text-slate-700">Rp <?= number_format($ord['ongkir'], 0, ',', '.') ?></strong></span>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Bayar:</span>
                                    <span class="text-sm font-black text-[#0f2d5a]">
                                        Rp <?= number_format($ord['total_harga'], 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination Controls -->
                <?php if ($totalPages > 1): ?>
                <div class="mt-6 pt-5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <span class="text-slate-500 font-medium">
                        Menampilkan <strong class="text-[#0f2d5a]"><?= ($page - 1) * $perPage + 1 ?> – <?= min($page * $perPage, $filteredCount) ?></strong> dari <strong class="text-[#0f2d5a]"><?= $filteredCount ?></strong> pesanan
                    </span>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-3.5 py-2 border border-slate-200 bg-white text-[#0f2d5a] hover:border-[#0f2d5a] font-bold transition">&larr; Sebelumnya</a>
                        <?php else: ?>
                            <span class="px-3.5 py-2 border border-slate-200 bg-slate-50 text-slate-400 font-bold cursor-not-allowed">&larr; Sebelumnya</span>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p == 1 || $p == $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="w-9 h-9 flex items-center justify-center border text-xs font-extrabold transition <?= $p == $page ? 'bg-[#0f2d5a] border-[#0f2d5a] text-white' : 'border-slate-200 bg-white text-[#0f2d5a] hover:border-[#0f2d5a]' ?>"><?= $p ?></a>
                            <?php elseif ($p == 2 || $p == $totalPages - 1): ?>
                                <span class="px-1 text-slate-400 font-bold">...</span>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-3.5 py-2 border border-slate-200 bg-white text-[#0f2d5a] hover:border-[#0f2d5a] font-bold transition">Selanjutnya &rarr;</a>
                        <?php else: ?>
                            <span class="px-3.5 py-2 border border-slate-200 bg-slate-50 text-slate-400 font-bold cursor-not-allowed">Selanjutnya &rarr;</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="py-16 px-4 text-center border border-dashed border-slate-200 bg-[#f8fafc]">
                    <div class="w-12 h-12 bg-[#eef6fb] border border-[#dcebf7] text-[#1a75d2] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <p class="text-sm font-extrabold text-[#0f2d5a] mb-1">Belum Ada Riwayat Pesanan</p>
                    <p class="text-xs text-slate-500 mb-5">Pesanan online yang Anda buat akan muncul di sini secara otomatis.</p>
                    <a href="index.php" class="inline-block px-5 py-2.5 bg-[#0f2d5a] text-white font-extrabold text-xs uppercase tracking-wider hover:bg-[#1a75d2] transition">
                        Mulai Belanja Sekarang
                    </a>
                </div>
            <?php endif; ?>

        </div>

    </div>

</main>

<!-- Midtrans Snap Sandbox JS -->
<script src="<?= MIDTRANS_SNAP_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>
<script>
    function lanjutkanPembayaran(token, orderId) {
        if (!token) {
            alert("Token pembayaran tidak ditemukan. Silakan batalkan pesanan dan buat ulang.");
            return;
        }
        snap.pay(token, {
            onSuccess: function(result) {
                let pType = result && result.payment_type ? result.payment_type : '';
                // Sinkronisasi paksa dari frontend (karena localhost tidak bisa terima webhook)
                fetch('../api/sync_payment.php?order_id=' + encodeURIComponent(orderId) + '&payment_type=' + encodeURIComponent(pType))
                    .then(res => res.json())
                    .then(data => {
                        alert('Pembayaran Berhasil! Pesanan akan segera diproses.');
                        window.location.reload();
                    }).catch(err => {
                        alert('Pembayaran Berhasil, namun gagal sinkronisasi ke server lokal. Harap hubungi admin.');
                        window.location.reload();
                    });
            },
            onPending: function(result) {
                let pType = result && result.payment_type ? result.payment_type : '';
                fetch('../api/sync_payment.php?order_id=' + encodeURIComponent(orderId) + '&payment_type=' + encodeURIComponent(pType))
                    .finally(() => {
                        alert('Menunggu Pembayaran' + (pType ? ' (' + pType.toUpperCase() + ')' : '') + '.');
                        window.location.reload();
                    });
            },
            onError: function(result) {
                alert('Transaksi Dibatalkan / Gagal.');
            }
        });
    }

    function startOrderCountdowns() {
        const timers = document.querySelectorAll('.countdown-timer');
        if (!timers.length) return;
        
        function update() {
            let hasActive = false;
            timers.forEach(t => {
                let sec = parseInt(t.getAttribute('data-seconds'), 10);
                if (isNaN(sec)) return;
                const txt = t.querySelector('.countdown-text') || t;
                if (sec <= 0) {
                    txt.innerHTML = '<span class="text-rose-600 font-bold">Waktu Habis (Otomatis Batal)</span>';
                    if (!t.dataset.expiredTriggered) {
                        t.dataset.expiredTriggered = '1';
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    hasActive = true;
                    sec--;
                    t.setAttribute('data-seconds', sec);
                    const h = Math.floor(sec / 3600).toString().padStart(2, '0');
                    const m = Math.floor((sec % 3600) / 60).toString().padStart(2, '0');
                    const s = (sec % 60).toString().padStart(2, '0');
                    txt.textContent = `${h}:${m}:${s}`;
                }
            });
            if (hasActive) {
                setTimeout(update, 1000);
            }
        }
        update();
    }
    document.addEventListener('DOMContentLoaded', startOrderCountdowns);

    function bukaModalBatalCustomer(invoice) {
        Swal.fire({
            title: 'Batalkan Pesanan #' + invoice + '?',
            html: `
                <div class="text-left text-xs text-slate-700">
                    <p class="mb-3 text-slate-500 font-medium">Pilih alasan mengapa Anda ingin membatalkan pesanan ini:</p>
                    <div class="space-y-2 mb-3">
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_alasan" value="Ingin mengubah rincian pesanan" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" checked onchange="toggleSwalLainnya(false)">
                            <span class="font-medium text-slate-700">Ingin mengubah rincian pesanan (varian/jumlah/alamat)</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_alasan" value="Berubah pikiran / Salah pesan" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" onchange="toggleSwalLainnya(false)">
                            <span class="font-medium text-slate-700">Berubah pikiran / Salah pesan produk</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_alasan" value="Waktu pengiriman atau ongkos kirim tidak sesuai" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" onchange="toggleSwalLainnya(false)">
                            <span class="font-medium text-slate-700">Waktu pengiriman atau ongkos kirim tidak sesuai</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_alasan" value="Lainnya" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" onchange="toggleSwalLainnya(true)">
                            <span class="font-medium text-slate-700">Alasan lainnya...</span>
                        </label>
                    </div>
                    <div id="swal_lainnya_box" class="hidden">
                        <label class="block text-[11px] font-bold text-[#0f2d5a] mb-1">Tuliskan alasan pembatalan: *</label>
                        <textarea id="swal_alasan_detail" rows="2" placeholder="Tuliskan keterangan singkat..." class="w-full text-xs border border-slate-300 p-2.5 focus:outline-none focus:border-[#0f2d5a]"></textarea>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan Pesanan',
            cancelButtonText: 'Kembali',
            customClass: {
                popup: 'rounded-none border border-slate-200',
                confirmButton: 'rounded-none font-bold text-xs px-4 py-2.5 uppercase tracking-wider',
                cancelButton: 'rounded-none font-bold text-xs px-4 py-2.5 uppercase tracking-wider'
            },
            preConfirm: () => {
                const selected = document.querySelector('input[name="swal_alasan"]:checked');
                if (!selected) {
                    Swal.showValidationMessage('Harap pilih salah satu alasan pembatalan.');
                    return false;
                }
                const kategori = selected.value;
                const detail = (document.getElementById('swal_alasan_detail')?.value || '').trim();
                if (kategori === 'Lainnya' && !detail) {
                    Swal.showValidationMessage('Harap tuliskan alasan pembatalan pada kolom teks.');
                    return false;
                }
                return { kategori, detail };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('cancel_invoice_inp').value = invoice;
                document.getElementById('cancel_kategori_inp').value = result.value.kategori;
                document.getElementById('cancel_detail_inp').value = result.value.detail;
                document.getElementById('form-cancel-customer').submit();
            }
        });
    }

    function toggleSwalLainnya(show) {
        const box = document.getElementById('swal_lainnya_box');
        if (box) {
            if (show) box.classList.remove('hidden');
            else box.classList.add('hidden');
        }
    }

    function bukaModalRequestBatal(invoice) {
        Swal.fire({
            title: 'Ajukan Pembatalan #' + invoice + '?',
            html: `
                <div class="text-left text-xs text-slate-700">
                    <p class="mb-2 text-slate-500 font-medium">Pesanan ini sedang disiapkan oleh tim gudang. Pengajuan pembatalan memerlukan persetujuan Admin Toko:</p>
                    <div class="space-y-2 mb-3">
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_req_alasan" value="Ingin mengubah rincian pesanan" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" checked onchange="toggleSwalReqLainnya(false)">
                            <span class="font-medium text-slate-700">Ingin mengubah pesanan (varian/jumlah/alamat kirim)</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_req_alasan" value="Berubah pikiran / Tidak sengaja pesan" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" onchange="toggleSwalReqLainnya(false)">
                            <span class="font-medium text-slate-700">Berubah pikiran / Tidak sengaja memesan</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_req_alasan" value="Waktu pengiriman terlalu lama" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" onchange="toggleSwalReqLainnya(false)">
                            <span class="font-medium text-slate-700">Waktu pengemasan atau pengiriman diperkirakan terlalu lama</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 border border-slate-200 hover:bg-[#eef6fb]/50 hover:border-[#1a75d2]/40 cursor-pointer transition">
                            <input type="radio" name="swal_req_alasan" value="Lainnya" class="text-[#0f2d5a] focus:ring-[#0f2d5a]" onchange="toggleSwalReqLainnya(true)">
                            <span class="font-medium text-slate-700">Alasan lainnya...</span>
                        </label>
                    </div>
                    <div id="swal_req_lainnya_box" class="hidden">
                        <label class="block text-[11px] font-bold text-[#0f2d5a] mb-1">Tuliskan alasan pengajuan batal: *</label>
                        <textarea id="swal_req_alasan_detail" rows="2" placeholder="Tuliskan keterangan detail untuk admin..." class="w-full text-xs border border-slate-300 p-2.5 focus:outline-none focus:border-[#0f2d5a]"></textarea>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0f2d5a',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Kirim Pengajuan Batal',
            cancelButtonText: 'Kembali',
            customClass: {
                popup: 'rounded-none border border-slate-200',
                confirmButton: 'rounded-none font-bold text-xs px-4 py-2.5 uppercase tracking-wider',
                cancelButton: 'rounded-none font-bold text-xs px-4 py-2.5 uppercase tracking-wider'
            },
            preConfirm: () => {
                const selected = document.querySelector('input[name="swal_req_alasan"]:checked');
                if (!selected) {
                    Swal.showValidationMessage('Harap pilih salah satu alasan pengajuan.');
                    return false;
                }
                const kategori = selected.value;
                const detail = (document.getElementById('swal_req_alasan_detail')?.value || '').trim();
                if (kategori === 'Lainnya' && !detail) {
                    Swal.showValidationMessage('Harap tuliskan alasan pada kolom teks.');
                    return false;
                }
                return { kategori, detail };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('req_cancel_invoice_inp').value = invoice;
                document.getElementById('req_cancel_kategori_inp').value = result.value.kategori;
                document.getElementById('req_cancel_detail_inp').value = result.value.detail;
                document.getElementById('form-request-cancel-customer').submit();
            }
        });
    }

    function toggleSwalReqLainnya(show) {
        const box = document.getElementById('swal_req_lainnya_box');
        if (box) {
            if (show) box.classList.remove('hidden');
            else box.classList.add('hidden');
        }
    }
</script>

<!-- Hidden Form Pembatalan Pelanggan Langsung (Menunggu Pembayaran) -->
<form id="form-cancel-customer" method="POST" class="hidden">
    <input type="hidden" name="action" value="cancel_order">
    <input type="hidden" name="no_invoice" id="cancel_invoice_inp">
    <input type="hidden" name="alasan_kategori" id="cancel_kategori_inp">
    <input type="hidden" name="alasan_detail" id="cancel_detail_inp">
</form>

<!-- Hidden Form Pengajuan Pembatalan Pelanggan (Diproses) -->
<form id="form-request-cancel-customer" method="POST" class="hidden">
    <input type="hidden" name="action" value="request_cancel">
    <input type="hidden" name="no_invoice" id="req_cancel_invoice_inp">
    <input type="hidden" name="alasan_kategori" id="req_cancel_kategori_inp">
    <input type="hidden" name="alasan_detail" id="req_cancel_detail_inp">
</form>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>if (typeof Swal === 'undefined') { document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>'); }</script>

<?php require_once __DIR__ . '/footer.php'; ?>
