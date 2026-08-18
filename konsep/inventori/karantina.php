<?php
// File: inventori/karantina.php
// Modul Gudang Karantina (Virtual Defect Storage / Klaim Garansi)
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'masuk_karantina') {
    $idVariasi = intval($_POST['id_variasi'] ?? 0);
    $idCabang  = intval($_POST['id_cabang'] ?? 0);
    $qty       = intval($_POST['qty'] ?? 0);
    $alasan    = trim($_POST['alasan'] ?? '');

    if ($idVariasi <= 0 || $idCabang <= 0 || $qty <= 0 || empty($alasan)) {
        $message = "Semua kolom form wajib diisi!";
        $messageType = 'error';
    } else {
        $pdo->beginTransaction();
        try {
            $stmtStok = $pdo->prepare("SELECT stok FROM stok_cabang WHERE id_variasi = ? AND id_cabang = ? FOR UPDATE");
            $stmtStok->execute([$idVariasi, $idCabang]);
            $stokCurrent = $stmtStok->fetchColumn();

            if ($stokCurrent === false || intval($stokCurrent) < $qty) {
                throw new Exception("Stok fisik di cabang ini tidak mencukupi untuk dikarantina! Tersisa: " . ($stokCurrent ?? 0));
            }

            // 1. Potong Stok
            $stmtSub = $pdo->prepare("UPDATE stok_cabang SET stok = stok - ? WHERE id_variasi = ? AND id_cabang = ?");
            $stmtSub->execute([$qty, $idVariasi, $idCabang]);

            // 2. Insert gudang_karantina
            $stmtKarantina = $pdo->prepare("INSERT INTO gudang_karantina (id_cabang, id_variasi, qty, alasan) VALUES (?, ?, ?, ?)");
            $stmtKarantina->execute([$idCabang, $idVariasi, $qty, $alasan]);

            // 3. Log Kartu Stok
            $sisaStok = intval($stokCurrent) - $qty;
            $stmtKartu = $pdo->prepare("INSERT INTO kartu_stok (id_cabang, id_variasi, jenis_mutasi, qty, sisa_stok, keterangan) VALUES (?, ?, 'Karantina', ?, ?, ?)");
            $stmtKartu->execute([$idCabang, $idVariasi, $qty, $sisaStok, "Dipindahkan ke Gudang Karantina. Alasan: $alasan"]);

            $pdo->commit();
            $message = "Berhasil memindahkan $qty unit barang ke Gudang Karantina!";
            $messageType = 'success';
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Gagal memproses karantina: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$cabangList = $pdo->query("SELECT * FROM cabang WHERE is_active = 1 ORDER BY id ASC")->fetchAll();
$variasiList = $pdo->query("
    SELECT v.id, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_item, v.sku_variasi
    FROM produk_variasi v
    JOIN produk_induk i ON v.id_produk_induk = i.id
    WHERE v.is_active = 1 AND i.is_active = 1
    ORDER BY i.nama_produk ASC
")->fetchAll();

$karantinaList = $pdo->query("
    SELECT gk.*, c.nama AS nama_cabang, CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama_item, v.sku_variasi
    FROM gudang_karantina gk
    JOIN cabang c ON gk.id_cabang = c.id
    JOIN produk_variasi v ON gk.id_variasi = v.id
    JOIN produk_induk i ON v.id_produk_induk = i.id
    ORDER BY gk.tanggal DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>ZenCare - Gudang Karantina & Klaim Garansi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              gray: {
                50: '#fafafa', 100: '#f4f4f5', 200: '#e4e4e7', 300: '#d4d4d8',
                400: '#a1a1aa', 500: '#71717a', 600: '#52525b', 700: '#3f3f46',
                800: '#27272a', 900: '#18181b',
              }
            }
          }
        }
      }
    </script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased">

    <header class="bg-black text-white border-b border-gray-800 px-6 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-lg font-bold tracking-wider">ZENCARE <span class="font-normal text-gray-400">KARANTINA &amp; GARANSI</span></h1>
            <p class="text-xs text-gray-400">Virtual Defect Storage &amp; Isolation Module</p>
        </div>
        <a href="../index.php" class="text-xs bg-white text-black font-bold px-3 py-1.5 rounded hover:bg-gray-200 transition">&laquo; Dashboard Utama</a>
    </header>

    <div class="max-w-6xl mx-auto px-4 py-8">
        
        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 text-xs font-semibold rounded border <?= $messageType === 'success' ? 'bg-black text-white border-black' : 'bg-gray-300 text-black border-gray-400' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-5 bg-white border border-gray-300 rounded-lg p-6">
                <h2 class="text-base font-bold text-black border-b border-gray-200 pb-2 mb-4">Lapor Barang Cacat / Garansi</h2>
                
                <form method="POST">
                    <input type="hidden" name="aksi" value="masuk_karantina">
                    
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Lokasi Cabang Barang *</label>
                            <select name="id_cabang" class="w-full border border-gray-300 rounded p-2.5 bg-white focus:outline-none focus:border-black" required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php foreach ($cabangList as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Pilih Item Barang *</label>
                            <select name="id_variasi" class="w-full border border-gray-300 rounded p-2.5 bg-white focus:outline-none focus:border-black" required>
                                <option value="">-- Pilih Barang --</option>
                                <?php foreach ($variasiList as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nama_item']) ?> [<?= htmlspecialchars($v['sku_variasi']) ?>]</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Jumlah Unit Cacat / Rusak *</label>
                            <input type="number" name="qty" min="1" class="w-full border border-gray-300 rounded p-2.5 focus:outline-none focus:border-black" placeholder="Contoh: 1" required>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Alasan Cacat / Deskripsi Rusak *</label>
                            <textarea name="alasan" rows="3" class="w-full border border-gray-300 rounded p-2.5 focus:outline-none focus:border-black" placeholder="Contoh: Layar digital tensimeter tidak menyala / Cacat pabrik" required></textarea>
                        </div>

                        <button type="submit" class="w-full bg-black text-white font-bold py-3 px-4 rounded border border-black hover:bg-gray-800 transition">
                            Isolasi ke Gudang Karantina
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-7 bg-white border border-gray-300 rounded-lg p-6">
                <h2 class="text-base font-bold text-black border-b border-gray-200 pb-2 mb-4">Daftar Barang Karantina (Virtual Defect Storage)</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-300 font-bold text-gray-800">
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Cabang</th>
                                <th class="p-3">Nama Barang</th>
                                <th class="p-3 text-center">Qty</th>
                                <th class="p-3">Alasan Cacat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($karantinaList)): ?>
                                <tr><td colspan="5" class="p-4 text-center text-gray-400 italic">Belum ada barang di Gudang Karantina.</td></tr>
                            <?php else: ?>
                                <?php foreach ($karantinaList as $k): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-3 text-gray-600"><?= date('d/m/Y H:i', strtotime($k['tanggal'])) ?></td>
                                        <td class="p-3 font-semibold"><?= htmlspecialchars($k['nama_cabang']) ?></td>
                                        <td class="p-3"><strong><?= htmlspecialchars($k['nama_item']) ?></strong><br><span class="text-[10px] text-gray-500"><?= htmlspecialchars($k['sku_variasi']) ?></span></td>
                                        <td class="p-3 text-center font-bold"><?= intval($k['qty']) ?></td>
                                        <td class="p-3 text-gray-700"><?= htmlspecialchars($k['alasan']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
