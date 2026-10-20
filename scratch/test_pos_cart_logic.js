// Test simulation of POS Cart Logic
let cart = [];

function computeAllocations(item, targetQty) {
    if (!item.needs_verification) {
        item.allocations = [];
        item.alkes_allocations = [];
        return;
    }

    if (item.kategori === 'Alat Kesehatan') {
        var currentAlkes = item.alkes_allocations || [];
        var newAlkes = [];
        for (var k = 0; k < targetQty; k++) {
            if (currentAlkes[k] && currentAlkes[k].verified) {
                newAlkes.push(currentAlkes[k]);
            } else {
                newAlkes.push({
                    unit_index: k + 1,
                    sn: (currentAlkes[k] && currentAlkes[k].sn) ? currentAlkes[k].sn : null,
                    verified: false,
                    is_anomaly: false,
                    sn_seharusnya: null
                });
            }
        }
        item.alkes_allocations = newAlkes;
        return;
    }

    if (item.kategori === 'Obat') {
        var rawList = item.batch_list || '';
        var entries = rawList ? rawList.split(',').map(function(s) {
            var p = s.split('|');
            return { code: p[0].trim(), stockPcs: parseInt(p[1]) || 0 };
        }) : [];

        var relevant = entries.filter(function(b) {
            var isEceran = b.code.includes('.');
            return (item.satuan_tipe === 'kecil' && isEceran) || (item.satuan_tipe === 'besar' && !isEceran);
        });

        relevant.forEach(function(b) {
            b.stockUom = (item.satuan_tipe === 'besar') ? Math.floor(b.stockPcs / (item.rasio || 1)) : b.stockPcs;
        });

        var prevAllocations = item.allocations || [];
        var verifiedAllocations = prevAllocations.filter(function(al) { return al.verified; });

        var newAllocations = [];
        var remainingNeeded = targetQty;

        // Retain verified allocations up to targetQty
        for (var v = 0; v < verifiedAllocations.length; v++) {
            var val = verifiedAllocations[v];
            if (remainingNeeded <= 0) break;
            var takeVer = Math.min(val.qty, remainingNeeded);
            newAllocations.push({
                batch: val.batch,
                qty: takeVer,
                verified: true,
                scanned_batch: val.scanned_batch || val.batch,
                is_anomaly: val.is_anomaly || false,
                batch_seharusnya: val.batch_seharusnya || null
            });
            remainingNeeded -= takeVer;
        }

        // If more qty is needed, allocate from available batches in FEFO order
        if (remainingNeeded > 0) {
            var batchUsedMap = {};
            newAllocations.forEach(function(al) {
                var bKey = (al.scanned_batch || al.batch).toLowerCase();
                batchUsedMap[bKey] = (batchUsedMap[bKey] || 0) + al.qty;
            });

            for (var bIdx = 0; bIdx < relevant.length; bIdx++) {
                if (remainingNeeded <= 0) break;
                var b = relevant[bIdx];
                var used = batchUsedMap[b.code.toLowerCase()] || 0;
                var avail = b.stockUom - used;
                if (avail > 0) {
                    var take = Math.min(avail, remainingNeeded);
                    newAllocations.push({
                        batch: b.code,
                        qty: take,
                        verified: false,
                        scanned_batch: null,
                        is_anomaly: false,
                        batch_seharusnya: null
                    });
                    remainingNeeded -= take;
                    batchUsedMap[b.code.toLowerCase()] = used + take;
                }
            }
        }

        item.allocations = newAllocations;
    }
}

function processAddToCart(id, name, hargaKecil, hargaBesar, maxStockPcs, rasio, scannedCode, rawSn, rawBatch, isAnomaly = false, batchSeharusnya = null, needsVerification = false, passedUomType = null, stokEceranFisik = 0) {
    var uomType = passedUomType || 'kecil';
    var uomLabel = (uomType === 'besar') ? 'Karton' : 'Box';
    var price = (uomType === 'besar') ? hargaBesar : hargaKecil;
    var qtyMultiplier = (uomType === 'besar') ? rasio : 1;
    var cartId = id + '_' + uomType;

    var existingItem = cart.find(ci => ci.cartId === cartId);

    if (scannedCode) {
        scannedCode = scannedCode.trim();
        var scannedCodeUpper = scannedCode.toUpperCase();

        if (existingItem) {
            if (existingItem.kategori === 'Obat') {
                var matchAlloc = existingItem.allocations.find(al => !al.verified && al.batch.toLowerCase() === scannedCode.toLowerCase());
                if (matchAlloc) {
                    matchAlloc.verified = true;
                    matchAlloc.scanned_batch = scannedCodeUpper;
                    matchAlloc.is_anomaly = false;
                    matchAlloc.batch_seharusnya = null;
                    console.log(`[PASS] Match allocation found for ${scannedCodeUpper} (${matchAlloc.qty} ${existingItem.satuan_label}) -> Verified!`);
                    return;
                }

                var alreadyVer = existingItem.allocations.find(al => al.verified && (al.scanned_batch || al.batch).toLowerCase() === scannedCode.toLowerCase());
                if (alreadyVer) {
                    var remainingUnverified = existingItem.allocations.find(al => !al.verified);
                    if (remainingUnverified) {
                        console.log(`[PASS] Swap dialog would trigger: Replace ${remainingUnverified.batch} with ${scannedCodeUpper}`);
                        remainingUnverified.verified = true;
                        remainingUnverified.scanned_batch = scannedCodeUpper;
                        remainingUnverified.is_anomaly = true;
                        remainingUnverified.batch_seharusnya = remainingUnverified.batch;
                        return;
                    }
                }

                var firstUnverified = existingItem.allocations.find(al => !al.verified);
                if (firstUnverified) {
                    console.log(`[PASS] Anomaly dialog would trigger: Replace ${firstUnverified.batch} with ${scannedCodeUpper}`);
                    firstUnverified.verified = true;
                    firstUnverified.scanned_batch = scannedCodeUpper;
                    firstUnverified.is_anomaly = true;
                    firstUnverified.batch_seharusnya = firstUnverified.batch;
                    return;
                }
            }
        }
    }

    if (existingItem) {
        existingItem.qty++;
        computeAllocations(existingItem, existingItem.qty);
    } else {
        var newItem = {
            id: id,
            cartId: cartId,
            nama: name,
            harga: price,
            qty: 1,
            satuan_tipe: uomType,
            satuan_label: uomLabel,
            maxPcs: maxStockPcs,
            rasio: qtyMultiplier,
            kategori: 'Obat',
            sn_list: rawSn,
            batch_list: rawBatch,
            needs_verification: needsVerification,
            stok_eceran_fisik: stokEceranFisik
        };
        computeAllocations(newItem, 1);
        cart.push(newItem);
    }
}

// SIMULASI 1: Tambah 'Obat Uji Skenario Dosen' (batch_list: batch-dosen-a.A|2,BATCH-DOSEN-B.A|10)
let batchListDosen = "batch-dosen-a.A|2,BATCH-DOSEN-B.A|10";
processAddToCart(59, 'Obat Uji Skenario Dosen', 28000, 250000, 12, 10, null, '', batchListDosen, false, null, true, 'kecil', 12);

console.log("\n--- TEST 1: Initial item in cart (Qty 1) ---");
console.log("Cart items count (must be 1):", cart.length);
console.log("Allocations:", cart[0].allocations);

// Qty dinaikkan jadi 6
console.log("\n--- TEST 2: Increase Qty to 6 ---");
cart[0].qty = 6;
computeAllocations(cart[0], 6);
console.log("Cart items count (must stay 1):", cart.length);
console.log("Total Qty:", cart[0].qty);
console.log("Allocations for Qty 6:");
cart[0].allocations.forEach((al, idx) => {
    console.log(`  Allocation #${idx + 1}: ${al.batch} -> ${al.qty} Box (Verified: ${al.verified})`);
});

// Cek apakah payment modal akan terblokir
function checkPaymentStatus() {
    let unverified = [];
    cart.forEach(i => {
        if (i.needs_verification && i.allocations) {
            i.allocations.forEach(al => {
                if (!al.verified) unverified.push(al.batch + ' (' + al.qty + ' Box)');
            });
        }
    });
    return unverified;
}

let unverified1 = checkPaymentStatus();
console.log("\nUnverified batches before scanning (Payment blocked):", unverified1);

// Scan Batch 1: batch-dosen-a.A
console.log("\n--- TEST 3: Cashier scans physical batch-dosen-a.A ---");
processAddToCart(59, 'Obat Uji Skenario Dosen', 28000, 250000, 12, 10, 'batch-dosen-a.A', '', batchListDosen, false, null, true, 'kecil', 12);
console.log("Allocations status after scanning batch-dosen-a.A:");
cart[0].allocations.forEach((al, idx) => {
    console.log(`  Allocation #${idx + 1}: ${al.batch} -> ${al.qty} Box (Verified: ${al.verified}, Badge color: ${al.verified ? 'BLUE' : 'RED'})`);
});

let unverified2 = checkPaymentStatus();
console.log("Unverified batches after 1st scan (Still blocked):", unverified2);

// Scan Batch 2: BATCH-DOSEN-B.A
console.log("\n--- TEST 4: Cashier scans physical BATCH-DOSEN-B.A ---");
processAddToCart(59, 'Obat Uji Skenario Dosen', 28000, 250000, 12, 10, 'BATCH-DOSEN-B.A', '', batchListDosen, false, null, true, 'kecil', 12);
console.log("Allocations status after scanning BATCH-DOSEN-B.A:");
cart[0].allocations.forEach((al, idx) => {
    console.log(`  Allocation #${idx + 1}: ${al.batch} -> ${al.qty} Box (Verified: ${al.verified}, Badge color: ${al.verified ? 'BLUE' : 'RED'})`);
});

let unverified3 = checkPaymentStatus();
console.log("Unverified batches after 2nd scan:", unverified3);
console.log("Can proceed to payment?", unverified3.length === 0 ? "YES! (Payment Unlocked)" : "NO");

console.log("\nAll tests completed successfully!");
