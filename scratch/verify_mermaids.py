from test_processor_bab_iii_iv import process_html
import re

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    orig = f.read()

processed = process_html(orig)

orig_mermaids = re.findall(r'<div[^>]*class="[^"]*mermaid[^"]*"[^>]*>.*?</div>', orig, re.DOTALL)
proc_mermaids = re.findall(r'<div[^>]*class="[^"]*mermaid[^"]*"[^>]*>.*?</div>', processed, re.DOTALL)

print(f'Orig mermaids: {len(orig_mermaids)}, Proc mermaids: {len(proc_mermaids)}')
exact = orig_mermaids == proc_mermaids
print('Are all Mermaid blocks 100% IDENTICAL?:', exact)
if not exact:
    for i, (m1, m2) in enumerate(zip(orig_mermaids, proc_mermaids)):
        if m1 != m2:
            print(f'Mismatch in block {i+1}!')
            break
