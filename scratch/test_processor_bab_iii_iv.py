import re
from apply_italics import master_pattern, tabel_penjualan_pattern

def process_html(html_content):
    # Matches tags, comments, script, style, head, svg, and mermaid divs
    token_pattern = re.compile(
        r'(<!--.*?-->|'
        r'<script.*?</script>|'
        r'<style.*?</style>|'
        r'<head.*?</head>|'
        r'<svg.*?</svg>|'
        r'<div[^>]*class="[^"]*mermaid[^"]*"[^>]*>.*?</div>|'
        r'<[^>]+>)',
        re.DOTALL | re.IGNORECASE
    )
    
    tokens = []
    last_end = 0
    for match in token_pattern.finditer(html_content):
        start, end = match.span()
        if start > last_end:
            tokens.append((False, html_content[last_end:start])) # Text node
        tokens.append((True, match.group(0))) # Tag or protected block
        last_end = end
    if last_end < len(html_content):
        tokens.append((False, html_content[last_end:]))
    
    output_tokens = []
    italic_depth = 0
    
    for is_tag, content in tokens:
        if is_tag:
            tag_lower = content.lower()
            # Track italic/em depth
            if re.match(r'<i(\s+[^>]*)?>', tag_lower):
                italic_depth += 1
            elif re.match(r'</i\s*>', tag_lower):
                italic_depth = max(0, italic_depth - 1)
            elif re.match(r'<em(\s+[^>]*)?>', tag_lower):
                italic_depth += 1
            elif re.match(r'</em\s*>', tag_lower):
                italic_depth = max(0, italic_depth - 1)
            
            output_tokens.append(content)
        else:
            if italic_depth > 0 or not content.strip():
                output_tokens.append(content)
            else:
                # First handle tabel penjualan
                text = tabel_penjualan_pattern.sub('tabel <i>penjualan</i>', content)
                # Apply master regex to text node
                new_text = master_pattern.sub(lambda m: f'<i>{m.group(0)}</i>', text)
                output_tokens.append(new_text)
                
    result = ''.join(output_tokens)
    
    # Upgrade copyText function in <script>
    old_copy_fn = """function copyText(elementId, btnElement) {
            const el = document.getElementById(elementId);
            const text = el.innerText || el.textContent;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btnElement.innerHTML;
                btnElement.innerHTML = `<svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!`;
                btnElement.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                setTimeout(() => {
                    btnElement.innerHTML = originalText;
                    btnElement.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                }, 2000);
            });
        }"""
    
    # We can match copyText using regex to replace it with rich HTML copy support
    rich_copy_fn = """function copyText(elementId, btnElement) {
            const el = document.getElementById(elementId);
            if (!el) return;
            const plainText = el.innerText || el.textContent;
            const originalText = btnElement.innerHTML;

            function showSuccess() {
                btnElement.innerHTML = `<svg class="w-4 h-4 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Tersalin!`;
                btnElement.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                setTimeout(() => {
                    btnElement.innerHTML = originalText;
                    btnElement.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
                }, 2000);
            }

            if (navigator.clipboard && window.ClipboardItem) {
                const blobHtml = new Blob([el.innerHTML], { type: 'text/html' });
                const blobText = new Blob([plainText], { type: 'text/plain' });
                navigator.clipboard.write([
                    new ClipboardItem({
                        'text/html': blobHtml,
                        'text/plain': blobText
                    })
                ]).then(showSuccess).catch(() => {
                    navigator.clipboard.writeText(plainText).then(showSuccess);
                });
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(plainText).then(showSuccess);
            }
        }"""
    
    result = re.sub(r'function copyText\(elementId,\s*btnElement\)\s*\{.*?\n\s*\}', rich_copy_fn, result, flags=re.DOTALL)
    
    return result

if __name__ == '__main__':
    with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
        orig = f.read()
    
    processed = process_html(orig)
    print('Orig length:', len(orig))
    print('Processed length:', len(processed))
    print('Difference:', len(processed) - len(orig))
    print('Processed <i> count:', len(re.findall(r'<i\b', processed)))

