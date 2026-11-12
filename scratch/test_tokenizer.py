import re

# An HTML text tokenizer separates HTML into (is_tag, content)
def tokenize_html(html):
    # Matches tags, comments, script, style
    pattern = re.compile(r'(<!--.*?-->|<script.*?</script>|<style.*?</style>|<[^>]+>)', re.DOTALL | re.IGNORECASE)
    tokens = []
    last_end = 0
    for match in pattern.finditer(html):
        start, end = match.span()
        if start > last_end:
            tokens.append((False, html[last_end:start])) # text
        tokens.append((True, match.group(0))) # tag/script/style/comment
        last_end = end
    if last_end < len(html):
        tokens.append((False, html[last_end:]))
    return tokens

# Test reconstruction
with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    orig = f.read()

tokens = tokenize_html(orig)
reconstructed = ''.join(t[1] for t in tokens)
print('Tokens count:', len(tokens))
print('Reconstruction exact match?:', orig == reconstructed)

