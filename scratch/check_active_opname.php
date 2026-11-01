<?php
require __DIR__ . '/../config/koneksi.php';
$c = $pdo->query("SELECT COUNT(*) FROM sesi_opname WHERE status = 'Aktif'")->fetchColumn();
echo "Active sessions: $c\n";

