<?php
$file = __DIR__ . '/../index.php';
$content = file_get_contents($file);

// 1. HAPUS SCRIPT CHART.JS
$chartScriptPattern = '/<!-- Script untuk Chart\.js \(Hanya Superadmin\) -->[\s\S]*?<\/script>\s*(?=\<\?php\s+else:)/i';
if (preg_match($chartScriptPattern, $content)) {
    $content = preg_replace($chartScriptPattern, '', $content);
    echo "Chart.js script removed successfully.\n";
} else {
    echo "Warning: Chart.js script pattern not found.\n";
}

// 2. TAMBAH PAGINASI PADA RIWAYAT KARTU STOK SUPERADMIN (ROW 4)
$oldKsSaLoop = '<?php foreach ($auditMutasi as $a): 
                $satuanLabel = $a[\'satuan_tipe\'] === \'besar\' ? $a[\'satuan_besar\'] : $a[\'satuan_kecil\'];
            ?>
            <tr style="border-top:1px solid #f1f5f9;hover:background:#f8fafc;">';

$newKsSaLoop = '<?php foreach ($auditMutasi as $i => $a): 
                $satuanLabel = $a[\'satuan_tipe\'] === \'besar\' ? $a[\'satuan_besar\'] : $a[\'satuan_kecil\'];
            ?>
            <tr class="ks-superadmin-item" style="border-top:1px solid #f1f5f9;display:none;">';

$oldKsSaClose = '            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>';

$newKsSaClose = '            <?php endforeach; ?>
            </tbody>
        </table>
        <!-- Paginator UI Kartu Stok Superadmin -->
        <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:9px; color:#64748b;"><span id="ks-sa-range">0-0</span> dari <span id="ks-sa-total"><?= count($auditMutasi) ?></span></div>
            <div style="display:flex; gap:3px;">
                <button type="button" onclick="prevKsSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
                <button type="button" onclick="nextKsSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>';

$normContent = str_replace("\r\n", "\n", $content);
$normKsSaLoop = str_replace("\r\n", "\n", $oldKsSaLoop);
$normKsSaClose = str_replace("\r\n", "\n", $oldKsSaClose);

if (strpos($normContent, $normKsSaLoop) !== false) {
    $normContent = str_replace($normKsSaLoop, $newKsSaLoop, $normContent);
    echo "Paginasi class pada Kartu Stok Superadmin berhasil ditambahkan.\n";
} else {
    echo "Warning: KsSaLoop not found.\n";
}

if (strpos($normContent, $normKsSaClose) !== false) {
    $normContent = str_replace($normKsSaClose, $newKsSaClose, $normContent);
    echo "Paginasi UI pada Kartu Stok Superadmin berhasil ditambahkan.\n";
} else {
    echo "Warning: KsSaClose not found.\n";
}

// 3. TAMBAH PAGINASI PADA RIWAYAT PENGADAAN SUPERADMIN (ROW 5)
$oldPoSaLoop = '<?php foreach ($riwayatPengadaan as $rp): ?>
            <tr style="border-top:1px solid #f1f5f9;hover:background:#f8fafc;">';

$newPoSaLoop = '<?php foreach ($riwayatPengadaan as $i => $rp): ?>
            <tr class="po-superadmin-item" style="border-top:1px solid #f1f5f9;display:none;">';

$oldPoSaClose = '            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>';

$newPoSaClose = '            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Paginator UI Riwayat Pengadaan Superadmin -->
    <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
        <div style="font-size:9px; color:#64748b;"><span id="po-sa-range">0-0</span> dari <span id="po-sa-total"><?= count($riwayatPengadaan) ?></span></div>
        <div style="display:flex; gap:3px;">
            <button type="button" onclick="prevPoSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
            <button type="button" onclick="nextPoSA()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
        </div>
    </div>
    <?php endif; ?>
</div>';

$normPoSaLoop = str_replace("\r\n", "\n", $oldPoSaLoop);
$normPoSaClose = str_replace("\r\n", "\n", $oldPoSaClose);

if (strpos($normContent, $normPoSaLoop) !== false) {
    $normContent = str_replace($normPoSaLoop, $newPoSaLoop, $normContent);
    echo "Paginasi class pada Riwayat Pengadaan Superadmin berhasil ditambahkan.\n";
} else {
    echo "Warning: PoSaLoop not found.\n";
}

if (strpos($normContent, $normPoSaClose) !== false) {
    $normContent = str_replace($normPoSaClose, $newPoSaClose, $normContent);
    echo "Paginasi UI pada Riwayat Pengadaan Superadmin berhasil ditambahkan.\n";
} else {
    echo "Warning: PoSaClose not found.\n";
}

// 4. TAMBAH PAGINASI PADA KARTU STOK ADMIN VIEW
$oldKsAdLoop = '        <?php foreach ($kartuTerbaru as $k): 
            $satuanLabel = $k[\'satuan_tipe\'] === \'besar\' ? $k[\'satuan_besar\'] : $k[\'satuan_kecil\'];
        ?>
        <div style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">';

$newKsAdLoop = '        <?php foreach ($kartuTerbaru as $i => $k): 
            $satuanLabel = $k[\'satuan_tipe\'] === \'besar\' ? $k[\'satuan_besar\'] : $k[\'satuan_kecil\'];
        ?>
        <div class="ks-admin-item" style="padding:9px 16px;border-bottom:1px solid #f8fafc;display:none;justify-content:space-between;align-items:center;">';

$oldKsAdClose = '        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>';

$newKsAdClose = '        <?php endforeach; ?>
        <!-- Paginator UI Kartu Stok Admin -->
        <div style="padding:8px 16px; display:flex; justify-content:space-between; align-items:center; background:#f8fafc; border-top:1px solid #e4e9f0;">
            <div style="font-size:9px; color:#64748b;"><span id="ks-ad-range">0-0</span> dari <span id="ks-ad-total"><?= count($kartuTerbaru) ?></span></div>
            <div style="display:flex; gap:3px;">
                <button type="button" onclick="prevKsAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&lt;</button>
                <button type="button" onclick="nextKsAD()" style="padding:2px 8px; border:1px solid #cbd5e1; background:#fff; border-radius:4px; font-size:10px; cursor:pointer; color:#334155;">&gt;</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>';

$normKsAdLoop = str_replace("\r\n", "\n", $oldKsAdLoop);
$normKsAdClose = str_replace("\r\n", "\n", $oldKsAdClose);

if (strpos($normContent, $normKsAdLoop) !== false) {
    $normContent = str_replace($normKsAdLoop, $newKsAdLoop, $normContent);
    echo "Paginasi class pada Kartu Stok Admin berhasil ditambahkan.\n";
} else {
    echo "Warning: KsAdLoop not found.\n";
}

if (strpos($normContent, $normKsAdClose) !== false) {
    $normContent = str_replace($normKsAdClose, $newKsAdClose, $normContent);
    echo "Paginasi UI pada Kartu Stok Admin berhasil ditambahkan.\n";
} else {
    echo "Warning: KsAdClose not found.\n";
}

// 5. HAPUS TABEL BESAR BOTTOM (PANEL ANOMALI FEFO & MONITORING RETUR) KARENA SUDAH ADA DI MODAL
$bottomPanelPattern = '/<!-- ============================================================ -->\s*<!-- PANEL ANOMALI FEFO HARI INI & MONITORING RETUR SUPPLIER       -->[\s\S]*?<\/div>\s*<\/div>\s*(?=<!-- Modal Dialog Functions)/i';
if (preg_match($bottomPanelPattern, $normContent)) {
    $normContent = preg_replace($bottomPanelPattern, '', $normContent);
    echo "Tabel besar bottom berhasil dihapus (dialihkan sepenuhnya ke modal interaktif).\n";
} else {
    echo "Warning: Bottom panel pattern not found.\n";
}

// 6. TAMBAH JAVASCRIPT HANDLER UNTUK PAGINASI BARU
$jsTarget = 'let ropSaPage = 1;';
$jsAddition = '
// Paginator for SuperAdmin Kartu Stok
let ksSaPage = 1;
const ksSaLimit = 5;
const ksSaItems = document.querySelectorAll(".ks-superadmin-item");
if(ksSaItems.length > 0) renderSaKs();

function renderSaKs() {
    let start = (ksSaPage - 1) * ksSaLimit;
    let end = start + ksSaLimit;
    ksSaItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = "table-row";
        else el.style.display = "none";
    });
    let endDisplay = end > ksSaItems.length ? ksSaItems.length : end;
    let startDisplay = ksSaItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById("ks-sa-range");
    if(rangeEl) rangeEl.innerText = startDisplay + "-" + endDisplay;
}
function nextKsSA() {
    if((ksSaPage * ksSaLimit) < ksSaItems.length) { ksSaPage++; renderSaKs(); }
}
function prevKsSA() {
    if(ksSaPage > 1) { ksSaPage--; renderSaKs(); }
}

// Paginator for SuperAdmin Riwayat Pengadaan
let poSaPage = 1;
const poSaLimit = 5;
const poSaItems = document.querySelectorAll(".po-superadmin-item");
if(poSaItems.length > 0) renderSaPo();

function renderSaPo() {
    let start = (poSaPage - 1) * poSaLimit;
    let end = start + poSaLimit;
    poSaItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = "table-row";
        else el.style.display = "none";
    });
    let endDisplay = end > poSaItems.length ? poSaItems.length : end;
    let startDisplay = poSaItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById("po-sa-range");
    if(rangeEl) rangeEl.innerText = startDisplay + "-" + endDisplay;
}
function nextPoSA() {
    if((poSaPage * poSaLimit) < poSaItems.length) { poSaPage++; renderSaPo(); }
}
function prevPoSA() {
    if(poSaPage > 1) { poSaPage--; renderSaPo(); }
}

// Paginator for Admin Kartu Stok
let ksAdPage = 1;
const ksAdLimit = 5;
const ksAdItems = document.querySelectorAll(".ks-admin-item");
if(ksAdItems.length > 0) renderAdKs();

function renderAdKs() {
    let start = (ksAdPage - 1) * ksAdLimit;
    let end = start + ksAdLimit;
    ksAdItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = "flex";
        else el.style.display = "none";
    });
    let endDisplay = end > ksAdItems.length ? ksAdItems.length : end;
    let startDisplay = ksAdItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById("ks-ad-range");
    if(rangeEl) rangeEl.innerText = startDisplay + "-" + endDisplay;
}
function nextKsAD() {
    if((ksAdPage * ksAdLimit) < ksAdItems.length) { ksAdPage++; renderAdKs(); }
}
function prevKsAD() {
    if(ksAdPage > 1) { ksAdPage--; renderAdKs(); }
}
';

if (strpos($normContent, $jsTarget) !== false && strpos($normContent, 'renderSaKs()') === false) {
    $normContent = str_replace($jsTarget, $jsAddition . "\n" . $jsTarget, $normContent);
    echo "JavaScript pagination functions added.\n";
} else {
    echo "Warning: jsTarget not found or already added.\n";
}

file_put_contents($file, $normContent);
echo "Refactoring of index.php finished.\n";

