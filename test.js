let cart = [];
let id = 1;
let uomType = 'kecil';
let scannedCode = null;
let needsVerification = true;

// 1. Pilih Manual
cart.push({ id: id, cartId: id + '_' + uomType, scanned_code: scannedCode, needs_verification: needsVerification });

// 2. Scan Barcode
scannedCode = 'amx-2024-a';
let cartId = id + '_' + uomType + '_' + scannedCode;
let foundUnverified = false;

if (scannedCode && needsVerification) {
    for (let i = 0; i < cart.length; i++) {
        if (cart[i].id === id && cart[i].needs_verification && !cart[i].scanned_code) {
            foundUnverified = true;
            cart[i].scanned_code = scannedCode;
            cart[i].cartId = cartId;
            break;
        }
    }
}

if (!foundUnverified) {
    cart.push({ id: id, cartId: cartId, scanned_code: scannedCode, needs_verification: needsVerification });
}
console.log(cart);
