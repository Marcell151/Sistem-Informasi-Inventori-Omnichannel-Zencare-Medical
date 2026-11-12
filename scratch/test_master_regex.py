import re

from apply_italics import PHRASES, SINGLE_WORDS, DB_IDENTIFIERS

# Combine all patterns
# Clean patterns: strip r'\b' if needed, sort by length descending
all_raw = []
for p in PHRASES:
    all_raw.append(p.strip(r'\b'))
for p in SINGLE_WORDS:
    all_raw.append(p.strip(r'\b'))
for p in DB_IDENTIFIERS:
    all_raw.append(p)

# De-duplicate while preserving longest first
unique_patterns = sorted(list(set(all_raw)), key=lambda x: len(x), reverse=True)

# Build regex
regex_parts = []
for p in unique_patterns:
    regex_parts.append(re.escape(p))

master_regex = re.compile(r'\b(' + '|'.join(regex_parts) + r')\b')

# Test on various sentences
samples = [
    "Pada sisi backend, sistem memvalidasi kredensial terhadap tabel users menggunakan pencocokan hash terenkripsi.",
    "Terminal Kasir (Point of Sale / POS) dan Struk Pembayaran",
    "Stok dicetak menggunakan mesin pencetak kertas termal (thermal slip) ukuran 58mm.",
    "Seluruh metrik dihimpun secara dinamis dari tabel penjualan, produk_variasi, dan stok_batch.",
    "Sistem menerapkan validasi otomatis: kolom Fisik Kedaluwarsa dinonaktifkan untuk nomor batch yang belum melewati tanggal kedaluwarsa.",
    "Pengujian Kotak Hitam (Black-Box Testing) Modul Stok Opname Terpusat",
    "Pengujian Penerimaan Pengguna (User Acceptance Testing / UAT)",
    "Pencacahan fisik rak (blind count) staf kasir dan mekanisme freeze lock superadmin.",
    "Konfigurasi multi-UOM pada tabel produk_induk dan konversi_satuan.",
]

for s in samples:
    out = master_regex.sub(lambda m: f'<i>{m.group(1)}</i>', s)
    print('ORIG:', s)
    print('OUT :', out)
    print()

