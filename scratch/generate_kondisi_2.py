# generate_kondisi_2.py
import os

with open('scratch/kondisi_2_template.php', 'w', encoding='utf-8') as f:
    f.write('''<?php else: ?>

            <?php
            // Hitung estimasi acuan kerugian persediaan (non-akuntansi) dari seluruh baris sesi
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
            $totalEstLoss = $estLossRusak + $estLossExp + $estLossHilang;
            ?>

            <!-- Banner Utama Sesi Aktif (Clean White ZenCare Design) -->
            <div class="p-5 rounded-2xl bg-white border border-zcBrd shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs bg-slate-100 text-slate-800 border border-slate-300 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider font-mono">
                                Sesi: <?= htmlspecialchars($activeSession['kode_sesi']) ?>
                            </span>
                            <?php if ($currentTahap === 'Penghitungan'): ?>
                                <span class="text-xs bg-amber-50 text-amber-800 border border-amber-200 font-bold px-2.5 py-0.5 rounded-full">
                                    Tahap 1: Penghitungan Fisik (Blind Count)
                                </span>
                            <?php else: ?>
                                <span class="text-xs bg-blue-50 text-blue-800 border border-blue-200 font-bold px-2.5 py-0.5 rounded-full">
                                    Tahap 2: Verifikasi &amp; Rekonsiliasi Selisih
                                </span>
                            <?php endif; ?>
                            <span class="text-[11px] text-zcMut">Dimulai: <?= date('d/m/Y H:i', strtotime($activeSession['dibekukan_pada'])) ?> WIB</span>
                        </div>
                        <p class="text-xs text-zcMut mt-1 leading-relaxed">
                            Status Server: Transaksi kasir POS, checkout toko daring, konversi UOM, dan penerimaan logistik sedang <strong class="text-zcTxt font-bold">DIBEKUKAN</strong>.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 self-start md:self-auto shrink-0 flex-wrap">
                    <!-- Link Cepat Dokumentasi Testing Bab 4 -->
                    <a href="dokumentasi_testing.php" target="_blank" class="px-3 py-2 rounded-xl border border-zc text-zc hover:bg-zc hover:text-white text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Dokumentasi Uji Coba</span>
                    </a>

                    <?php if ($currentTahap === 'Penghitungan'): ?>
                        <?php if ($canCountPhysical && $stats['belum_hitung'] > 0): ?>
                            <!-- Tombol Konfirmasi Simpan Hitungan Sekaligus ke Server (Kasir & Superadmin) -->
                            <button type="submit" form="form_bulk_simpan" id="btn_bulk_simpan" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Simpan seluruh hasil hitung fisik yang tertera di tabel sekaligus">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Simpan Semua Hitungan Fisik</span>
                            </button>
                        <?php endif; ?>

                        <?php if ($isSuperadmin && $stats['belum_hitung'] > 0): ?>
                            <!-- Tombol Auto-Generate Skenario Uji Coba Bab 4 (Khusus Superadmin) -->
                            <form method="POST" class="inline" onsubmit="return confirm('Jalankan Auto-Generate Skenario Uji Coba Bab 4? Sistem akan mengisi 121 baris fisik dengan 5 anomali terencana (Cefadroxil rusak, Sanmol expired, Amoxicillin hilang, Omron hilang, Betadine surplus) dan 116 item cocok.');">
                                <input type="hidden" name="aksi" value="isi_cepat_demo">
                                <button type="submit" class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Pintasan Uji Coba: Generate 121 hasil fisik dengan 5 anomali terencana">
                                    <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                    <span>Auto-Fill Skenario Uji Coba (121 Item)</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if ($canVerify): ?>
                            <!-- Tombol Tutup Penghitungan & Buka Verifikasi -->
                            <form method="POST" class="inline" onsubmit="return confirm('Tutup tahap penghitungan fisik? Seluruh formulir pencacahan akan dikunci dan antarmuka beralih ke Verifikasi Selisih.');">
                                <input type="hidden" name="aksi" value="tutup_penghitungan">
                                <button type="submit" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- TAHAP VERIFIKASI -->
                        <?php 
                        $pendingMutasiCount = $stats['berselisih'] - $stats['sudah_dimutasi'];
                        if ($canVerify && $pendingMutasiCount > 0): 
                        ?>
                            <button type="button" onclick="document.getElementById('modal_rekonsiliasi_masal').classList.remove('hidden')" class="px-3 py-2 rounded-xl border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="Rekonsiliasi sekaligus seluruh <?= $pendingMutasiCount ?> selisih yang terdeteksi">
                                <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                                <span>Rekonsiliasi Semua Selisih (<?= $pendingMutasiCount ?>)</span>
                            </button>
                        <?php endif; ?>

                        <?php if ($isSuperadmin): ?>
                            <?php 
                            $bisaSelesai = ($stats['belum_hitung'] === 0 && ($stats['berselisih'] - $stats['sudah_dimutasi']) === 0);
                            ?>
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin seluruh hasil fisik sudah diverifikasi dan ingin menutup sesi opname ini? Pembekuan sistem akan dicabut.');">
                                <input type="hidden" name="aksi" value="selesaikan_sesi">
                                <button type="submit" <?= !$bisaSelesai ? 'disabled' : '' ?> class="px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 <?= $bisaSelesai ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer' : 'bg-slate-100 border border-slate-200 text-slate-400 cursor-not-allowed' ?>" title="<?= !$bisaSelesai ? 'Sesi hanya bisa diselesaikan jika semua item sudah dihitung dan semua selisih sudah dimutasi!' : 'Buka kembali sistem normal' ?>">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span>Selesaikan Sesi (Buka Kunci)</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($isSuperadmin): ?>
                        <button onclick="document.getElementById('modal_batal_sesi').classList.remove('hidden')" class="px-3 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition cursor-pointer" title="Jalur Darurat jika sesi perlu dibatalkan">
                            Batal Sesi
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ringkasan Metrik Dashboard Opname -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-zcMut font-bold uppercase tracking-wider block">Total Baris</span>
                    <span class="text-xl font-black text-zcTxt mt-1 block"><?= $stats['total'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-amber-700 font-bold uppercase tracking-wider block">Belum Dihitung</span>
                    <span class="text-xl font-black text-amber-600 mt-1 block"><?= $stats['belum_hitung'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider block">Sudah Dihitung</span>
                    <span class="text-xl font-black text-emerald-600 mt-1 block"><?= $stats['sudah_hitung'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-blue-700 font-bold uppercase tracking-wider block">Sesuai (Selisih 0)</span>
                    <span class="text-xl font-black text-blue-600 mt-1 block"><?= $stats['sesuai'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-rose-700 font-bold uppercase tracking-wider block">Ada Selisih</span>
                    <span class="text-xl font-black text-rose-600 mt-1 block"><?= $stats['berselisih'] ?></span>
                </div>
                <div class="p-3.5 rounded-xl bg-white border border-zcBrd shadow-2xs">
                    <span class="text-[10px] text-slate-600 font-bold uppercase tracking-wider block">Sudah Dimutasi</span>
                    <span class="text-xl font-black text-slate-800 mt-1 block"><?= $stats['sudah_dimutasi'] ?> / <?= $stats['berselisih'] ?></span>
                </div>
            </div>

            <!-- JIKA TAHAP 2: VERIFIKASI -> TAMPILKAN ESTIMASI NILAI ACUAN KERUGIAN PERSEDIAAN -->
            <?php if ($currentTahap === 'Verifikasi'): ?>
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-3 border-b border-slate-100 gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-700"></span>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Estimasi Nilai Acuan Dampak Persediaan (Non-Akuntansi)</h3>
                        </div>
                        <span class="text-[10px] text-slate-400 italic">Valuasi operasional internal berbasis harga acuan produk</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 font-semibold block">Kerugian Barang Rusak:</span>
                            <span class="text-sm font-bold text-slate-900 mt-0.5 block">Rp <?= number_format($estLossRusak, 0, ',', '.') ?></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 font-semibold block">Kerugian Barang Expired:</span>
                            <span class="text-sm font-bold text-slate-900 mt-0.5 block">Rp <?= number_format($estLossExp, 0, ',', '.') ?></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-[10px] text-slate-500 font-semibold block">Kerugian Barang Hilang:</span>
                            <span class="text-sm font-bold text-slate-900 mt-0.5 block">Rp <?= number_format($estLossHilang, 0, ',', '.') ?></span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-100 border border-slate-300">
                            <span class="text-[10px] text-slate-600 font-bold block">Total Estimasi Dampak:</span>
                            <span class="text-sm font-black text-slate-900 mt-0.5 block">Rp <?= number_format($totalEstLoss, 0, ',', '.') ?></span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2.5">
                        * Catatan: Angka di atas merupakan indikator acuan pengendalian internal untuk mendukung keputusan mutasi dan berita acara, bukan laporan laba rugi akuntansi formal.
                    </p>
                </div>
            <?php endif; ?>

            <!-- JIKA TAHAP 1: PENGHITUNGAN & USER ADALAH ADMIN -> MONITORING PROGRESS ONLY -->
            <?php if ($currentTahap === 'Penghitungan' && $isAdmin && !$isSuperadmin): ?>
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div class="text-xs text-slate-700 leading-relaxed">
                            <strong class="font-bold text-slate-900 block">SOP Pengendalian Internal: Pemisahan Tugas Pencacahan Fisik</strong>
                            Sesuai aturan tata kelola gudang, staf gudang (Admin) <strong>tidak diperkenankan melakukan penghitungan fisik</strong> di lapangan untuk mencegah manipulasi persediaan. Penghitungan fisik dilakukan secara mandiri oleh staf Kasir lapangan. Anda dapat memantau progres pencatatan kasir di bawah ini dan menutup tahap penghitungan jika seluruh rak telah selesai dihitung.
                        </div>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Status Pencatatan Fisik oleh Kasir</h3>
                        <span class="text-xs font-medium text-slate-500">Progres: <strong><?= $stats['sudah_hitung'] ?> / <?= $stats['total'] ?></strong> baris (<?= round(($stats['total'] > 0 ? ($stats['sudah_hitung'] / $stats['total'] * 100) : 0)) ?>%)</span>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 text-slate-600 font-semibold text-[11px] border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">#</th>
                                    <th class="py-2.5 px-3">Produk &amp; Variasi</th>
                                    <th class="py-2.5 px-3">Batch / SN Tracker</th>
                                    <th class="py-2.5 px-3 text-center">Satuan</th>
                                    <th class="py-2.5 px-3 text-center">Status Pencatatan Kasir</th>
                                    <th class="py-2.5 px-3">Waktu &amp; Petugas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php 
                                $noProg = 1;
                                foreach ($detailItems as $di): 
                                ?>
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-2.5 px-3 text-center text-slate-400 font-mono"><?= $noProg++ ?></td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-semibold text-slate-800"><?= htmlspecialchars($di['nama_produk']) ?></div>
                                        <div class="text-[11px] text-slate-500"><?= htmlspecialchars($di['nama_variasi']) ?></div>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px] text-slate-700">
                                        <?= htmlspecialchars($di['no_batch'] ?: ($di['jenis_komoditas'] === 'Alkes' ? 'SN Tracking' : '-')) ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center text-slate-600"><?= htmlspecialchars($di['nama_satuan']) ?></td>
                                    <td class="py-2.5 px-3 text-center">
                                        <?php if ($di['status_hitung'] === 'Sudah'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Telah Dihitung</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Menunggu Kasir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                        <?php if ($di['status_hitung'] === 'Sudah'): ?>
                                            <?= htmlspecialchars($di['nama_penghitung'] ?? 'Kasir') ?> (<?= $di['waktu_hitung'] ? date('H:i:s', strtotime($di['waktu_hitung'])) : '-' ?>)
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <form method="POST" onsubmit="return confirm('Tutup tahap penghitungan fisik? Formulir pencacahan kasir akan dikunci dan sistem beralih ke Verifikasi Selisih.');">
                            <input type="hidden" name="aksi" value="tutup_penghitungan">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                            </button>
                        </form>
                    </div>
                </div>

            <!-- JIKA TAHAP 1: PENGHITUNGAN & USER ADALAH KASIR ATAU SUPERADMIN -> FORM HITUNG BUTA KONDISI -->
            <?php elseif ($currentTahap === 'Penghitungan'): ?>

                <!-- Banner Petunjuk Kasir (Blind Count) -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-950 text-xs shadow-2xs flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-white border border-amber-300 text-amber-800 flex items-center justify-center shrink-0 font-bold">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <div class="leading-relaxed">
                        <strong class="font-bold text-amber-950 block">Panduan Pencacahan Fisik (Blind Count Multi-Kondisi)</strong>
                        Periksa fisik barang di rak etalase secara independen. Masukkan jumlah fisik ke dalam 3 kolom kondisi:
                        <strong>Kondisi Baik</strong> (layak jual), <strong>Kondisi Rusak</strong> (cacat kemasan/botol pecah), dan <strong>Kondisi Expired</strong> (melewati tanggal kedaluwarsa). Sesuai standar audit, angka sistem dan selisih dirahasiakan selama tahap penghitungan.
                    </div>
                </div>

                <!-- Bilah Pemindai Barcode / Fast SKU Locator -->
                <div class="p-3.5 bg-white border border-zcBrd rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                <span>Pemindai Barcode / Pencarian Cepat Rak</span>
                            </div>
                            <p class="text-[10px] text-slate-500">Scan barcode fisik / ketikkan SKU, Batch, atau SN untuk langsung menyorot baris input.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-1 max-w-md">
                        <input type="text" id="barcode_scan_input" placeholder="Scan Barcode / SKU / No. Batch / SN..." class="w-full border border-slate-300 focus:border-slate-800 rounded-xl px-3 py-1.5 text-xs font-mono font-bold bg-white text-slate-900 shadow-2xs outline-none">
                        <button type="button" onclick="handleBarcodeScan()" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shrink-0 cursor-pointer flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Cari</span>
                        </button>
                    </div>
                </div>

                <!-- Form Bulk Simpan Seluruh Hitungan -->
                <form id="form_bulk_simpan" method="POST">
                    <input type="hidden" name="aksi" value="simpan_semua_hitung_fisik">

                    <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-50 border-b border-zcBrd text-slate-600 font-semibold text-[11px]">
                                    <tr>
                                        <th class="py-3 px-3 w-8 text-center">#</th>
                                        <th class="py-3 px-3">Komoditas &amp; Produk</th>
                                        <th class="py-3 px-3">Batch / SN Tracker</th>
                                        <th class="py-3 px-3 text-center">Satuan</th>
                                        <th class="py-3 px-3 text-center w-28 bg-emerald-50/50 border-x border-emerald-100">
                                            <div class="text-emerald-900 font-bold">KONDISI BAIK</div>
                                            <div class="text-[9px] text-emerald-700">(Layak Jual)</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 bg-amber-50/50 border-r border-amber-100">
                                            <div class="text-amber-900 font-bold">KONDISI RUSAK</div>
                                            <div class="text-[9px] text-amber-700">(Cacat Fisik)</div>
                                        </th>
                                        <th class="py-3 px-3 text-center w-28 bg-rose-50/50 border-r border-rose-100">
                                            <div class="text-rose-900 font-bold">KONDISI EXPIRED</div>
                                            <div class="text-[9px] text-rose-700">(Kedaluwarsa)</div>
                                        </th>
                                        <th class="py-3 px-3 text-center whitespace-nowrap min-w-[110px]">Aksi Baris</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (empty($groupedDetailItems)): ?>
                                        <tr>
                                            <td colspan="8" class="py-8 text-center text-zcMut italic">Tidak ada item yang ditemukan.</td>
                                        </tr>
                                    <?php else: 
                                        $pNo = 1;
                                        foreach ($groupedDetailItems as $grp): 
                                    ?>
                                        <!-- Header Produk Induk & Variasi -->
                                        <tr class="bg-slate-100/80 border-t-2 border-slate-300 font-semibold text-slate-800 text-xs">
                                            <td class="py-2.5 px-3 text-center text-slate-500 font-bold"><?= $pNo++ ?></td>
                                            <td colspan="3" class="py-2.5 px-3">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $grp['jenis_komoditas'] === 'Obat' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                                        <?= $grp['jenis_komoditas'] === 'Obat' ? 'Obat' : 'Alkes (SN)' ?>
                                                    </span>
                                                    <span class="text-sm font-bold text-slate-900"><?= htmlspecialchars($grp['nama_produk']) ?></span>
                                                    <span class="text-slate-600 font-medium">— <?= htmlspecialchars($grp['nama_variasi']) ?></span>
                                                    <?php if (!empty($grp['sku_variasi'])): ?>
                                                        <span class="text-[10px] font-mono text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">[<?= htmlspecialchars($grp['sku_variasi']) ?>]</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td colspan="4" class="py-2.5 px-3 text-right">
                                                <?php if ($grp['jenis_komoditas'] === 'Obat' && $grp['rasio_konversi'] > 1): ?>
                                                    <span class="text-[11px] font-medium text-slate-700 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                                        Rasio: 1 <?= htmlspecialchars($grp['satuan_besar']) ?> = <?= $grp['rasio_konversi'] ?> <?= htmlspecialchars($grp['satuan_kecil']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Baris Sub-Item / Batch / Unit Fisik -->
                                        <?php foreach ($grp['items'] as $it): 
                                            $isCalculated = ($it['status_hitung'] === 'Sudah');
                                            $searchableStr = htmlspecialchars($it['nama_produk'] . ' ' . $it['nama_variasi'] . ' ' . ($it['sku_variasi'] ?? '') . ' ' . ($it['no_batch'] ?? ''));
                                            $isObatExp = ($it['jenis_komoditas'] === 'Obat' && !empty($it['tgl_exp']) && strtotime($it['tgl_exp']) < strtotime(date('Y-m-d')));
                                            $isAlkes = ($it['jenis_komoditas'] === 'Alkes');
                                        ?>
                                        <tr class="hover:bg-slate-50/70 transition" data-barcode-search="<?= $searchableStr ?>">
                                            <td class="py-3 px-3 text-center text-slate-400 font-mono text-xs">></td>
                                            
                                            <td class="py-3 px-3">
                                                <div class="text-slate-700 font-medium">
                                                    <?= htmlspecialchars($it['satuan_tipe'] === 'besar' ? 'Satuan Besar (Dus/Box)' : 'Satuan Kecil (Eceran)') ?>
                                                </div>
                                            </td>

                                            <td class="py-3 px-3">
                                                <?php if (!$isAlkes): ?>
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="font-mono font-bold text-slate-800 text-[11px]"><?= htmlspecialchars($it['no_batch'] ?: '-') ?></span>
                                                        <?php if ($isObatExp): ?>
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-200">KEDALUWARSA</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-[10px] text-zcMut">Exp: <?= $it['tgl_exp'] ? date('d/m/Y', strtotime($it['tgl_exp'])) : '-' ?></div>
                                                <?php else: 
                                                    $allSn = $alkesSnMap[$it['id_variasi']] ?? [];
                                                    $snTersedia = array_values(array_filter($allSn, fn($s) => $s['status'] === 'Tersedia'));
                                                ?>
                                                    <div class="text-[10px] text-emerald-800 font-semibold">
                                                        SN Tracker (<?= count($snTersedia) ?> Unit Tersedia di Sistem)
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <td class="py-3 px-3 text-center font-semibold text-slate-700">
                                                <?= htmlspecialchars($it['nama_satuan']) ?>
                                            </td>

                                            <!-- INPUT FISIK BAIK -->
                                            <td class="py-2.5 px-3 text-center bg-emerald-50/20 border-x border-emerald-100">
                                                <?php if ($isObatExp): ?>
                                                    <input type="hidden" name="bulk_fisik_baik[<?= $it['id'] ?>]" value="0">
                                                    <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 block">Kunci (0) Expired</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_baik[<?= $it['id'] ?>]" 
                                                           id="input_baik_<?= $it['id'] ?>"
                                                           value="<?= $it['fisik_baik'] !== null ? $it['fisik_baik'] : '' ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border border-emerald-300 rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-emerald-600 bg-white shadow-2xs">
                                                <?php endif; ?>
                                            </td>

                                            <!-- INPUT FISIK RUSAK -->
                                            <td class="py-2.5 px-3 text-center bg-amber-50/20 border-r border-amber-100">
                                                <input type="number" 
                                                       name="bulk_fisik_rusak[<?= $it['id'] ?>]" 
                                                       id="input_rusak_<?= $it['id'] ?>"
                                                       value="<?= intval($it['fisik_rusak'] ?? 0) ?>"
                                                       min="0" 
                                                       placeholder="0"
                                                       class="w-16 border border-amber-300 rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-amber-600 bg-white shadow-2xs">
                                            </td>

                                            <!-- INPUT FISIK EXPIRED -->
                                            <td class="py-2.5 px-3 text-center bg-rose-50/20 border-r border-rose-100">
                                                <?php if ($isAlkes): ?>
                                                    <input type="hidden" name="bulk_fisik_expired[<?= $it['id'] ?>]" value="0">
                                                    <span class="text-[10px] text-slate-400 font-mono">-</span>
                                                <?php else: ?>
                                                    <input type="number" 
                                                           name="bulk_fisik_expired[<?= $it['id'] ?>]" 
                                                           id="input_exp_<?= $it['id'] ?>"
                                                           value="<?= intval($it['fisik_expired'] ?? 0) ?>"
                                                           min="0" 
                                                           placeholder="0"
                                                           class="w-16 border <?= $isObatExp ? 'border-rose-400 bg-rose-50/40 text-rose-900 font-black' : 'border-rose-300 bg-white' ?> rounded-lg py-1 px-1.5 text-center font-mono font-bold text-xs focus:outline-none focus:border-rose-600 shadow-2xs">
                                                <?php endif; ?>
                                            </td>

                                            <!-- AKSI BARIS -->
                                            <td class="py-2.5 px-3 text-center">
                                                <?php if ($isCalculated): ?>
                                                    <div class="text-[10px] text-emerald-700 font-bold">Tersimpan</div>
                                                    <div class="text-[9px] text-slate-400"><?= htmlspecialchars($it['nama_penghitung'] ?? 'Kasir') ?></div>
                                                <?php else: ?>
                                                    <span class="text-[10px] text-amber-700 font-medium">Belum Simpan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>

            <!-- JIKA TAHAP 2: VERIFIKASI (ADMIN & SUPERADMIN) -> TABEL FOKUS ANOMALI & 1-KLIK MUTASI -->
            <?php else: ?>

                <!-- Notice Otomatisasi Filter Anomali Sesuai Arahan Dosen -->
                <?php if ($filterSts === 'berselisih'): ?>
                    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-600 shrink-0"></span>
                            <span><strong>Fokus Anomali Terfilter Otomatis:</strong> Menampilkan <strong><?= count($detailItems) ?> barang dengan selisih/anomali fisik</strong>. Sebanyak <strong><?= $stats['sesuai'] ?> barang yang cocok (0 selisih) otomatis disembunyikan</strong> agar Anda langsung fokus menyelesaikan rekonsiliasi.</span>
                        </div>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=semua&q=<?= urlencode($searchQ) ?>" class="text-[11px] font-bold text-slate-900 underline hover:text-black shrink-0">Tampilkan Semua Data Termasuk yang Cocok &rarr;</a>
                    </div>
                <?php else: ?>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3 text-xs text-slate-700">
                        <span>Menampilkan seluruh <?= count($detailItems) ?> data persediaan.</span>
                        <a href="?komoditas=<?= urlencode($filterKom) ?>&status_eval=berselisih&q=<?= urlencode($searchQ) ?>" class="text-[11px] font-bold text-rose-700 underline shrink-0">&larr; Kembali ke Fokus Anomali Saja</a>
                    </div>
                <?php endif; ?>

                <!-- Tabel Rekonsiliasi Anomali & Eksekusi Mutasi -->
                <div class="bg-white border border-zcBrd rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 border-b border-zcBrd text-slate-600 font-semibold text-[11px]">
                                <tr>
                                    <th class="py-3 px-3 w-8 text-center">#</th>
                                    <th class="py-3 px-3">Komoditas &amp; Produk</th>
                                    <th class="py-3 px-3">Batch / SN Tracker</th>
                                    <th class="py-3 px-3 text-center">Satuan</th>
                                    <th class="py-3 px-3 text-center">Fisik Baik</th>
                                    <th class="py-3 px-3 text-center">Fisik Rusak</th>
                                    <th class="py-3 px-3 text-center">Fisik Exp</th>
                                    <th class="py-3 px-3 text-center">Stok Sistem</th>
                                    <th class="py-3 px-3 text-center">Selisih Buku</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap min-w-[100px]">Status</th>
                                    <th class="py-3 px-3 text-center whitespace-nowrap min-w-[150px]">Tindakan Mutasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($groupedDetailItems)): ?>
                                    <tr>
                                        <td colspan="11" class="py-8 text-center text-zcMut italic">Tidak ada item anomali yang perlu disesuaikan. Seluruh stok cocok!</td>
                                    </tr>
                                <?php else: 
                                    $pNo = 1;
                                    foreach ($groupedDetailItems as $grp): 
                                ?>
                                    <!-- Header Produk -->
                                    <tr class="bg-slate-100/80 border-t-2 border-slate-300 font-semibold text-slate-800 text-xs">
                                        <td class="py-2 px-3 text-center text-slate-500 font-bold"><?= $pNo++ ?></td>
                                        <td colspan="4" class="py-2 px-3">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider <?= $grp['jenis_komoditas'] === 'Obat' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                                    <?= $grp['jenis_komoditas'] === 'Obat' ? 'Obat' : 'Alkes (SN)' ?>
                                                </span>
                                                <span class="text-sm font-bold text-slate-900"><?= htmlspecialchars($grp['nama_produk']) ?></span>
                                                <span class="text-slate-600 font-medium">— <?= htmlspecialchars($grp['nama_variasi']) ?></span>
                                            </div>
                                        </td>
                                        <td colspan="6" class="py-2 px-3 text-right">
                                            <span class="text-[10px] text-slate-500"><?= htmlspecialchars($grp['satuan_besar'] ? "1 {$grp['satuan_besar']} = {$grp['rasio_konversi']} {$grp['satuan_kecil']}" : 'Satuan Tunggal') ?></span>
                                        </td>
                                    </tr>

                                    <?php foreach ($grp['items'] as $it): 
                                        $fBaik = intval($it['fisik_baik'] ?? $it['hitung_fisik'] ?? 0);
                                        $fRusak = intval($it['fisik_rusak'] ?? 0);
                                        $fExp = intval($it['fisik_expired'] ?? 0);
                                        $sistem = intval($it['stok_sistem_fisik']);
                                        $selisih = intval($it['selisih'] ?? 0);
                                        $isMutated = ($it['status_mutasi'] === 'Sudah Dimutasi');
                                    ?>
                                    <tr class="hover:bg-slate-50/70 transition <?= ($selisih != 0 || $fRusak > 0 || $fExp > 0) && !$isMutated ? 'bg-rose-50/20' : '' ?>">
                                        <td class="py-3 px-3 text-center text-slate-400 font-mono text-xs">></td>
                                        <td class="py-3 px-3 font-medium text-slate-700">
                                            <?= htmlspecialchars($it['satuan_tipe'] === 'besar' ? 'Satuan Besar (Dus/Box)' : 'Satuan Kecil (Eceran)') ?>
                                        </td>
                                        <td class="py-3 px-3 font-mono text-[11px] text-slate-700">
                                            <?= htmlspecialchars($it['no_batch'] ?: ($it['jenis_komoditas'] === 'Alkes' ? 'SN Tracking' : '-')) ?>
                                            <?php if ($it['tgl_exp']): ?>
                                                <div class="text-[10px] text-slate-400 font-sans">Exp: <?= date('d/m/Y', strtotime($it['tgl_exp'])) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center font-semibold text-slate-700"><?= htmlspecialchars($it['nama_satuan']) ?></td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-800 font-mono"><?= $fBaik ?></td>
                                        <td class="py-3 px-3 text-center font-bold font-mono <?= $fRusak > 0 ? 'text-amber-700 bg-amber-50/50' : 'text-slate-400' ?>"><?= $fRusak ?></td>
                                        <td class="py-3 px-3 text-center font-bold font-mono <?= $fExp > 0 ? 'text-rose-700 bg-rose-50/50' : 'text-slate-400' ?>"><?= $fExp ?></td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-700 font-mono"><?= $sistem ?></td>
                                        <td class="py-3 px-3 text-center font-bold font-mono">
                                            <?php if ($selisih === 0): ?>
                                                <span class="text-emerald-700">0</span>
                                            <?php elseif ($selisih > 0): ?>
                                                <span class="text-blue-700">+<?= $selisih ?></span>
                                            <?php else: ?>
                                                <span class="text-rose-700"><?= $selisih ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($isMutated): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Sudah Dimutasi</span>
                                                <div class="text-[9px] text-slate-400 mt-0.5"><?= htmlspecialchars($it['alasan_mutasi'] ?? '') ?></div>
                                            <?php elseif ($selisih === 0 && $fRusak === 0 && $fExp === 0): ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Sesuai / Pas</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200">Perlu Mutasi</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($isMutated): ?>
                                                <span class="text-[11px] text-emerald-700 font-semibold">Tuntas Direkonsiliasi</span>
                                            <?php else: ?>
                                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                    <?php if ($fRusak > 0): ?>
                                                        <!-- Tombol 1-Klik Mutasi Rusak -->
                                                        <form method="POST" class="inline" onsubmit="return confirm('Eksekusi Mutasi Barang Rusak sebanyak <?= $fRusak ?> <?= $it['nama_satuan'] ?> untuk <?= addslashes($it['nama_produk']) ?>? Stok akan dipotong dan dicatat di Kartu Stok.');">
                                                            <input type="hidden" name="aksi" value="mutasi_1klik_rusak">
                                                            <input type="hidden" name="id_detail" value="<?= $it['id'] ?>">
                                                            <button type="submit" class="px-2 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer" title="Eksekusi 1-Klik Mutasi Rusak">
                                                                Mutasi Rusak (<?= $fRusak ?>)
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($fExp > 0): ?>
                                                        <!-- Tombol 1-Klik Mutasi Expired -->
                                                        <form method="POST" class="inline" onsubmit="return confirm('Eksekusi Mutasi Barang Kedaluwarsa sebanyak <?= $fExp ?> <?= $it['nama_satuan'] ?> untuk <?= addslashes($it['nama_produk']) ?>? Stok akan dipotong dan dicatat di Kartu Stok.');">
                                                            <input type="hidden" name="aksi" value="mutasi_1klik_expired">
                                                            <input type="hidden" name="id_detail" value="<?= $it['id'] ?>">
                                                            <button type="submit" class="px-2 py-1 rounded-lg bg-rose-700 hover:bg-rose-800 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer" title="Eksekusi 1-Klik Mutasi Expired">
                                                                Mutasi Expired (<?= $fExp ?>)
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($selisih < 0): ?>
                                                        <!-- Buka Modal Mutasi Defisit / Hilang -->
                                                        <button type="button" onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer">
                                                            Mutasi Hilang
                                                        </button>
                                                    <?php elseif ($selisih > 0): ?>
                                                        <!-- Buka Modal Mutasi Koreksi Lebih -->
                                                        <button type="button" onclick="openModalMutasi(<?= htmlspecialchars(json_encode($it)) ?>)" class="px-2 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold transition shadow-2xs cursor-pointer">
                                                            Koreksi Lebih
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>
''')

print("Template kondisi_2 written successfully.")

