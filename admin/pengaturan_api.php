<?php
// File: admin/pengaturan_api.php
// Pengaturan API Global System (Shopee, Midtrans, RajaOngkir) – Super Admin Only
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/layout.php';

requireRole(['super_admin']);

$msg = ''; $msgType = '';

// Handle toggle & key update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'toggle_api') {
        $platform = $_POST['platform'] ?? '';
        $currentState = intval($_POST['current_state'] ?? 0);
        $newState = 1 - $currentState;
        
        $pdo->prepare("UPDATE pengaturan_api SET is_active = ? WHERE platform = ?")->execute([$newState, $platform]);
        $msg = "Status API " . strtoupper($platform) . " berhasil diubah ke " . ($newState ? 'AKTIF (ON)' : 'NONAKTIF (OFF)') . ".";
        $msgType = 'success';
    }

    if ($aksi === 'update_key') {
        $platform = $_POST['platform'] ?? '';
        $apiKey   = trim($_POST['api_key'] ?? '');
        $apiSec   = trim($_POST['api_secret'] ?? '');
        $webhook  = trim($_POST['webhook_url'] ?? '');
        
        $pdo->prepare("UPDATE pengaturan_api SET api_key=?, api_secret=?, webhook_url=? WHERE platform=?")
            ->execute([$apiKey ?: null, $apiSec ?: null, $webhook ?: null, $platform]);
        $msg = "Konfigurasi API " . strtoupper($platform) . " berhasil diperbarui.";
        $msgType = 'success';
    }
}

// Ensure 1 row per platform in pengaturan_api
$platforms = ['shopee', 'midtrans', 'rajaongkir'];
foreach ($platforms as $p) {
    $chk = $pdo->prepare("SELECT id FROM pengaturan_api WHERE platform = ?");
    $chk->execute([$p]);
    if (!$chk->fetch()) {
        $pdo->prepare("INSERT INTO pengaturan_api (platform, api_key, api_secret, webhook_url, is_active) VALUES (?, NULL, NULL, NULL, 1)")
            ->execute([$p]);
    }
}

// Fetch 1 row per platform
$apiList = $pdo->query("SELECT * FROM pengaturan_api GROUP BY platform ORDER BY id ASC")->fetchAll();

$platformMeta = [
    'shopee'     => [
        'label' => 'Shopee Sandbox API',
        'desc' => 'Sinkronisasi stok otomatis ke toko Shopee saat transaksi kasir terjadi. Jika OFF, POS hanya memotong stok lokal.',
        'icon' => 'bolt',
        'color' => 'orange'
    ],
    'midtrans'   => [
        'label' => 'Midtrans Snap Payment Gateway',
        'desc' => 'Gerbang pembayaran E-Commerce (QRIS, Transfer Bank, Credit Card). Jika OFF, checkout toko online dinonaktifkan.',
        'icon' => 'store',
        'color' => 'blue'
    ],
    'rajaongkir' => [
        'label' => 'RajaOngkir Komerce API',
        'desc' => 'Kalkulasi ongkos kirim real-time JNE/POS untuk luar kota. Jika OFF, sistem menggunakan tarif flat ekspedisi.',
        'icon' => 'truck',
        'color' => 'green'
    ],
];

layoutHead('Pengaturan API System');
layoutBodyOpen();
layoutSidebar('pengaturan');
layoutHeader('Pengaturan Integrasi API System', 'Kontrol status ON/OFF dan kunci API Shopee, Midtrans, dan RajaOngkir secara terpusat');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2
        <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
        <span><?= $msgType === 'success' ? '✅' : '⛔' ?></span>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Skenario Bisnis Info Banner -->
<div class="bg-zc/5 border border-zc/20 rounded-2xl p-5 mb-6">
    <h2 class="text-sm font-semibold text-zcTxt mb-2 flex items-center gap-2">
        <svg class="w-4 h-4 text-zc" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Sistem Integrasi Terpusat ZenCare Omnichannel
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-zcMut">
        <div class="p-3 bg-white border border-zcBrd rounded-xl">
            <span class="font-semibold text-zcTxt block mb-1">📦 1. Synchronous Shopee API</span>
            Mengontrol sinkronisasi stok otomatis saat kasir POS memproses transaksi offline.
        </div>
        <div class="p-3 bg-white border border-zcBrd rounded-xl">
            <span class="font-semibold text-zcTxt block mb-1">🛒 2. E-Commerce & RajaOngkir API</span>
            Mengatur gerbang pembayaran Snap Sandbox dan kalkulasi ongkir ekspedisi nasional.
        </div>
    </div>
</div>

<!-- API Cards List -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <?php foreach ($apiList as $api): ?>
        <?php
        $pKey = $api['platform'];
        $meta = $platformMeta[$pKey] ?? ['label' => strtoupper($pKey), 'desc' => '', 'icon' => 'settings'];
        $isOn = (bool)$api['is_active'];
        ?>
        <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden flex flex-col justify-between">
            <!-- Card Header & Toggle -->
            <div class="p-5 border-b border-zcBrd">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <span class="text-sm font-bold text-zcTxt block"><?= $meta['label'] ?></span>
                        <p class="text-[11px] text-zcMut mt-1 leading-relaxed"><?= $meta['desc'] ?></p>
                    </div>
                </div>

                <!-- Status & Switch -->
                <div class="pt-3 border-t border-zcBrd/60 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full <?= $isOn ? 'bg-emerald-500' : 'bg-slate-300' ?>"></div>
                        <span class="text-xs font-semibold <?= $isOn ? 'text-emerald-700' : 'text-zcMut' ?>">
                            <?= $isOn ? 'AKTIF (ON)' : 'NONAKTIF (OFF)' ?>
                        </span>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="aksi" value="toggle_api">
                        <input type="hidden" name="platform" value="<?= $pKey ?>">
                        <input type="hidden" name="current_state" value="<?= $isOn ? 1 : 0 ?>">
                        <button type="submit" onclick="return confirm('Ubah status API <?= $meta['label'] ?>?')"
                            class="text-xs font-semibold px-3.5 py-1.5 rounded-xl border transition
                                <?= $isOn
                                    ? 'bg-white border-slate-300 text-slate-600 hover:bg-slate-50'
                                    : 'bg-zc border-zc text-white hover:bg-zcHv' ?>">
                            <?= $isOn ? 'Matikan API' : 'Aktifkan API' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Key Config Collapsible -->
            <div class="p-4 bg-slate-50/50">
                <button onclick="toggleKeyForm('kf_<?= $pKey ?>')"
                    class="text-xs font-semibold text-zcMut hover:text-zc flex items-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                    Konfigurasi Kunci API
                </button>

                <div id="kf_<?= $pKey ?>" class="hidden mt-3 pt-3 border-t border-zcBrd space-y-2.5">
                    <form method="POST" class="space-y-2.5">
                        <input type="hidden" name="aksi" value="update_key">
                        <input type="hidden" name="platform" value="<?= $pKey ?>">
                        <div>
                            <label class="block text-[11px] font-medium text-zcMut mb-1">API Key</label>
                            <input type="text" name="api_key" value="<?= htmlspecialchars($api['api_key'] ?? '') ?>"
                                placeholder="API Key..."
                                class="w-full text-xs border border-zcBrd rounded-lg px-3 py-2 focus:outline-none focus:border-zc bg-white font-mono">
                        </div>
                        <?php if ($pKey !== 'rajaongkir'): ?>
                        <div>
                            <label class="block text-[11px] font-medium text-zcMut mb-1">API Secret / Client Key</label>
                            <input type="text" name="api_secret" value="<?= htmlspecialchars($api['api_secret'] ?? '') ?>"
                                placeholder="API Secret..."
                                class="w-full text-xs border border-zcBrd rounded-lg px-3 py-2 focus:outline-none focus:border-zc bg-white font-mono">
                        </div>
                        <?php endif; ?>
                        <div>
                            <label class="block text-[11px] font-medium text-zcMut mb-1">Webhook Endpoint URL</label>
                            <input type="text" name="webhook_url" value="<?= htmlspecialchars($api['webhook_url'] ?? '') ?>"
                                class="w-full text-xs border border-zcBrd rounded-lg px-3 py-2 focus:outline-none focus:border-zc bg-white font-mono">
                        </div>
                        <button type="submit" class="w-full text-xs font-semibold py-2 bg-zc hover:bg-zcHv text-white rounded-lg transition shadow-xs">Simpan Kunci API</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function toggleKeyForm(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
</script>

<?php layoutEnd(); ?>
