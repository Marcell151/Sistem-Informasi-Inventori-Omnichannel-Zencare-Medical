with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add POST action for jalankan_simulasi_demo
old_post_demo = """    // --- RESET DATABASE KE BASELINE AWAL (Khusus Superadmin / Pengujian Demo) ---
    if ($aksi === 'reset_database_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset database pengujian hanya dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } else {
            try {
                require_once __DIR__ . '/../scratch/restore_baseline.php';
                restoreBaselineInventory($pdo);
                cekSesiOpnameAktif($pdo, true);
                $msg = "Database inventori berhasil di-reset ke kondisi bersih awal (Baseline Clean State)! Seluruh sesi uji coba dan mutasi sementara telah dibersihkan.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal mereset database: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }"""

new_post_demo = """    // --- RESET DATABASE KE BASELINE AWAL (Khusus Superadmin / Pengujian Demo) ---
    if ($aksi === 'reset_database_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset database pengujian hanya dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } else {
            try {
                require_once __DIR__ . '/../scratch/restore_baseline.php';
                restoreBaselineInventory($pdo);
                cekSesiOpnameAktif($pdo, true);
                $msg = "Database inventori berhasil di-reset ke kondisi bersih awal (Baseline Clean State)! Seluruh sesi uji coba dan mutasi sementara telah dibersihkan.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal mereset database: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }

    // --- JALANKAN SIMULASI DEMO PENGUJIAN OTOMATIS (Full 121 Item & 5 Anomali) ---
    elseif ($aksi === 'jalankan_simulasi_demo') {
        if (!$canVerify && !$isKasir) {
            $msg = "Akses ditolak!";
            $msgType = 'error';
        } else {
            try {
                ob_start();
                include __DIR__ . '/../scratch/run_full_opname_simulation.php';
                $simLog = ob_get_clean();
                cekSesiOpnameAktif($pdo, true);
                header("Location: /inventory_zencare/inventori/stok_opname.php?msg=" . urlencode("Simulasi pengujian Stok Opname (Full 121 Item & 5 Anomali) berhasil dijalankan secara otomatis!") . "&type=success");
                exit;
            } catch (Exception $e) {
                $msg = "Gagal menjalankan simulasi: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }"""

assert old_post_demo in content, "old_post_demo not found"
content = content.replace(old_post_demo, new_post_demo)

# 2. Add button in landing page next to Reset Data Demo
old_landing_btns = """                    <?php if ($canVerify): ?>
                        <!-- Tombol Reset Database Demo -->
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan database inventori ke kondisi bersih awal (Baseline)? Seluruh sesi uji coba dan mutasi sementara akan dibersihkan.');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Memulihkan database inventori ke kondisi awal bersih sebelum atau sesudah demonstrasi pengujian.">
                                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>Reset Data Demo</span>
                            </button>
                        </form>
                    <?php endif; ?>"""

new_landing_btns = """                    <?php if ($canVerify): ?>
                        <!-- Tombol Jalankan Simulasi Demo (121 Item) -->
                        <form method="POST" onsubmit="return confirm('Jalankan simulasi otomatis pengujian Stok Opname komprehensif pada seluruh 121 item dengan 5 skenario anomali (Rusak, Expired, Hilang, Surplus)?');" class="inline">
                            <input type="hidden" name="aksi" value="jalankan_simulasi_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Jalankan skenario uji coba otomatis pada seluruh produk saat ini">
                                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                <span>Jalankan Simulasi Demo (121 Item)</span>
                            </button>
                        </form>

                        <!-- Tombol Reset Database Demo -->
                        <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan database inventori ke kondisi bersih awal (Baseline)? Seluruh sesi uji coba dan mutasi sementara akan dibersihkan.');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Memulihkan database inventori ke kondisi awal bersih sebelum atau sesudah demonstrasi pengujian.">
                                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                <span>Reset Data Demo</span>
                            </button>
                        </form>
                    <?php endif; ?>"""

assert old_landing_btns in content, "old_landing_btns not found"
content = content.replace(old_landing_btns, new_landing_btns)

# 3. Refine isiOtomatisAngkaDemo in JavaScript
import re
old_func_pattern = r'// Fungsi Cepat Pengisian Angka Fisik Demo / Uji Coba Sesuai 5 Skenario\s*function isiOtomatisAngkaDemo\(\)\s*\{.*?^\}'
m = re.search(old_func_pattern, content, re.DOTALL | re.MULTILINE)
assert m, "old_func_pattern not found"

new_func = """// Fungsi Cepat Pengisian Angka Fisik Demo / Uji Coba Sesuai 5 Skenario
function isiOtomatisAngkaDemo() {
    let rows = document.querySelectorAll('tr[data-id]');
    let countFilled = 0;
    let anomaliCount = 0;

    let flagCefadroxil = false;
    let flagSanmol = false;
    let flagAmox = false;
    let flagOmron = false;
    let flagBetadine = false;

    rows.forEach(tr => {
        let id = tr.getAttribute('data-id');
        let nama = (tr.getAttribute('data-nama') || '').toLowerCase();
        let barcodeSearch = (tr.getAttribute('data-barcode-search') || '').toLowerCase();
        let satuanTipe = tr.getAttribute('data-satuan-tipe') || 'kecil';
        let sistem = parseInt(tr.getAttribute('data-stok-sistem') || '0', 10);
        let isExp = tr.getAttribute('data-is-exp') === '1';

        let inBaik = document.getElementById('input_baik_' + id);
        let inRusak = document.getElementById('input_rusak_' + id);
        let inExp = document.getElementById('input_exp_' + id);

        if (!inBaik && !inRusak && !inExp) return;

        // Reset highlight
        tr.classList.remove('bg-rose-50/70', 'bg-amber-50/70', 'bg-blue-50/70');

        // 1. Cefadroxil 500mg Strip (Satuan Kecil) -> Rusak 2 Strip:
        if (!flagCefadroxil && nama.includes('cefadroxil') && satuanTipe === 'kecil') {
            flagCefadroxil = true;
            anomaliCount++;
            let rusakQty = Math.min(2, sistem);
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - rusakQty);
            if (inRusak) inRusak.value = rusakQty;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-amber-50/70');
        }
        // 2. Sanmol Sirup Anak 60ml (Expired Batch) -> Kedaluwarsa:
        else if (!flagSanmol && nama.includes('sanmol') && isExp) {
            flagSanmol = true;
            anomaliCount++;
            let expFisik = Math.max(0, sistem - 3); // 2 botol fisik, selisih defisit -3
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = 0;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = expFisik;
            tr.classList.add('bg-rose-50/70');
        }
        // 3. Amoxicillin 500mg Strip (Satuan Kecil) -> Hilang 2 Strip:
        else if (!flagAmox && nama.includes('amoxicillin') && satuanTipe === 'kecil') {
            flagAmox = true;
            anomaliCount++;
            let hilangQty = Math.min(2, sistem);
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - hilangQty);
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-rose-50/70');
        }
        // 4. Tensimeter Digital Omron (Alkes SN) -> Hilang 1 Unit:
        else if (!flagOmron && nama.includes('tensimeter') && sistem >= 1) {
            flagOmron = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = Math.max(0, sistem - 1);
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-rose-50/70');
        }
        // 5. Betadine Solution 15ml (Satuan Kecil) -> Surplus +2 Botol:
        else if (!flagBetadine && nama.includes('betadine') && satuanTipe === 'kecil') {
            flagBetadine = true;
            anomaliCount++;
            if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem + 2;
            if (inRusak) inRusak.value = 0;
            if (inExp) inExp.value = 0;
            tr.classList.add('bg-blue-50/70');
        }
        // 106 Item Normal Lainnya: Sesuai Persis dengan Kuantitas Sistem (Klop)
        else {
            if (isExp) {
                if (inBaik && inBaik.type !== 'hidden') inBaik.value = 0;
                if (inRusak) inRusak.value = 0;
                if (inExp) inExp.value = sistem;
            } else {
                if (inBaik && inBaik.type !== 'hidden') inBaik.value = sistem;
                if (inRusak) inRusak.value = 0;
                if (inExp) inExp.value = 0;
            }
        }
        countFilled++;
    });

    let msgAnomali = 'Berhasil mengisi ' + countFilled + ' baris hitungan fisik di layar sesuai skenario pengujian:\\n' +
        '• Cefadroxil: 2 Strip Rusak (tercatat di kolom Kondisi Rusak)\\n' +
        '• Sanmol Sirup: Kedaluwarsa (tercatat di kolom Kondisi Expired)\\n' +
        '• Amoxicillin: Kurang 2 Strip (Fisik 6 dari Sistem 8)\\n' +
        '• Tensimeter Omron: Kurang 1 Unit (Fisik 7 dari Sistem 8)\\n' +
        '• Betadine Solution: Surplus +2 Botol (Fisik 14 dari Sistem 12)\\n' +
        '• 106 Item lainnya: 100% Klop / Sesuai.\\n\\n' +
        'Silakan tekan tombol "Simpan Hasil Hitung" untuk menyimpan data ke server.';
    alert(msgAnomali);
}"""

content = content[:m.start()] + new_func + content[m.end():]

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated stok_opname.php with simulation action and refined JS!")

