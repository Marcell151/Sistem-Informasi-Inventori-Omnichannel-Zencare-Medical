<?php
// File: scratch/setup_tragedi_5_kardus.php
// Script penyedia skenario uji "Tragedi 5 Kardus" untuk Sidang / Bimbingan Dosen
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$action = $_GET['action'] ?? '';
$message = '';
$product = null;

// Cari atau buat variasi khusus untuk simulasi uji
$skuTarget = 'SIM-5BOX';
$stmt = $pdo->prepare("SELECT pv.*, pi.nama_produk, COALESCE(st.stok, 0) AS stok_toko 
                       FROM produk_variasi pv 
                       JOIN produk_induk pi ON pv.id_produk_induk = pi.id 
                       LEFT JOIN stok_toko st ON st.id_variasi = pv.id 
                       WHERE pv.sku_variasi = ?");
$stmt->execute([$skuTarget]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    $chkInduk = $pdo->prepare("SELECT id FROM produk_induk WHERE sku_induk = 'SIM-IND-5BOX'");
    $chkInduk->execute();
    $idInduk = $chkInduk->fetchColumn();
    if (!$idInduk) {
        $pdo->prepare("INSERT INTO produk_induk (sku_induk, nama_produk, kategori, deskripsi, created_at) 
                       VALUES ('SIM-IND-5BOX', 'Obat Uji Skenario Dosen (5 Kardus)', 'Obat', 'Produk khusus demonstrasi skenario Tragedi 5 Kardus & Omnichannel FEFO.', NOW())")
            ->execute();
        $idInduk = $pdo->lastInsertId();
    }

    $pdo->prepare("INSERT INTO produk_variasi (id_produk_induk, sku_variasi, nama_variasi, satuan_kecil, satuan_besar, rasio_konversi, harga_jual_kecil, harga_jual_besar, berat, tampil_di_online, is_active) 
                   VALUES (?, ?, 'Karton Isi 10 Box', 'Box', 'Karton', 10, 28000, 250000, 500, 1, 1)")
        ->execute([$idInduk, $skuTarget]);
    $idVariasi = $pdo->lastInsertId();

    $stmt->execute([$skuTarget]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

$idVariasi = $product['id'];

// Eksekusi Reset ke 5 Kardus Utuh (50 Box eceran, 0 kardus robek)
if ($action === 'reset_5_kardus') {
    $pdo->beginTransaction();
    try {
        // 1. Bersihkan stok toko & stok batch lama untuk variasi ini
        $pdo->prepare("DELETE FROM stok_batch WHERE id_variasi = ?")->execute([$idVariasi]);
        $pdo->prepare("DELETE FROM histori_konversi WHERE id_variasi = ?")->execute([$idVariasi]);
        
        // 2. Set stok_toko persis 50 unit kecil (= 5 Karton x 10 Box)
        $chkStok = $pdo->prepare("SELECT id FROM stok_toko WHERE id_variasi = ?");
        $chkStok->execute([$idVariasi]);
        if ($chkStok->fetch()) {
            $pdo->prepare("UPDATE stok_toko SET stok = 50 WHERE id_variasi = ?")->execute([$idVariasi]);
        } else {
            $pdo->prepare("INSERT INTO stok_toko (id_variasi, stok) VALUES (?, 50)")->execute([$idVariasi]);
        }

        // 3. Masukkan 2 Batch Kardus Utuh (> 60 hari expired sesuai kaidah FEFO):
        // Batch 1 (FEFO Terdekat): 2 Karton (20 unit) - Exp: 90 hari lagi
        // Batch 2 (FEFO Lanjutan): 3 Karton (30 unit) - Exp: 180 hari lagi
        $expBatch1 = date('Y-m-d', strtotime('+90 days'));
        $expBatch2 = date('Y-m-d', strtotime('+180 days'));

        $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa, created_at) 
                       VALUES (?, 'BATCH-DOSEN-A', ?, 20, NOW())")
            ->execute([$idVariasi, $expBatch1]);

        $pdo->prepare("INSERT INTO stok_batch (id_variasi, no_batch, tgl_exp, stok_sisa, created_at) 
                       VALUES (?, 'BATCH-DOSEN-B', ?, 30, NOW())")
            ->execute([$idVariasi, $expBatch2]);

        // 4. Catat ke kartu stok
        $pdo->prepare("INSERT INTO kartu_stok (id_variasi, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh, tanggal) 
                       VALUES (?, 'kecil', 'Masuk', 'Gudang', 'Penerimaan', 'INIT-5BOX', 50, 50, 'Reset Skenario Dosen: Saldo Awal 5 Karton Utuh (50 Box)', 1, NOW())")
            ->execute([$idVariasi]);

        $pdo->commit();
        $message = "✅ Berhasil me-reset stok produk uji! Stok saat ini: Tepat 5 Karton utuh (50 Box eceran, 0 eceran terpisah).";
        
        // Refresh product data
        $stmt->execute([$skuTarget]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "❌ Gagal reset: " . $e->getMessage();
    }
}

// Ambil rincian batch aktif
$batches = $pdo->prepare("SELECT * FROM stok_batch WHERE id_variasi = ? ORDER BY tgl_exp ASC");
$batches->execute([$idVariasi]);
$batchList = $batches->fetchAll(PDO::FETCH_ASSOC);

// Ambil histori konversi
$historiKonv = $pdo->prepare("SELECT * FROM histori_konversi WHERE id_variasi = ? ORDER BY id DESC LIMIT 5");
$historiKonv->execute([$idVariasi]);
$listKonversi = $historiKonv->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skenario Uji: Tragedi 5 Kardus – ZenCare Medical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center font-black text-xl shadow-lg shadow-blue-500/20">
                    📦
                </div>
                <div>
                    <h1 class="text-xl font-black text-white">Skenario Uji: "Tragedi 5 Kardus"</h1>
                    <p class="text-xs text-slate-400">Pengujian Konsistensi Omnichannel, Konversi UOM, & FEFO Multi-Kanal</p>
                </div>
            </div>
            <a href="../index.php" class="text-xs font-semibold px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition">
                Kembali ke Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="p-4 rounded-xl text-xs font-bold <?= str_starts_with($message, '✅') ? 'bg-emerald-950 border border-emerald-800 text-emerald-300' : 'bg-rose-950 border border-rose-800 text-rose-300' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Alur Narasi Uji Dosen -->
        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6 space-y-3">
            <h2 class="text-sm font-bold text-blue-400 uppercase tracking-wider flex items-center gap-2">
                <span>📋 Alur Simulasi Dosen (Bimbingan 12-10-26)</span>
            </h2>
            <div class="text-xs text-slate-300 space-y-2 leading-relaxed">
                <p>1. <strong>Kondisi Awal:</strong> Stok barang di gudang ada <strong>5 Kardus</strong> (50 pcs) dan <strong>0 eceran</strong>.</p>
                <p>2. <strong>Aksi Pembeli Online (E-Commerce):</strong> Pelanggan online menambahkan <strong>4 Kardus</strong> ke keranjang belanja.</p>
                <p>3. <strong>Aksi Kasir Fisik (POS Offline):</strong> Datang pasien fisik ingin beli <strong>2 pcs eceran</strong>. Karena eceran 0, kasir merobek <strong>1 Kardus</strong> (Konversi UOM darurat). Sisa kardus di gudang tercatat menjadi <strong>4 Kardus</strong>.</p>
                <p>4. <strong>Hasil Sinkronisasi Omnichannel:</strong> Sisa 4 kardus tersebut langsung klop memenuhi pesanan 4 kardus online tadi (4 online + 1 offline = tepat 5 kardus terhitung 100% tanpa selisih!).</p>
            </div>
        </div>

        <!-- Kartu Produk Uji -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl md:col-span-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Produk Demonstrasi Uji</span>
                <h3 class="text-lg font-black text-white mt-1"><?= htmlspecialchars($product['nama_produk']) ?></h3>
                <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-400 block text-[10px]">SKU:</span>
                        <span class="font-mono font-bold text-blue-400"><?= $product['sku_variasi'] ?></span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-400 block text-[10px]">Rasio Konversi:</span>
                        <span class="font-bold text-white">1 <?= $product['satuan_besar'] ?> = <?= $product['rasio_konversi'] ?> <?= $product['satuan_kecil'] ?></span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                        <span class="text-slate-400 block text-[10px]">Total Stok Saat Ini:</span>
                        <span class="font-bold text-emerald-400"><?= $product['stok_toko'] ?> <?= $product['satuan_kecil'] ?> (<?= floor($product['stok_toko'] / $product['rasio_konversi']) ?> <?= $product['satuan_besar'] ?>)</span>
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <a href="?action=reset_5_kardus" onclick="return confirm('Reset stok produk ini ke 5 Kardus utuh (50 pcs) dan 0 eceran?')" 
                       class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center gap-1.5">
                        🔄 Reset Stok ke 5 Kardus Utuh
                    </a>
                    <a href="../pos/pos.php" target="_blank" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        🛒 Buka Terminal POS Kasir
                    </a>
                    <a href="../ecommerce/index.php" target="_blank" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        🛍️ Buka Toko E-Commerce
                    </a>
                </div>
            </div>

            <!-- Panel Navigasi Cepat Audit -->
            <div class="bg-slate-800 border border-slate-700 p-5 rounded-2xl flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Halaman Verifikasi Audit</span>
                    <ul class="mt-3 space-y-2 text-xs">
                        <li>
                            <a href="../inventori/histori_konversi.php" target="_blank" class="text-blue-400 hover:underline flex items-center justify-between p-2 rounded-lg bg-slate-900 hover:bg-slate-900/80">
                                <span>📦 Histori Konversi (Sisa Dus)</span>
                                <span>↗</span>
                            </a>
                        </li>
                        <li>
                            <a href="../laporan/kartu_stok.php?id_variasi=<?= $idVariasi ?>&filter_konversi=buka_kardus" target="_blank" class="text-blue-400 hover:underline flex items-center justify-between p-2 rounded-lg bg-slate-900 hover:bg-slate-900/80">
                                <span>📊 Kartu Stok (Filter Buka Dus)</span>
                                <span>↗</span>
                            </a>
                        </li>
                        <li>
                            <a href="../admin/pesanan.php" target="_blank" class="text-blue-400 hover:underline flex items-center justify-between p-2 rounded-lg bg-slate-900 hover:bg-slate-900/80">
                                <span>🏷️ FEFO Packing Pesanan Online</span>
                                <span>↗</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="pt-4 text-[10px] text-slate-500 border-t border-slate-700/60 text-center">
                    Gunakan saat demonstrasi sidang/bimbingan dosen
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Batch Stok Aktif -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5">
            <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3">Rincian Batch Fisik Aktif (FEFO Order)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-900 text-slate-400 uppercase text-[10px]">
                        <tr>
                            <th class="p-3">No. Batch</th>
                            <th class="p-3">Tgl. Expired</th>
                            <th class="p-3">Sisa Hari</th>
                            <th class="p-3">Sisa Stok Fisik</th>
                            <th class="p-3">Tipe Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        <?php if (empty($batchList)): ?>
                            <tr><td colspan="5" class="p-4 text-center text-slate-500">Belum ada batch. Silakan klik tombol "Reset Stok ke 5 Kardus Utuh".</td></tr>
                        <?php else: ?>
                            <?php foreach ($batchList as $b): 
                                $days = (int)ceil((strtotime($b['tgl_exp']) - time()) / 86400);
                                $isEceran = str_contains($b['no_batch'], '.');
                            ?>
                                <tr class="hover:bg-slate-750">
                                    <td class="p-3 font-mono font-bold text-white"><?= htmlspecialchars($b['no_batch']) ?></td>
                                    <td class="p-3 text-slate-300"><?= date('d/m/Y', strtotime($b['tgl_exp'])) ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $days <= 90 ? 'bg-amber-900/60 text-amber-300 border border-amber-700' : 'bg-emerald-900/60 text-emerald-300 border border-emerald-700' ?>">
                                            <?= $days ?> Hari
                                        </span>
                                    </td>
                                    <td class="p-3 font-bold text-white"><?= $b['stok_sisa'] ?> Box</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $isEceran ? 'bg-purple-900 text-purple-300' : 'bg-blue-900 text-blue-300' ?>">
                                            <?= $isEceran ? 'Eceran Sobekan (.1)' : 'Kardus Utuh' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>
