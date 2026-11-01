var cart = [
    {
        id: 6,
        cartId: '6_kecil',
        nama: 'Hufagrip Merah (TMP)',
        satuan_tipe: 'kecil',
        satuan_label: 'Botol',
        allocations: [{ batch: 'HUF-TMP-26.A', qty: 2, verified: false }]
    },
    {
        id: 6,
        cartId: '6_besar',
        nama: 'Hufagrip Merah (TMP)',
        satuan_tipe: 'besar',
        satuan_label: 'Karton',
        allocations: [{ batch: 'HUF-EXP-SOON', qty: 1, verified: false }]
    }
];

function testScan(scannedCode, rawBatch) {
    var id = 6;
    var rasio = 50;
    var uomType = 'kecil';
    
    var isScannedSubBatch = scannedCode.includes('.');
    var scannedUom = isScannedSubBatch ? 'kecil' : 'besar';

    var matchingCartItem = cart.find(ci => parseInt(ci.id) === id && ci.satuan_tipe === scannedUom);

    if (matchingCartItem) {
        uomType = scannedUom;
    } else {
        var oppositeUom = (scannedUom === 'besar') ? 'kecil' : 'besar';
        var oppositeCartItem = cart.find(ci => parseInt(ci.id) === id && ci.satuan_tipe === oppositeUom);
        var hasUnverifiedOpposite = oppositeCartItem && (oppositeCartItem.allocations || []).some(al => !al.verified);
        if (hasUnverifiedOpposite) {
            return "ERROR: Salah Ambil Fisik!";
        }
        uomType = scannedUom;
    }

    var cartId = id + '_' + uomType;
    var existingItem = cart.find(ci => ci.cartId === cartId);
    if (!existingItem) return "ERROR: Item not found in cart";

    var matchAlloc = existingItem.allocations.find(al => !al.verified && al.batch.toLowerCase() === scannedCode.toLowerCase());
    if (matchAlloc) {
        matchAlloc.verified = true;
        matchAlloc.scanned_batch = scannedCode.toUpperCase();
        return "SUCCESS: Verified " + matchAlloc.scanned_batch + " for " + existingItem.satuan_tipe;
    }
    return "NO MATCH ALLOC";
}

console.log("Scan Grosir (HUF-EXP-SOON):", testScan('HUF-EXP-SOON', 'HUF-EXP-SOON,HUF-TMP-26,HUF-TMP-26.A'));
console.log("Scan Eceran (HUF-TMP-26.A):", testScan('HUF-TMP-26.A', 'HUF-EXP-SOON,HUF-TMP-26,HUF-TMP-26.A'));
console.log("Cart Status:", JSON.stringify(cart, null, 2));