with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Remove the Reset Database form in HTML
form_start = '<!-- Tombol Reset Database Demo -->'
form_end = '</form>'

pos_start = text.find(form_start)
if pos_start != -1:
    pos_end = text.find(form_end, pos_start) + len(form_end)
    text = text[:pos_start] + text[pos_end:]
    print("✓ Reset Database button form removed from HTML.")
else:
    print("! form_start not found.")

# 2. Update Valuasi query to use last_harga_beli
val_start = '$stmtVal = $pdo->prepare("'
pos_val_start = text.find(val_start)

val_end = '$totalEstLoss = $estLossRusak + $estLossExp + $estLossHilang;'
pos_val_end = text.find(val_end)

if pos_val_start != -1 and pos_val_end != -1:
    pos_val_end += len(val_end)
    new_val_code = """$stmtVal = $pdo->prepare("
                SELECT dob.*, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,
                       pv.harga_jual_kecil, pv.harga_jual_besar,
                       (SELECT pd.harga_beli FROM penerimaan_detail pd WHERE pd.id_variasi = pv.id ORDER BY pd.id DESC LIMIT 1) AS last_harga_beli
                FROM detail_opname_batch dob
                JOIN produk_variasi pv ON dob.id_variasi = pv.id
                WHERE dob.id_sesi = ?
            ");
            $stmtVal->execute([$activeSession['id']]);
            $allSesiRows = $stmtVal->fetchAll(PDO::FETCH_ASSOC);

            $estLossRusak = 0;
            $estLossExp = 0;
            $estLossHilang = 0;
            $anomaliCount = 0;

            foreach ($allSesiRows as $r) {
                $rasio = max(1, intval($r['rasio_konversi'] ?? 1));
                $hBeli = floatval($r['last_harga_beli'] ?? 0);
                if ($hBeli <= 0) {
                    $hBeli = floatval($r['harga_jual_besar'] ?? 0) * 0.8;
                }
                if ($hBeli <= 0) {
                    $hBeli = floatval($r['harga_jual_kecil'] ?? 0) * $rasio * 0.8;
                }
                if ($hBeli <= 0) $hBeli = 15000;

                // Harga Beli per satuan baris (besar vs kecil)
                $hargaBeliSatuan = ($r['satuan_tipe'] === 'besar') ? $hBeli : round($hBeli / $rasio);

                $fRusak = intval($r['fisik_rusak'] ?? 0);
                $fExp   = intval($r['fisik_expired'] ?? 0);
                $sel    = intval($r['selisih'] ?? 0);

                if ($fRusak > 0 || $fExp > 0 || $sel != 0) {
                    $anomaliCount++;
                }

                $estLossRusak += ($fRusak * $hargaBeliSatuan);
                $estLossExp   += ($fExp * $hargaBeliSatuan);
                if ($sel < 0) {
                    $qtyHilang = max(0, abs($sel) - $fRusak - $fExp);
                    $estLossHilang += ($qtyHilang * $hargaBeliSatuan);
                }
                // Kelebihan stok (Surplus) = Kerugian Rp 0
            }
            $totalEstLoss = $estLossRusak + $estLossExp + $estLossHilang;"""

    text = text[:pos_val_start] + new_val_code + text[pos_val_end:]
    print("✓ Valuasi query replaced with Harga Beli calculation.")
else:
    print("! Valuasi positions not found.")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated inventori/stok_opname.php successfully.")

