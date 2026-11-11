import re

files = [
    'inventori/stok_opname.php',
    'laporan_ta/DOKUMENTASI_TESTING_STOK_OPNAME.html',
    'pos/pos.php'
]

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        text = f.read()

    emojis = re.findall(r"[\U00010000-\U0010ffff]", text)
    symbols = re.findall(r"[⏳⚠️🔒📋✓✔❌]", text)
    print(f"{filepath}:")
    print(f"  Emojis: {len(emojis)} -> {set(emojis)}")
    print(f"  Symbols: {len(symbols)} -> {set(symbols)}")
