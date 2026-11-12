import re

with open('scratch/latest_user_prompt.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Extract sections 3.5.X
sections = re.findall(r'(3\.5\.\d+\s+Tabel\s+([a-zA-Z0-9_]+))', text)
print(f'Total tables found: {len(sections)}')
for s in sections:
    print(s[0])

# Check table names list
expected_tables = [
    'users', 'pengaturan_web', 'supplier', 'produk_induk', 'produk_variasi',
    'stok_toko', 'stok_batch', 'unit_serial', 'penerimaan_stok', 'penerimaan_detail',
    'penjualan', 'detail_penjualan', 'pesanan_batch_alokasi', 'kartu_stok', 'histori_konversi',
    'log_anomali_fefo', 'sesi_opname', 'detail_opname_batch', 'detail_opname_sn', 'riwayat_hitung_opname'
]

found_tables = [s[1] for s in sections]
print('Matches expected:', found_tables == expected_tables)
if found_tables != expected_tables:
    print('Diff:', set(expected_tables) ^ set(found_tables))

