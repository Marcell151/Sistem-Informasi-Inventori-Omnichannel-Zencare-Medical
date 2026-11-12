import re
from test_full_processing import processed_html

# Print paragraph of 4.1.1.1, 4.1.1.2, 4.1.1.3
for num in ['4.1.1.1', '4.1.1.2', '4.1.1.3', '4.1.1.10']:
    pos = processed_html.find(num)
    if pos != -1:
        snippet = processed_html[pos:pos+2000]
        # find the <p class="text-xs font-serif text-slate-700
        m = re.search(r'<p class=\"text-xs font-serif text-slate-700[^\"]*\">(.*?)</p>', snippet, re.DOTALL)
        if m:
            print(f'=== {num} ===')
            print(m.group(1).strip()[:400])
            print('...')

