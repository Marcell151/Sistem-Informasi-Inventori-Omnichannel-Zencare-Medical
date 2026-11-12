import re

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_iii_iv = f.read()

mermaids = re.findall(r'<div[^>]*class="[^"]*mermaid[^"]*"[^>]*>.*?</div>', text_iii_iv, re.DOTALL)
print(f'Total mermaid blocks: {len(mermaids)}')
for i, m in enumerate(mermaids):
    print(f'Block {i+1} length: {len(m)}')

