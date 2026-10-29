<?php
$f = 'pos/cetak_invoice.php';
$c = file_get_contents($f);
$old = '<div class="title">ZenCare Medical</div>
                <div class="subtitle"><?= htmlspecialchars($trx[\'nama_cabang\']) ?></div>
                <div class="subtitle"><?= htmlspecialchars($trx[\'alamat_cabang\']) ?></div>';
$new = '<div class="title">ZenCare Medical</div>
                <div class="subtitle" style="font-weight: bold; color: #1e293b;">Toko Alat Kesehatan dan Obat</div>
                <div class="subtitle"><?= htmlspecialchars($trx[\'nama_cabang\']) ?> - <?= htmlspecialchars($trx[\'alamat_cabang\']) ?></div>';

$normC = str_replace("\r\n", "\n", $c);
$normOld = str_replace("\r\n", "\n", $old);
$normNew = str_replace("\r\n", "\n", $new);

if (strpos($normC, $normOld) !== false) {
    $normC = str_replace($normOld, $normNew, $normC);
    file_put_contents($f, $normC);
    echo "SUCCESS: Updated subtitle in pos/cetak_invoice.php\n";
} else {
    echo "FAILED: Target not found\n";
}

