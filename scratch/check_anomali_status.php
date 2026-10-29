<?php
require_once __DIR__ . '/../config/koneksi.php';

$p = $pdo->query("SELECT p.id, p.no_invoice, dp.catatan_logistik FROM penjualan p JOIN detail_penjualan dp ON dp.id_penjualan=p.id WHERE p.id=22")->fetchAll(PDO::FETCH_ASSOC);
echo "Penjualan 22: " . json_encode($p, JSON_PRETTY_PRINT) . "\n";

// Update row 3 since HUFPIL-2027-01.A was sold in Penjualan 22 (POS-20261029-6112)
$pdo->prepare("UPDATE log_anomali_fefo SET status = 'disesuaikan', resolved_in_penjualan = 22, resolved_at = '2026-10-29 13:58:00' WHERE id = 3")->execute();

$rows = $pdo->query("SELECT la.*, p.no_invoice, p_res.no_invoice AS res_inv FROM log_anomali_fefo la LEFT JOIN penjualan p ON la.id_penjualan = p.id LEFT JOIN penjualan p_res ON la.resolved_in_penjualan = p_res.id")->fetchAll(PDO::FETCH_ASSOC);
echo "Anomali after update: " . json_encode($rows, JSON_PRETTY_PRINT);
