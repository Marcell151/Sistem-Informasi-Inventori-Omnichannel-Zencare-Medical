with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

# 1. Update backend handler: remove confirm_reset requirement
old_handler = """    // --- RESET DATABASE KE BASELINE AWAL DENGAN CADANGAN OTOMATIS & KONFIRMASI KETIK RESET ---
    if ($aksi === 'reset_database_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset pangkalan data pengujian hanya dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } elseif (trim($_POST['confirm_reset'] ?? '') !== 'RESET') {
            $msg = "Reset dibatalkan! Anda wajib mengetik kata 'RESET' dengan huruf kapital untuk mengonfirmasi pemulihan.";
            $msgType = 'error';
        } else {"""

new_handler = """    // --- RESET DATABASE KE BASELINE AWAL DENGAN CADANGAN OTOMATIS (UTILITAS DEMO) ---
    if ($aksi === 'reset_database_demo') {
        if (!$canVerify) {
            $msg = "Akses ditolak! Utilitas reset pangkalan data pengujian hanya dapat diakses oleh Admin atau Superadmin.";
            $msgType = 'error';
        } else {"""

text = text.replace(old_handler, new_handler)

# 2. Update button in toolbar to be direct form with confirm
old_btn = """                        <button type="button" onclick="document.getElementById('modal_reset_database').classList.remove('hidden')" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Reset seluruh data inventori ke kondisi awal bersih">
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                            <span>[Utilitas Demo] Reset Data Demo</span>
                        </button>"""

new_btn = """                        <form method="POST" onsubmit="return confirm('Peringatan: Seluruh data inventori dan riwayat opname akan dikembalikan ke kondisi awal (Demo_Bersih.sql). Sistem akan membuat cadangan otomatis terlebih dahulu ke backup_data. Lanjutkan pemulihan?');" class="inline">
                            <input type="hidden" name="aksi" value="reset_database_demo">
                            <button type="submit" class="h-9 px-3.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer" title="Reset seluruh data inventori ke kondisi awal bersih">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                                <span>[Utilitas Demo] Reset Data Demo</span>
                            </button>
                        </form>"""

text = text.replace(old_btn, new_btn)

# 3. Remove modal HTML if present
modal_to_remove_start = text.find('<!-- Modal Pengaman Reset Data Demo')
if modal_to_remove_start != -1:
    modal_to_remove_end = text.find('</div>\n    </div>', modal_to_remove_start) + len('</div>\n    </div>')
    text = text[:modal_to_remove_start] + text[modal_to_remove_end:]
    print("Removed modal_reset_database HTML.")

# Verify forbidden words
forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text.lower().count(w)
    if c > 0:
        found[w] = c
print("stok_opname.php forbidden words:", found)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated inventori/stok_opname.php successfully without RESET typing.")

