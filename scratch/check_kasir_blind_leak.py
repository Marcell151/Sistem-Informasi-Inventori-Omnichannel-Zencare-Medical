with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

import re

# Find the cashier block (Tahap 1 / Kasir View)
pos_kasir = code.find('FORM HITUNG BUTA')
pos_kasir_end = code.find('TAHAP 2: VERIFIKASI', pos_kasir)

kasir_block = code[pos_kasir:pos_kasir_end] if pos_kasir != -1 and pos_kasir_end != -1 else code[pos_kasir:]
print('Kasir block length:', len(kasir_block))

# Check for keywords that could leak numbers
leak_keywords = ['stok_sistem', 'stok_tersedia', 'stok_reserved', 'Unit Tersedia', 'selisih', 'sistem_fisik']
for kw in leak_keywords:
    matches = [m.start() for m in re.finditer(kw, kasir_block, re.IGNORECASE)]
    print(f"Keyword '{kw}' in Kasir block: {len(matches)} occurrences")
    for m in matches[:5]:
        print("  ->", kasir_block[m-30:m+80].strip().replace('\n', ' '))

