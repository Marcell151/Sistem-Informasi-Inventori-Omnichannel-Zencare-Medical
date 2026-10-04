let scannedCode = 'amx-2024-b';
let kat = 'Obat';
let rawBatch = 'amx-2024-a,amx-2024-b,amx-2024-c';
let batches = rawBatch.split(',');
let triggered = false;
if (batches.length > 0 && batches[0] !== scannedCode && batches.includes(scannedCode)) {
    triggered = true;
}
console.log('Triggered: ' + triggered);
