
    let cart = [];

    function computeAllocations(item, targetQty) {
        if (!item.needs_verification) {
            item.allocations = [];
            item.alkes_allocations = [];
            return;
        }

        // 1. Alkes dengan Nomor Seri (SN)
        if (item.sn_list && item.sn_list.trim().length > 0) {
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
            item.allocations = [];
            return;
        }

        // 2. Produk dengan Batch (Obat ATAU Alkes BMHP seperti Strip Gula Darah, dsb)
        if (item.batch_list && item.batch_list.trim().length > 0) {
            var rawList = item.batch_list || '';
            var entries = rawList ? rawList.split(',').map(function(s) {
                var p = s.split('|');
                return { code: p[0].trim(), stockPcs: parseInt(p[1]) || 0 };
            }) : [];

            var productRasio = item.product_rasio || item.rasio || 1;
            var relevant = entries.filter(function(b) {
                var isEceran = b.code.includes('.');
                if (productRasio <= 1) return true; // Single UOM: semua batch dapat dialokasikan
                return (item.satuan_tipe === 'kecil' && isEceran) || (item.satuan_tipe === 'besar' && !isEceran);
            });

            relevant.forEach(function(b) {
                b.stockUom = (item.satuan_tipe === 'besar' && productRasio > 1) ? Math.floor(b.stockPcs / productRasio) : b.stockPcs;
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
            item.alkes_allocations = [];
            return;
        }

        item.allocations = [];
        item.alkes_allocations = [];
    }

    function addToPos(btnElement, scannedCode = null) {
        try {
            var card = btnElement.closest('.product-card');
            var rawSn = card ? (card.getAttribute('data-sn') || '') : '';
            var rawBatch = card ? (card.getAttribute('data-batch') || '') : '';
            
            var id = parseInt(btnElement.getAttribute('data-id'));
            var name = btnElement.getAttribute('data-nama');
            var hargaKecil = parseFloat(btnElement.getAttribute('data-hk'));
            var hargaBesar = parseFloat(btnElement.getAttribute('data-hb'));
            var rawTotalStock = parseInt(btnElement.getAttribute('data-stok'));
            var rasio = parseInt(btnElement.getAttribute('data-rasio')) || 1;
            var kat = btnElement.getAttribute('data-kat');
            
            var rawEceran = card ? card.getAttribute('data-stok-eceran') : null;
            var rawDus = card ? card.getAttribute('data-stok-dus') : null;
            var stokEceran = (rawEceran !== null) ? parseInt(rawEceran) : rawTotalStock;
            var stokDus = (rawDus !== null) ? parseInt(rawDus) : 0;

            var hasBatch = (rawBatch && rawBatch.trim().length > 0);
            var hasSn = (rawSn && rawSn.trim().length > 0);
            var needsVerification = (hasBatch || hasSn);

            var sel = document.getElementById('uom_' + id);
            var uomType = 'kecil';
            if (sel) {
                uomType = (sel.tagName.toLowerCase() === 'select') ? sel.value : (sel.value || 'kecil');
            }
            if (rasio <= 1) {
                uomType = 'kecil';
            }

            if (scannedCode) {
                if (rasio <= 1) {
                    uomType = 'kecil';
                } else {
                    // Produk Multi-UOM (rasio > 1, e.g. Obat Dus & Eceran)
                    var isScannedSubBatch = scannedCode.includes('.');
                    var existingInCart = cart.find(ci => parseInt(ci.id) === id);

                    if (existingInCart) {
                        var cartUom = existingInCart.satuan_tipe;
                        if (cartUom === 'kecil' && !isScannedSubBatch) {
                            var allBatchesRaw = rawBatch ? rawBatch.split(',').map(b => b.split('|')[0].trim().toLowerCase()) : [];
                            if (allBatchesRaw.includes(scannedCode.toLowerCase())) {
                                Swal.fire({
                                    title: '❌ Salah Ambil Fisik!', 
                                    html: 'Di keranjang pesanan adalah <b>ECERAN (Kecil)</b>, tetapi fisik yang Anda scan adalah <b>GROSIR / DUS (Besar)</b>.<br><br><span class="text-xs text-slate-500">Silakan ambil kemasan eceran (sub-batch bertitik) atau tambahkan kardus ke keranjang terlebih dahulu.</span>', 
                                    icon: 'error'
                                });
                                return;
                            }
                        } else if (cartUom === 'besar' && isScannedSubBatch) {
                            Swal.fire({
                                title: '❌ Salah Ambil Fisik!', 
                                html: 'Di keranjang pesanan adalah <b>GROSIR / DUS (Besar)</b>, tetapi fisik yang Anda scan adalah <b>ECERAN (Kecil)</b>.<br><br><span class="text-xs text-slate-500">Silakan ambil kemasan kardus utuh atau tambahkan eceran ke keranjang terlebih dahulu.</span>', 
                                icon: 'error'
                            });
                            return;
                        }
                        uomType = cartUom;
                    } else {
                        // Belum ada di keranjang (Direct scan dari rak)
                        if (isScannedSubBatch) {
                            uomType = 'kecil';
                            if (sel && sel.value !== 'kecil') sel.value = 'kecil';
                        } else {
                            uomType = (sel && sel.value === 'besar') ? 'besar' : 'kecil';
                        }
                    }
                }
            }

            var maxStockToPass = rawTotalStock;
            if (hasBatch && rasio > 1) {
                maxStockToPass = (uomType === 'kecil') ? stokEceran : stokDus;
            }

            // === KONVERSI DARURAT CHECK (Berlaku untuk Klik Manual & Scan Umum barang multi-UOM) ===
            if (hasBatch && rasio > 1 && uomType === 'kecil' && !scannedCode) {
                var allBatchesManual = rawBatch.split(',').map(b => b.split('|')[0]);
                var eceranBatchesManual = allBatchesManual.filter(b => b.includes('.'));
                var dusBatchesManual = allBatchesManual.filter(b => !b.includes('.'));
                
                if (eceranBatchesManual.length === 0 && dusBatchesManual.length > 0) {
                    Swal.fire({
                        title: 'Stok Eceran Kosong!',
                        html: '<div class="text-left text-sm mb-3">Sistem mendeteksi stok etalase (eceran) kosong, tetapi masih ada stok <b>Dus/Karton utuh</b> di gudang.<br><br>Apakah Anda ingin membuka 1 Dus untuk dijual eceran sekarang?</div><div class="bg-rose-50 text-rose-700 p-3 rounded text-xs font-bold border border-rose-200">⚠ KONFIRMASI: Pastikan Anda benar-benar akan merobek/membuka fisik kardus utuh! Aksi ini akan mencatat histori log secara permanen.</div>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Robek 1 Dus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            konversiCepat(id, name, hargaKecil, hargaBesar, rawTotalStock, rasio, scannedCode, rawSn, rawBatch, dusBatchesManual[0], needsVerification, uomType);
                        }
                    });
                    return;
                }
            }

            processAddToCart(id, name, hargaKecil, hargaBesar, maxStockToPass, rasio, scannedCode, rawSn, rawBatch, false, null, needsVerification, uomType, stokEceran, kat);
        } catch(e) {
            alert("Terjadi Error Javascript di addToPos: " + e.message);
        }
    }

    function processAddToCart(id, name, hargaKecil, hargaBesar, maxStockPcs, rasio, scannedCode, rawSn, rawBatch, isAnomaly = false, batchSeharusnya = null, needsVerification = false, passedUomType = null, stokEceranFisik = 0, kat = null) {
        if (!kat) {
            var btnRef = document.querySelector('button[data-id="' + id + '"]');
            kat = btnRef ? (btnRef.getAttribute('data-kat') || 'Obat') : 'Obat';
        }
        if (kat === 'Obat' || kat === 'Alat Kesehatan') {
            needsVerification = true;
        }
        var sel = document.getElementById('uom_' + id);
        var uomType = passedUomType ? passedUomType : (sel ? sel.value : 'kecil');
        var uomLabel = '';
        if (sel) {
            if (sel.tagName.toLowerCase() === 'select') {
                uomLabel = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
            } else {
                uomLabel = sel.getAttribute('data-label') || '';
            }
        }
        if (!uomLabel) uomLabel = (uomType === 'besar') ? 'Karton' : 'Box';
        
        var price = (uomType === 'besar') ? hargaBesar : hargaKecil;
        var qtyMultiplier = (uomType === 'besar') ? rasio : 1;
        var cartId = id + '_' + uomType; // SATU BARIS PER PRODUK + SATUAN!

        var existingItem = cart.find(ci => ci.cartId === cartId);

        if (scannedCode) {
            scannedCode = scannedCode.trim();
            var scannedCodeUpper = scannedCode.toUpperCase();

            if (existingItem) {
                // Item SUDAH ADA di keranjang
                if (existingItem.allocations && existingItem.allocations.length > 0) {
                    // 1. Cek apakah barcode cocok dengan salah satu alokasi batch yang BELUM diverifikasi
                    var matchAlloc = existingItem.allocations.find(al => !al.verified && al.batch.toLowerCase() === scannedCode.toLowerCase());
                    if (matchAlloc) {
                        // Sesuai alokasi FEFO! Langsung tervalidasi jadi biru tanpa popup anomali
                        matchAlloc.verified = true;
                        matchAlloc.scanned_batch = scannedCodeUpper;
                        matchAlloc.is_anomaly = false;
                        matchAlloc.batch_seharusnya = null;
                        
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'success',
                            title: '✅ Fisik Tervalidasi (' + matchAlloc.qty + ' ' + existingItem.satuan_label + '): ' + scannedCodeUpper,
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        });
                        updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                        renderCart();
                        return;
                    }

                    // 2. Cek apakah batch ini sudah tervalidasi sebelumnya di keranjang
                    var alreadyVer = existingItem.allocations.find(al => al.verified && (al.scanned_batch || al.batch).toLowerCase() === scannedCode.toLowerCase());
                    if (alreadyVer) {
                        var remainingUnverified = existingItem.allocations.find(al => !al.verified);
                        if (remainingUnverified) {
                            Swal.fire({
                                title: '⚠ ANOMALI FEFO TERDETEKSI!',
                                html: `Anda men-scan kembali Batch <b>${scannedCodeUpper}</b>.<br><br>` +
                                      `Terdapat alokasi batch lain yang belum diverifikasi: <b>${remainingUnverified.batch.toUpperCase()}</b> (${remainingUnverified.qty} ${existingItem.satuan_label}).<br><br>` +
                                      `Apakah Anda ingin menukar sisa fisik tersebut menggunakan Batch <b>${scannedCodeUpper}</b>?`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Tukar Fisik',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#d33',
                                focusCancel: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    remainingUnverified.verified = true;
                                    remainingUnverified.scanned_batch = scannedCodeUpper;
                                    remainingUnverified.is_anomaly = true;
                                    remainingUnverified.batch_seharusnya = remainingUnverified.batch;
                                    Swal.fire({
                                        toast: true,
                                        position: 'top',
                                        icon: 'success',
                                        title: '✅ Fisik Ditukar: ' + scannedCodeUpper,
                                        showConfirmButton: false,
                                        timer: 1800
                                    });
                                    updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                                    renderCart();
                                }
                            });
                            return;
                        } else {
                            // Semua alokasi sudah tervalidasi, scan lagi berarti menambah Qty (+1)
                            if ((existingItem.qty + 1) * existingItem.rasio > existingItem.maxPcs) {
                                Swal.fire('Stok Terbatas', 'Stok fisik tidak mencukupi untuk ditambah.', 'warning');
                                return;
                            }
                            existingItem.qty++;
                            computeAllocations(existingItem, existingItem.qty);
                            var newAlloc = existingItem.allocations.find(al => !al.verified && al.batch.toLowerCase() === scannedCode.toLowerCase());
                            if (newAlloc) {
                                newAlloc.verified = true;
                                newAlloc.scanned_batch = scannedCodeUpper;
                            }
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: 'success',
                                title: '✅ Tambah Qty: ' + name + ' [' + scannedCodeUpper + ']',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                            renderCart();
                            return;
                        }
                    }

                    // 3. Batch tidak cocok dengan alokasi (Scan batch yang berbeda dari FEFO)
                    var firstUnverified = existingItem.allocations.find(al => !al.verified);
                    if (firstUnverified) {
                        Swal.fire({
                            title: '⚠ ANOMALI FEFO TERDETEKSI!',
                            html: `Anda men-scan Batch <b>${scannedCodeUpper}</b>.<br><br>` +
                                  `Batch yang dialokasikan sesuai FEFO adalah <b>${firstUnverified.batch.toUpperCase()}</b> (${firstUnverified.qty} ${existingItem.satuan_label}).<br><br>` +
                                  `Apakah Anda yakin ingin menukar fisik batch ini?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Tetap Lanjutkan (Abaikan)',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#d33',
                            allowEnterKey: false,
                            focusCancel: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                firstUnverified.verified = true;
                                firstUnverified.scanned_batch = scannedCodeUpper;
                                firstUnverified.is_anomaly = true;
                                firstUnverified.batch_seharusnya = firstUnverified.batch;
                                Swal.fire({
                                    toast: true,
                                    position: 'top',
                                    icon: 'success',
                                    title: '✅ Fisik Ditukar: ' + scannedCodeUpper,
                                    showConfirmButton: false,
                                    timer: 1800
                                });
                                updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                                renderCart();
                            }
                        });
                        return;
                    } else {
                        // Semua sudah verified, tambah qty
                        if ((existingItem.qty + 1) * existingItem.rasio > existingItem.maxPcs) {
                            Swal.fire('Stok Terbatas', 'Stok fisik tidak mencukupi untuk ditambah.', 'warning');
                            return;
                        }
                        existingItem.qty++;
                        computeAllocations(existingItem, existingItem.qty);
                        renderCart();
                        updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                        return;
                    }
                } else if (existingItem.alkes_allocations && existingItem.alkes_allocations.length > 0) {
                    var unverifiedUnit = (existingItem.alkes_allocations || []).find(u => !u.verified);
                    if (unverifiedUnit) {
                        var snUsed = (existingItem.alkes_allocations || []).some(u => u.verified && u.sn.toUpperCase() === scannedCodeUpper);
                        if (snUsed) {
                            Swal.fire('Error', 'SN ' + scannedCodeUpper + ' sudah divalidasi di keranjang!', 'error');
                            return;
                        }
                        unverifiedUnit.verified = true;
                        unverifiedUnit.sn = scannedCodeUpper;
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'success',
                            title: '✅ SN Tervalidasi: ' + scannedCodeUpper,
                            showConfirmButton: false,
                            timer: 1800
                        });
                        updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                        renderCart();
                        return;
                    } else {
                        if ((existingItem.qty + 1) * existingItem.rasio > existingItem.maxPcs) {
                            Swal.fire('Stok Terbatas', 'Stok fisik tidak mencukupi untuk ditambah.', 'warning');
                            return;
                        }
                        existingItem.qty++;
                        computeAllocations(existingItem, existingItem.qty);
                        var newUnit = existingItem.alkes_allocations.find(u => !u.verified);
                        if (newUnit) {
                            newUnit.verified = true;
                            newUnit.sn = scannedCodeUpper;
                        }
                        renderCart();
                        updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                        return;
                    }
                }
            } else {
                // Item BELUM ADA di keranjang (Direct Scan dari Rak)
                var allBatchesRaw = rawBatch ? rawBatch.split(',') : [];
                var relevantBatches = allBatchesRaw.filter(function(b) {
                    var code = b.split('|')[0].trim();
                    var isEceran = code.includes('.');
                    if (rasio <= 1) return true;
                    return (uomType === 'kecil' && isEceran) || (uomType === 'besar' && !isEceran);
                });
                var expectedFefoBatch = (relevantBatches.length > 0) ? relevantBatches[0].split('|')[0].trim() : null;
                
                var doAddDirect = function(isAnom, batchSeh) {
                    if (qtyMultiplier > maxStockPcs) { Swal.fire('Stok Terbatas', 'Stok tidak cukup untuk satuan ini!', 'warning'); return; }
                    var newItem = {
                        id: id,
                        cartId: cartId,
                        nama: name,
                        harga: price,
                        qty: 1,
                        satuan_tipe: uomType,
                        satuan_label: uomLabel,
                        maxPcs: maxStockPcs,
                        product_rasio: rasio,
                        rasio: qtyMultiplier,
                        multiplier: qtyMultiplier,
                        kategori: kat,
                        sn_list: rawSn,
                        batch_list: rawBatch,
                        needs_verification: needsVerification,
                        stok_eceran_fisik: stokEceranFisik
                    };
                    computeAllocations(newItem, 1);
                    if (newItem.allocations && newItem.allocations.length > 0) {
                        newItem.allocations[0].verified = true;
                        newItem.allocations[0].scanned_batch = scannedCodeUpper;
                        newItem.allocations[0].is_anomaly = isAnom;
                        newItem.allocations[0].batch_seharusnya = batchSeh;
                    } else if (newItem.alkes_allocations && newItem.alkes_allocations.length > 0) {
                        newItem.alkes_allocations[0].verified = true;
                        newItem.alkes_allocations[0].sn = scannedCodeUpper;
                    }
                    cart.push(newItem);
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'success',
                        title: '✅ Masuk Keranjang: ' + name + ' [' + scannedCodeUpper + ']',
                        showConfirmButton: false,
                        timer: 1800
                    });
                    updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
                    renderCart();
                };

                if (expectedFefoBatch && expectedFefoBatch.toLowerCase() !== scannedCode.toLowerCase()) {
                    Swal.fire({
                        title: '⚠ ANOMALI FEFO TERDETEKSI!',
                        html: `Anda men-scan Batch <b>${scannedCodeUpper}</b>.<br><br>` +
                              `Terdapat Batch lain <b>(${expectedFefoBatch.toUpperCase()})</b> yang akan kedaluwarsa lebih cepat.<br>` +
                              `Sesuai sistem FEFO, Anda seharusnya mengeluarkan batch yang lebih cepat expired terlebih dahulu.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Tetap Lanjutkan (Abaikan)',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33',
                        allowEnterKey: false,
                        focusCancel: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            doAddDirect(true, expectedFefoBatch);
                        }
                    });
                    return;
                } else {
                    doAddDirect(false, null);
                    return;
                }
            }
        }

        // Penambahan Normal (Klik manual dari Katalog tanpa scan)
        if (existingItem) {
            if ((existingItem.qty + 1) * existingItem.rasio > existingItem.maxPcs) {
                Swal.fire('Stok Terbatas', 'Sisa stok tidak mencukupi untuk ditambah!', 'warning');
                return;
            }
            existingItem.qty++;
            if (existingItem.kategori === 'Obat' || existingItem.kategori === 'Alat Kesehatan') {
                existingItem.needs_verification = true;
            }
            computeAllocations(existingItem, existingItem.qty);
        } else {
            if (qtyMultiplier > maxStockPcs) {
                Swal.fire('Stok Terbatas', 'Stok tidak cukup untuk satuan ini!', 'warning');
                return;
            }
            var newItem = {
                id: id,
                cartId: cartId,
                nama: name,
                harga: price,
                qty: 1,
                satuan_tipe: uomType,
                satuan_label: uomLabel,
                maxPcs: maxStockPcs,
                product_rasio: rasio,
                rasio: qtyMultiplier,
                multiplier: qtyMultiplier,
                kategori: kat,
                sn_list: rawSn,
                batch_list: rawBatch,
                needs_verification: needsVerification,
                stok_eceran_fisik: stokEceranFisik
            };
            computeAllocations(newItem, 1);
            cart.push(newItem);
        }

        updateCameraModalCartPreview(name + ' (' + uomLabel + ')');
        renderCart();
    }

    function triggerManualBongkar(idVariasi, name, rawBatch, hargaKecil, hargaBesar, rawTotalStock, rasio) {
        var allBatchesManual = rawBatch ? rawBatch.split(',').map(b => b.split('|')[0]) : [];
        var dusBatchesManual = allBatchesManual.filter(b => !b.includes('.'));
        if (dusBatchesManual.length === 0) {
            Swal.fire('Gagal', 'Tidak ada stok kardus utuh (Grosir) yang bisa dibongkar.', 'error');
            return;
        }
        Swal.fire({
            title: 'Bongkar Dus Manual',
            html: '<div class="text-left text-sm mb-3">Anda akan membongkar <b>1 Dus/Karton utuh</b> secara manual untuk dijadikan eceran.<br><br>Sistem mendeteksi angkatan terlama: <b>' + dusBatchesManual[0].toUpperCase() + '</b></div><div class="bg-rose-50 text-rose-700 p-3 rounded text-xs font-bold border border-rose-200">&#9888;&#65039; KONFIRMASI: Pastikan Anda benar-benar merobek kardus utuh secara fisik!</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Robek 1 Dus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                konversiCepat(idVariasi, name, hargaKecil, hargaBesar, rawTotalStock, rasio, null, '', rawBatch, dusBatchesManual[0], true, 'kecil');
            }
        });
    }

    function konversiCepat(idVariasi, name, hargaKecil, hargaBesar, maxStockPcs, rasio, scannedCode, rawSn, rawBatch, batchAsal, needsVerification, uomType) {
        Swal.fire({ title: 'Membongkar Dus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
        var formData = new FormData();
        formData.append('id_variasi', idVariasi);
        formData.append('batch_asal', batchAsal);
        
        fetch('../api/pos_konversi.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Berhasil membongkar 1 Dus!',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                // Update atribut DOM card secara dinamis agar realtime tanpa reload manual
                var buttonCard = document.querySelector('button[data-id="'+idVariasi+'"]');
                var card = buttonCard ? buttonCard.closest('.product-card') : null;
                var updatedBatchList = data.batch_list || ((rawBatch ? rawBatch + ',' : '') + data.new_batch + '|' + data.qty_pcs);
                var updatedEceran = (data.stok_eceran !== undefined) ? data.stok_eceran : (maxStockPcs + data.qty_pcs);
                var updatedDus = (data.stok_dus !== undefined) ? data.stok_dus : 0;
                var updatedTotal = (data.stok_total !== undefined) ? data.stok_total : (maxStockPcs + data.qty_pcs);

                if (card) {
                    card.setAttribute('data-batch', updatedBatchList.toLowerCase());
                    card.setAttribute('data-stok-eceran', updatedEceran);
                    card.setAttribute('data-stok-dus', updatedDus);
                    
                    if (buttonCard) {
                        buttonCard.setAttribute('data-stok', updatedTotal);
                        buttonCard.setAttribute('data-batch', updatedBatchList);
                        buttonCard.setAttribute('data-stok-eceran', updatedEceran);
                        buttonCard.setAttribute('data-stok-dus', updatedDus);
                    }
                    
                    // Update tampilan badge batch pada kartu
                    var badgeContainer = card.querySelector('.mt-2.flex.flex-wrap.gap-1\\.5');
                    if (badgeContainer && updatedBatchList) {
                        var bItems = updatedBatchList.split(',').slice(0, 3);
                        var bHtml = '';
                        bItems.forEach(function(b) {
                            var bCode = b.split('|')[0];
                            var bStok = b.split('|')[1] || '';
                            bHtml += '<span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-bold shadow-sm">B: ' + bCode + (bStok ? ' | ' + bStok : '') + '</span> ';
                        });
                        if (updatedBatchList.split(',').length > 3) {
                            bHtml += '<span class="inline-block px-2 py-1 bg-slate-50 text-slate-500 border border-slate-200 rounded text-[10px] font-bold shadow-sm">...</span>';
                        }
                        badgeContainer.innerHTML = bHtml;
                    }
                    
                    // Update tampilan sisa stok kartu (spesifik pada sisa-stok-container agar tidak menimpa kotak harga)
                    var sisaContainer = card.querySelector('.sisa-stok-container');
                    if (sisaContainer) {
                        var dusUtuh = Math.floor(updatedDus / rasio);
                        sisaContainer.innerHTML = '<span class="font-bold">Sisa Stok:</span><div class="flex items-center gap-1 font-extrabold text-[10px]">' +
                            '<span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700">' + dusUtuh + ' ' + (data.satuan_besar || 'Karton') + '</span>' +
                            '<span class="text-slate-400">•</span>' +
                            '<span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700">' + updatedEceran + ' ' + (data.satuan_kecil || 'Box') + '</span>' +
                            '</div>';
                        sisaContainer.className = 'sisa-stok-container p-1.5 rounded-lg border text-[10px] bg-emerald-50/60 border-emerald-200 text-emerald-800 flex items-center justify-between';
                    }

                    // Update tombol bongkar
                    var bongkarBtn = card.querySelector('.bg-rose-50, .bg-rose-100');
                    if (bongkarBtn) {
                        if (updatedDus <= 0) {
                            bongkarBtn.style.display = 'none';
                        } else {
                            bongkarBtn.innerHTML = '📦 Bongkar Box (Tersisa: ' + updatedEceran + ' ' + (data.satuan_kecil || 'Box') + ')';
                        }
                    }
                }
                
                // Update batch_list & maxPcs pada keranjang jika ada item produk ini
                var itemAlreadyInCart = false;
                cart.forEach(function(ci) {
                    if (parseInt(ci.id) === parseInt(idVariasi)) {
                        ci.batch_list = updatedBatchList;
                        ci.maxPcs = (ci.satuan_tipe === 'kecil') ? updatedEceran : updatedDus;
                        ci.needs_verification = (ci.kategori === 'Obat' || ci.kategori === 'Alat Kesehatan');
                        computeAllocations(ci, ci.qty);
                        if (ci.satuan_tipe === uomType) {
                            itemAlreadyInCart = true;
                        }
                    }
                });
                
                // Jika konversi dipicu otomatis dari klik addToPos / scan, atau item belum ada di keranjang:
                if (scannedCode || !itemAlreadyInCart) {
                    processAddToCart(idVariasi, name, hargaKecil, hargaBesar, updatedEceran, rasio, scannedCode, rawSn, updatedBatchList, false, null, true, uomType, updatedEceran, 'Obat');
                } else {
                    renderCart();
                }
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Terjadi kesalahan sistem saat konversi: ' + err.message, 'error');
        });
    }

    function changeQty(cartId, d) {
        var item = cart.find(i => i.cartId === cartId);
        if (!item) return;
        if (d > 0 && (item.qty + d) * item.rasio > item.maxPcs) {
            Swal.fire('Stok Terbatas', 'Sisa stok fisik tidak mencukupi untuk ditambah.', 'warning');
            return;
        }
        item.qty += d;
        if (item.qty <= 0) {
            cart = cart.filter(i => i.cartId !== cartId);
        } else {
            computeAllocations(item, item.qty);
        }
        renderCart();
    }

    function setQtyManual(cartId, val) {
        var item = cart.find(i => i.cartId === cartId);
        if (!item) return;
        var newQty = parseInt(val) || 0;
        if (newQty <= 0) {
            cart = cart.filter(i => i.cartId !== cartId);
            renderCart();
            return;
        }
        var maxAllowed = Math.floor(item.maxPcs / item.rasio);
        if (newQty > maxAllowed) {
            Swal.fire('Stok Terbatas', 'Jumlah melebihi batas stok fisik (Maks: ' + maxAllowed + ').', 'warning');
            item.qty = maxAllowed > 0 ? maxAllowed : 1;
        } else {
            item.qty = newQty;
        }
        computeAllocations(item, item.qty);
        renderCart();
    }

    function removeItem(cartId) { 
        cart = cart.filter(function(i) { return i.cartId !== cartId; }); 
        renderCart(); 
    }
    function clearCart() { 
        cart = []; 
        try { sessionStorage.removeItem('zencare_pos_cart'); } catch(e) {}
        renderCart(); 
    }

    function renderCart() {
        var tbody = document.getElementById('pos_cart_body');
        var totalEl = document.getElementById('pos_total');
        var btn = document.getElementById('btn_pay');
        var inp = document.getElementById('cart_input');

        if (cart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-zcMut italic">Pilih barang dari katalog...</td></tr>';
            totalEl.innerText = 'Rp 0';
            btn.disabled = true;
            btn.className = 'w-full py-3.5 rounded-xl text-xs font-bold bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed';
            inp.value = '[]';
            try { sessionStorage.removeItem('zencare_pos_cart'); } catch(e) {}
            updateCameraModalCartPreview();
            return;
        }

        var total = 0, html = '';
        cart.forEach(function(i) {
            var sub = i.harga * i.qty;
            total += sub;

            var badgesHtml = '';
            if (i.needs_verification) {
                if (i.allocations && i.allocations.length > 0) {
                    badgesHtml += '<div class="mt-1.5 space-y-1">';
                    i.allocations.forEach(function(al) {
                        if (al.verified) {
                            var displayBatch = (al.scanned_batch || al.batch).toUpperCase();
                            var swappedTag = al.is_anomaly ? '<span class="ml-1 px-1.5 py-0.2 bg-amber-200 text-amber-900 rounded text-[8px] font-extrabold uppercase">DITUKAR</span>' : '';
                            badgesHtml += '<div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold shadow-xs">' +
                                '<span class="text-blue-600 font-extrabold">✔</span>' +
                                '<span>Fisik Tervalidasi: <b class="font-mono text-blue-900">' + displayBatch + '</b> (' + al.qty + ' ' + i.satuan_label + ')</span>' +
                                swappedTag +
                            '</div>';
                        } else {
                            var expectedBatch = (al.batch || '').toUpperCase();
                            badgesHtml += '<div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold animate-pulse shadow-xs">' +
                                '<span class="text-rose-600 font-extrabold">⚠</span>' +
                                '<span>Ambil Fisik: <b class="font-mono text-rose-900">' + expectedBatch + '</b> (' + al.qty + ' ' + i.satuan_label + ')</span>' +
                            '</div>';
                        }
                    });
                    badgesHtml += '</div>';
                } else if (i.alkes_allocations && i.alkes_allocations.length > 0) {
                    badgesHtml += '<div class="mt-1.5 space-y-1">';
                    i.alkes_allocations.forEach(function(u, idx) {
                        if (u.verified) {
                            badgesHtml += '<div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-blue-50 border border-blue-200 text-blue-700 text-[10px] font-bold shadow-xs">' +
                                '<span class="text-blue-600 font-extrabold">✔</span>' +
                                '<span>SN Tervalidasi: <b class="font-mono text-blue-900">' + u.sn.toUpperCase() + '</b> (Unit ' + (idx + 1) + ')</span>' +
                            '</div>';
                        } else {
                            badgesHtml += '<div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-bold animate-pulse shadow-xs">' +
                                '<span class="text-rose-600 font-extrabold">⚠</span>' +
                                '<span>Ambil Unit ' + (idx + 1) + ': <b class="font-mono text-rose-900">Scan SN Fisik</b></span>' +
                            '</div>';
                        }
                    });
                    badgesHtml += '</div>';
                } else if (i.kategori === 'Obat') {
                    badgesHtml += '<div class="mt-1.5 space-y-1">' +
                        '<div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-bold shadow-xs">' +
                            '<span class="text-amber-600 font-extrabold">⚠</span>' +
                            '<span>Perlu Robek Kardus (Sub-batch Belum Ada)</span>' +
                        '</div>' +
                    '</div>';
                }
            }

            var maxAllowed = Math.floor(i.maxPcs / i.rasio);
            var stokInfo = '<span class="block text-[9px] text-slate-500 font-medium mt-1">📦 Total Tersedia: ' + maxAllowed + ' ' + i.satuan_label + '</span>';

            html += '<tr class="hover:bg-slate-50/60 transition-colors">' +
                '<td class="px-4 py-3 font-semibold text-zcTxt text-xs align-top">' +
                    '<div class="font-bold text-slate-800">' + i.nama + '</div>' +
                    '<span class="inline-block text-[10px] text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 font-bold mt-0.5">' + i.satuan_label + ' @ Rp ' + i.harga.toLocaleString('id-ID') + '</span>' +
                    badgesHtml + stokInfo +
                '</td>' +
                '<td class="px-4 py-3 text-center align-top">' +
                    '<div class="flex items-center justify-center gap-1.5 pt-1">' +
                        '<button type="button" onclick="changeQty(\'' + i.cartId + '\',-1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition flex items-center justify-center cursor-pointer">-</button>' +
                        '<input type="number" min="1" max="' + maxAllowed + '" value="' + i.qty + '" onchange="setQtyManual(\'' + i.cartId + '\', this.value)" class="w-12 text-center font-bold text-xs border border-zcBrd rounded-lg py-1 px-1 focus:outline-none focus:border-zc bg-white shadow-xs">' +
                        '<button type="button" onclick="changeQty(\'' + i.cartId + '\',1)" class="w-6 h-6 bg-slate-200 hover:bg-slate-300 rounded-lg text-xs font-bold border border-slate-300 transition flex items-center justify-center cursor-pointer">+</button>' +
                    '</div>' +
                '</td>' +
                '<td class="px-4 py-3 text-right font-black text-xs text-slate-800 align-top pt-2.5">Rp ' + sub.toLocaleString('id-ID') + '</td>' +
                '<td class="px-4 py-3 text-center align-top pt-2"><button type="button" onclick="removeItem(\'' + i.cartId + '\')" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition p-1 hover:bg-rose-50 rounded cursor-pointer">[X]</button></td>' +
            '</tr>';
        });

        tbody.innerHTML = html;
        totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
        btn.disabled = false;
        btn.className = 'w-full py-3.5 rounded-xl text-xs font-bold bg-zc hover:bg-zcHv text-white transition shadow-sm cursor-pointer';
        inp.value = JSON.stringify(cart);
        try {
            sessionStorage.setItem('zencare_pos_cart', JSON.stringify(cart));
        } catch(e) {}
        updateCameraModalCartPreview();
    }

    // --- Payment UI Logic ---
    var currentTotal = 0;
    var modalInput = document.getElementById('modal_nominal');
    
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function openPaymentModal() {
        if (!cart.length) return;
        
        // Blokir jika ada item yang belum divalidasi fisik (di-scan)
        var unverifiedBatches = [];
        cart.forEach(function(i) {
            if (i.needs_verification) {
                if (i.allocations && i.allocations.length > 0) {
                    i.allocations.forEach(function(al) {
                        if (!al.verified) {
                            unverifiedBatches.push(al.batch + ' (' + al.qty + ' ' + i.satuan_label + ')');
                        }
                    });
                }
                if (i.alkes_allocations && i.alkes_allocations.length > 0) {
                    i.alkes_allocations.forEach(function(u, idx) {
                        if (!u.verified) {
                            unverifiedBatches.push(i.nama + ' (Unit ' + (idx + 1) + ')');
                        }
                    });
                }
            }
        });

        if (unverifiedBatches.length > 0) {
            Swal.fire({
                title: 'Validasi Fisik Belum Selesai!',
                html: '<div class="text-left text-sm"><p class="mb-2">Terdapat batch fisik obat/alkes di keranjang yang <b>belum diverifikasi</b> melalui scan barcode:</p>' +
                      '<ul class="list-disc pl-5 font-mono text-xs text-rose-700 bg-rose-50 p-2.5 rounded-lg border border-rose-200 space-y-1">' +
                      unverifiedBatches.map(b => '<li><b>' + b + '</b></li>').join('') +
                      '</ul>' +
                      '<p class="mt-3 text-xs text-slate-500">Harap ambil fisik barang di rak sesuai batch yang tertera dan SCAN barcode-nya untuk mencocokkan sebelum memproses pembayaran!</p></div>',
                icon: 'warning',
                confirmButtonText: 'Saya Mengerti',
                confirmButtonColor: '#1a75d2'
            });
            return;
        }

        currentTotal = 0;
        cart.forEach(function(i) { currentTotal += i.harga * i.qty; });
        
        document.getElementById('modal_total_tagihan').innerText = 'Rp ' + formatRupiah(currentTotal);
        modalInput.value = '';
        calcKembalian();
        
        var modal = document.getElementById('payment_modal');
        var content = document.getElementById('modal_content');
        modal.classList.remove('hidden');
        
        setTimeout(function() {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
            modalInput.focus();
        }, 10);
    }

    function closePaymentModal() {
        stopPosQrisTimer();
        var modal = document.getElementById('payment_modal');
        var content = document.getElementById('modal_content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(function() { modal.classList.add('hidden'); }, 200);
    }

    function setNominal(val) {
        modalInput.value = formatRupiah(val);
        calcKembalian();
    }
    
    function setNominalUangPas() {
        modalInput.value = formatRupiah(currentTotal);
        calcKembalian();
    }

    function calcKembalian() {
        var metode = document.getElementById('modal_metode').value;
        var box = document.getElementById('box_kembalian');
        var lbl = document.getElementById('lbl_kembalian');
        var val = document.getElementById('val_kembalian');
        var btn = document.getElementById('btn_submit_pay');

        if (metode !== 'Tunai') {
            box.classList.add('hidden');
            btn.disabled = false;
            btn.className = 'flex-[2] py-3.5 rounded-xl text-sm font-bold bg-zc hover:bg-zcHv text-white transition shadow-sm cursor-pointer shadow-zc/30';
            return;
        }
        
        box.classList.remove('hidden');

        var raw = modalInput.value.replace(/[^0-9]/g, '');
        if(raw) modalInput.value = formatRupiah(raw);
        var nom = parseInt(raw) || 0;
        
        if (nom === 0) {
            box.className = "flex items-center justify-between p-4 rounded-xl border-2 border-slate-200 bg-slate-50 transition-colors";
            lbl.className = "text-sm font-bold text-zcMut";
            lbl.innerText = "Menunggu Nominal";
            val.className = "text-2xl font-bold text-zcMut";
            val.innerText = "-";
            btn.disabled = true;
            btn.className = "flex-[2] py-3.5 rounded-xl text-sm font-bold bg-slate-300 text-slate-500 cursor-not-allowed transition";
        } else if (nom < currentTotal) {
            box.className = "flex items-center justify-between p-4 rounded-xl border-2 border-rose-300 bg-rose-50 transition-colors";
            lbl.className = "text-sm font-bold text-rose-600";
            lbl.innerText = "Kekurangan Bayar";
            val.className = "text-2xl font-black text-rose-600";
            val.innerText = "Rp " + formatRupiah(currentTotal - nom);
            btn.disabled = true;
            btn.className = "flex-[2] py-3.5 rounded-xl text-sm font-bold bg-slate-300 text-slate-500 cursor-not-allowed transition";
        } else {
            box.className = "flex items-center justify-between p-4 rounded-xl border-2 border-emerald-300 bg-emerald-50 transition-colors";
            lbl.className = "text-sm font-bold text-emerald-600";
            lbl.innerText = "Uang Kembalian";
            val.className = "text-2xl font-black text-emerald-600";
            val.innerText = "Rp " + formatRupiah(nom - currentTotal);
            btn.disabled = false;
            btn.className = "flex-[2] py-3.5 rounded-xl text-sm font-bold bg-zc hover:bg-zcHv text-white shadow-lg shadow-zc/30 transition cursor-pointer";
        }
    }

    let currentPosSnapToken = null;
    let currentPosOrderId = null;
    let posQrisTimerInterval = null;
    let posQrisSecondsLeft = 300;

    function stopPosQrisTimer() {
        if (posQrisTimerInterval) {
            clearInterval(posQrisTimerInterval);
            posQrisTimerInterval = null;
        }
    }

    function startPosQrisTimer(durationSeconds) {
        stopPosQrisTimer();
        posQrisSecondsLeft = durationSeconds || 300;
        let timerBox = document.getElementById('qris_timer_box');
        let timerDisplay = document.getElementById('qris_countdown_display');
        let badge = document.getElementById('qris_status_badge');
        let btn = document.getElementById('btn_open_snap_qris');
        
        if (timerBox) timerBox.classList.remove('hidden');

        function renderTime() {
            let m = Math.floor(posQrisSecondsLeft / 60).toString().padStart(2, '0');
            let s = (posQrisSecondsLeft % 60).toString().padStart(2, '0');
            if (timerDisplay) timerDisplay.innerText = `${m}:${s}`;
        }
        renderTime();

        posQrisTimerInterval = setInterval(() => {
            posQrisSecondsLeft--;
            if (posQrisSecondsLeft <= 0) {
                stopPosQrisTimer();
                if (timerDisplay) timerDisplay.innerText = '00:00';
                if (badge) {
                    badge.innerText = '⚠️ Kadaluarsa (5 Menit)';
                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-800';
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '🔄 Buat Ulang QRIS Kasir (Waktu Habis)';
                }
                Swal.fire({
                    title: 'Batas Waktu QRIS Habis',
                    text: 'Batas waktu pembayaran QRIS di kasir (5 menit) telah habis. Silakan buat QRIS baru jika customer ingin scan ulang.',
                    icon: 'warning'
                });
            } else {
                renderTime();
            }
        }, 1000);
    }

    function toggleNominalInput() {
        var metode = document.getElementById('modal_metode').value;
        var nominalSec = document.getElementById('nominal_section');
        var infoSec = document.getElementById('non_tunai_info');
        var qrisSec = document.getElementById('qris_dynamic_section');
        
        if (metode === 'Tunai') {
            stopPosQrisTimer();
            nominalSec.classList.remove('hidden');
            infoSec.classList.add('hidden');
            if (qrisSec) qrisSec.classList.add('hidden');
        } else if (metode === 'QRIS') {
            nominalSec.classList.add('hidden');
            infoSec.classList.add('hidden');
            if (qrisSec) qrisSec.classList.remove('hidden');
        } else {
            stopPosQrisTimer();
            nominalSec.classList.add('hidden');
            infoSec.classList.remove('hidden');
            if (qrisSec) qrisSec.classList.add('hidden');
        }
        calcKembalian();
    }

    async function triggerMidtransPosQRIS() {
        let btn = document.getElementById('btn_open_snap_qris');
        let badge = document.getElementById('qris_status_badge');
        btn.disabled = true;
        btn.innerHTML = 'Membuat QRIS Midtrans (5 Menit)...';
        badge.innerText = 'Menghubungkan...';

        let now = new Date();
        let ymd = now.getFullYear().toString() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0');
        let rand = Math.floor(1000 + Math.random() * 9000);
        currentPosOrderId = 'POS-' + ymd + '-' + rand;

        try {
            let res = await fetch('../api/pos_snap_token.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    total: currentTotal,
                    cart: cart,
                    invoice: currentPosOrderId
                })
            });
            let data = await res.json();
            if (data.status === 'success' && data.token) {
                currentPosSnapToken = data.token;
                currentPosOrderId = data.order_id;
                badge.innerText = 'Menunggu Scan (5 Menit)...';
                badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800';
                btn.innerHTML = '⚡ Tampilkan Ulang QRIS Midtrans';
                btn.disabled = false;

                // Mulai visual live countdown timer 5 menit di kasir
                startPosQrisTimer(data.expiry_duration_seconds || 300);

                if (typeof snap !== 'undefined' && snap.pay) {
                    snap.pay(data.token, {
                        onSuccess: function(result) {
                            stopPosQrisTimer();
                            badge.innerText = '✅ Lunas';
                            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800';
                            Swal.fire({
                                title: 'Pembayaran QRIS Berhasil!',
                                text: 'Pembayaran Midtrans sukses terverifikasi. Menyimpan struk POS...',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            document.getElementById('form_custom_invoice').value = currentPosOrderId;
                            document.getElementById('form_metode_pembayaran').value = 'QRIS';
                            setTimeout(() => {
                                document.getElementById('form_pos').submit();
                            }, 1200);
                        },
                        onPending: function(result) {
                            badge.innerText = 'Menunggu Konfirmasi';
                            Swal.fire({
                                title: 'Menunggu Pembayaran',
                                text: 'QRIS telah ditampilkan kepada customer. Batas waktu pembayaran di kasir adalah 5 menit. Klik Selesaikan Transaksi jika customer sudah bayar.',
                                icon: 'info'
                            });
                        },
                        onError: function(result) {
                            stopPosQrisTimer();
                            badge.innerText = 'Gagal';
                            Swal.fire('Pembayaran Gagal', 'Transaksi QRIS gagal atau dibatalkan.', 'error');
                        },
                        onClose: function() {
                            badge.innerText = 'QRIS Ditutup (Timer Berjalan)';
                        }
                    });
                } else {
                    alert('Midtrans Snap JS tidak terdeteksi. Silakan periksa koneksi.');
                }
            } else {
                throw new Error(data.message || 'Gagal generate snap token');
            }
        } catch (err) {
            stopPosQrisTimer();
            btn.disabled = false;
            btn.innerHTML = '⚡ Coba Lagi QRIS Midtrans';
            badge.innerText = 'Error API';
            Swal.fire('Midtrans Error', 'Gagal memanggil API Midtrans: ' + err.message, 'warning');
        }
    }

    function submitPayment() {
        document.getElementById('form_metode_pembayaran').value = document.getElementById('modal_metode').value;
        if (currentPosOrderId && document.getElementById('modal_metode').value === 'QRIS') {
            document.getElementById('form_custom_invoice').value = currentPosOrderId;
        }
        document.getElementById('btn_submit_pay').innerText = 'Memproses...';
        document.getElementById('form_pos').submit();
    }

    // --- Camera Scanner Implementation (html5-qrcode) ---
    let html5QrCode = null;
    let isCameraScannerRunning = false;
    let lastScannedBarcode = '';
    let lastScanTimestamp = 0;

    function updateCameraModalCartPreview(lastProductName = null) {
        let countEl = document.getElementById('cam_cart_count_badge');
        let totalEl = document.getElementById('cam_cart_total_badge');
        let prodEl = document.getElementById('cam_last_product_label');
        let cdEl = document.getElementById('cam_cooldown_indicator');
        
        let totalQty = 0;
        let totalPrice = 0;
        if (typeof cart !== 'undefined' && Array.isArray(cart)) {
            cart.forEach(item => {
                totalQty += item.qty;
                totalPrice += (item.harga * item.qty);
            });
        }
        
        if (countEl) countEl.innerText = totalQty + ' Item';
        if (totalEl) totalEl.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
        
        if (lastProductName && prodEl) {
            prodEl.innerHTML = `<span class="text-emerald-700 font-extrabold truncate block">✅ ${lastProductName}</span>`;
            if (cdEl) {
                cdEl.innerText = '✅ Berhasil Masuk!';
                cdEl.className = 'text-[10px] font-extrabold text-emerald-800 bg-emerald-100 px-2 py-1 rounded-lg border border-emerald-300 shrink-0';
                setTimeout(() => {
                    if (cdEl) {
                        cdEl.innerText = 'Siap Scan';
                        cdEl.className = 'text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 shrink-0';
                    }
                }, 2000);
            }
        }
    }

    async function openCameraScannerModal() {
        let modal = document.getElementById('camera_modal');
        let modalContent = document.getElementById('camera_modal_content');
        updateCameraModalCartPreview();
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
        await startCameraScanner();
    }

    async function closeCameraScannerModal() {
        let modal = document.getElementById('camera_modal');
        let modalContent = document.getElementById('camera_modal_content');
        if (html5QrCode && isCameraScannerRunning) {
            try {
                await html5QrCode.stop();
            } catch(e) {}
            isCameraScannerRunning = false;
        }
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    async function startCameraScanner(cameraId = null) {
        let loading = document.getElementById('camera_loading_indicator');
        let badge = document.getElementById('scan_status_badge');
        if (loading) loading.classList.remove('hidden');

        try {
            if (typeof Html5Qrcode === 'undefined') {
                throw new Error('Library Html5Qrcode belum selesai dimuat.');
            }
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("camera_scanner_view");
            }
            
            const devices = await Html5Qrcode.getCameras();
            let select = document.getElementById('camera_device_select');
            if (select && devices && devices.length) {
                select.innerHTML = '';
                devices.forEach((dev, idx) => {
                    let opt = document.createElement('option');
                    opt.value = dev.id;
                    opt.text = dev.label || `Kamera ${idx + 1}`;
                    if (cameraId && dev.id === cameraId) opt.selected = true;
                    select.appendChild(opt);
                });
            }

            const chosenCamera = cameraId || (devices && devices.length > 0 ? devices[0].id : { facingMode: "environment" });

            const config = {
                fps: 20,
                qrbox: function(viewfinderWidth, viewfinderHeight) {
                    let w = Math.min(Math.floor(viewfinderWidth * 0.75), 280);
                    let h = Math.min(Math.floor(viewfinderHeight * 0.65), 180);
                    return { width: Math.max(w, 200), height: Math.max(h, 120) };
                }
            };

            await html5QrCode.start(
                chosenCamera,
                config,
                (decodedText) => {
                    onBarcodeScanSuccess(decodedText);
                },
                (errorMessage) => {}
            );

            isCameraScannerRunning = true;
            if (loading) loading.classList.add('hidden');
            if (badge) {
                badge.innerText = '🟢 Kamera Aktif';
                badge.className = 'inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
            }

        } catch (err) {
            if (loading) loading.classList.add('hidden');
            if (badge) {
                badge.innerText = '🔴 Gagal Akses Kamera';
                badge.className = 'inline-block px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800';
            }
            Swal.fire({
                title: 'Akses Kamera Terkendala',
                text: 'Pastikan browser Anda mengizinkan akses webcam atau kamera. (' + err.message + ')',
                icon: 'warning'
            });
        }
    }

    async function changeCameraDevice(newDeviceId) {
        if (html5QrCode && isCameraScannerRunning) {
            await html5QrCode.stop();
            isCameraScannerRunning = false;
        }
        if (newDeviceId) {
            await startCameraScanner(newDeviceId);
        }
    }

    function onBarcodeScanSuccess(code) {
        code = code.trim();
        let now = Date.now();
        
        // Cooldown 3.5 detik khusus barcode yang SAMA PERSIS agar kasir tidak men-scan ganda secara tak sengaja
        if (code.toLowerCase() === (lastScannedBarcode || '').toLowerCase() && (now - lastScanTimestamp) < 3500) {
            let sisaDetik = Math.ceil((3500 - (now - lastScanTimestamp)) / 1000);
            let cdEl = document.getElementById('cam_cooldown_indicator');
            if (cdEl) {
                cdEl.innerText = `Cooldown (${sisaDetik}s)`;
                cdEl.className = 'text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded-lg border border-amber-200 shrink-0';
            }
            return;
        }
        
        // Jeda minimal 1 detik antar barang berbeda
        if ((now - lastScanTimestamp) < 1000) {
            return;
        }
        
        lastScannedBarcode = code;
        lastScanTimestamp = now;

        // Visual shutter flash pada viewport scanner kamera
        let scanBox = document.getElementById('camera_scanner_view');
        if (scanBox) {
            scanBox.classList.add('ring-4', 'ring-emerald-400');
            setTimeout(() => { scanBox.classList.remove('ring-4', 'ring-emerald-400'); }, 600);
        }

        let codeEl = document.getElementById('last_scanned_code');
        let timeEl = document.getElementById('last_scanned_time');
        let inputEl = document.getElementById('manual_barcode_input');
        if (codeEl) codeEl.innerText = code;
        if (timeEl) timeEl.innerText = new Date().toLocaleTimeString('id-ID');
        if (inputEl) inputEl.value = code;

        // PENTING: Suara beep HANYA dibunyikan 1x di dalam processBarcode() saat item valid masuk keranjang!
        processBarcode(code);
    }

    // --- Barcode Scanner Listener ---
    var barcodeString = '';
    var barcodeTimer = null;
    
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        if (e.key === 'Enter') {
            e.preventDefault(); // Mencegah Enter dari scanner menekan tombol yang sedang fokus (ex: + Pilih Manual)
            if (barcodeString.length > 2) {
                processBarcode(barcodeString);
            }
            barcodeString = '';
            clearTimeout(barcodeTimer);
            return;
        }
        
        if (e.key.length === 1) { 
            barcodeString += e.key;
            clearTimeout(barcodeTimer);
            barcodeTimer = setTimeout(function() {
                barcodeString = ''; 
            }, 300); // Diperpanjang menjadi 300ms untuk mentoleransi delay WiFi dari aplikasi HP
        }
    });

    function handleManualBarcode(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var val = e.target.value.trim();
            if (val.length > 0) {
                processBarcode(val);
                e.target.value = '';
            }
        }
    }

    function processBarcode(sku) {
        try {
            sku = sku.trim().toLowerCase();
            var found = false;
            var cards = document.getElementsByClassName('product-card');
            
            for (var i = 0; i < cards.length; i++) {
                var c = cards[i];
                var skuSpan = c.querySelector('span.font-mono');
                var dataSn = c.getAttribute('data-sn') || '';
                var dataBatch = c.getAttribute('data-batch') || '';
                var batchArray = dataBatch ? dataBatch.split(',').map(b => b.split('|')[0].toLowerCase()) : [];
                
                var isMatch = false;
                if (skuSpan && skuSpan.innerText.toLowerCase() === sku) isMatch = true;
                else if (dataSn.split(',').includes(sku)) isMatch = true;
                else if (batchArray.includes(sku)) isMatch = true;

                if (isMatch) {
                    var btn = c.querySelector('button');
                    if (btn && !btn.disabled) {
                        // Pass the scanned code!
                        // Ensure we pass the exact scanned code
                        var matchedCode = sku;
                        
                        found = true;
                        c.classList.add('bg-zcLt', 'border-zc');
                        setTimeout(function() { c.classList.remove('bg-zcLt', 'border-zc'); }, 300);
                        
                        try {
                            var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                            var oscillator = audioCtx.createOscillator();
                            oscillator.type = 'sine';
                            oscillator.frequency.setValueAtTime(800, audioCtx.currentTime);
                            oscillator.connect(audioCtx.destination);
                            oscillator.start();
                            oscillator.stop(audioCtx.currentTime + 0.1);
                        } catch(err) {}
                        
                        // Execute addToPos with scannedCode
                        if (skuSpan && skuSpan.innerText.toLowerCase() === sku) {
                            // They scanned the general SKU, not the Batch/SN barcode.
                            // We pass null to force them to scan the physical item if it's Obat/Alkes.
                            addToPos(btn, null);
                        } else {
                            addToPos(btn, matchedCode);
                        }
                        break;
                    }
                }
            }
            if (!found) {
                // 1. Cek apakah barcode cocok dengan batch obat yang SUDAH KADALUARSA di katalog
                var expiredMatch = null;
                for (var i = 0; i < cards.length; i++) {
                    var c = cards[i];
                    var expAttr = c.getAttribute('data-expired-batch') || '';
                    if (expAttr) {
                        var expItems = expAttr.split(',');
                        for (var j = 0; j < expItems.length; j++) {
                            var parts = expItems[j].split('|');
                            var bCode = parts[0].trim().toLowerCase();
                            var bExp = parts[1] || '';
                            if (bCode === sku || (bCode.split('.')[0] === sku)) {
                                var pName = c.querySelector('span.text-xs.font-bold') ? c.querySelector('span.text-xs.font-bold').innerText : 'Obat';
                                expiredMatch = { batch: parts[0].toUpperCase(), exp: bExp, name: pName };
                                break;
                            }
                        }
                        if (expiredMatch) break;
                    }
                }

                if (expiredMatch) {
                    Swal.fire({
                        icon: 'error',
                        title: '⛔ OBAT KADALUARSA!',
                        html: `Fisik batch yang Anda scan: <b>${expiredMatch.batch}</b> (${expiredMatch.name})<br><br>` +
                              `Status: <span class="text-rose-600 font-extrabold text-sm">SUDAH KADALUARSA</span> (Exp: <b>${expiredMatch.exp}</b>).<br><br>` +
                              `<div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">` +
                              `⚠️ <b>PERINGATAN KELAYAKAN OBAT:</b><br>` +
                              `Sistem secara otomatis <b>MEMBLOKIR</b> penjualan obat yang telah melewati tanggal kadaluarsa demi keselamatan pasien dan kepatuhan BPOM.<br><br>` +
                              `Silakan pisahkan fisik obat ini ke karantina dan lakukan <b>Mutasi Keluar (Barang Rusak/Kadaluarsa)</b>.</div>`,
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Saya Mengerti'
                    });
                    return;
                }

                // 2. Jika tidak ditemukan di kartu yang tampil, verifikasi ke database via API
                fetch('../api/check_barcode.php?barcode=' + encodeURIComponent(sku))
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.found && res.is_expired) {
                            Swal.fire({
                                icon: 'error',
                                title: '⛔ OBAT KADALUARSA!',
                                html: `Fisik batch yang Anda scan: <b>${res.no_batch}</b> (${res.nama})<br><br>` +
                                      `Status: <span class="text-rose-600 font-extrabold text-sm">SUDAH KADALUARSA</span> (Exp: <b>${res.tgl_exp}</b>).<br><br>` +
                                      `<div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">` +
                                      `⚠️ <b>PERINGATAN KELAYAKAN OBAT:</b><br>` +
                                      `Sistem secara otomatis <b>MEMBLOKIR</b> penjualan obat yang telah melewati tanggal kadaluarsa demi keselamatan pasien dan kepatuhan BPOM.<br><br>` +
                                      `Silakan pisahkan fisik obat ini ke karantina dan lakukan <b>Mutasi Keluar (Barang Rusak/Kadaluarsa)</b>.</div>`,
                                confirmButtonColor: '#e11d48',
                                confirmButtonText: 'Saya Mengerti'
                            });
                        } else if (res.found && res.tipe === 'batch' && res.stok_sisa <= 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Stok Batch Habis',
                                html: `Batch <b>${res.no_batch}</b> (${res.nama}) tercatat di sistem tetapi <b>stok sisa = 0</b>.`,
                                confirmButtonColor: '#1a75d2'
                            });
                        } else if (res.found && res.tipe === 'sn' && res.status !== 'Tersedia') {
                            let camLabel = document.getElementById('cam_last_product_label');
                            let camCooldown = document.getElementById('cam_cooldown_indicator');
                            if (res.status === 'Retur/Rusak') {
                                if (camLabel) camLabel.innerHTML = `<span class="text-rose-600 font-bold">⛔ DITOLAK: UNIT RUSAK (${res.serial_number})</span>`;
                                if (camCooldown) {
                                    camCooldown.innerText = 'Unit Rusak';
                                    camCooldown.className = 'text-[10px] font-extrabold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200 shrink-0';
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: '⛔ ALAT KESEHATAN RUSAK / AFKIR!',
                                    html: `Fisik unit yang Anda scan: <b>${res.serial_number}</b><br>` +
                                          `Produk: <b>${res.nama}</b><br><br>` +
                                          `Status Sistem: <span class="text-rose-600 font-extrabold text-sm uppercase">RUSAK / DIKARANTINA</span><br>` +
                                          (res.catatan ? `<div class="mt-2 p-2.5 bg-slate-100 rounded-lg text-slate-700 text-xs border border-slate-200 text-left"><b>Catatan:</b> ${res.catatan}</div><br>` : `<br>`) +
                                          `<div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 text-left font-medium">` +
                                          `⚠️ <b>PERINGATAN OPERASIONAL KASIR:</b><br>` +
                                          `Unit ini telah dicatat sebagai <b>Barang Rusak / Cacat</b> dan dilarang keras untuk dijual ke pelanggan!<br><br>` +
                                          `Silakan pisahkan fisik alat ini ke area karantina atau kembalikan (retur) ke supplier untuk diganti unit baru.</div>`,
                                    confirmButtonColor: '#e11d48',
                                    confirmButtonText: 'Saya Mengerti'
                                });
                            } else if (res.status === 'Terjual') {
                                if (camLabel) camLabel.innerHTML = `<span class="text-amber-600 font-bold">⚠️ SUDAH TERJUAL: ${res.serial_number}</span>`;
                                let garansiHtml = '';
                                if (res.sisa_garansi !== null && res.sisa_garansi !== undefined) {
                                    if (res.sisa_garansi >= 0) {
                                        garansiHtml = `<div class="mt-2 p-2.5 bg-emerald-50 rounded-lg text-emerald-800 text-xs border border-emerald-200 text-left">` +
                                                      `<b>Invoice:</b> ${res.no_invoice || '-'}<br>` +
                                                      `<b>Tgl Pembelian:</b> ${res.tgl_terjual || '-'}<br>` +
                                                      `<b>Status Garansi:</b> <span class="font-bold text-emerald-700">AKTIF (${res.sisa_garansi} hari tersisa)</span></div>`;
                                    } else {
                                        garansiHtml = `<div class="mt-2 p-2.5 bg-slate-100 rounded-lg text-slate-700 text-xs border border-slate-200 text-left">` +
                                                      `<b>Invoice:</b> ${res.no_invoice || '-'}<br>` +
                                                      `<b>Tgl Pembelian:</b> ${res.tgl_terjual || '-'}<br>` +
                                                      `<b>Status Garansi:</b> <span class="font-bold text-slate-500">KADALUWARSA / HABIS</span></div>`;
                                    }
                                }
                                Swal.fire({
                                    icon: 'warning',
                                    title: '⚠️ UNIT ALKES SUDAH TERJUAL!',
                                    html: `Serial Number <b>${res.serial_number}</b> (${res.nama}) tercatat sudah <b>TERJUAL</b> pada transaksi sebelumnya.<br>` +
                                          garansiHtml + `<br>` +
                                          `<div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 text-left font-medium">` +
                                          `ℹ️ <b>Petunjuk Layanan Kasir:</b><br>` +
                                          `Jika pelanggan datang untuk klaim garansi kerusakan, silakan arahkan ke <b>Laporan Logistik Medis (Jejak SN)</b> untuk memproses isolasi Karantina.</div>`,
                                    confirmButtonColor: '#d97706',
                                    confirmButtonText: 'Saya Mengerti'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Unit Alkes Tidak Tersedia',
                                    html: `Serial Number <b>${res.serial_number}</b> (${res.nama}) sudah berstatus <b>${res.status}</b>.`,
                                    confirmButtonColor: '#1a75d2'
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Barcode Tidak Ditemukan',
                                html: `SKU / Barcode tidak ditemukan atau stok kosong: <b>${sku.toUpperCase()}</b>`,
                                confirmButtonColor: '#1a75d2'
                            });
                        }
                    })
                    .catch(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Barcode Tidak Ditemukan',
                            html: `SKU / Barcode tidak ditemukan atau stok kosong: <b>${sku.toUpperCase()}</b>`,
                            confirmButtonColor: '#1a75d2'
                        });
                    });
            }
        } catch(e) { alert("Error JS di Barcode: " + e.message); }
    }

    function filterPos() {
        try {
            var q = document.getElementById('pos_search').value.toLowerCase().trim();
            var cards = document.getElementsByClassName('product-card');
            for (var i = 0; i < cards.length; i++) {
                var c = cards[i];
                var matchStr = c.getAttribute('data-search') + ' ' + (c.getAttribute('data-sn') || '') + ' ' + (c.getAttribute('data-batch') || '');
                if (matchStr.indexOf(q) !== -1) {
                    c.style.display = '';
                } else {
                    c.style.display = 'none';
                }
            }
        } catch(e) {
            alert("Error JS di filterPos: " + e.message);
        }
    }

    // =========================================================================
    // REALTIME LIVE STOCK SYNC DARI E-COMMERCE & MULTI-TERMINAL
    // =========================================================================
    var isSyncingLive = false;
    function syncPosCatalogLive() {
        if (isSyncingLive) return;
        isSyncingLive = true;
        
        fetch('../api/pos_sync_catalog.php')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            isSyncingLive = false;
            if (data.status !== 'success' || !data.items) return;
            
            data.items.forEach(function(item) {
                var card = document.querySelector('.product-card[data-id="' + item.id + '"]');
                var btn = card ? card.querySelector('button[data-id="' + item.id + '"]') : null;
                if (!card || !btn) return;
                
                var oldStokDus = parseInt(card.getAttribute('data-stok-dus') || 0);
                var oldStokEceran = parseInt(card.getAttribute('data-stok-eceran') || 0);
                var oldBatch = card.getAttribute('data-batch') || '';
                
                var newStokDus = parseInt(item.stok_dus || 0);
                var newStokEceran = parseInt(item.stok_eceran || 0);
                var newTotalStok = parseInt(item.stok || 0);
                var newBatch = (item.batch_list || '').toLowerCase();
                var rasio = parseInt(item.rasio_konversi) || 1;
                
                // Cek apakah ada perubahan stok atau batch
                if (oldStokDus !== newStokDus || oldStokEceran !== newStokEceran || oldBatch !== newBatch) {
                    card.setAttribute('data-stok-dus', newStokDus);
                    card.setAttribute('data-stok-eceran', newStokEceran);
                    card.setAttribute('data-batch', newBatch);
                    if (item.sn_list) card.setAttribute('data-sn', (item.sn_list || '').toLowerCase());
                    
                    btn.setAttribute('data-stok', newTotalStok);
                    btn.setAttribute('data-stok-dus', newStokDus);
                    btn.setAttribute('data-stok-eceran', newStokEceran);
                    btn.setAttribute('data-batch', item.batch_list || '');
                    
                    var sel = document.getElementById('uom_' + item.id);
                    if (newTotalStok <= 0) {
                        btn.disabled = true;
                        btn.className = 'w-1/2 text-[11px] font-bold px-2 py-1.5 rounded-lg border transition bg-slate-200 text-slate-400 border-slate-300 cursor-not-allowed';
                        if (sel) sel.disabled = true;
                    } else {
                        btn.disabled = false;
                        btn.className = 'w-1/2 text-[11px] font-bold px-2 py-1.5 rounded-lg border transition bg-zc hover:bg-zcHv text-white border-zc shadow-xs';
                        if (sel) sel.disabled = false;
                    }
                    
                    // Update tampilan badge batch pada kartu
                    var badgeContainer = card.querySelector('.mt-2.flex.flex-wrap.gap-1\\.5');
                    if (badgeContainer) {
                        if (item.batch_list) {
                            var bItems = item.batch_list.split(',').slice(0, 2);
                            var bHtml = '';
                            bItems.forEach(function(b) {
                                bHtml += '<span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-bold shadow-sm">B: ' + b + '</span> ';
                            });
                            if (item.batch_list.split(',').length > 2) {
                                bHtml += '<span class="inline-block px-2 py-1 bg-slate-50 text-slate-500 border border-slate-200 rounded text-[10px] font-bold shadow-sm">...</span>';
                            }
                            badgeContainer.innerHTML = bHtml;
                        } else {
                            badgeContainer.innerHTML = '';
                        }
                    }
                    
                    // Update Sisa Stok Terpisah (spesifik pada sisa-stok-container)
                    var sisaContainer = card.querySelector('.sisa-stok-container');
                    if (sisaContainer) {
                        var dusUtuh = (item.kategori === 'Obat') ? Math.floor(newStokDus / rasio) : Math.floor(newTotalStok / rasio);
                        var eceranSisa = (item.kategori === 'Obat') ? newStokEceran : (newTotalStok % rasio);
                        
                        var stokContent = '';
                        if (rasio > 1) {
                            stokContent = '<span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700">' + dusUtuh + ' ' + (item.satuan_besar || 'Karton') + '</span>' +
                                          '<span class="text-slate-400">•</span>' +
                                          '<span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700">' + eceranSisa + ' ' + (item.satuan_kecil || 'Box') + '</span>';
                        } else {
                            stokContent = '<span class="bg-white px-1.5 py-0.5 rounded border border-emerald-200/60 text-slate-700">' + newTotalStok + ' ' + (item.satuan_kecil || 'Pcs') + '</span>';
                        }
                        
                        sisaContainer.innerHTML = '<span class="font-bold">Sisa Stok:</span><div class="flex items-center gap-1 font-extrabold text-[10px]">' + stokContent + '</div>';
                        sisaContainer.className = 'sisa-stok-container p-1.5 rounded-lg border text-[10px] ' + (newTotalStok > 0 ? 'bg-emerald-50/60 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-700') + ' flex items-center justify-between';
                    }
                    
                    // Update tombol bongkar
                    var bongkarBtn = card.querySelector('.bg-rose-50, .bg-rose-100');
                    if (bongkarBtn) {
                        if (item.kategori === 'Obat' && newStokDus > 0) {
                            bongkarBtn.style.display = 'block';
                            bongkarBtn.innerHTML = '📦 Bongkar Box (Tersisa: ' + newStokEceran + ' ' + (item.satuan_kecil || 'Box') + ')';
                        } else {
                            bongkarBtn.style.display = 'none';
                        }
                    }
                    
                    // Update keranjang belanja jika ada produk ini
                    var cartChanged = false;
                    cart.forEach(function(ci) {
                        if (parseInt(ci.id) === parseInt(item.id)) {
                            ci.batch_list = item.batch_list;
                            var isMulti = (ci.product_rasio || ci.rasio) > 1;
                            var maxAvail = isMulti ? ((ci.satuan_tipe === 'kecil') ? newStokEceran : newStokDus) : newTotalStok;
                            ci.maxPcs = maxAvail;
                            computeAllocations(ci, ci.qty);
                            var mult = ci.multiplier || ((ci.satuan_tipe === 'besar') ? (ci.product_rasio || ci.rasio) : 1);
                            if (ci.qty * mult > maxAvail) {
                                ci.qty = Math.max(1, Math.floor(maxAvail / mult));
                                computeAllocations(ci, ci.qty);
                                cartChanged = true;
                            }
                        }
                    });
                    if (cartChanged) {
                        renderCart();
                        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        Toast.fire({ icon: 'info', title: `Stok ${item.nama} telah diperbarui (Sinkron E-Commerce)` });
                    }
                }
            });
        })
        .catch(function() {
            isSyncingLive = false;
        });
    }

    // Interval background sync setiap 4 detik & saat window tab aktif
    setInterval(syncPosCatalogLive, 4000);
    window.addEventListener('focus', syncPosCatalogLive);
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) syncPosCatalogLive();
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Deteksi jika kasir dialihkan kembali oleh Midtrans (misal waktu habis / tombol kembali)
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const returnedOrderId = urlParams.get('order_id');
            const transStatus = urlParams.get('transaction_status');
            if (returnedOrderId) {
                if (transStatus === 'settlement' || transStatus === 'capture') {
                    Swal.fire({
                        title: 'Pembayaran Berhasil',
                        text: 'Transaksi QRIS ' + returnedOrderId + ' telah berhasil dibayar.',
                        icon: 'success'
                    });
                } else if (transStatus === 'expire' || transStatus === 'cancel' || transStatus === 'deny') {
                    Swal.fire({
                        title: 'Waktu QRIS Habis / Dibatalkan',
                        text: 'Transaksi ' + returnedOrderId + ' telah kedaluwarsa. Kasir dapat membuat QRIS baru jika customer ingin scan ulang.',
                        icon: 'warning'
                    });
                }
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        } catch(e) {}

        /* php */
            try { sessionStorage.removeItem('zencare_pos_cart'); } catch(e) {}
        /* php */
            try {
                var savedCart = sessionStorage.getItem('zencare_pos_cart');
                if (savedCart) {
                    var parsed = JSON.parse(savedCart);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        cart = parsed;
                        cart.forEach(function(ci) {
                            var cardEl = document.querySelector('.product-card[data-id="' + ci.id + '"]');
                            if (cardEl) {
                                var latestBatch = cardEl.getAttribute('data-batch');
                                if (latestBatch) ci.batch_list = latestBatch;
                                var latestSn = cardEl.getAttribute('data-sn');
                                if (latestSn) ci.sn_list = latestSn;
                                var latestEceran = cardEl.getAttribute('data-stok-eceran');
                                var latestDus = cardEl.getAttribute('data-stok-dus');
                                var isMulti = (ci.product_rasio || ci.rasio) > 1;
                                if (isMulti) {
                                    if (ci.satuan_tipe === 'kecil' && latestEceran !== null) ci.maxPcs = parseInt(latestEceran);
                                    if (ci.satuan_tipe === 'besar' && latestDus !== null) ci.maxPcs = parseInt(latestDus);
                                } else {
                                    var btnRef = cardEl.querySelector('button[data-stok]');
                                    var rawStok = btnRef ? btnRef.getAttribute('data-stok') : null;
                                    if (rawStok !== null) ci.maxPcs = parseInt(rawStok);
                                }
                            }
                            var hasB = ci.batch_list && ci.batch_list.trim().length > 0;
                            var hasS = ci.sn_list && ci.sn_list.trim().length > 0;
                            ci.needs_verification = (hasB || hasS);
                            computeAllocations(ci, ci.qty);
                        });
                        renderCart();
                    }
                }
            } catch(e) {}
        /* php */
    });
    