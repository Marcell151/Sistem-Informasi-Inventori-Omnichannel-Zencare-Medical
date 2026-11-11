import re

# ==============================================================================
# 1. FIX STOK_OPNAME.PHP
# ==============================================================================
with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    opname = f.read()

# 1.A Hapus Tombol "Reset Database Demo" dari UI (Point 7)
btn_reset_pattern = re.compile(
    r'<!-- Tombol Reset Database Demo -->\s*<form method="POST" onsubmit="return confirm\([^\)]+\);"[^>]*>[\s\S]*?</form>',
    re.DOTALL
)
if btn_reset_pattern.search(opname):
    opname = btn_reset_pattern.sub('', opname)
    print("✓ Tombol 'Reset Database' berhasil dihapus dari UI stok_opname.php.")
else:
    print("! Pola tombol reset database tidak ditemukan / sudah dihapus.")

# 1.B Hapus / Ganti kata "HPP", "Akuntansi", "Audit" di stok_opname.php (Point 5)
opname = opname.replace('(non-akuntansi)', '(estimasi acuan kerugian)')
opname = opname.replace('HPP', 'Harga Beli')
opname = opname.replace('Akuntansi', 'Acuan Kerugian')
opname = opname.replace('Standar audit', 'Standar pemeriksaan fisik')
opname = opname.replace('standar audit', 'standar pemeriksaan fisik')
opname = opname.replace('log audit', 'log rekonsiliasi')
opname = opname.replace('Log audit', 'Log rekonsiliasi')
opname = opname.replace('Audit Menyeluruh', 'Pemeriksaan Menyeluruh')
opname = opname.replace('wewenang audit tertinggi', 'wewenang rekonsiliasi tertinggi')
opname = opname.replace('Jalur Audit', 'Jalur Rekonsiliasi')
opname = opname.replace('riwayat audit', 'riwayat verifikasi')
opname = opname.replace('TAB NAVIGASI AUDIT KHUSUS SUPERADMIN', 'TAB NAVIGASI VERIFIKASI KHUSUS SUPERADMIN')

# 1.C Perbaiki penghitungan Valuasi Kerugian di stok_opname.php agar menggunakan Harga Beli
old_val_query = """            // Hitung estimasi acuan kerugian persediaan (non-akuntansi) dari seluruh baris sesi
            $stmtVal = $pdo->prepare("
                SELECT dob.*, pv.satuan_besar, pv.satuan_kecil, pv.harga_jual_kecil, pv.harga_jual_besar
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
                $price = ($r['satuan_tipe'] === 'besar') ? floatval($r['harga_jual_besar'] ?? 0) : floatval($r['harga_jual_kecil'] ?? 0);
                if ($price <= 0) $price = 15000; // Harga acuan fallback realistis

                $fRusak = intval($r['fisik_rusak'] ?? 0);
                $fExp   = intval($r['fisik_expired'] ?? 0);
                $sel    = intval($r['selisih'] ?? 0);

                if ($fRusak > 0 || $fExp > 0 || $sel != 0) {
                    $anomaliCount++;
                }

                $estLossRusak += ($fRusak * $price);
                $estLossExp   += ($fExp * $price);
                if ($sel < 0) {
                    $qtyHilang = max(0, abs($sel) - $fRusak - $fExp);
                    $estLossHilang += ($qtyHilang * $price);
                }
            }
            $totalEstLoss = $estLossRusak + $estLossExp + $estLossHilang;"""

new_val_query = """            // Hitung estimasi nilai acuan kerugian persediaan berdasarkan Harga Beli satuan
            $stmtVal = $pdo->prepare("
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

if old_val_query in opname:
    opname = opname.replace(old_val_query, new_val_query)
    print("✓ Valuasi kerugian berbasis Harga Beli berhasil diperbarui di stok_opname.php.")
else:
    print("! Pola query valuasi tidak cocok.")

# 1.D Tampilkan Estimasi Nilai Acuan Kerugian di Baris Tabel Tahap 2
old_tahap2_selisih_td = """                                        <td class="py-3 px-3 text-center font-bold font-mono">
                                            <?php if ($selisih === 0): ?>
                                                <span class="text-emerald-700">0</span>
                                            <?php elseif ($selisih > 0): ?>
                                                <span class="text-blue-700">+<?= $selisih ?></span>
                                            <?php else: ?>
                                                <span class="text-rose-700"><?= $selisih ?></span>
                                            <?php endif; ?>
                                        </td>"""

new_tahap2_selisih_td = """                                        <td class="py-3 px-3 text-center font-mono">
                                            <div class="font-bold text-xs">
                                                <?php if ($selisih === 0): ?>
                                                    <span class="text-emerald-700">0</span>
                                                <?php elseif ($selisih > 0): ?>
                                                    <span class="text-blue-700">+<?= $selisih ?></span>
                                                <?php else: ?>
                                                    <span class="text-rose-700"><?= $selisih ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php
                                            // Tampilkan estimasi nilai acuan kerugian berbasis Harga Beli per baris
                                            $rasioIt = max(1, intval($it['rasio_konversi'] ?? 1));
                                            $hBeliIt = floatval($it['last_harga_beli'] ?? 0);
                                            if ($hBeliIt <= 0) $hBeliIt = floatval($it['harga_jual_besar'] ?? 0) * 0.8;
                                            if ($hBeliIt <= 0) $hBeliIt = floatval($it['harga_jual_kecil'] ?? 0) * $rasioIt * 0.8;
                                            if ($hBeliIt <= 0) $hBeliIt = 15000;
                                            $hargaBeliRow = ($it['satuan_tipe'] === 'besar') ? $hBeliIt : round($hBeliIt / $rasioIt);

                                            if ($selisih < 0) {
                                                $barisLoss = abs($selisih) * $hargaBeliRow;
                                                echo '<div class="text-[9px] text-rose-600 font-sans font-semibold mt-0.5">Beban: Rp ' . number_format($barisLoss, 0, ',', '.') . '</div>';
                                            } elseif ($selisih > 0) {
                                                echo '<div class="text-[9px] text-blue-600 font-sans font-semibold mt-0.5">Surplus: Rp 0</div>';
                                            }
                                            ?>
                                        </td>"""

if old_tahap2_selisih_td in opname:
    opname = opname.replace(old_tahap2_selisih_td, new_tahap2_selisih_td)
    print("✓ Tampilan detail kerugian per baris di Tahap 2 berhasil ditambahkan.")
else:
    print("! Pola kolom selisih Tahap 2 tidak cocok.")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(opname)
print("inventori/stok_opname.php saved.")

# ==============================================================================
# 2. FIX CETAK_BERITA_ACARA_OPNAME.PHP (Highlight Merah Baris Anomali & Hapus HPP)
# ==============================================================================
with open('inventori/cetak_berita_acara_opname.php', 'r', encoding='utf-8') as f:
    ba = f.read()

# Ganti HPP dengan Harga Beli
ba = ba.replace('HPP Modal', 'Harga Beli')
ba = ba.replace('(HPP):', '(Harga Beli):')
ba = ba.replace('Harga Modal', 'Harga Beli Acuan')
ba = ba.replace('HPP', 'Harga Beli')
ba = ba.replace('audit', 'verifikasi fisik')
ba = ba.replace('Audit', 'Verifikasi Fisik')

# Berikan highlight visual tebal pada baris yang berselisih/anomali
old_row_render = """                <!-- Baris Sub-Item / Batch / SN di Bawah Produk Tersebut -->
                <?php foreach ($grp['items'] as $it): 
                    $sel = intval($it['selisih'] ?? 0);
                ?>
                    <tr style="border-bottom: 1px solid #e2e8f0;">"""

new_row_render = """                <!-- Baris Sub-Item / Batch / SN di Bawah Produk Tersebut -->
                <?php foreach ($grp['items'] as $it): 
                    $sel = intval($it['selisih'] ?? 0);
                    $fRusakRow = intval($it['fisik_rusak'] ?? 0);
                    $fExpRow = intval($it['fisik_expired'] ?? 0);
                    $isAnomali = ($sel !== 0 || $fRusakRow > 0 || $fExpRow > 0);
                ?>
                    <tr style="border-bottom: 1px solid #cbd5e1; <?= $isAnomali ? 'background-color: #fff1f2; outline: 2px solid #e11d48; font-weight: 600;' : '' ?>">"""

if old_row_render in ba:
    ba = ba.replace(old_row_render, new_row_render)
    print("✓ Highlight merah tegas baris berselisih di Berita Acara berhasil diterapkan.")
else:
    print("! Pola baris Berita Acara tidak cocok.")

with open('inventori/cetak_berita_acara_opname.php', 'w', encoding='utf-8') as f:
    f.write(ba)
print("inventori/cetak_berita_acara_opname.php saved.")

# ==============================================================================
# 3. FIX DOKUMENTASI_TESTING.PHP (Hapus Mockup Browser & Buat Placeholder Screenshot)
# ==============================================================================
with open('inventori/dokumentasi_testing.php', 'r', encoding='utf-8') as f:
    dt = f.read()

# Hapus seluruh header mockup browser dengan 3 titik & URL localhost
mockup_header_pattern = re.compile(
    r'<div class="bg-slate-200 px-3 py-2 border-b border-slate-300 flex items-center justify-between">[\s\S]*?<span>http://localhost/inventory_zencare/inventori/stok_opname\.php[^<]*</span>[\s\S]*?<span class="text-\[9px\] text-slate-500 font-semibold hidden sm:inline">Peran: [^<]*</span>\s*</div>',
    re.DOTALL
)

matches_found = len(mockup_header_pattern.findall(dt))
if matches_found > 0:
    dt = mockup_header_pattern.sub('', dt)
    print(f"✓ Berhasil menghapus {matches_found} header mockup browser palsu dari dokumentasi_testing.php.")
else:
    print("! Mockup header pattern tidak ditemukan.")

# Hapus kata HPP, Bab 4, Dosen, Sidang jika masih ada
dt = dt.replace('HPP', 'Harga Beli')
dt = dt.replace('hpp', 'harga beli')
dt = dt.replace('Akuntansi', 'Acuan Finansial')

with open('inventori/dokumentasi_testing.php', 'w', encoding='utf-8') as f:
    f.write(dt)
print("inventori/dokumentasi_testing.php saved.")

