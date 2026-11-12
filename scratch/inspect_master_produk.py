import re

with open(r'c:\xampp\htdocs\inventory_zencare\admin\master_produk.php', 'r', encoding='utf-8') as f:
    text = f.read()

# Modals
modals = re.findall(r'<div[^>]*id="([^"]*modal[^"]*)"', text, re.IGNORECASE)
print('Modals found:', modals)

# Buttons
buttons = re.findall(r'<button[^>]*onclick="([^"]*)"[^>]*>(.*?)</button>', text, re.DOTALL)
for fn, label in buttons[:20]:
    clean_label = ' '.join(re.sub(r'<[^>]+>', '', label).split())
    print(f'Button: {clean_label} -> {fn}')

# Let's see what modals or forms exist in the HTML body
print("\n--- Search for modal contents ---")
modal_blocks = re.findall(r'(<div[^>]*id="modal[^"]*"[^>]*>.*?</div>\s*</div>\s*</div>)', text, re.DOTALL)
print(f'Total modal blocks: {len(modal_blocks)}')
for mb in re.finditer(r'<h[1-5][^>]*>(.*?)</h[1-5]>', text):
    print('Heading:', ' '.join(re.sub(r'<[^>]+>', '', mb.group(1)).split()))

