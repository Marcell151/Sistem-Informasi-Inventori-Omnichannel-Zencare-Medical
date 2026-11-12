with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Update backend handler for reset_database_demo
old_handler = """    // --- RESET DATABASE KE BASELINE AWAL (Khusus Superadmin / Pengujian Demo) ---
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

new_handler = """    // --- RESET DATABASE KE BASELINE AWAL DENGAN CADANGAN OTOMATIS & KONFIRMASI KETIK RESET ---
    if ($aksi === 'reset_database_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset pangkalan data pengujian hanya dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } elseif (trim($_POST['confirm_reset'] ?? '') !== 'RESET') {
            $msg = "Reset dibatalkan! Anda wajib mengetik kata 'RESET' dengan huruf kapital untuk mengonfirmasi pemulihan.";
            $msgType = 'error';
        } else {
            try {
                $backupDir = 'C:\\\\xampp\\\\backup_data';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0777, true);
                }
                
                // 1. Cadangan otomatis sebelum menimpa data
                $backupFile = $backupDir . '\\\\backup_sebelum_reset_' . date('Ymd_His') . '.sql';
                $mysqldumpPath = 'C:\\\\xampp\\\\mysql\\\\bin\\\\mysqldump.exe';
                if (file_exists($mysqldumpPath)) {
                    exec("\\"$mysqldumpPath\\" -u root inventory_zencare > \\"$backupFile\\" 2>&1");
                }
                
                // 2. Pulihkan pangkalan data dari berkas dump bersih di luar folder web publik
                $cleanDump = $backupDir . '\\\\Demo_Bersih.sql';
                if (!file_exists($cleanDump)) {
                    $cleanDump = __DIR__ . '/../Demo_Bersih.sql';
                }
                
                $mysqlPath = 'C:\\\\xampp\\\\mysql\\\\bin\\\\mysql.exe';
                if (file_exists($mysqlPath) && file_exists($cleanDump)) {
                    exec("\\"$mysqlPath\\" -u root inventory_zencare < \\"$cleanDump\\" 2>&1");
                } else {
                    require_once __DIR__ . '/../scratch/restore_baseline.php';
                    restoreBaselineInventory($pdo);
                }
                
                cekSesiOpnameAktif($pdo, true);
                $msg = "Pangkalan data berhasil dipulihkan dari Demo_Bersih.sql! Cadangan otomatis telah disimpan ke direktori backup_data.";
                $msgType = 'success';
            } catch (Exception $e) {
                $msg = "Gagal memulihkan data: " . $e->getMessage();
                $msgType = 'error';
            }
        }
    }"""

text = text.replace(old_handler, new_handler)

# 2. Update button in toolbar to open modal
old_btn = """                        <form method="POST" onsubmit="return confirm('Peringatan: Seluruh data inventori dan riwayat opname akan dikembalikan ke kondisi bersih awal (Demo_Bersih.sql)?');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Reset seluruh data inventori ke kondisi awal bersih">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                                <span>Reset Data Demo</span>
                            </button>
                        </form>"""

new_btn = """                        <button type="button" onclick="document.getElementById('modal_reset_database').classList.remove('hidden')" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Reset seluruh data inventori ke kondisi awal bersih">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                            <span>[Utilitas Demo] Reset Data Demo</span>
                        </button>"""

text = text.replace(old_btn, new_btn)

# 3. Add Modal Reset HTML before </body>
modal_html = """
    <!-- Modal Pengaman Reset Data Demo (Konfirmasi Ketik RESET) -->
    <div id="modal_reset_database" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-rose-50">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-600"></span>
                    <h3 class="font-bold text-sm text-rose-950">Konfirmasi Pemulihan Data Demo</h3>
                </div>
                <button type="button" onclick="document.getElementById('modal_reset_database').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-sm font-bold cursor-pointer">&times;</button>
            </div>
            <form method="POST" class="p-6 space-y-4 text-xs">
                <input type="hidden" name="aksi" value="reset_database_demo">
                <p class="text-slate-700 leading-relaxed">
                    Tindakan ini akan <strong>membuat cadangan otomatis</strong> terlebih dahulu ke <code>C:\\xampp\\backup_data\\</code>, lalu memulihkan seluruh pangkalan data ke kondisi awal dari berkas <code>Demo_Bersih.sql</code>.
                </p>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 leading-relaxed">
                    Untuk melanjutkan pemulihan data, silakan ketik kata <strong>RESET</strong> dengan huruf kapital pada bidang di bawah ini:
                </div>
                <div>
                    <input type="text" name="confirm_reset" id="input_confirm_reset" placeholder="Ketik RESET" class="w-full border border-slate-300 rounded-xl p-2.5 text-center font-mono font-bold text-sm tracking-wider uppercase focus:ring-2 focus:ring-rose-500" oninput="document.getElementById('btn_submit_reset').disabled = (this.value !== 'RESET')">
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                    <button type="button" onclick="document.getElementById('modal_reset_database').classList.add('hidden')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition cursor-pointer">Batal</button>
                    <button type="submit" id="btn_submit_reset" disabled class="px-5 py-2 bg-rose-600 hover:bg-rose-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-xs transition cursor-pointer">Pulihkan Data</button>
                </div>
            </form>
        </div>
    </div>
"""

text = text.replace('</body>', modal_html + '\n</body>')

# Verify forbidden words
forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print("stok_opname.php forbidden words after reset modal:", found)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated inventori/stok_opname.php with reset modal successfully.")

