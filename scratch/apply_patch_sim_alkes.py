with open('scratch/run_full_opname_simulation.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Update array of anomalies
old_arr = """$anomaliSkenario = [
    'rusak' => null,
    'expired' => null,
    'hilang_obat' => null,
    'hilang_alkes' => null,
    'surplus' => null
];"""

new_arr = """$anomaliSkenario = [
    'rusak' => null,
    'expired' => null,
    'hilang_obat' => null,
    'hilang_alkes' => null,
    'rusak_alkes' => null,
    'surplus' => null
];"""

assert old_arr in text, "old_arr not found"
text = text.replace(old_arr, new_arr)

# 2. Update loop to inject rusak_alkes (Termometer Digital Omron)
old_loop = """    // Skenario 4: Tensimeter Digital Omron (Hilang -1 Unit)
    elseif (!$anomaliSkenario['hilang_alkes'] && strpos($r['nama_produk'] ?? '', 'Tensimeter Digital') !== false) {
        $fisik = $sistem - 1;
        $anomaliSkenario['hilang_alkes'] = $r;
    }
    // Skenario 5: Betadine Solution 15ml Batch BET-15-26 (Surplus +2 Botol)"""

new_loop = """    // Skenario 4: Tensimeter Digital Omron (Alkes Hilang -1 Unit)
    elseif (!$anomaliSkenario['hilang_alkes'] && strpos($r['nama_produk'] ?? '', 'Tensimeter Digital') !== false) {
        $fisik = $sistem - 1;
        $anomaliSkenario['hilang_alkes'] = $r;
    }
    // Skenario 5: Termometer Digital Omron (Alkes Rusak -1 Unit)
    elseif (!$anomaliSkenario['rusak_alkes'] && strpos($r['nama_produk'] ?? '', 'Termometer Digital') !== false) {
        $fisik = $sistem - 1;
        $anomaliSkenario['rusak_alkes'] = $r;
    }
    // Skenario 6: Betadine Solution 15ml Batch BET-15-26 (Surplus +2 Botol)"""

assert old_loop in text, "old_loop not found"
text = text.replace(old_loop, new_loop)

# 3. Add Mutasi 5: Termometer Omron (Alkes Rusak) and adjust Mutasi 6 (Betadine)
old_mut = """// Mutasi 4: Barang Hilang (Alkes Serial Number)
$rHilangA = $anomaliSkenario['hilang_alkes'];
$targetSn = $pdo->query("SELECT id, serial_number FROM unit_serial WHERE id_variasi = {$rHilangA['id_variasi']} AND status = 'Tersedia' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$pdo->beginTransaction();
$pdo->query("UPDATE unit_serial SET status = 'Hilang', catatan = '[Opname $kodeSesi] Hilang saat audit' WHERE id = {$targetSn['id']}");
$pdo->query("UPDATE stok_toko SET stok = stok - 1 WHERE id_variasi = {$rHilangA['id_variasi']}");
$sisaToko4 = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rHilangA['id_variasi']}")->fetchColumn();
$pdo->query("
    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
    VALUES ({$rHilangA['id_variasi']}, NULL, 'kecil', 'Keluar', 'Manual', 'Barang Hilang', '$kodeSesi', 1, $sisaToko4, '[Stok Opname: $kodeSesi] SN: {$targetSn['serial_number']} dinyatakan hilang', $userIdAdmin)
");
$pdo->query("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Hilang (SN: {$targetSn['serial_number']})', dimutasi_oleh = $userIdAdmin, waktu_mutasi = NOW() WHERE id = {$rHilangA['id']}");
$pdo->query("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES ({$rHilangA['id']}, {$targetSn['id']}, '{$targetSn['serial_number']}', 'Kurang_Hilang')");
$pdo->commit();
echo "✓ 4. Tensimeter Omron dimutasi ke Kategori: Barang Hilang (SN: {$targetSn['serial_number']})\\n";

// Mutasi 5: Barang Surplus (Kelebihan Kirim Supplier / Ditemukan)"""

new_mut = """// Mutasi 4: Barang Hilang (Alkes Serial Number - Tensimeter)
$rHilangA = $anomaliSkenario['hilang_alkes'];
$targetSn = $pdo->query("SELECT id, serial_number FROM unit_serial WHERE id_variasi = {$rHilangA['id_variasi']} AND status = 'Tersedia' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$pdo->beginTransaction();
$pdo->query("UPDATE unit_serial SET status = 'Hilang', catatan = '[Opname $kodeSesi] Hilang saat audit' WHERE id = {$targetSn['id']}");
$pdo->query("UPDATE stok_toko SET stok = stok - 1 WHERE id_variasi = {$rHilangA['id_variasi']}");
$sisaToko4 = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rHilangA['id_variasi']}")->fetchColumn();
$pdo->query("
    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
    VALUES ({$rHilangA['id_variasi']}, NULL, 'kecil', 'Keluar', 'Manual', 'Barang Hilang', '$kodeSesi', 1, $sisaToko4, '[Stok Opname: $kodeSesi] SN: {$targetSn['serial_number']} dinyatakan hilang', $userIdAdmin)
");
$pdo->query("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Hilang (SN: {$targetSn['serial_number']})', dimutasi_oleh = $userIdAdmin, waktu_mutasi = NOW() WHERE id = {$rHilangA['id']}");
$pdo->query("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES ({$rHilangA['id']}, {$targetSn['id']}, '{$targetSn['serial_number']}', 'Kurang_Hilang')");
$pdo->commit();
echo "✓ 4. Tensimeter Omron dimutasi ke Kategori: Barang Hilang (SN: {$targetSn['serial_number']})\\n";

// Mutasi 5: Barang Rusak (Alkes Serial Number - Termometer Digital Omron)
$rRusakA = $anomaliSkenario['rusak_alkes'];
$targetSnTermo = $pdo->query("SELECT id, serial_number FROM unit_serial WHERE id_variasi = {$rRusakA['id_variasi']} AND status = 'Tersedia' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$pdo->beginTransaction();
$pdo->query("UPDATE unit_serial SET status = 'Retur/Rusak', catatan = '[Opname $kodeSesi] Cacat fisik / LCD pecah' WHERE id = {$targetSnTermo['id']}");
$pdo->query("UPDATE stok_toko SET stok = stok - 1 WHERE id_variasi = {$rRusakA['id_variasi']}");
$sisaToko5A = $pdo->query("SELECT stok FROM stok_toko WHERE id_variasi = {$rRusakA['id_variasi']}")->fetchColumn();
$pdo->query("
    INSERT INTO kartu_stok (id_variasi, id_batch, satuan_tipe, jenis_mutasi, kanal, alasan_mutasi, no_ref_dokumen, qty, sisa_stok, keterangan, dibuat_oleh)
    VALUES ({$rRusakA['id_variasi']}, NULL, 'kecil', 'Keluar', 'Manual', 'Barang Rusak', '$kodeSesi', 1, $sisaToko5A, '[Stok Opname: $kodeSesi] SN: {$targetSnTermo['serial_number']} cacat fisik/rusak', $userIdAdmin)
");
$pdo->query("UPDATE detail_opname_batch SET status_mutasi = 'Sudah Dimutasi', alasan_mutasi = 'Barang Rusak (SN: {$targetSnTermo['serial_number']})', dimutasi_oleh = $userIdAdmin, waktu_mutasi = NOW() WHERE id = {$rRusakA['id']}");
$pdo->query("INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES ({$rRusakA['id']}, {$targetSnTermo['id']}, '{$targetSnTermo['serial_number']}', 'Kurang_Rusak')");
$pdo->commit();
echo "✓ 5. Termometer Omron dimutasi ke Kategori: Barang Rusak (SN: {$targetSnTermo['serial_number']})\\n";

// Mutasi 6: Barang Surplus (Kelebihan Kirim Supplier / Ditemukan)"""

assert old_mut in text, "old_mut not found"
text = text.replace(old_mut, new_mut)
text = text.replace('echo "✓ 5. Betadine Solution', 'echo "✓ 6. Betadine Solution')

with open('scratch/run_full_opname_simulation.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated run_full_opname_simulation.php with 6 anomalies (Alkes Rusak + Hilang)!")

