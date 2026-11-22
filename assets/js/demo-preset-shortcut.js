/**
 * ZenCare Demo Preset Quick-Switch Shortcut (Discreet)
 * Shortcut: Ctrl + Alt + 1 .. Ctrl + Alt + 6
 * - Mengganti database ke preset demo 1..6 langsung tanpa membuka demo/index.php
 * - Otomatis me-refresh halaman agar data langsung sinkron
 * - Menampilkan indikator centang kecil (tidak mencolok) di pojok kanan bawah setelah reload
 */
(function () {
    'use strict';
    if (window.__zcPresetShortcutInit) return;
    window.__zcPresetShortcutInit = true;

    const STORAGE_KEY = 'zc_demo_preset_switched';
    let isSwitching = false;

    function getIndicatorEl() {
        let el = document.getElementById('zc-demo-stealth-dot');
        if (el) return el;
        el = document.createElement('div');
        el.id = 'zc-demo-stealth-dot';
        el.setAttribute('aria-hidden', 'true');
        el.style.cssText = [
            'position:fixed',
            'right:14px',
            'bottom:14px',
            'width:22px',
            'height:22px',
            'border-radius:9999px',
            'display:flex',
            'align-items:center',
            'justify-content:center',
            'pointer-events:none',
            'z-index:2147483647',
            'opacity:0',
            'transform:scale(0.85)',
            'transition:opacity 0.22s ease, transform 0.22s ease',
            'box-shadow:0 2px 6px rgba(0,0,0,0.14)'
        ].join(';');
        document.body.appendChild(el);
        return el;
    }

    function showStealthIcon(state) {
        if (!document.body) return;
        const el = getIndicatorEl();
        if (state === 'loading') {
            el.style.background = 'rgba(15, 23, 42, 0.55)';
            el.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round"><path d="M12 3a9 9 0 0 1 9 9"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="0.7s" repeatCount="indefinite"/></path></svg>';
            requestAnimationFrame(() => {
                el.style.opacity = '0.85';
                el.style.transform = 'scale(1)';
            });
        } else if (state === 'ok') {
            el.style.background = 'rgba(16, 185, 129, 0.88)';
            el.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
            requestAnimationFrame(() => {
                el.style.opacity = '0.92';
                el.style.transform = 'scale(1)';
            });
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'scale(0.85)';
            }, 1700);
        } else if (state === 'err') {
            el.style.background = 'rgba(220, 38, 38, 0.85)';
            el.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            requestAnimationFrame(() => {
                el.style.opacity = '0.9';
                el.style.transform = 'scale(1)';
            });
            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'scale(0.85)';
            }, 2000);
        }
    }

    // Setelah reload pasca ganti preset, tampilkan centang kecil di pojok kanan bawah
    function checkPostReloadBadge() {
        try {
            const flag = sessionStorage.getItem(STORAGE_KEY);
            if (flag) {
                sessionStorage.removeItem(STORAGE_KEY);
                showStealthIcon('ok');
            }
        } catch (e) {}
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkPostReloadBadge);
    } else {
        checkPostReloadBadge();
    }

    // Sembunyikan saat print
    try {
        const st = document.createElement('style');
        st.textContent = '@media print { #zc-demo-stealth-dot { display: none !important; } }';
        document.head.appendChild(st);
    } catch (e) {}

    async function switchDemoPreset(num) {
        if (isSwitching) return;
        isSwitching = true;
        showStealthIcon('loading');
        try {
            const fd = new FormData();
            fd.append('preset', String(num));
            const res = await fetch('/inventory_zencare/demo/switch_preset.php', {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const data = await res.json();
            if (res.ok && data && data.ok) {
                try {
                    sessionStorage.setItem(STORAGE_KEY, String(num));
                } catch (e) {}
                // Refresh halaman (GET bersih agar tidak muncul dialog Confirm Form Resubmission)
                window.location.replace(window.location.pathname + window.location.search);
                return;
            }
            showStealthIcon('err');
        } catch (e) {
            showStealthIcon('err');
        } finally {
            isSwitching = false;
        }
    }

    window.addEventListener('keydown', function (e) {
        if (!e.ctrlKey || !e.altKey || e.shiftKey || e.metaKey) return;
        let num = 0;
        if (e.code && /^Digit[1-6]$/.test(e.code)) {
            num = parseInt(e.code.slice(5), 10);
        } else if (e.code && /^Numpad[1-6]$/.test(e.code)) {
            num = parseInt(e.code.slice(6), 10);
        } else if (e.key >= '1' && e.key <= '6') {
            num = parseInt(e.key, 10);
        }
        if (num >= 1 && num <= 6) {
            e.preventDefault();
            e.stopPropagation();
            switchDemoPreset(num);
        }
    }, true);
})();
