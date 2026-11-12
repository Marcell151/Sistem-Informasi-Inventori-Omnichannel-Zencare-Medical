import re
from apply_italics import master_regex

def process_html_text(html_content):
    # Regex to split HTML into tokens: tags/comments/scripts/styles vs text
    tag_pattern = re.compile(r'(<!--.*?-->|<script.*?</script>|<style.*?</style>|<head.*?</head>|<svg.*?</svg>|<[^>]+>)', re.DOTALL | re.IGNORECASE)
    
    tokens = []
    last_end = 0
    for match in tag_pattern.finditer(html_content):
        start, end = match.span()
        if start > last_end:
            tokens.append((False, html_content[last_end:start])) # text
        tokens.append((True, match.group(0))) # tag
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
                # Already inside italic or empty whitespace
                output_tokens.append(content)
            else:
                # Apply master regex
                new_text = master_regex.sub(lambda m: f'<i>{m.group(1)}</i>', content)
                output_tokens.append(new_text)
                
    return ''.join(output_tokens)

# Test on BAB_IV_ZENCARE_MEDICAL.html
with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    orig_html = f.read()

processed_html = process_html_text(orig_html)

# Let's count occurrences of <i> before and after
print('Orig <i> count:', len(re.findall(r'<i\b', orig_html)))
print('Processed <i> count:', len(re.findall(r'<i\b', processed_html)))
print('Processed length difference:', len(processed_html) - len(orig_html))

