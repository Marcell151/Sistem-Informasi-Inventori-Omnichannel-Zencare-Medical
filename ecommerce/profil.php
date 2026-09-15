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
    echo "<main class='max-w-md mx-auto py-16 px-4 flex-1 flex flex-col justify-center text-center'>
            <div class='w-16 h-16 rounded-3xl bg-zcLt text-zc mx-auto flex items-center justify-center mb-4 shadow-xs'>
                <svg class='w-8 h-8' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round'><path d='M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2'/><circle cx='12' cy='7' r='4'/></svg>
            </div>
            <h2 class='text-lg font-extrabold text-zcTxt mb-1.5'>Silakan Masuk Terlebih Dahulu</h2>
            <p class='text-xs text-zcMut mb-6 leading-relaxed'>
                Masuk ke akun pelanggan ZenCare Medical untuk mengelola alamat pengiriman, kontak, dan melacak riwayat pesanan online Anda.
            </p>
            <div class='flex flex-col gap-2.5'>
                <a href='../login_customer.php?redirect=ecommerce/profil.php' class='w-full py-2.5 px-4 bg-zc hover:bg-zcHv text-white font-bold text-xs rounded-xl shadow-xs transition'>
                    Masuk ke Akun
                </a>
                <a href='../register.php' class='w-full py-2.5 px-4 bg-white border border-zcBrd hover:bg-slate-50 text-zcTxt font-bold text-xs rounded-xl shadow-xs transition'>
                    Daftar Akun Baru
                </a>
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
    $cancelInvoice = $_POST['no_invoice'];
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

        // Update status ke Dibatalkan
        $pdo->prepare("UPDATE penjualan SET status_pesanan = 'Dibatalkan' WHERE no_invoice = ?")->execute([$cancelInvoice]);

        // Kembalikan Stok
        $stmtItems = $pdo->prepare("SELECT d.id_variasi, d.qty, v.rasio_konversi, v.satuan_besar FROM detail_penjualan d JOIN produk_variasi v ON d.id_variasi = v.id WHERE d.id_penjualan = ?");
        $stmtItems->execute([$orderToCancel['id']]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $item) {
            $idVar = $item['id_variasi'];
            $qtyBox = intval($item['qty']);
            $rasio = intval($item['rasio_konversi']) ?: 1;
            $qtyPotong = $qtyBox * $rasio;
            $satBesar = $item['satuan_besar'];

            $pdo->prepare("UPDATE stok_toko SET stok = stok + ? WHERE id_variasi = ?")->execute([$qtyPotong, $idVar]);
        }

        $pdo->commit();
        $successMsg = "Pesanan $cancelInvoice berhasil dibatalkan dan stok telah dikembalikan.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMsg = "Gagal membatalkan pesanan: " . $e->getMessage();
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

                    $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_variasi, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh) VALUES (?, 'Keluar', 'E-Commerce', 'Penjualan E-Commerce', ?, ?, ?, ?, ?)");
                    $stmtKartu->execute([$idVar, $selesaiInvoice, $qtyPotong, $sisaStok, "Konfirmasi Pengambilan di Toko (Pick-up)", $userId]);
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

// Fetch current user data
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// Fetch orders history for this customer
$stmtOrders = $pdo->prepare("
    SELECT p.*, 'Pusat' AS nama_cabang
    FROM penjualan p
    WHERE p.id_user = ?
    ORDER BY p.id DESC
");
$stmtOrders->execute([$userId]);
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

<main class="max-w-7xl mx-auto px-4 lg:px-8 py-6 flex-1 w-full">

    <!-- ── Breadcrumbs ── -->
    <nav class="flex items-center gap-2 text-xs text-zcMut mb-6">
        <a href="index.php" class="hover:text-zc transition">Beranda</a>
        <span>/</span>
        <span class="text-zcTxt font-bold">Akun &amp; Profil Pelanggan</span>
    </nav>

    <!-- ── Alerts ── -->
    <?php if ($successMsg): ?>
        <div class="p-4 mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
            <span><?= htmlspecialchars($successMsg) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="p-4 mb-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2.5 shadow-xs">
            <svg class="w-4 h-4 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            <span><?= htmlspecialchars($errorMsg) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ── LEFT: Edit Profile Card (4 cols) ── -->
        <div class="lg:col-span-4 bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
            
            <div class="flex items-center gap-3.5 pb-5 border-b border-slate-100 mb-5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-zc to-zcHv text-white font-extrabold text-lg flex items-center justify-center shadow-xs">
                    <?= strtoupper(substr($user['nama_lengkap'] ?? 'P', 0, 1)) ?>
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-zcTxt leading-tight"><?= htmlspecialchars($user['nama_lengkap'] ?? '') ?></h2>
                    <span class="text-xs text-zcMut">@<?= htmlspecialchars($user['username'] ?? '') ?></span>
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md bg-blue-50 text-zc font-bold text-[9px] uppercase tracking-wide">
                        <?= htmlspecialchars($user['role'] ?? 'pelanggan') ?>
                    </span>
                </div>
            </div>

            <form method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="action" value="update_profil">

                <div>
                    <label class="block font-bold text-zcTxt mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" required value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-zc transition">
                </div>

                <div>
                    <label class="block font-bold text-zcTxt mb-1.5">Alamat Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="nama@email.com"
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-zc transition">
                </div>

                <div>
                    <label class="block font-bold text-zcTxt mb-1.5">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>" placeholder="08123456789"
                           class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-zc transition">
                </div>

                <div>
                    <label class="block font-bold text-zcTxt mb-1.5 text-sm">Alamat Pengiriman Lengkap</label>
                    <textarea name="alamat" rows="3" placeholder="Jl. Raya No. 123, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Provinsi, Kode Pos"
                              class="w-full px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-zc transition text-sm"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                    <p class="text-xs text-slate-400 mt-1">Tulis alamat lengkap termasuk nama kota, kecamatan, dan kode pos agar pengiriman lebih akurat.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 bg-zc hover:bg-zcHv text-white font-bold rounded-xl shadow-xs transition cursor-pointer active:scale-95 text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>

        <!-- ── RIGHT: Order History (8 cols) ── -->
        <div class="lg:col-span-8 bg-white border border-slate-100 rounded-3xl p-6 sm:p-7 shadow-sm">
            
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                <div>
                    <h2 class="text-base font-extrabold text-zcTxt">Riwayat Pesanan Saya</h2>
                    <p class="text-sm text-zcMut">Lihat status dan detail pesanan yang telah Anda buat</p>
                </div>
                <span class="text-xs font-extrabold bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full">
                    Total: <?= count($orders) ?> Pesanan
                </span>
            </div>

            <?php if (!empty($orders)): ?>
                <div class="space-y-4">
                    <?php foreach ($orders as $ord): 
                        $status = $ord['status_pesanan'];
                        if ($status === 'Selesai') {
                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        } elseif ($status === 'Diproses' || $status === 'Dikirim') {
                            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                        } elseif ($status === 'Dibatalkan') {
                            $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                        } else {
                            $badgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
                        }
                        $items = $orderDetails[$ord['id']] ?? [];
                    ?>
                    <div class="border border-slate-100 rounded-2xl p-4 hover:border-zc/30 hover:shadow-xs transition-all bg-slate-50/40">
                        
                        <!-- Order Header Row -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-zcTxt text-[13px]"><?= htmlspecialchars($ord['no_invoice']) ?></span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-zcMut text-[11px]"><?= date('d M Y, H:i', strtotime($ord['created_at'])) ?></span>
                            </div>
                            <div class="flex items-center gap-2 self-start sm:self-auto">
                                <span class="px-2.5 py-0.5 rounded-md border text-[10px] font-bold <?= $badgeClass ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                                
                                <?php if ($status === 'Menunggu Pembayaran'): ?>
                                    <button onclick="lanjutkanPembayaran('<?= htmlspecialchars($ord['snap_token']) ?>', '<?= htmlspecialchars($ord['no_invoice']) ?>')" class="px-2 py-0.5 bg-zc border border-zc hover:bg-zcHv text-white text-[10px] font-bold rounded-md shadow-2xs transition flex items-center gap-1">
                                        Bayar
                                    </button>
                                    <form method="POST" class="m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                                        <input type="hidden" name="action" value="cancel_order">
                                        <input type="hidden" name="no_invoice" value="<?= htmlspecialchars($ord['no_invoice']) ?>">
                                        <button type="submit" class="px-2 py-0.5 bg-white border border-rose-200 hover:border-rose-500 text-rose-500 text-[10px] font-bold rounded-md shadow-2xs transition flex items-center gap-1">
                                            Batal
                                        </button>
                                    </form>
                                <?php elseif (in_array($status, ['Dikirim', 'Siap Diambil'])): ?>
                                    <form method="POST" class="m-0 p-0" onsubmit="return confirm('Apakah Anda yakin telah menerima pesanan ini dengan baik?');">
                                        <input type="hidden" name="action" value="selesai_order">
                                        <input type="hidden" name="no_invoice" value="<?= htmlspecialchars($ord['no_invoice']) ?>">
                                        <button type="submit" class="px-2 py-0.5 bg-emerald-500 hover:bg-emerald-600 border border-emerald-600 text-white text-[10px] font-bold rounded-md shadow-2xs transition flex items-center gap-1">
                                            Pesanan Diterima
                                        </button>
                                    </form>
                                    <a href="../pos/cetak_invoice.php?no_invoice=<?= urlencode($ord['no_invoice']) ?>" target="_blank"
                                       class="px-2 py-0.5 bg-white border border-slate-200 hover:border-zc text-zc text-[10px] font-bold rounded-md shadow-2xs transition flex items-center gap-1">
                                        Struk
                                    </a>
                                <?php else: ?>
                                    <a href="../pos/cetak_invoice.php?no_invoice=<?= urlencode($ord['no_invoice']) ?>" target="_blank"
                                       class="px-2 py-0.5 bg-white border border-slate-200 hover:border-zc text-zc text-[10px] font-bold rounded-md shadow-2xs transition flex items-center gap-1">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                        Struk
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Purchased Items List -->
                        <div class="py-3 text-xs space-y-1.5">
                            <?php foreach ($items as $it): ?>
                            <div class="flex items-center justify-between text-slate-700">
                                <div class="flex items-center gap-2 truncate max-w-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-zc/50 shrink-0"></span>
                                    <span class="font-semibold text-zcTxt truncate"><?= htmlspecialchars($it['nama_produk']) ?> &mdash; <?= htmlspecialchars($it['nama_variasi']) ?></span>
                                    <span class="text-zcMut text-[11px]">x<?= intval($it['qty']) ?> <?= htmlspecialchars($it['satuan_besar'] ?: 'Box') ?></span>
                                </div>
                                <span class="font-medium text-[11px] shrink-0">
                                    Rp <?= number_format($it['harga_satuan'] * $it['qty'], 0, ',', '.') ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Footer Summary -->
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                            <div class="text-[11px] text-zcMut">
                                <span>Kurir: <strong class="text-zcTxt"><?= strtoupper(htmlspecialchars($ord['kurir'] ?: 'REGULER')) ?> (<?= htmlspecialchars($ord['layanan'] ?: '-') ?>)</strong></span>
                                <span class="mx-1.5">&bull;</span>
                                <span>Ongkir: Rp <?= number_format($ord['ongkir'], 0, ',', '.') ?></span>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-xs text-zcMut">Total Bayar:</span>
                                <span class="text-sm font-extrabold text-zcTxt">
                                    Rp <?= number_format($ord['total_harga'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="py-16 text-center text-slate-400">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <p class="text-xs font-semibold text-zcTxt mb-1">Belum Ada Riwayat Pesanan</p>
                    <p class="text-[11px] text-zcMut mb-4">Pesanan online yang Anda buat akan muncul di sini secara otomatis.</p>
                    <a href="index.php" class="inline-block px-4 py-2 bg-zc text-white font-bold text-xs rounded-xl shadow-xs hover:bg-zcHv transition">
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
                // Sinkronisasi paksa dari frontend (karena localhost tidak bisa terima webhook)
                fetch('../api/sync_payment.php?order_id=' + orderId)
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
                alert('Menunggu Pembayaran.');
            },
            onError: function(result) {
                alert('Transaksi Dibatalkan / Gagal.');
            }
        });
    }
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
