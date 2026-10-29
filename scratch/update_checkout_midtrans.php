<?php
// 1. UPDATE zencare_checkout.php
$zcFile = __DIR__ . '/../zencare_checkout.php';
$zcContent = file_get_contents($zcFile);

$oldPaySectionPattern = '/<!-- Pilihan Kategori Pembayaran Midtrans \(Batas Waktu Tunggal 2 Jam\) -->[\s\S]*?<\/div>\s*<\/div>\s*(?=\s*<div class="mt-5 space-y-2">)/i';

$newPaySection = '<!-- Metode Pembayaran Otomatis Midtrans (Pilihan Lengkap Langsung di Popup Midtrans) -->
                    <div class="mt-4 pt-4 border-t border-zcBrd">
                        <label class="block text-xs font-bold text-zcTxt mb-2 flex items-center justify-between">
                            <span>Metode Pembayaran Online:</span>
                            <span class="text-[10px] text-emerald-700 font-bold bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                Batas Waktu: 2 Jam
                            </span>
                        </label>
                        <div class="border-2 border-zc rounded-xl p-3 bg-gradient-to-r from-blue-50/70 to-indigo-50/30 shadow-xs">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-black text-zcTxt flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-zc" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                    Pembayaran Otomatis Midtrans
                                </span>
                                <span class="text-[10px] font-bold text-zc bg-white border border-zc/30 px-2 py-0.5 rounded shadow-2xs">Pilihan Lengkap</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed mb-2">
                                Pilih kanal pembayaran langsung di jendela Midtrans saat tombol Bayar Sekarang ditekan:
                            </p>
                            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
                                <span class="px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-700 shadow-2xs">QRIS (GoPay/ShopeePay)</span>
                                <span class="px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-700 shadow-2xs">BCA Virtual Account</span>
                                <span class="px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-700 shadow-2xs">Mandiri Bill</span>
                                <span class="px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-700 shadow-2xs">BNI &amp; BRI VA</span>
                                <span class="px-2 py-0.5 bg-white border border-slate-200 rounded text-slate-700 shadow-2xs">Transfer Bank Lain</span>
                            </div>
                        </div>
                    </div>';

if (preg_match($oldPaySectionPattern, $zcContent)) {
    $zcContent = preg_replace($oldPaySectionPattern, $newPaySection, $zcContent);
    echo "Payment section in zencare_checkout.php replaced successfully.\n";
} else {
    echo "Warning: Old payment section pattern not found in zencare_checkout.php.\n";
}

// Update JS selectedPayCategory default
$zcContent = str_replace("let selectedPayCategory = 'qris';", "let selectedPayCategory = 'midtrans';", $zcContent);
file_put_contents($zcFile, $zcContent);

// 2. UPDATE api/checkout.php
$apiFile = __DIR__ . '/../api/checkout.php';
$apiContent = file_get_contents($apiFile);

$oldApiPaymentBlock = '$kategoriPembayaran = strtolower(trim($input[\'kategori_pembayaran\'] ?? \'qris\'));
    if ($kategoriPembayaran === \'va\') {
        $enabledPayments = [\'bank_transfer\', \'bca_va\', \'bni_va\', \'bri_va\', \'echannel\', \'permata_va\'];
        $paymentMethodDb = \'bank_transfer\';
    } else {
        $enabledPayments = [\'qris\', \'gopay\', \'shopeepay\'];
        $paymentMethodDb = \'qris\';
    }';

$newApiPaymentBlock = '$kategoriPembayaran = strtolower(trim($input[\'kategori_pembayaran\'] ?? \'midtrans\'));
    if ($kategoriPembayaran === \'va\') {
        $enabledPayments = [\'bank_transfer\', \'bca_va\', \'bni_va\', \'bri_va\', \'echannel\', \'permata_va\'];
        $paymentMethodDb = \'bank_transfer\';
    } else if ($kategoriPembayaran === \'qris\') {
        $enabledPayments = [\'qris\', \'gopay\', \'shopeepay\'];
        $paymentMethodDb = \'qris\';
    } else {
        // Mode Midtrans Lengkap: Biarkan pembeli memilih langsung di UI Midtrans (QRIS, Semua Bank VA, E-Wallet)
        $enabledPayments = [\'qris\', \'gopay\', \'shopeepay\', \'bank_transfer\', \'bca_va\', \'bni_va\', \'bri_va\', \'echannel\', \'permata_va\', \'other_va\'];
        $paymentMethodDb = \'midtrans\';
    }';

$normApi = str_replace("\r\n", "\n", $apiContent);
$normOldApi = str_replace("\r\n", "\n", $oldApiPaymentBlock);

if (strpos($normApi, $normOldApi) !== false) {
    $normApi = str_replace($normOldApi, $newApiPaymentBlock, $normApi);
    file_put_contents($apiFile, $normApi);
    echo "api/checkout.php updated with full Midtrans channels.\n";
} else {
    echo "Warning: Old payment block not found in api/checkout.php.\n";
}

echo "Checkout updates completed.\n";

