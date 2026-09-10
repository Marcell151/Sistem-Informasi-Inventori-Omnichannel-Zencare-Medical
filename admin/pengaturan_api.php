<?php
session_start();
require_once '../config/config.php';
require_once '../config/koneksi.php';
require_once '../config/auth.php';
require_once '../config/layout.php';

// Hanya superadmin
requireRole(['superadmin']);

$msg = '';
$msgType = '';
$configFile = __DIR__ . '/../config/api_keys.php';

// Ensure config file exists
if (!file_exists($configFile)) {
    $defaultConfig = [
        'midtrans' => [
            'is_active' => false,
            'is_production' => false,
            'server_key' => '',
            'client_key' => ''
        ],
        'rajaongkir' => [
            'is_active' => false,
            'api_key' => '',
            'account_type' => 'starter'
        ]
    ];
    file_put_contents($configFile, "<?php\nreturn " . var_export($defaultConfig, true) . ";\n");
}

$apiConfig = require $configFile;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $platform = $_POST['platform'] ?? '';
    
    if (isset($apiConfig[$platform])) {
        if ($aksi === 'toggle_api') {
            $apiConfig[$platform]['is_active'] = !$apiConfig[$platform]['is_active'];
            $msg = "Status API {$platform} berhasil diubah.";
            $msgType = 'success';
        } elseif ($aksi === 'update_key') {
            if ($platform === 'midtrans') {
                $apiConfig['midtrans']['server_key'] = $_POST['server_key'] ?? '';
                $apiConfig['midtrans']['client_key'] = $_POST['client_key'] ?? '';
            } elseif ($platform === 'rajaongkir') {
                $apiConfig['rajaongkir']['api_key'] = $_POST['api_key'] ?? '';
            }
            $msg = "Kredensial API {$platform} berhasil disimpan secara aman.";
            $msgType = 'success';
        }
        
        // Save back to file
        $content = "<?php\n// FILE INI HARUS DI-IGNORE DI .gitignore PADA PRODUKSI\n// Berisi kredensial rahasia (API Keys)\n\nreturn " . var_export($apiConfig, true) . ";\n";
        file_put_contents($configFile, $content);
    }
}

$platformMeta = [
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
layoutHeader('Pengaturan Integrasi API System', 'Kontrol status ON/OFF dan kunci API Midtrans dan RajaOngkir secara aman tanpa database.');
?>

<?php if ($msg): ?>
    <div class="mb-5 p-4 rounded-xl border text-xs font-semibold flex items-center gap-2
        <?= $msgType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' ?>">
        <span><?= $msgType === 'success' ? '✓' : '✗' ?></span>
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- API Cards List -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <?php foreach ($platformMeta as $pKey => $meta): ?>
        <?php
        $isOn = $apiConfig[$pKey]['is_active'] ?? false;
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
                    Konfigurasi Kunci API
                </button>

                <div id="kf_<?= $pKey ?>" class="hidden mt-3 pt-3 border-t border-zcBrd space-y-2.5">
                    <form method="POST" class="space-y-2.5">
                        <input type="hidden" name="aksi" value="update_key">
                        <input type="hidden" name="platform" value="<?= $pKey ?>">
                        
                        <?php if ($pKey === 'rajaongkir'): ?>
                        <div>
                            <label class="block text-[11px] font-medium text-zcMut mb-1">API Key</label>
                            <input type="text" name="api_key" value="<?= htmlspecialchars($apiConfig['rajaongkir']['api_key'] ?? '') ?>"
                                placeholder="API Key RajaOngkir..."
                                class="w-full text-xs border border-zcBrd rounded-lg px-3 py-2 focus:outline-none focus:border-zc bg-white font-mono">
                        </div>
                        <?php endif; ?>

                        <?php if ($pKey === 'midtrans'): ?>
                        <div>
                            <label class="block text-[11px] font-medium text-zcMut mb-1">Server Key</label>
                            <input type="text" name="server_key" value="<?= htmlspecialchars($apiConfig['midtrans']['server_key'] ?? '') ?>"
                                placeholder="SB-Mid-server-..."
                                class="w-full text-xs border border-zcBrd rounded-lg px-3 py-2 focus:outline-none focus:border-zc bg-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-zcMut mb-1">Client Key</label>
                            <input type="text" name="client_key" value="<?= htmlspecialchars($apiConfig['midtrans']['client_key'] ?? '') ?>"
                                placeholder="SB-Mid-client-..."
                                class="w-full text-xs border border-zcBrd rounded-lg px-3 py-2 focus:outline-none focus:border-zc bg-white font-mono">
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="w-full text-xs font-semibold py-2 bg-zc hover:bg-zcHv text-white rounded-lg transition shadow-xs">Simpan Kunci API Secara Aman</button>
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
