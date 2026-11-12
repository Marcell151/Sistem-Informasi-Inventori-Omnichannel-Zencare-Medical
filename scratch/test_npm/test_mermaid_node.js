const fs = require('fs');
const path = require('path');
const { JSDOM } = require('jsdom');

const dom = new JSDOM('<!DOCTYPE html><html><body></body></html>');
global.window = dom.window;
global.document = dom.window.document;
global.navigator = dom.window.navigator;

const mermaid = require('mermaid');

async function testFile(filename) {
    console.log(`\n================ Testing ${filename} ================`);
    const htmlPath = path.resolve(__dirname, '../../laporan_ta', filename);
    const html = fs.readFileSync(htmlPath, 'utf8');
    const regex = /<div class="mermaid[^"]*">([\s\S]*?)<\/div>/g;
    let match;
    let count = 0;
    let errorCount = 0;

    mermaid.default.initialize({ startOnLoad: false });
    while ((match = regex.exec(html)) !== null) {
        count++;
        let code = match[1].trim()
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&amp;/g, '&');
        try {
            await mermaid.default.parse(code);
            console.log(`  Block ${count}: SUCCESS`);
        } catch (e) {
            errorCount++;
            console.log(`  Block ${count}: ERROR ->`, e.str || e.message || e);
        }
    }
    console.log(`Total blocks in ${filename}: ${count}, Errors: ${errorCount}`);
    return errorCount;
}

async function run() {
    let err1 = await testFile('BAB_III_ZENCARE_MEDICAL.html');
    let err2 = await testFile('BAB_III_DAN_IV_ZENCARE_MEDICAL.html');
    if (err1 === 0 && err2 === 0) {
        console.log('\n>>> ALL MERMAID BLOCKS IN BOTH FILES PASSED 100% SUCCESSFULLY! <<<');
    } else {
        console.error('\n>>> SOME MERMAID BLOCKS FAILED! <<<');
        process.exit(1);
    }
}

run().catch(console.error);
