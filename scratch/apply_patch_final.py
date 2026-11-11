import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Backend handler for tutup_penghitungan
old_backend = """    // --- 2A3. TUTUP PENGHITUNGAN & BUKA VERIFIKASI (Admin & Superadmin) ---
    elseif ($aksi === 'tutup_penghitungan') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang menutup tahap penghitungan.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $idSesi = $activeSession['id'];
                $pdo->prepare("
                    UPDATE sesi_opname
                    SET tahap = 'Verifikasi',
                        ditutup_hitung_oleh = ?,
                        ditutup_hitung_pada = NOW()
                    WHERE id = ?
                ")->execute([$userId, $idSesi]);

                header("Location: stok_opname.php?status_eval=berselisih&msg=" . urlencode("Tahap penghitungan fisik berhasil DITUTUP! Formulir pencacahan terkunci dan antarmuka verifikasi selisih dibuka. Barang yang sesuai otomatis disembunyikan.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal menutup penghitungan: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }"""

new_backend = """    // --- 2A3. BUKA VERIFIKASI SELISIH (Admin & Superadmin) ---
    elseif ($aksi === 'tutup_penghitungan') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang membuka verifikasi selisih.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $idSesi = $activeSession['id'];

                // Validasi ketat: Kasir harus menyelesaikan perhitungan fisik rak terlebih dahulu
                $stmtCheckUncounted = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM detail_opname_batch 
                    WHERE id_sesi = ? AND hitung_fisik IS NULL
                ");
                $stmtCheckUncounted->execute([$idSesi]);
                $uncountedCount = (int)$stmtCheckUncounted->fetchColumn();

                if ($uncountedCount > 0) {
                    $msg = "Tahap verifikasi belum dapat dibuka! Masih ada $uncountedCount item yang belum dihitung fisiknya oleh kasir. Harap tunggu tim kasir menyelesaikan dan menyimpan seluruh hitungan fisik.";
                    $msgType = 'error';
                } else {
                    $pdo->prepare("
                        UPDATE sesi_opname
                        SET tahap = 'Verifikasi',
                            ditutup_hitung_oleh = ?,
                            ditutup_hitung_pada = NOW()
                        WHERE id = ?
                    ")->execute([$userId, $idSesi]);

                    header("Location: stok_opname.php?status_eval=berselisih&msg=" . urlencode("Tahap Verifikasi Selisih berhasil dibuka! Formulir pencacahan kasir terkunci dan antarmuka verifikasi aktif. Barang yang sesuai otomatis disembunyikan.") . "&type=success");
                    exit;
                }
            } catch (Exception $e) {
                $msg = "Gagal membuka verifikasi selisih: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }"""

if old_backend in content:
    content = content.replace(old_backend, new_backend)
    print("✓ Backend handler replaced successfully.")
else:
    print("! Backend handler pattern not found exactly, searching...")

# 2. Update Toolbar Button
old_toolbar = """                            <?php if ($canVerify): ?>
                                <!-- 4. Tombol Tutup Penghitungan & Buka Verifikasi -->
                                <form method="POST" class="inline" onsubmit="return confirm('Tutup tahap penghitungan fisik? Seluruh formulir pencacahan akan dikunci dan antarmuka beralih ke Verifikasi Selisih.');">
                                    <input type="hidden" name="aksi" value="tutup_penghitungan">
                                    <button type="submit" class="h-9 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                        <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                                    </button>
                                </form>
                            <?php endif; ?>"""

new_toolbar = """                            <?php if ($canVerify): ?>
                                <!-- 4. Tombol Verifikasi Selisih (Hanya aktif jika kasir selesai menghitung seluruh item) -->
                                <?php if ($stats['belum_hitung'] > 0): ?>
                                    <button type="button" disabled class="h-9 px-3.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 text-xs font-semibold flex items-center gap-1.5 cursor-not-allowed opacity-80" title="Verifikasi belum aktif. Kasir masih menghitung fisik rak (sisa <?= $stats['belum_hitung'] ?> item belum dihitung)">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        <span>Verifikasi Selisih</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-200 text-slate-600 font-mono font-medium">Kasir: <?= $stats['belum_hitung'] ?> Item</span>
                                    </button>
                                <?php else: ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Buka tahap Verifikasi Selisih? Formulir pencacahan fisik kasir akan dikunci dan antarmuka beralih ke Verifikasi.');">
                                        <input type="hidden" name="aksi" value="tutup_penghitungan">
                                        <button type="submit" class="h-9 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Buka antarmuka verifikasi selisih">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                            <span>Verifikasi Selisih</span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>"""

if old_toolbar in content:
    content = content.replace(old_toolbar, new_toolbar)
    print("✓ Toolbar button replaced successfully.")
else:
    print("! Toolbar button pattern not found exactly, searching...")

# 3. Update Admin Monitoring Bottom Button
old_admin_btn = """                    <div class="flex items-center justify-end pt-2">
                        <form method="POST" onsubmit="return confirm('Tutup tahap penghitungan fisik? Formulir pencacahan kasir akan dikunci dan sistem beralih ke Verifikasi Selisih.');">
                            <input type="hidden" name="aksi" value="tutup_penghitungan">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                            </button>
                        </form>
                    </div>"""

new_admin_btn = """                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                        <div class="text-xs text-slate-500">
                            <?php if ($stats['belum_hitung'] > 0): ?>
                                <span class="text-amber-600 font-medium">Menunggu tim kasir menyelesaikan pencacahan fisik rak (<?= $stats['belum_hitung'] ?> item lagi).</span>
                            <?php else: ?>
                                <span class="text-emerald-600 font-medium">Seluruh item fisik (<?= $stats['total_item'] ?>) telah selesai dihitung oleh tim kasir.</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($stats['belum_hitung'] > 0): ?>
                            <button type="button" disabled class="px-5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 text-xs font-bold flex items-center gap-2 cursor-not-allowed" title="Tahap verifikasi belum dapat dibuka karena kasir masih menghitung fisik rak">
                                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span>Verifikasi Selisih (Menunggu Kasir: <?= $stats['belum_hitung'] ?> Item)</span>
                            </button>
                        <?php else: ?>
                            <form method="POST" onsubmit="return confirm('Buka tahap Verifikasi Selisih? Formulir pencacahan kasir akan dikunci dan sistem beralih ke Verifikasi Selisih.');">
                                <input type="hidden" name="aksi" value="tutup_penghitungan">
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    <span>Verifikasi Selisih</span>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>"""

if old_admin_btn in content:
    content = content.replace(old_admin_btn, new_admin_btn)
    print("✓ Admin monitoring button replaced successfully.")
else:
    print("! Admin monitoring button pattern not found exactly, searching...")

# 4. Remove old duplicate isiCepatAngkaDemo
old_js_dup = """// Fungsi Mode Demo: Hanya mengisi angka di kotak input pada layar tanpa menyimpan ke server
function isiCepatAngkaDemo() {
    let inputs = document.querySelectorAll('.input-hitung-fisik');
    if (!inputs.length) {
        alert('Tidak ada kotak input hitung fisik yang perlu diisi!');
        return;
    }

    let diffInjectedObat = false;
    let diffInjectedAlkes = false;
    let countFilled = 0;

    inputs.forEach(inp => {
        let sistem = parseInt(inp.dataset.sistem, 10) || 0;
        let komoditas = inp.dataset.komoditas;
        let satuanTipe = inp.dataset.satuanTipe;
        let fisik = sistem;

        // Berikan 2 sampel selisih -1 untuk demonstrasi rekonsiliasi (1 obat satuan besar dan 1 alkes)
        if (!diffInjectedObat && komoditas === 'Obat' && satuanTipe === 'besar' && sistem >= 2) {
            fisik = sistem - 1;
            diffInjectedObat = true;
            inp.classList.remove('border-amber-300');
            inp.classList.add('bg-rose-50', 'border-rose-400');
        } else if (!diffInjectedAlkes && komoditas === 'Alkes' && sistem >= 2) {
            fisik = sistem - 1;
            diffInjectedAlkes = true;
            inp.classList.remove('border-amber-300');
            inp.classList.add('bg-rose-50', 'border-rose-400');
        } else {
            inp.classList.remove('border-amber-300');
            inp.classList.add('bg-emerald-50/50', 'border-emerald-400');
        }

        inp.value = fisik;
        countFilled++;
    });

    let btnSimpan = document.getElementById('btn_bulk_simpan');
    if (btnSimpan) {
        btnSimpan.classList.add('ring-2', 'ring-amber-400', 'ring-offset-1');
        setTimeout(() => btnSimpan.classList.remove('ring-2', 'ring-amber-400', 'ring-offset-1'), 3000);
    }

    alert('Mode Demo: ' + countFilled + ' kotak input telah terisi angka fisik di layar (2 item disimulasikan berselisih).\\n\\nAngka BELUM disimpan ke server. Anda dapat meninjau atau mengubah angka di kotak input mana pun, lalu klik tombol "Simpan Hasil Hitung" untuk menyimpannya sekaligus ke server.');
}"""

if old_js_dup in content:
    content = content.replace(old_js_dup, "// Alias untuk kompatibilitas\nwindow.isiCepatAngkaDemo = function() { isiOtomatisAngkaDemo(); };")
    print("✓ Old duplicate JS function replaced.")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Finished writing inventori/stok_opname.php")

