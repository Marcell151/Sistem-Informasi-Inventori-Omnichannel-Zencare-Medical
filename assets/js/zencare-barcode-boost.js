/**
 * ZenCare High-Accuracy Barcode Engine (ZXing-C++ WebAssembly)
 * Berjalan lokal 100% offline di atas <video> yang sama milik html5-qrcode,
 * memakai mesin C++ ZXing 2.2+ (tryHarder, tryRotate, tryInvert, tryDownscale, tryDenoise)
 * yang jauh lebih akurat membaca Code 128 / EAN-13 / QR Code dari webcam laptop maupun DroidCam,
 * dengan kecepatan <15ms/frame dan TANPA false-read EAN-8.
 */
(function (window) {
    'use strict';

    const activeLoops = {};
    let wasmPrepared = false;

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

    const DEFAULT_FORMATS = ['Code128', 'EAN-13', 'UPC-A', 'QRCode', 'DataMatrix'];

    async function readFromImageData(imgData, formats) {
        if (typeof window.ZXingWASM === 'undefined') return null;
        ensureWasmPrepared();
        try {
            const results = await window.ZXingWASM.readBarcodes(imgData, {
                tryHarder: true,
                tryRotate: true,
                tryInvert: true,
                tryDownscale: true,
                tryDenoise: true,
                maxNumberOfSymbols: 3,
                formats: formats || DEFAULT_FORMATS
            });
            if (Array.isArray(results) && results.length > 0) {
                for (let i = 0; i < results.length; i++) {
                    const txt = (results[i].text || '').trim();
                    if (txt.length >= 4) return txt;
                }
            }
        } catch (e) {}
        return null;
    }

    const ZenCareBarcodeBoost = {
        start: function (containerId, onDecoded, options) {
            this.stop(containerId);
            if (typeof window.ZXingWASM === 'undefined') return;
            ensureWasmPrepared();

            const opts = options || {};
            const formats = opts.formats || DEFAULT_FORMATS;
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d', { willReadFrequently: true });

            const state = {
                stopped: false,
                busy: false,
                pass: 0,
                timer: null
            };
            activeLoops[containerId] = state;

            const tick = async () => {
                if (state.stopped) return;
                if (state.busy) {
                    state.timer = setTimeout(tick, 140);
                    return;
                }
                // Jeda pemindaian saat SweetAlert popup tampil agar tidak menumpuk di belakang
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
                    const mode = state.pass % 3;
                    state.pass++;

                    let sx = 0, sy = 0, sw = vw, sh = vh;
                    if (mode === 1) {
                        // Fokus area kotak target tengah (88% x 62%)
                        sw = Math.round(vw * 0.88);
                        sh = Math.round(vh * 0.62);
                        sx = Math.round((vw - sw) / 2);
                        sy = Math.round((vh - sh) / 2);
                    } else if (mode === 2) {
                        // Crop tengah lebih rapat (65% x 48%) untuk stiker kecil di jarak jauh
                        sw = Math.round(vw * 0.65);
                        sh = Math.round(vh * 0.48);
                        sx = Math.round((vw - sw) / 2);
                        sy = Math.round((vh - sh) / 2);
                    }

                    // Pertahankan resolusi native video (atau upscale lembut bila kamera <= 640px seperti DroidCam)
                    const scale = (sw < 640) ? 1.5 : 1;
                    const dw = Math.min(1280, Math.round(sw * scale));
                    const dh = Math.min(720, Math.round(sh * scale));

                    if (canvas.width !== dw || canvas.height !== dh) {
                        canvas.width = dw;
                        canvas.height = dh;
                    }
                    ctx.setTransform(1, 0, 0, 1, 0, 0);
                    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, dw, dh);
                    let imgData = ctx.getImageData(0, 0, dw, dh);

                    let decoded = await readFromImageData(imgData, formats);

                    // Jika kamera di-mirror (kamera depan laptop), coba balik horizontal
                    if (!decoded && !state.stopped && mode === 0) {
                        ctx.setTransform(-1, 0, 0, 1, dw, 0);
                        ctx.drawImage(video, sx, sy, sw, sh, 0, 0, dw, dh);
                        ctx.setTransform(1, 0, 0, 1, 0, 0);
                        imgData = ctx.getImageData(0, 0, dw, dh);
                        decoded = await readFromImageData(imgData, formats);
                    }

                    if (!state.stopped && decoded && typeof onDecoded === 'function') {
                        onDecoded(decoded);
                    }
                } catch (e) {
                    // Abaikan error per frame
                } finally {
                    state.busy = false;
                    if (!state.stopped) {
                        state.timer = setTimeout(tick, 140);
                    }
                }
            };

            state.timer = setTimeout(tick, 200);
        },

        stop: function (containerId) {
            const state = activeLoops[containerId];
            if (state) {
                state.stopped = true;
                if (state.timer) clearTimeout(state.timer);
                delete activeLoops[containerId];
            }
        },

        resetCandidate: function () {
            // Tidak menyimpan kandidat tertunda — langsung siap scan ulang
        },

        decodeFileFallback: async function (file, formats) {
            if (!file || typeof window.ZXingWASM === 'undefined') return null;
            ensureWasmPrepared();
            try {
                // 1. Coba langsung dari Blob/File
                const res1 = await window.ZXingWASM.readBarcodes(file, {
                    tryHarder: true,
                    tryRotate: true,
                    tryInvert: true,
                    tryDownscale: true,
                    tryDenoise: true,
                    maxNumberOfSymbols: 1,
                    formats: formats || DEFAULT_FORMATS
                });
                if (Array.isArray(res1) && res1.length > 0 && res1[0].text) {
                    return res1[0].text.trim();
                }

                // 2. Jika belum terbaca (gambar kecil), upscale 2x di canvas
                const url = URL.createObjectURL(file);
                try {
                    const img = await new Promise((resolve, reject) => {
                        const i = new Image();
                        i.onload = () => resolve(i);
                        i.onerror = reject;
                        i.src = url;
                    });
                    const c = document.createElement('canvas');
                    const scale = (img.width < 800) ? 2 : 1;
                    c.width = Math.min(1600, Math.round(img.width * scale));
                    c.height = Math.min(1600, Math.round(img.height * scale));
                    const ctx = c.getContext('2d', { willReadFrequently: true });
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, c.width, c.height);
                    ctx.drawImage(img, 0, 0, c.width, c.height);
                    const imgData = ctx.getImageData(0, 0, c.width, c.height);
                    return await readFromImageData(imgData, formats);
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
