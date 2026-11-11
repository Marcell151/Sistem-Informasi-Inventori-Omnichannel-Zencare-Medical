with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update backend check in tutup_penghitungan
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
            $msg = "Akses ditolak! Hanya Admin atau Superadmin yang berwenang membuka tahap verifikasi.";
            $msgType = 'error';
        } elseif (!$activeSession) {
            $msg = "Tidak ada sesi opname yang sedang aktif.";
            $msgType = 'error';
        } else {
            try {
                $idSesi = $activeSession['id'];
                // Validasi: Seluruh barang wajib selesai dihitung oleh Kasir terlebih dahulu (Kecuali Superadmin)
                $uncountedCount = $pdo->query("SELECT COUNT(*) FROM detail_opname_batch WHERE id_sesi = $idSesi AND hitung_fisik IS NULL")->fetchColumn();
                if ($uncountedCount > 0 && !$isSuperadmin) {
                    throw new Exception("Tahap verifikasi belum dapat dibuka! Masih terdapat $uncountedCount baris barang yang belum selesai dihitung oleh Kasir di lapangan.");
                }

                $pdo->prepare("
                    UPDATE sesi_opname
                    SET tahap = 'Verifikasi',
                        ditutup_hitung_oleh = ?,
                        ditutup_hitung_pada = NOW()
                    WHERE id = ?
                ")->execute([$userId, $idSesi]);

                header("Location: stok_opname.php?status_eval=berselisih&msg=" . urlencode("Tahap Verifikasi Selisih berhasil dibuka! Formulir pencacahan kasir telah dikunci dan antarmuka beralih ke Verifikasi Selisih.") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal membuka verifikasi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }"""

assert old_backend in content, "old_backend not found"
content = content.replace(old_backend, new_backend)

# 2. Update toolbar button (lines ~1813)
old_toolbar_btn = """                            <?php if ($canVerify): ?>
                                <!-- 4. Tombol Tutup Penghitungan & Buka Verifikasi -->
                                <form method="POST" class="inline" onsubmit="return confirm('Tutup tahap penghitungan fisik? Seluruh formulir pencacahan akan dikunci dan antarmuka beralih ke Verifikasi Selisih.');">
                                    <input type="hidden" name="aksi" value="tutup_penghitungan">
                                    <button type="submit" class="h-9 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                        <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                                    </button>
                                </form>
                            <?php endif; ?>"""

new_toolbar_btn = """                            <?php if ($canVerify): ?>
                                <?php $siapVerifikasi = ($stats['belum_hitung'] === 0 || $isSuperadmin); ?>
                                <!-- 4. Tombol Verifikasi Selisih (Aktif hanya jika Kasir selesai input semua barang) -->
                                <form method="POST" class="inline" onsubmit="return confirm('Buka tahap Verifikasi Selisih? Formulir pencacahan kasir akan dikunci dan sistem beralih ke antarmuka Verifikasi Selisih.');">
                                    <input type="hidden" name="aksi" value="tutup_penghitungan">
                                    <?php if ($siapVerifikasi): ?>
                                        <button type="submit" class="h-9 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Seluruh barang telah selesai dihitung. Klik untuk membuka Verifikasi Selisih.">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                            <span>Verifikasi Selisih</span>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" disabled class="h-9 px-3.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 text-xs font-semibold flex items-center gap-1.5 cursor-not-allowed" title="Menunggu Kasir: Masih ada <?= $stats['belum_hitung'] ?> item yang belum dihitung di lapangan.">
                                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            <span>Verifikasi Selisih (Menunggu Kasir: <?= $stats['belum_hitung'] ?> Item)</span>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>"""

assert old_toolbar_btn in content, "old_toolbar_btn not found"
content = content.replace(old_toolbar_btn, new_toolbar_btn)

# 3. Update bottom button in Admin monitoring table (lines ~2007)
old_admin_btn = """                    <div class="flex items-center justify-end pt-2">
                        <form method="POST" onsubmit="return confirm('Tutup tahap penghitungan fisik? Formulir pencacahan kasir akan dikunci dan sistem beralih ke Verifikasi Selisih.');">
                            <input type="hidden" name="aksi" value="tutup_penghitungan">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                <span>Tutup Penghitungan &amp; Buka Verifikasi</span>
                            </button>
                        </form>
                    </div>"""

new_admin_btn = """                    <div class="flex items-center justify-end pt-2">
                        <?php $siapVerifikasi = ($stats['belum_hitung'] === 0 || $isSuperadmin); ?>
                        <form method="POST" onsubmit="return confirm('Buka tahap Verifikasi Selisih? Formulir pencacahan kasir akan dikunci dan sistem beralih ke Verifikasi Selisih.');">
                            <input type="hidden" name="aksi" value="tutup_penghitungan">
                            <?php if ($siapVerifikasi): ?>
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer" title="Seluruh barang telah selesai dihitung. Klik untuk membuka Verifikasi Selisih.">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    <span>Verifikasi Selisih</span>
                                </button>
                            <?php else: ?>
                                <button type="button" disabled class="px-4 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 text-xs font-semibold flex items-center gap-2 cursor-not-allowed" title="Menunggu Kasir: Masih ada <?= $stats['belum_hitung'] ?> item yang belum dihitung di lapangan.">
                                    <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <span>Verifikasi Selisih (Menunggu Kasir: <?= $stats['belum_hitung'] ?> Item)</span>
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>"""

assert old_admin_btn in content, "old_admin_btn not found"
content = content.replace(old_admin_btn, new_admin_btn)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated inventori/stok_opname.php with Verifikasi Selisih button and conditional disabled state!")

