/**
 * ZenCare High-Accuracy Barcode Engine (ZXing-C++ WebAssembly + 1D Blur-Recovery + Fallback OCR)
 * Berjalan lokal 100% offline di atas <video> yang sama milik html5-qrcode:
 * 1. Utama   : Mesin C++ ZXing 2.2+ (tryHarder, tryRotate, tryInvert, tryDownscale, tryDenoise)
 *              + preprocessing penajaman 1D & auto-contrast + anti false-read.
 * 2. Cadangan: Otomatis membaca teks kode (Batch / SN / SKU) tepat di bawah garis barcode
 *              menggunakan Tesseract LSTM lokal (assets/js/ocr/) jika garis barcode 1D terlalu
 *              buram untuk didecode kamera VGA, tervalidasi ketat terhadap katalog produk.
 */
(function (window) {
    'use strict';

    const activeLoops = {};
    let wasmPrepared = false;
    let ocrScriptPromise = null;
    let ocrWorkerPromise = null;
    let ocrWorkerInstance = null;
    let ocrBusy = false;

    function getWasmBaseUrl() {
        try {
            const scripts = document.getElementsByTagName('script');
            for (let i = scripts.length - 1; i >= 0; i--) {
                const src = scripts[i].src || '';
                if (src.includes('zencare-barcode-boost.js')) {
                    return src.substring(0, src.lastIndexOf('/') + 1);
                }
            }
        } catch (e) {}
        return '../assets/js/';
    }

    function ensureWasmPrepared() {
        if (wasmPrepared || typeof window.ZXingWASM === 'undefined') return;
        const baseUrl = getWasmBaseUrl();
        try {
            window.ZXingWASM.prepareZXingModule({
                overrides: {
                    locateFile: function (path) {
                        return baseUrl + path;
                    }
                }
            });
            wasmPrepared = true;
        } catch (e) {}
    }

    function loadOcrScript() {
        if (typeof window.Tesseract !== 'undefined') return Promise.resolve(true);
        if (ocrScriptPromise) return ocrScriptPromise;
        ocrScriptPromise = new Promise((resolve) => {
            try {
                const script = document.createElement('script');
                script.src = getWasmBaseUrl() + 'ocr/tesseract.min.js';
                script.async = true;
                script.onload = () => resolve(typeof window.Tesseract !== 'undefined');
                script.onerror = () => resolve(false);
                document.head.appendChild(script);
            } catch (e) {
                resolve(false);
            }
        });
        return ocrScriptPromise;
    }

    function getOcrWorker() {
        if (ocrWorkerPromise) return ocrWorkerPromise;
        ocrWorkerPromise = (async () => {
            try {
                const loaded = await loadOcrScript();
                if (!loaded || typeof window.Tesseract === 'undefined') return null;
                const ocrBase = new URL(getWasmBaseUrl() + 'ocr/', window.location.href).href.replace(/\/$/, '');
                const worker = await window.Tesseract.createWorker('eng', 1, {
                    workerPath: ocrBase + '/worker.min.js',
                    corePath: ocrBase,
                    langPath: ocrBase,
                    workerBlobURL: false,
                    gzip: true
                });
                await worker.setParameters({
                    tessedit_pageseg_mode: '7', // PSM.SINGLE_LINE
                    tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-',
                    load_system_dawg: '0',
                    load_freq_dawg: '0'
                });
                ocrWorkerInstance = worker;
                return worker;
            } catch (e) {
                ocrWorkerPromise = null;
                ocrWorkerInstance = null;
                return null;
            }
        })();
        return ocrWorkerPromise;
    }

    const DEFAULT_FORMATS = ['Code128', 'EAN-13', 'UPC-A', 'QRCode', 'DataMatrix'];

    // Kumpulkan daftar kode valid (batch aktif, batch expired, SN, SKU) yang ada di halaman saat ini
    function collectKnownCatalogCodes() {
        const codes = new Map(); // UPPER -> original string
        try {
            const addCode = (raw) => {
                const c = (raw || '').trim();
                if (c.length >= 4) {
                    const up = c.toUpperCase();
                    if (!codes.has(up)) codes.set(up, c);
                }
            };
            const cards = document.getElementsByClassName('product-card');
            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const skuSpan = card.querySelector('span.font-mono');
                if (skuSpan) addCode(skuSpan.textContent);

                const bAttr = card.getAttribute('data-batch') || '';
                if (bAttr) {
                    const parts = bAttr.split(',');
                    for (let j = 0; j < parts.length; j++) addCode(parts[j].split('|')[0]);
                }
                const expAttr = card.getAttribute('data-expired-batch') || '';
                if (expAttr) {
                    const parts = expAttr.split(',');
                    for (let j = 0; j < parts.length; j++) addCode(parts[j].split('|')[0]);
                }
                const snAttr = card.getAttribute('data-sn') || '';
                if (snAttr) {
                    const parts = snAttr.split(',');
                    for (let j = 0; j < parts.length; j++) addCode(parts[j]);
                }
            }
        } catch (e) {}
        return codes;
    }

    function normalizeConfusables(str) {
        return str
            .replace(/O/g, '0')
            .replace(/[IL]/g, '1')
            .replace(/Z/g, '2')
            .replace(/S/g, '5')
            .replace(/G/g, '6')
            .replace(/B/g, '8');
    }

    function editDistanceMax2(a, b) {
        const la = a.length, lb = b.length;
        if (Math.abs(la - lb) > 1) return 3;
        let prev = new Int16Array(lb + 1);
        let curr = new Int16Array(lb + 1);
        for (let j = 0; j <= lb; j++) prev[j] = j;
        for (let i = 1; i <= la; i++) {
            curr[0] = i;
            let rowMin = curr[0];
            const ca = a.charCodeAt(i - 1);
            for (let j = 1; j <= lb; j++) {
                const cost = (ca === b.charCodeAt(j - 1)) ? 0 : 1;
                let v = prev[j] + 1;
                const ins = curr[j - 1] + 1;
                const sub = prev[j - 1] + cost;
                if (ins < v) v = ins;
                if (sub < v) v = sub;
                curr[j] = v;
                if (v < rowMin) rowMin = v;
            }
            if (rowMin > 2) return 3;
            const tmp = prev; prev = curr; curr = tmp;
        }
        return prev[lb];
    }

    function cleanOcrText(raw) {
        return (raw || '')
            .toUpperCase()
            .replace(/\s+/g, '')
            .replace(/[^A-Z0-9.-]/g, '')
            .replace(/^[-.]+|[-.]+$/g, '');
    }

    function isLikelyNoiseText(txt) {
        if (!txt || txt.length < 5) return true;
        if (/ZENCARE|EXP|HUFAGRIP|MEDICAL|STIKER|UNIT|STRIP|BOTOL|KARTON/i.test(txt)) return true;
        // Abaikan format tanggal murni seperti 15.10.2028 atau 15-10-2028
        if (/^\d{2}[-.]\d{2}[-.]\d{4}$/.test(txt)) return true;
        return false;
    }

    function recordOcrVote(state, code, neededVotes) {
        if (!state) return false;
        const now = Date.now();
        if (!Array.isArray(state.ocrVotes)) state.ocrVotes = [];
        // Simpan jendela suara 4 detik terakhir (tahan getaran tangan — tidak perlu 2 frame berturut-turut persis!)
        state.ocrVotes = state.ocrVotes.filter(v => (now - v.t) < 4000);
        state.ocrVotes.push({ code: code, t: now });
        let cnt = 0;
        for (let i = 0; i < state.ocrVotes.length; i++) {
            if (state.ocrVotes[i].code === code) cnt++;
        }
        if (cnt >= neededVotes) {
            state.ocrVotes = [];
            return true;
        }
        return false;
    }

    function isOnlyLetterOrConfusableDiff(a, b) {
        if (Math.abs(a.length - b.length) > 1) return false;
        // Semua digit angka di kedua kode harus identik persis (cegah salah batch seperti 28 vs 26)
        const digitsA = a.replace(/\D/g, '');
        const digitsB = b.replace(/\D/g, '');
        if (digitsA !== digitsB) return false;
        return editDistanceMax2(a, b) === 1;
    }

    // Pre-warm worker OCR secara diam-diam saat halaman idle agar saat modal kamera dibuka, OCR langsung siap (0ms tunggu)
    if (typeof window.addEventListener === 'function') {
        window.addEventListener('load', function () {
            setTimeout(function () {
                if (!ocrWorkerPromise && typeof window.ZXingWASM !== 'undefined') {
                    getOcrWorker();
                }
            }, 1200);
        });
    }

    function resolveOcrCandidate(rawText, confidence, state, isFileFallback) {
        const clean = cleanOcrText(rawText);
        if (isLikelyNoiseText(clean)) return null;

        const known = collectKnownCatalogCodes();

        // 1. Cocok persis dengan kode di katalog halaman (langsung akurat & instan!)
        if (known.size > 0 && known.has(clean)) {
            if (state) state.ocrVotes = [];
            return known.get(clean);
        }

        // 2. Normalisasi karakter mirip OCR (O/0, I/L/1, B/8, S/5, Z/2, G/6) — hanya jika unik
        if (known.size > 0 && clean.length >= 6) {
            const normClean = normalizeConfusables(clean);
            const normMatches = [];
            known.forEach((orig, up) => {
                if (up.length === clean.length && normalizeConfusables(up) === normClean) {
                    normMatches.push(orig);
                }
            });
            if (normMatches.length === 1) {
                const candidate = normMatches[0];
                if (isFileFallback || recordOcrVote(state, candidate, 2)) {
                    return candidate;
                }
                return null;
            }

            // 3. Beda 1 huruf non-angka yang unik terhadap katalog (misal MUF-TMP-28 -> HUF-TMP-28),
            //    TIDAK PERNAH menukar angka berbeda (misal HUF-TMP-28 tidak akan diubah jadi HUF-TMP-26!)
            if (clean.length >= 7) {
                const letterDiffs = [];
                known.forEach((orig, up) => {
                    if (isOnlyLetterOrConfusableDiff(clean, up)) {
                        letterDiffs.push(orig);
                    }
                });
                if (letterDiffs.length === 1) {
                    const candidate = letterDiffs[0];
                    if (isFileFallback || recordOcrVote(state, candidate, 2)) {
                        return candidate;
                    }
                    return null;
                }
            }
        }

        // 4. Kode terstruktur yang belum ada di DOM katalog (misal batch kadaluarsa / belum terdaftar / halaman tambah_stok / pesanan)
        const hasStructuredCode = (/^[A-Z0-9]{2,}[-.][A-Z0-9.-]{2,}$/.test(clean) && /\d/.test(clean)) || /^\d{8,14}$/.test(clean);
        if (hasStructuredCode && clean.length >= 6) {
            if (isFileFallback) return clean;
            if (recordOcrVote(state, clean, 2)) {
                return clean;
            }
        }

        return null;
    }

    /**
     * Ekstrak potongan gambar teks tepat di bawah garis barcode (misal: HUF-TMP-28, HUF-24J089.A)
     * - Cepat (< 5ms di main thread) dengan estimasi background 1D/blok ringan
     * - Mengunci HANYA baris teks pertama di bawah barcode (anti bocor ke footer ZenCare / EXP)
     * - Mengikuti posisi horizontal barcode secara dinamis (tidak terpotong walau stiker agak ke kiri/kanan)
     */
    function extractBelowBarcodeOcrCanvas(sourceCanvas, outCanvas, biasOverride) {
        const srcCtx = sourceCanvas.getContext('2d', { willReadFrequently: true });
        const cw = sourceCanvas.width;
        const ch = sourceCanvas.height;
        if (cw < 80 || ch < 60) return null;

        const imgData = srcCtx.getImageData(0, 0, cw, ch);
        const rgba = imgData.data;
        const len = cw * ch;
        const gray = new Uint8Array(len);
        for (let i = 0, p = 0; i < len; i++, p += 4) {
            gray[i] = (rgba[p] * 77 + rgba[p + 1] * 150 + rgba[p + 2] * 29) >> 8;
        }

        // 1. Cari pita garis barcode melalui energi gradien horizontal per baris
        const xStart = Math.floor(cw * 0.06);
        const xEnd = Math.floor(cw * 0.94);
        const yStart = Math.floor(ch * 0.08);
        const yEnd = Math.floor(ch * 0.78);
        const rowE = new Float32Array(ch);
        let bestY = Math.floor(ch * 0.45);
        let bestE = 0;
        for (let y = yStart; y < yEnd; y++) {
            let e = 0;
            const off = y * cw;
            for (let x = xStart + 2; x < xEnd; x += 2) {
                const d = gray[off + x] - gray[off + x - 2];
                e += d < 0 ? -d : d;
            }
            rowE[y] = e;
            if (e > bestE) {
                bestE = e;
                bestY = y;
            }
        }
        // Tidak ada pola barcode kontras tinggi di frame
        if (bestE < (xEnd - xStart) * 3.5) return null;

        const thrE = bestE * 0.40;
        let barTop = bestY;
        let barBot = bestY;
        while (barTop > yStart && rowE[barTop] > thrE) barTop--;
        while (barBot < yEnd && rowE[barBot] > thrE) barBot++;
        const barH = Math.max(12, barBot - barTop);

        // Deteksi rentang horizontal barcode pada baris tengah agar stiker yang sedikit miring/geser tidak terpotong
        let barLeft = xStart;
        let barRight = xEnd;
        {
            const midOff = bestY * cw;
            let rowMaxG = 0;
            for (let x = xStart; x < xEnd; x++) {
                if (gray[midOff + x] > rowMaxG) rowMaxG = gray[midOff + x];
            }
            const colE = new Float32Array(cw);
            let maxColE = 0;
            for (let x = xStart + 4; x < xEnd - 4; x++) {
                let s = 0;
                for (let k = -3; k <= 3; k++) {
                    const d = gray[midOff + x + k] - gray[midOff + x + k - 1];
                    s += d < 0 ? -d : d;
                }
                colE[x] = s;
                if (s > maxColE) maxColE = s;
            }
            if (maxColE > 25) {
                const cThr = maxColE * 0.22;
                let l = xStart + 4;
                while (l < xEnd - 4 && colE[l] < cThr) l++;
                let r = xEnd - 5;
                while (r > l && colE[r] < cThr) r--;
                if ((r - l) > cw * 0.25) {
                    // Teks kode di bawah barcode selalu berada di dalam lebar pita garis barcode,
                    // jadi tetap jaga margin dalam agar bayangan jari/latar gelap di luar stiker tidak ikut masuk
                    barLeft = Math.max(Math.floor(cw * 0.14), l + 4);
                    barRight = Math.min(Math.floor(cw * 0.86), r - 4);
                }
            }
        }

        // Area pencarian teks tepat di bawah ujung bawah garis barcode
        const searchTop = Math.min(ch - 12, barBot + Math.max(3, Math.round(barH * 0.10)));
        const searchBot = Math.min(ch - 2, barBot + Math.max(Math.round(barH * 0.80), Math.round(ch * 0.18)));
        const searchLeft = barLeft;
        const searchRight = barRight;
        const sw = searchRight - searchLeft;
        const sh = searchBot - searchTop;
        if (sw < 36 || sh < 8) return null;

        // 2. Pengurangan background lokal CEPAT (estimasi kertas putih per 2-step grid + dilasi horizontal 1D)
        const rowBg = new Uint8Array(sw * sh);
        for (let y = 0; y < sh; y++) {
            const gy = searchTop + y;
            const gRow = gy * cw;
            const outRow = y * sw;
            for (let x = 0; x < sw; x++) {
                const gx = searchLeft + x;
                const x0 = gx - 12 < searchLeft ? searchLeft : gx - 12;
                const x1 = gx + 12 >= searchRight ? searchRight - 1 : gx + 12;
                let bg = 0;
                for (let nx = x0; nx <= x1; nx += 4) {
                    const v = gray[gRow + nx];
                    if (v > bg) bg = v;
                }
                rowBg[outRow + x] = bg;
            }
        }
        const sub = new Float32Array(sw * sh);
        let maxInk = 0;
        for (let y = 0; y < sh; y++) {
            const gy = searchTop + y;
            const y0 = y > 6 ? y - 6 : 0;
            const y1 = y + 6 < sh ? y + 6 : sh - 1;
            const gRow = gy * cw;
            const outRow = y * sw;
            for (let x = 0; x < sw; x++) {
                let bg = rowBg[outRow + x];
                const bgTop = rowBg[y0 * sw + x];
                const bgBot = rowBg[y1 * sw + x];
                if (bgTop > bg) bg = bgTop;
                if (bgBot > bg) bg = bgBot;
                const ink = bg - gray[gRow + searchLeft + x];
                const val = ink > 0 ? ink : 0;
                sub[outRow + x] = val;
                if (val > maxInk) maxInk = val;
            }
        }
        if (maxInk < 16) return null;

        // 3. Kunci HANYA pita baris teks PERTAMA di bawah barcode (berhenti saat ketemu 2 baris kosong agar ZenCare/EXP tidak ikut)
        const inkThr = maxInk * 0.30;
        let minY = -1, maxY = -1;
        let emptyStreak = 0;
        for (let y = 0; y < sh; y++) {
            let rowCnt = 0;
            const off = y * sw;
            for (let x = 0; x < sw; x++) {
                if (sub[off + x] > inkThr) rowCnt++;
            }
            if (rowCnt >= 4) {
                if (minY === -1) minY = y;
                maxY = y;
                emptyStreak = 0;
            } else if (minY !== -1) {
                emptyStreak++;
                if (emptyStreak >= 2 && (maxY - minY) >= 4) break;
            }
        }
        if (minY === -1 || maxY <= minY) return null;
        minY = Math.max(0, minY - 2);
        maxY = Math.min(sh - 1, maxY + 2);

        let minX = sw, maxX = 0;
        for (let x = 0; x < sw; x++) {
            for (let y = minY; y <= maxY; y++) {
                if (sub[y * sw + x] > inkThr) {
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                }
            }
        }
        if (maxX <= minX) return null;
        minX = Math.max(0, minX - 4);
        maxX = Math.min(sw - 1, maxX + 4);

        const cw2 = maxX - minX + 1;
        const ch2 = maxY - minY + 1;
        if (cw2 < 18 || ch2 < 5) return null;

        // 4. Upscale ke tinggi huruf ~44px + 3x3 unsharp mask cepat + padding putih 24px
        const scale = Math.max(2, Math.min(4, Math.round(44 / Math.max(8, ch2))));
        const pad = 24;
        const uw = cw2 * scale;
        const uh = ch2 * scale;
        const ow = uw + pad * 2;
        const oh = uh + pad * 2;

        const up = new Float32Array(uw * uh);
        for (let y = 0; y < uh; y++) {
            const sy = minY + y / scale;
            const y0 = sy >= sh - 1 ? sh - 1 : Math.floor(sy);
            const y1 = y0 + 1 < sh ? y0 + 1 : sh - 1;
            const fy = sy - y0;
            const r0 = y0 * sw, r1 = y1 * sw;
            for (let x = 0; x < uw; x++) {
                const sx = minX + x / scale;
                const x0 = sx >= sw - 1 ? sw - 1 : Math.floor(sx);
                const x1 = x0 + 1 < sw ? x0 + 1 : sw - 1;
                const fx = sx - x0;
                up[y * uw + x] = (sub[r0 + x0] * (1 - fx) + sub[r0 + x1] * fx) * (1 - fy) +
                                 (sub[r1 + x0] * (1 - fx) + sub[r1 + x1] * fx) * fy;
            }
        }

        const sharpAmt = 1.5;
        const bias = (typeof biasOverride === 'number') ? biasOverride : 0.36;
        const loCut = maxInk * bias;
        const hiCut = maxInk * 0.90;
        const span = Math.max(8, hiCut - loCut);

        outCanvas.width = ow;
        outCanvas.height = oh;
        const outCtx = outCanvas.getContext('2d', { willReadFrequently: true });
        const outImg = outCtx.createImageData(ow, oh);
        const outData = outImg.data;
        outData.fill(255);

        for (let y = 0; y < uh; y++) {
            const outRow = (y + pad) * ow;
            const yA = y > 0 ? y - 1 : 0;
            const yB = y + 1 < uh ? y + 1 : uh - 1;
            const rowA = yA * uw, rowC = y * uw, rowB = yB * uw;
            for (let x = 0; x < uw; x++) {
                const xA = x > 0 ? x - 1 : 0;
                const xB = x + 1 < uw ? x + 1 : uw - 1;
                const avg = (
                    up[rowA + xA] + up[rowA + x] + up[rowA + xB] +
                    up[rowC + xA] + up[rowC + x] + up[rowC + xB] +
                    up[rowB + xA] + up[rowB + x] + up[rowB + xB]
                ) / 9;
                const cur = up[rowC + x];
                const shVal = cur + sharpAmt * (cur - avg);
                let normInk = (shVal - loCut) / span;
                if (normInk < 0) normInk = 0;
                else if (normInk > 1) normInk = 1;
                const pxVal = Math.round((1 - normInk) * 255);
                const p = (outRow + x + pad) * 4;
                outData[p] = pxVal;
                outData[p + 1] = pxVal;
                outData[p + 2] = pxVal;
                outData[p + 3] = 255;
            }
        }
        outCtx.putImageData(outImg, 0, 0);
        return outCanvas;
    }

    /**
     * Preprocessing cepat (in-place pada ImageData) khusus menolong barcode 1D yang agak buram / redup:
     * 1. Konversi grayscale cepat
     * 2. Vertical binomial denoise (2-3 pass)
     * 3. Horizontal 1D Unsharp Mask (radius=2)
     * 4. Histogram Auto-Contrast (3%..97%)
     */
    function enhanceImageDataFor1D(imgData, strength) {
        const w = imgData.width;
        const h = imgData.height;
        const data = imgData.data;
        const len = w * h;
        if (len === 0) return imgData;

        const gray = new Uint8Array(len);
        for (let i = 0, p = 0; i < len; i++, p += 4) {
            gray[i] = (data[p] * 77 + data[p + 1] * 150 + data[p + 2] * 29) >> 8;
        }

        const vPasses = (strength >= 2) ? 3 : 2;
        let cur = gray;
        let nxt = new Uint8Array(len);
        for (let vp = 0; vp < vPasses; vp++) {
            for (let x = 0; x < w; x++) {
                nxt[x] = cur[x];
                nxt[(h - 1) * w + x] = cur[(h - 1) * w + x];
            }
            for (let y = 1; y < h - 1; y++) {
                const row = y * w;
                const prev = row - w;
                const next = row + w;
                for (let x = 0; x < w; x++) {
                    nxt[row + x] = (cur[prev + x] + (cur[row + x] << 1) + cur[next + x]) >> 2;
                }
            }
            const tmp = cur;
            cur = nxt;
            nxt = tmp;
        }
        const vavg = cur;

        const radius = 2;
        const amount = (strength >= 2) ? 2.4 : 1.5;
        const sharp = nxt;
        const hist = new Int32Array(256);

        const prefix = new Int32Array(w + 1);
        for (let y = 0; y < h; y++) {
            const row = y * w;
            prefix[0] = 0;
            for (let x = 0; x < w; x++) {
                prefix[x + 1] = prefix[x] + vavg[row + x];
            }
            for (let x = 0; x < w; x++) {
                const x0 = x > radius ? x - radius : 0;
                const x1 = x + radius < w - 1 ? x + radius : w - 1;
                const blur = (prefix[x1 + 1] - prefix[x0]) / (x1 - x0 + 1);
                const c = vavg[row + x];
                let v = Math.round(c + amount * (c - blur));
                if (v < 0) v = 0;
                else if (v > 255) v = 255;
                sharp[row + x] = v;
                hist[v]++;
            }
        }

        const loTarget = Math.floor(len * 0.03);
        const hiTarget = Math.floor(len * 0.97);
        let acc = 0, lo = 0, hi = 255;
        for (let i = 0; i < 256; i++) {
            acc += hist[i];
            if (acc >= loTarget) { lo = i; break; }
        }
        acc = 0;
        for (let i = 255; i >= 0; i--) {
            acc += hist[i];
            if (acc >= (len - hiTarget)) { hi = i; break; }
        }

        const span = hi - lo;
        if (span > 12 && span < 245) {
            const lut = new Uint8Array(256);
            const scale = 255 / span;
            for (let i = 0; i < 256; i++) {
                let v = Math.round((i - lo) * scale);
                if (v < 0) v = 0;
                else if (v > 255) v = 255;
                lut[i] = v;
            }
            for (let i = 0, p = 0; i < len; i++, p += 4) {
                const v = lut[sharp[i]];
                data[p] = v;
                data[p + 1] = v;
                data[p + 2] = v;
                data[p + 3] = 255;
            }
        } else {
            for (let i = 0, p = 0; i < len; i++, p += 4) {
                const v = sharp[i];
                data[p] = v;
                data[p + 1] = v;
                data[p + 2] = v;
                data[p + 3] = 255;
            }
        }

        return imgData;
    }

    async function readFromImageData(imgData, formats, extraOpts) {
        if (typeof window.ZXingWASM === 'undefined') return null;
        ensureWasmPrepared();
        try {
            const opts = Object.assign({
                tryHarder: true,
                tryRotate: true,
                tryInvert: true,
                tryDownscale: true,
                tryDenoise: true,
                minLineCount: 2,
                maxNumberOfSymbols: 3,
                formats: formats || DEFAULT_FORMATS
            }, extraOpts || {});

            const results = await window.ZXingWASM.readBarcodes(imgData, opts);
            if (Array.isArray(results) && results.length > 0) {
                for (let i = 0; i < results.length; i++) {
                    const txt = (results[i].text || '').trim();
                    if (txt.length >= 4) return txt;
                }
            }
        } catch (e) {}
        return null;
    }

    // Validasi hasil bacaan minLineCount=1 agar tidak menghasilkan false-read seperti "HJy1928"
    function validateSoftBarcode(txt, state) {
        if (!txt || txt.length < 4) return null;
        const known = collectKnownCatalogCodes();
        const up = txt.toUpperCase();
        if (known.size > 0 && known.has(up)) {
            return known.get(up);
        }
        const now = Date.now();
        if (state && state.lastBarcodeCandidate === up && (now - state.lastBarcodeAt) < 1500) {
            state.lastBarcodeCandidate = '';
            return txt;
        }
        if (state) {
            state.lastBarcodeCandidate = up;
            state.lastBarcodeAt = now;
        }
        return null;
    }

    const ZenCareBarcodeBoost = {
        start: function (containerId, onDecoded, options) {
            this.stop(containerId);
            if (typeof window.ZXingWASM === 'undefined') return;
            ensureWasmPrepared();
            // Pre-warm OCR worker di background agar siap instan saat barcode 1D gagal terbaca
            getOcrWorker();

            const opts = options || {};
            const formats = opts.formats || DEFAULT_FORMATS;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });
            const ocrSrcCanvas = document.createElement('canvas');
            const ocrSrcCtx = ocrSrcCanvas.getContext('2d', { willReadFrequently: true });
            const ocrOutCanvas = document.createElement('canvas');

            const state = {
                stopped: false,
                busy: false,
                pass: 0,
                ocrPass: 0,
                consecutiveMisses: 0,
                lastOcrRunAt: 0,
                ocrVotes: [],
                lastBarcodeCandidate: '',
                lastBarcodeAt: 0,
                timer: null
            };
            activeLoops[containerId] = state;

            const runOcrFallbackAsync = (video) => {
                if (state.stopped || ocrBusy) return;
                // Tunggu sampai worker OCR benar-benar siap agar tidak menyita frame kosong saat inisialisasi
                if (!ocrWorkerInstance) {
                    getOcrWorker();
                    return;
                }
                const now = Date.now();
                if (now - state.lastOcrRunAt < 120) return;
                if (typeof window.Swal !== 'undefined' && window.Swal.isVisible()) return;

                const vw = video.videoWidth;
                const vh = video.videoHeight;
                if (!vw || !vh) return;

                // Tangkap area viewfinder (96% x 84%) ke ukuran kerja cepat ~400px
                const sw = Math.round(vw * 0.96);
                const sh = Math.round(vh * 0.84);
                const sx = Math.round((vw - sw) / 2);
                const sy = Math.round((vh - sh) / 2);
                const scale = sw > 400 ? (400 / sw) : 1;
                const dw = Math.round(sw * scale);
                const dh = Math.round(sh * scale);

                ocrSrcCanvas.width = dw;
                ocrSrcCanvas.height = dh;
                ocrSrcCtx.drawImage(video, sx, sy, sw, sh, 0, 0, dw, dh);

                // Ganti bergantian bias kontras (0.34 vs 0.43) agar adaptif terhadap cahaya terang/redup & blur
                const bias = (state.ocrPass % 2 === 0) ? 0.34 : 0.43;
                state.ocrPass++;

                const textCanvas = extractBelowBarcodeOcrCanvas(ocrSrcCanvas, ocrOutCanvas, bias);
                if (!textCanvas) return;

                ocrBusy = true;
                state.lastOcrRunAt = now;

                ocrWorkerInstance.recognize(textCanvas).then((result) => {
                    if (state.stopped) return;
                    if (typeof window.Swal !== 'undefined' && window.Swal.isVisible()) return;

                    const rawText = (result && result.data && result.data.text) ? result.data.text : '';
                    const conf = (result && result.data && typeof result.data.confidence === 'number') ? result.data.confidence : 0;
                    const resolved = resolveOcrCandidate(rawText, conf, state, false);
                    if (resolved && !state.stopped && typeof onDecoded === 'function') {
                        state.consecutiveMisses = 0;
                        onDecoded(resolved);
                    }
                }).catch(() => {}).finally(() => {
                    ocrBusy = false;
                });
            };

            const tick = async () => {
                if (state.stopped) return;
                if (state.busy) {
                    state.timer = setTimeout(tick, 120);
                    return;
                }
                if (typeof window.Swal !== 'undefined' && window.Swal.isVisible()) {
                    state.timer = setTimeout(tick, 220);
                    return;
                }

                const container = document.getElementById(containerId);
                const video = container ? container.querySelector('video') : null;
                if (!video || video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
                    state.timer = setTimeout(tick, 220);
                    return;
                }

                state.busy = true;
                try {
                    const vw = video.videoWidth;
                    const vh = video.videoHeight;
                    const mode = state.pass % 4;
                    state.pass++;

                    let sx = 0, sy = 0, sw = vw, sh = vh;
                    let enhanceStrength = 0;
                    let binarizer = 'LocalAverage';
                    let targetMinWidth = 640;
                    let useSoftLine = false;

                    if (mode === 0) {
                        targetMinWidth = (vw <= 640) ? 800 : vw;
                    } else if (mode === 1) {
                        sw = Math.round(vw * 0.94);
                        sh = Math.round(vh * 0.60);
                        sx = Math.round((vw - sw) / 2);
                        sy = Math.round((vh - sh) / 2);
                        enhanceStrength = 1;
                        binarizer = 'LocalAverage';
                        targetMinWidth = 768;
                        useSoftLine = true;
                    } else if (mode === 2) {
                        sw = Math.round(vw * 0.94);
                        sh = Math.round(vh * 0.56);
                        sx = Math.round((vw - sw) / 2);
                        sy = Math.round((vh - sh) / 2);
                        enhanceStrength = 2;
                        binarizer = 'GlobalHistogram';
                        targetMinWidth = 800;
                        useSoftLine = true;
                    } else {
                        sw = Math.round(vw * 0.78);
                        sh = Math.round(vh * 0.50);
                        sx = Math.round((vw - sw) / 2);
                        sy = Math.round((vh - sh) / 2);
                        enhanceStrength = 1;
                        binarizer = 'LocalAverage';
                        targetMinWidth = 768;
                    }

                    const scale = (sw < targetMinWidth) ? Math.min(2, targetMinWidth / sw) : 1;
                    const dw = Math.min(1280, Math.round(sw * scale));
                    const dh = Math.min(720, Math.round(sh * scale));

                    if (canvas.width !== dw || canvas.height !== dh) {
                        canvas.width = dw;
                        canvas.height = dh;
                    }
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    ctx.setTransform(1, 0, 0, 1, 0, 0);
                    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, dw, dh);
                    let imgData = ctx.getImageData(0, 0, dw, dh);

                    if (enhanceStrength > 0) {
                        enhanceImageDataFor1D(imgData, enhanceStrength);
                    }

                    let decoded = await readFromImageData(imgData, formats, {
                        binarizer: binarizer,
                        minLineCount: useSoftLine ? 1 : 2
                    });
                    if (decoded && useSoftLine) {
                        decoded = validateSoftBarcode(decoded, state);
                    }

                    if (!decoded && !state.stopped && mode === 0) {
                        ctx.setTransform(-1, 0, 0, 1, dw, 0);
                        ctx.drawImage(video, sx, sy, sw, sh, 0, 0, dw, dh);
                        ctx.setTransform(1, 0, 0, 1, 0, 0);
                        imgData = ctx.getImageData(0, 0, dw, dh);
                        decoded = await readFromImageData(imgData, formats, {
                            binarizer: 'LocalAverage',
                            minLineCount: 2
                        });
                    }

                    if (!state.stopped && decoded && typeof onDecoded === 'function') {
                        state.consecutiveMisses = 0;
                        state.ocrVotes = [];
                        onDecoded(decoded);
                    } else if (!state.stopped) {
                        state.consecutiveMisses++;
                        // Setelah 2 pass barcode gagal (~280ms), langsung jalankan cadangan OCR di background
                        if (state.consecutiveMisses >= 2) {
                            runOcrFallbackAsync(video);
                        }
                    }
                } catch (e) {
                    // Abaikan error per frame
                } finally {
                    state.busy = false;
                    if (!state.stopped) {
                        state.timer = setTimeout(tick, 150);
                    }
                }
            };

            state.timer = setTimeout(tick, 180);
        },

        stop: function (containerId) {
            const state = activeLoops[containerId];
            if (state) {
                state.stopped = true;
                if (state.timer) clearTimeout(state.timer);
                delete activeLoops[containerId];
            }
        },

        resetCandidate: function (containerId) {
            if (containerId && activeLoops[containerId]) {
                activeLoops[containerId].ocrVotes = [];
                activeLoops[containerId].lastBarcodeCandidate = '';
                activeLoops[containerId].lastBarcodeAt = 0;
                activeLoops[containerId].consecutiveMisses = 0;
            }
        },

        decodeFileFallback: async function (file, formats) {
            if (!file || typeof window.ZXingWASM === 'undefined') return null;
            ensureWasmPrepared();
            try {
                // 1. Coba langsung dari Blob/File (ZXing)
                const res1 = await window.ZXingWASM.readBarcodes(file, {
                    tryHarder: true,
                    tryRotate: true,
                    tryInvert: true,
                    tryDownscale: true,
                    tryDenoise: true,
                    minLineCount: 2,
                    maxNumberOfSymbols: 1,
                    formats: formats || DEFAULT_FORMATS
                });
                if (Array.isArray(res1) && res1.length > 0 && res1[0].text) {
                    return res1[0].text.trim();
                }

                // 2. Upscale + penajaman 1D bertahap, lalu cadangan OCR teks di bawah barcode bila foto agak buram
                const url = URL.createObjectURL(file);
                try {
                    const img = await new Promise((resolve, reject) => {
                        const i = new Image();
                        i.onload = () => resolve(i);
                        i.onerror = reject;
                        i.src = url;
                    });
                    const c = document.createElement('canvas');
                    const scale = (img.width < 900) ? Math.min(2, 1200 / Math.max(1, img.width)) : 1;
                    c.width = Math.min(1600, Math.round(img.width * scale));
                    c.height = Math.min(1600, Math.round(img.height * scale));
                    const ctx = c.getContext('2d', { willReadFrequently: true });

                    const attempts = [
                        { strength: 0, binarizer: 'LocalAverage' },
                        { strength: 1, binarizer: 'LocalAverage' },
                        { strength: 2, binarizer: 'GlobalHistogram' }
                    ];
                    for (let a = 0; a < attempts.length; a++) {
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, c.width, c.height);
                        ctx.drawImage(img, 0, 0, c.width, c.height);
                        const imgData = ctx.getImageData(0, 0, c.width, c.height);
                        if (attempts[a].strength > 0) {
                            enhanceImageDataFor1D(imgData, attempts[a].strength);
                        }
                        const txt = await readFromImageData(imgData, formats, {
                            binarizer: attempts[a].binarizer,
                            minLineCount: 2
                        });
                        if (txt) return txt;
                    }

                    // 3. Cadangan OCR teks tepat di bawah barcode pada foto upload
                    const ocrSrc = document.createElement('canvas');
                    const oScale = img.width > 500 ? (500 / img.width) : 1;
                    ocrSrc.width = Math.round(img.width * oScale);
                    ocrSrc.height = Math.round(img.height * oScale);
                    ocrSrc.getContext('2d', { willReadFrequently: true }).drawImage(img, 0, 0, ocrSrc.width, ocrSrc.height);
                    const ocrOut = document.createElement('canvas');
                    const textCanvas = extractBelowBarcodeOcrCanvas(ocrSrc, ocrOut);
                    if (textCanvas) {
                        const worker = await getOcrWorker();
                        if (worker) {
                            const result = await worker.recognize(textCanvas);
                            const rawText = (result && result.data && result.data.text) ? result.data.text : '';
                            const conf = (result && result.data && typeof result.data.confidence === 'number') ? result.data.confidence : 0;
                            const resolved = resolveOcrCandidate(rawText, conf, null, true);
                            if (resolved) return resolved;
                        }
                    }
                    return null;
                } finally {
                    URL.revokeObjectURL(url);
                }
            } catch (e) {
                return null;
            }
        }
    };

    window.ZenCareBarcodeBoost = ZenCareBarcodeBoost;
})(window);
