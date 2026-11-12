with open(r'c:\xampp\htdocs\inventory_zencare\laporan\penerimaan_barang.php', 'r', encoding='utf-8') as f:
    text = f.read()

print('Length of laporan/penerimaan_barang.php:', len(text))
import re
headings = re.findall(r'<h[1-4][^>]*>(.*?)</h[1-4]>', text)
for h in headings:
    print('Heading:', re.sub(r'<[^>]+>', '', h).strip())

