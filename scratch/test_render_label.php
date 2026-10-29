<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_GET['no_invoice'] = 'POS-20261029-6112';
ob_start();
include __DIR__ . '/../admin/cetak_label_pengiriman.php';
$html = ob_get_clean();
// Let's print out the relevant parts
echo "=== HEADER BADGES ===\n";
if (preg_match('/<div style="text-align: right; white-space: nowrap;">(.*?)<\/div>/s', $html, $m)) {
    echo trim($m[1]) . "\n";
}
echo "=== FOOTER SECTION ===\n";
if (preg_match('/<div class="footer-section">(.*?)<\/div>\s*<\/div>\s*<script>/s', $html, $m)) {
    echo trim($m[1]) . "\n";
}
