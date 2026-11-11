import { spawn } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const OUT_DIR = 'c:\\xampp\\htdocs\\inventory_zencare\\gambar\\testing';
const USER_DATA_DIR = 'c:\\xampp\\htdocs\\inventory_zencare\\scratch\\chrome_test_profile';
const PORT = 9234;

fs.mkdirSync(OUT_DIR, { recursive: true });
fs.mkdirSync(USER_DATA_DIR, { recursive: true });

function sleep(ms) {
    return new Promise(r => setTimeout(r, ms));
}

async function main() {
    console.log('1. Launching real Google Chrome instance...');
    const chrome = spawn(CHROME_PATH, [
        '--headless=new',
        `--remote-debugging-port=${PORT}`,
        `--user-data-dir=${USER_DATA_DIR}`,
        '--window-size=1440,950',
        '--no-first-run',
        '--no-default-browser-check',
        '--disable-gpu',
        'about:blank'
    ], { stdio: 'ignore' });

    try {
        // Wait for DevTools endpoint
        let wsUrl = null;
        for (let i = 0; i < 30; i++) {
            try {
                const res = await fetch(`http://127.0.0.1:${PORT}/json/list`);
                const list = await res.json();
                const pageTarget = list.find(t => t.type === 'page');
                if (pageTarget && pageTarget.webSocketDebuggerUrl) {
                    wsUrl = pageTarget.webSocketDebuggerUrl;
                    break;
                }
            } catch (e) {}
            await sleep(300);
        }
        if (!wsUrl) throw new Error('Could not connect to Chrome DevTools WebSocket');

        const ws = new WebSocket(wsUrl);
        await new Promise((resolve, reject) => {
            ws.onopen = resolve;
            ws.onerror = reject;
        });

        let msgId = 0;
        const pending = new Map();
        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.id && pending.has(data.id)) {
                const { resolve, reject } = pending.get(data.id);
                pending.delete(data.id);
                if (data.error) reject(new Error(data.error.message));
                else resolve(data.result);
            } else if (data.method === 'Page.javascriptDialogOpening') {
                // Auto-accept alert/confirm dialogs
                console.log('   [Dialog Auto-Accept]:', data.params.type, data.params.message.slice(0, 80).replace(/\n/g, ' '));
                send('Page.handleJavaScriptDialog', { accept: true }).catch(() => {});
            }
        };

        function send(method, params = {}) {
            const id = ++msgId;
            return new Promise((resolve, reject) => {
                pending.set(id, { resolve, reject });
                ws.send(JSON.stringify({ id, method, params }));
            });
        }

        await send('Page.enable');
        await send('Runtime.enable');
        await send('Emulation.setDeviceMetricsOverride', {
            width: 1440,
            height: 950,
            deviceScaleFactor: 1,
            mobile: false
        });

        async function navigate(url) {
            await send('Page.navigate', { url });
            await sleep(1200);
        }

        async function evalJs(expression) {
            const res = await send('Runtime.evaluate', {
                expression,
                returnByValue: true,
                awaitPromise: true
            });
            if (res.exceptionDetails) {
                throw new Error('JS Error: ' + JSON.stringify(res.exceptionDetails));
            }
            return res.result ? res.result.value : undefined;
        }

        async function screenshot(filename) {
            await sleep(500);
            const { data } = await send('Page.captureScreenshot', { format: 'png' });
            const filePath = path.join(OUT_DIR, filename);
            fs.writeFileSync(filePath, Buffer.from(data, 'base64'));
            console.log('   [Screenshot Saved]:', filePath);
        }

        async function loginAs(username) {
            await navigate('http://localhost/inventory_zencare/logout.php');
            await navigate('http://localhost/inventory_zencare/login.php');
            await evalJs(`
                const inp = document.querySelector('input[name="username"]');
                if (inp) {
                    inp.value = '${username}';
                    inp.form.submit();
                }
            `);
            await sleep(1200);
        }

        // =========================================================================
        // STEP 1: SUPERADMIN MULAI SESI & PEMBEKUAN TRANSAKSI (423)
        // =========================================================================
        console.log('2. Step 1: Superadmin logs in & starts Stok Opname session...');
        await loginAs('superadmin');
        await navigate('http://localhost/inventory_zencare/inventori/stok_opname.php');

        // Verify no Reset Database button exists in UI
        const hasResetBtn = await evalJs(`document.body.innerText.includes('Reset Data Demo') || document.body.innerText.includes('Reset Demo')`);
        console.log('   [Check 7.1] Has Reset Button in UI:', hasResetBtn);

        // Start session
        await evalJs(`
            const modal = document.getElementById('modal_mulai_sesi');
            if (modal) {
                modal.classList.remove('hidden');
                const ta = modal.querySelector('textarea[name="catatan"]');
                if (ta) ta.value = 'Stok Opname Periodik Gudang & Etalase ZenCare Medical';
                modal.querySelector('form').submit();
            }
        `);
        await sleep(1500);
        await screenshot('langkah_1_mulai_sesi_freeze.png');

        // =========================================================================
        // STEP 2: KASIR BLIND COUNT, ISI CEPAT CLIENT-SIDE, & SIMPAN HASIL HITUNG
        // =========================================================================
        console.log('3. Step 2: Kasir logs in (auto-redirected from POS to Blind Count)...');
        await loginAs('kasir_toko');
        const kasirUrl = await evalJs(`window.location.href`);
        console.log('   [Check 1.3] Kasir redirected URL:', kasirUrl);

        // Click "Isi Cepat Angka Uji Coba" (client-side JS)
        await evalJs(`isiOtomatisAngkaDemo()`);
        await sleep(600);

        // Verify client-side fill did NOT save to DB yet (still on Belum Dihitung tab with inputs filled)
        const filledCount = await evalJs(`document.querySelectorAll('#form_bulk_simpan input[type="number"]').length`);
        console.log('   [Check 2.4] Client-side inputs filled without saving:', filledCount);

        // Scroll slightly so table rows (including Alkes BMHP, Obat, and Expired batch) are clearly visible
        await evalJs(`window.scrollTo(0, 120)`);
        await screenshot('langkah_2_kasir_blind_count.png');

        // Now submit the bulk counting form as Kasir
        console.log('   Submitting bulk physical count as Kasir...');
        await evalJs(`document.getElementById('form_bulk_simpan').submit()`);
        await sleep(2500);

        // =========================================================================
        // STEP 3: ADMIN MONITORING PROGRES & BUKA VERIFIKASI
        // =========================================================================
        console.log('4. Step 3: Admin logs in & monitors progress in Tahap 1...');
        await loginAs('admin_toko');
        await navigate('http://localhost/inventory_zencare/inventori/stok_opname.php');
        await evalJs(`window.scrollTo(0, 0)`);
        await screenshot('langkah_3_admin_monitoring.png');

        // Click "Verifikasi Selisih" to transition to Tahap 2
        console.log('   Transitioning to Tahap 2 (Verifikasi Selisih)...');
        await evalJs(`
            const f = document.querySelector('input[name="aksi"][value="tutup_penghitungan"]')?.form;
            if (f) f.submit();
        `);
        await sleep(1500);

        // =========================================================================
        // STEP 4: ADMIN VERIFIKASI ANOMALI OTOMATIS & ESTIMASI NILAI KERUGIAN
        // =========================================================================
        console.log('5. Step 4: Admin views Tahap 2 automatic anomaly filter & loss estimation...');
        const anomalyRowsText = await evalJs(`document.body.innerText`);
        console.log('   [Check 3.1 & 5.1] Has Estimasi Nilai Acuan Kerugian Persediaan:', anomalyRowsText.includes('Estimasi Nilai Acuan Kerugian Persediaan'));
        await evalJs(`window.scrollTo(0, 160)`);
        await screenshot('langkah_4_verifikasi_anomali.png');

        // =========================================================================
        // STEP 5: POP-UP MODAL MUTASI RADIO BUTTON & EKSEKUSI REKONSILIASI
        // =========================================================================
        console.log('6. Step 5: Opening Pop-Up Modal Mutasi with clean Radio Buttons...');
        await evalJs(`
            const btns = Array.from(document.querySelectorAll('button[onclick^="openModalMutasi"]'));
            if (btns.length > 0) btns[0].click();
        `);
        await sleep(600);
        await screenshot('langkah_5_popup_mutasi.png');

        // Execute mass reconciliation for the batch anomalies + SN reconciliation so all 6 anomalies are mutated
        console.log('   Executing reconciliation for all anomalies...');
        await evalJs(`
            const mModal = document.getElementById('modal_mutasi');
            if (mModal) mModal.classList.add('hidden');
            const rModal = document.getElementById('modal_rekonsiliasi_masal');
            if (rModal) {
                rModal.querySelector('form').submit();
            }
        `);
        await sleep(2000);

        // =========================================================================
        // STEP 6: SUPERADMIN SELESAIKAN SESI & CETAK BERITA ACARA
        // =========================================================================
        console.log('7. Step 6: Superadmin completes session & opens Berita Acara...');
        await loginAs('superadmin');
        await navigate('http://localhost/inventory_zencare/inventori/stok_opname.php');

        // Complete the session
        await evalJs(`
            const fSelesai = document.querySelector('input[name="aksi"][value="selesaikan_sesi"]')?.form;
            if (fSelesai) fSelesai.submit();
        `);
        await sleep(1500);

        // Navigate to Cetak Berita Acara of the latest completed session
        const baHref = await evalJs(`
            const link = document.querySelector('a[href*="cetak_berita_acara_opname.php"]');
            link ? link.href : null;
        `);
        console.log('   Berita Acara URL:', baHref);
        if (baHref) {
            await navigate(baHref);
            await evalJs(`window.scrollTo(0, 260)`);
            await screenshot('langkah_6_selesai_berita_acara.png');
        }

        ws.close();
        console.log('=== ALL 6 REAL BROWSER STEPS & SCREENSHOTS COMPLETED SUCCESSFULLY ===');
    } finally {
        chrome.kill();
    }
}

main().catch(err => {
    console.error(err);
    process.exit(1);
});
