function runTest() {
    var item = {
        id: 6,
        satuan_tipe: 'besar',
        product_rasio: 50,
        rasio: 50,
        needs_verification: true,
        batch_list: 'HUF-EXP-SOON|1200,HUF-TMP-26|1500', // in pcs: 1200 pcs = 24 Karton
        allocations: [
            { batch: 'HUF-EXP-SOON', qty: 2, verified: true, scanned_batch: 'HUF-EXP-SOON' }
        ]
    };

    function computeAllocationsFixed(item, targetQty) {
        var rawList = item.batch_list || '';
        var entries = rawList ? rawList.split(',').map(function(s) {
            var p = s.split('|');
            return { code: p[0].trim(), stockPcs: parseInt(p[1]) || 0 };
        }) : [];

        var productRasio = item.product_rasio || item.rasio || 1;
        var relevant = entries.filter(function(b) {
            var isEceran = b.code.includes('.');
            if (productRasio <= 1) return true;
            return (item.satuan_tipe === 'kecil' && isEceran) || (item.satuan_tipe === 'besar' && !isEceran);
        });

        relevant.forEach(function(b) {
            b.stockUom = (item.satuan_tipe === 'besar' && productRasio > 1) ? Math.floor(b.stockPcs / productRasio) : b.stockPcs;
        });

        var prevAllocations = item.allocations || [];
        var verifiedAllocations = prevAllocations.filter(function(al) { return al.verified; });

        var newAllocations = [];
        var remainingNeeded = targetQty;

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

                    // If this batch is already verified in newAllocations, merge the added qty into it!
                    var existingAlloc = newAllocations.find(function(al) {
                        return (al.scanned_batch || al.batch).toLowerCase() === b.code.toLowerCase();
                    });

                    if (existingAlloc && existingAlloc.verified) {
                        existingAlloc.qty += take;
                    } else {
                        newAllocations.push({
                            batch: b.code,
                            qty: take,
                            verified: false,
                            scanned_batch: null,
                            is_anomaly: false,
                            batch_seharusnya: null
                        });
                    }
                    remainingNeeded -= take;
                    batchUsedMap[b.code.toLowerCase()] = used + take;
                }
            }
        }

        item.allocations = newAllocations;
    }

    console.log("Before increasing qty (qty = 2):", JSON.stringify(item.allocations));
    computeAllocationsFixed(item, 3);
    console.log("After increasing qty to 3:", JSON.stringify(item.allocations));
}

runTest();