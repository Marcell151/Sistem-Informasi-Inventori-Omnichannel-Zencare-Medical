const fs = require('fs');

// Read pos.php and extract main script
const phpContent = fs.readFileSync('pos/pos.php', 'utf8');

// Extract Script 6 (the main script)
const scriptRegex = /<script(?:\s+[^>]*)?>([\s\S]*?)<\/script>/gi;
let match;
let mainScript = '';
while ((match = scriptRegex.exec(phpContent)) !== null) {
    if (match[1].includes('function addToPos')) {
        mainScript = match[1];
        break;
    }
}

// Replace php tags in mainScript
mainScript = mainScript.replace(/<\?php[\s\S]*?\?>/gi, '/* php */');
mainScript = mainScript.replace(/<\?=[\s\S]*?\?>/gi, '"mock_php_val"');

// Mock minimal DOM
class MockElement {
    constructor(tag, id = '', attrs = {}) {
        this.tagName = tag.toUpperCase();
        this.id = id;
        this.attributes = attrs;
        this.innerHTML = '';
        this.innerText = '';
        this.value = attrs.value || '';
        this.options = [];
        this.selectedIndex = 0;
        this.style = {};
        this.classList = {
            add: () => {},
            remove: () => {},
            contains: () => false
        };
    }
    getAttribute(attr) { return this.attributes[attr] || null; }
    setAttribute(attr, val) { this.attributes[attr] = String(val); }
    closest(selector) { return this._parent || null; }
    querySelector(selector) { return null; }
    querySelectorAll(selector) { return []; }
}

const elements = {};
function reg(id, el) { elements[id] = el; return el; }

const doc = {
    getElementById: (id) => elements[id] || null,
    querySelector: (sel) => {
        if (sel.startsWith('#')) return elements[sel.substring(1)] || null;
        for (let k in elements) {
            let el = elements[k];
            if (sel.includes('data-id="51"') && el.getAttribute('data-id') === '51') return el;
            if (sel.includes('data-id="1"') && el.getAttribute('data-id') === '1') return el;
            if (sel.includes('data-id="4"') && el.getAttribute('data-id') === '4') return el;
            if (sel.includes('data-id="26"') && el.getAttribute('data-id') === '26') return el;
        }
        return null;
    },
    querySelectorAll: () => [],
    addEventListener: () => {}
};

// Setup actual POS elements
reg('pos_cart_body', new MockElement('tbody', 'pos_cart_body'));
reg('pos_total', new MockElement('span', 'pos_total'));
reg('btn_pay', new MockElement('button', 'btn_pay'));
reg('cart_input', new MockElement('input', 'cart_input'));
reg('form_metode_pembayaran', new MockElement('input', 'form_metode_pembayaran'));
reg('form_custom_invoice', new MockElement('input', 'form_custom_invoice'));

reg('cam_cart_count_badge', new MockElement('span', 'cam_cart_count_badge'));
reg('cam_cart_total_badge', new MockElement('span', 'cam_cart_total_badge'));
reg('cam_last_product_label', new MockElement('span', 'cam_last_product_label'));
reg('cam_cooldown_indicator', new MockElement('span', 'cam_cooldown_indicator'));
reg('last_scanned_code', new MockElement('span', 'last_scanned_code'));
reg('last_scanned_time', new MockElement('span', 'last_scanned_time'));

// Product 1: EasyTouch (Alkes BMHP, Rasio 1)
const card51 = new MockElement('div', 'card51', {
    'class': 'product-card',
    'data-id': '51',
    'data-sn': '',
    'data-batch': 'bmhp-esyua-28|15',
    'data-stok-eceran': '15',
    'data-stok-dus': '0'
});
const uom51 = reg('uom_51', new MockElement('input', 'uom_51', { value: 'kecil', 'data-label': 'Tube' }));
const btn51 = new MockElement('button', 'btn51', {
    'data-id': '51',
    'data-nama': 'Alat Cek Asam Urat EasyTouch - Tube Isi 25',
    'data-hk': '95000',
    'data-hb': '0',
    'data-stok': '15',
    'data-rasio': '1',
    'data-kat': 'Alat Kesehatan'
});
btn51._parent = card51;

// Product 2: Tensimeter (Alkes Serial Number)
const card4 = new MockElement('div', 'card4', {
    'class': 'product-card',
    'data-id': '4',
    'data-sn': 'omr-tens-05,omr-tens-04',
    'data-batch': '',
    'data-stok-eceran': '8',
    'data-stok-dus': '0'
});
const uom4 = reg('uom_4', new MockElement('input', 'uom_4', { value: 'kecil', 'data-label': 'Unit' }));
const btn4 = new MockElement('button', 'btn4', {
    'data-id': '4',
    'data-nama': 'Tensimeter Digital Omron - Unit HEM-7120',
    'data-hk': '650000',
    'data-hb': '0',
    'data-stok': '8',
    'data-rasio': '1',
    'data-kat': 'Alat Kesehatan'
});
btn4._parent = card4;

// Product 3: Amoxicillin (Obat Multi-UOM)
const card1 = new MockElement('div', 'card1', {
    'class': 'product-card',
    'data-id': '1',
    'data-sn': '',
    'data-batch': 'amx-2026-01|40,amx-2026-01.a|8',
    'data-stok-eceran': '8',
    'data-stok-dus': '40'
});
const uom1 = reg('uom_1', new MockElement('select', 'uom_1', { value: 'kecil' }));
uom1.options = [{ value: 'kecil', text: 'Ecer (Strip)' }, { value: 'besar', text: 'Grosir (Dus)' }];
uom1.selectedIndex = 0;
const btn1 = new MockElement('button', 'btn1', {
    'data-id': '1',
    'data-nama': 'Amoxicillin 500mg - Tablet 500mg',
    'data-hk': '5000',
    'data-hb': '40000',
    'data-stok': '48',
    'data-rasio': '10',
    'data-kat': 'Obat'
});
btn1._parent = card1;

// Global environment
global.window = global;
global.window.addEventListener = () => {};
global.alert = (msg) => console.log("ALERT:", msg);
global.document = doc;
global.Swal = {
    fire: () => Promise.resolve({ isConfirmed: true }),
    mixin: () => ({ fire: () => {} })
};
global.sessionStorage = {
    setItem: () => {},
    getItem: () => null,
    removeItem: () => {}
};
global.MIDTRANS_CLIENT_KEY = "mock_key";

// Test applying product_rasio fix to mainScript before eval
mainScript = mainScript.replace(/var newItem = \{([\s\S]*?)rasio: qtyMultiplier,([\s\S]*?)\};/g, 
    'var newItem = {$1product_rasio: rasio, rasio: qtyMultiplier, multiplier: qtyMultiplier,$2};'
);

mainScript = mainScript.replace(
    'if (item.rasio <= 1) return true; // Single UOM: semua batch dapat dialokasikan',
    'var pRasio = item.product_rasio || item.rasio || 1; if (pRasio <= 1) return true;'
);

mainScript = mainScript.replace(
    'b.stockUom = (item.satuan_tipe === \'besar\' && item.rasio > 1) ? Math.floor(b.stockPcs / item.rasio) : b.stockPcs;',
    'var pRasio = item.product_rasio || item.rasio || 1; b.stockUom = (item.satuan_tipe === \'besar\' && pRasio > 1) ? Math.floor(b.stockPcs / pRasio) : b.stockPcs;'
);
eval(mainScript.replace('let cart = [];', 'var cart = []; global.cart = cart; setTimeout(() => { global.addToPos = addToPos; global.changeQty = changeQty; global.removeItem = removeItem; }, 0);'));
global.addToPos = addToPos; global.changeQty = changeQty; global.removeItem = removeItem;
console.log("✅ Main script evaluated successfully!");

// TEST 1: Click + Pilih on EasyTouch (Alkes BMHP)
console.log("\n--- TEST 1: Click + Pilih on EasyTouch ---");
addToPos(btn51, null);
console.log("Cart length:", cart.length);
console.log("Item 0:", cart[0].nama, "| Qty:", cart[0].qty, "| Allocations:", cart[0].allocations);
if (cart.length === 1 && cart[0].qty === 1 && cart[0].allocations[0].batch === 'bmhp-esyua-28') {
    console.log("✅ PASS: EasyTouch added with correct allocation!");
} else {
    console.error("❌ FAIL: EasyTouch not added properly!");
    process.exit(1);
}

// TEST 2: Click + Pilih on Tensimeter (Alkes SN)
console.log("\n--- TEST 2: Click + Pilih on Tensimeter ---");
addToPos(btn4, null);
console.log("Cart length:", cart.length);
console.log("Item 1:", cart[1].nama, "| Qty:", cart[1].qty, "| Alkes Allocations:", cart[1].alkes_allocations);
if (cart.length === 2 && cart[1].alkes_allocations.length === 1) {
    console.log("✅ PASS: Tensimeter added with serial unit allocation!");
} else {
    console.error("❌ FAIL: Tensimeter not added properly!");
    process.exit(1);
}

// TEST 3: Click + Pilih on Amoxicillin (Eceran Strip)
console.log("\n--- TEST 3: Click + Pilih on Amoxicillin (Eceran Strip) ---");
addToPos(btn1, null);
console.log("Cart length:", cart.length);
console.log("Item 2:", cart[2].nama, "| Satuan:", cart[2].satuan_label, "| Allocations:", cart[2].allocations);
if (cart.length === 3 && cart[2].satuan_tipe === 'kecil' && cart[2].allocations[0].batch === 'amx-2026-01.a') {
    console.log("✅ PASS: Amoxicillin Eceran added with FEFO sub-batch!");
} else {
    console.error("❌ FAIL: Amoxicillin not added properly!");
    process.exit(1);
}

// TEST 4: Select Grosir (Dus) on Amoxicillin and click + Pilih
console.log("\n--- TEST 4: Select Grosir (Dus) on Amoxicillin and click + Pilih ---");
uom1.value = 'besar';
uom1.selectedIndex = 1;
addToPos(btn1, null);
console.log("Cart length:", cart.length);
console.log("Item 3:", cart[3].nama, "| Satuan:", cart[3].satuan_label, "| Allocations:", cart[3].allocations);
if (cart.length === 4 && cart[3].satuan_tipe === 'besar' && cart[3].allocations[0].batch === 'amx-2026-01') {
    console.log("✅ PASS: Amoxicillin Grosir (Dus) added with main batch!");
} else {
    console.error("❌ FAIL: Amoxicillin Grosir not added properly!");
    process.exit(1);
}

// TEST 5: Scan barcode on EasyTouch to verify
console.log("\n--- TEST 5: Scan barcode on EasyTouch ---");
addToPos(btn51, 'bmhp-esyua-28');
console.log("EasyTouch verified:", cart[0].allocations[0].verified);
if (cart[0].allocations[0].verified === true) {
    console.log("✅ PASS: EasyTouch verified successfully!");
} else {
    console.error("❌ FAIL: EasyTouch verification failed!");
    process.exit(1);
}

// TEST 6: Change Qty (+1)
console.log("\n--- TEST 6: Change Qty on EasyTouch (+1) ---");
changeQty(cart[0].cartId, 1);
console.log("EasyTouch qty:", cart[0].qty, "| Allocations:", cart[0].allocations.length);
if (cart[0].qty === 2 && cart[0].allocations.length === 2) {
    console.log("✅ PASS: Qty incremented and new allocation added!");
} else {
    console.error("❌ FAIL: Qty increment failed!");
    process.exit(1);
}

// TEST 7: Remove item from cart
console.log("\n--- TEST 7: Remove item from cart ---");
const initialCount = cart.length;
removeItem(cart[0].cartId);
if (cart.length === initialCount - 1) {
    console.log("✅ PASS: Item removed successfully!");
} else {
    console.error("❌ FAIL: Item removal failed!");
    process.exit(1);
}

console.log("\n=======================================================");
console.log("🎉 ALL 7 POS RUNTIME CHECKS PASSED WITH ZERO ERRORS!");
console.log("=======================================================");
