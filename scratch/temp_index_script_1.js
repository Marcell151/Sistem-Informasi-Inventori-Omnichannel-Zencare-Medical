
// Paginator for SuperAdmin ROP

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

let ropSaPage = 1;
const ropSaLimit = 5;
const saItems = document.querySelectorAll('.rop-superadmin-item');
if(saItems.length > 0) renderSaRop();

function renderSaRop() {
    let start = (ropSaPage - 1) * ropSaLimit;
    let end = start + ropSaLimit;
    
    saItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > saItems.length ? saItems.length : end;
    let startDisplay = saItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('rop-sa-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextRopSA() {
    if(ropSaPage * ropSaLimit < saItems.length) {
        ropSaPage++;
        renderSaRop();
    }
}
function prevRopSA() {
    if(ropSaPage > 1) {
        ropSaPage--;
        renderSaRop();
    }
}


// Paginator for SuperAdmin EXP
let expSaPage = 1;
const expSaLimit = 5;
const expSaItems = document.querySelectorAll('.exp-superadmin-item');
if(expSaItems.length > 0) renderExpSa();

function renderExpSa() {
    let start = (expSaPage - 1) * expSaLimit;
    let end = start + expSaLimit;
    
    expSaItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > expSaItems.length ? expSaItems.length : end;
    let startDisplay = expSaItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('exp-sa-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextExpSA() {
    if(expSaPage * expSaLimit < expSaItems.length) {
        expSaPage++;
        renderExpSa();
    }
}
function prevExpSA() {
    if(expSaPage > 1) {
        expSaPage--;
        renderExpSa();
    }
}

// Paginator for Admin ROP
let ropAdPage = 1;
const ropAdLimit = 5;
const adItems = document.querySelectorAll('.rop-admin-item');
if(adItems.length > 0) renderAdRop();

function renderAdRop() {
    let start = (ropAdPage - 1) * ropAdLimit;
    let end = start + ropAdLimit;
    
    adItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > adItems.length ? adItems.length : end;
    let startDisplay = adItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('rop-ad-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextRopAD() {
    if(ropAdPage * ropAdLimit < adItems.length) {
        ropAdPage++;
        renderAdRop();
    }
}
function prevRopAD() {
    if(ropAdPage > 1) {
        ropAdPage--;
        renderAdRop();
    }
}

// Paginator for Admin EXP
let expAdPage = 1;
const expAdLimit = 5;
const expAdItems = document.querySelectorAll('.exp-admin-item');
if(expAdItems.length > 0) renderExpAd();

function renderExpAd() {
    let start = (expAdPage - 1) * expAdLimit;
    let end = start + expAdLimit;
    
    expAdItems.forEach((el, i) => {
        if(i >= start && i < end) el.style.display = 'flex';
        else el.style.display = 'none';
    });
    
    let endDisplay = end > expAdItems.length ? expAdItems.length : end;
    let startDisplay = expAdItems.length === 0 ? 0 : start + 1;
    let rangeEl = document.getElementById('exp-ad-range');
    if(rangeEl) rangeEl.innerText = startDisplay + '-' + endDisplay;
}
function nextExpAD() {
    if(expAdPage * expAdLimit < expAdItems.length) {
        expAdPage++;
        renderExpAd();
    }
}
function prevExpAD() {
    if(expAdPage > 1) {
        expAdPage--;
        renderExpAd();
    }
}

