<?php
$files = [
    'lain/Gambar_3.18_Logical_Database_Model.svg',
    'lain/logical_database_zencare.html',
    'laporan_ta/BAB_III_ZENCARE_MEDICAL.html',
    'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html'
];

$forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna'];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    echo "Auditing $file...\n";
    foreach ($forbidden as $word) {
        if (preg_match("/\b" . preg_quote($word, '/') . "\b/i", $content, $m)) {
            // Note: In BAB_III_DAN_IV_ZENCARE_MEDICAL.html, 'Bab 4' is legitimate as the document title, but let's check.
            if ($word === 'Bab 4' && strpos($file, 'DAN_IV') !== false) continue;
            echo "  [FOUND FORBIDDEN] $word in $file\n";
        }
    }
}
echo "Audit complete.\n";

