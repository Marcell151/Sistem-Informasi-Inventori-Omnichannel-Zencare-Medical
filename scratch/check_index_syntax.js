const fs = require('fs');

const content = fs.readFileSync('index.php', 'utf8');

// Find all script tags
const scriptRegex = /<script(?:\s+[^>]*)?>([\s\S]*?)<\/script>/gi;
let match;
let scriptIndex = 1;

while ((match = scriptRegex.exec(content)) !== null) {
    let scriptCode = match[1];
    
    // Replace PHP blocks with neutral placeholders
    scriptCode = scriptCode.replace(/<\?php[\s\S]*?\?>/gi, '/* php */');
    scriptCode = scriptCode.replace(/<\?=[\s\S]*?\?>/gi, '"mock_php_val"');

    const tempFile = `scratch/temp_index_script_${scriptIndex}.js`;
    fs.writeFileSync(tempFile, scriptCode);

    try {
        require('child_process').execSync(`node --check ${tempFile}`);
        console.log(`index.php Script ${scriptIndex}: SYNTAX OK`);
    } catch (e) {
        console.error(`index.php Script ${scriptIndex}: SYNTAX ERROR!\n`, e.stderr.toString());
    }
    scriptIndex++;
}

