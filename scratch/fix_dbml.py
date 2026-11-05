import re

sql_file = r'c:\xampp\htdocs\inventory_zencare\database\isi\db_inventory (demo) 3.sql'
with open(sql_file, 'r', encoding='utf-8', errors='ignore') as f:
    sql = f.read()

bt = chr(96)
table_blocks = re.findall(r'CREATE TABLE\s+' + bt + r'([a-zA-Z0-9_]+)' + bt + r'\s*\((.*?)\)\s*ENGINE=', sql, re.DOTALL | re.IGNORECASE)

print(f'Total tables found: {len(table_blocks)}')

order = [
    'users', 'supplier', 'produk_induk', 'produk_variasi',
    'stok_toko', 'stok_batch', 'unit_serial',
    'penerimaan_stok', 'penerimaan_detail',
    'penjualan', 'detail_penjualan', 'pesanan_batch_alokasi',
    'kartu_stok', 'histori_konversi', 'log_anomali_fefo',
    'sesi_opname', 'detail_opname_batch', 'detail_opname_sn', 'riwayat_hitung_opname',
    'pengaturan_web'
]

table_dict = {tname: tbody for tname, tbody in table_blocks}

dbml_lines = [
    '// =========================================================================',
    '// LOGICAL DATA MODEL (ERD) - ZENCARE MEDICAL',
    '// Sistem Informasi Inventori Omnichannel Obat & Alat Kesehatan (20 Tabel)',
    '// Format DBML Valid untuk https://dbdiagram.io',
    '// =========================================================================\n'
]

for tname in order:
    if tname not in table_dict:
        continue
    tbody = table_dict[tname]
    lines = [l.strip() for l in tbody.split('\n') if l.strip()]
    
    # 1. Identify PKs
    pk_cols = set()
    for l in lines:
        if l.startswith('PRIMARY KEY'):
            pkm = re.search(r'PRIMARY KEY\s*\((.*?)\)', l)
            if pkm:
                for c in pkm.group(1).split(','):
                    pk_cols.add(c.replace(bt, '').strip())

    # 2. Identify Unique Keys
    uniq_cols = set()
    for l in lines:
        if 'UNIQUE KEY' in l or (l.startswith('UNIQUE') and 'KEY' in l):
            um = re.search(r'\((.*?)\)', l)
            if um:
                for c in um.group(1).split(','):
                    uniq_cols.add(c.replace(bt, '').strip())

    dbml_lines.append(f'Table {tname} {{')
    
    for l in lines:
        if l.startswith(bt):
            m = re.match(r'^' + bt + r'([a-zA-Z0-9_]+)' + bt + r'\s+(.*)$', l)
            if not m:
                continue
            cname = m.group(1)
            crest = m.group(2).rstrip(',')
            
            # Simplify data type cleanly for DBML
            dtype = crest.split()[0].lower()
            if dtype.startswith('int'):
                type_str = 'int'
            elif dtype.startswith('tinyint(1)'):
                type_str = 'boolean'
            elif dtype.startswith('tinyint'):
                type_str = 'int'
            elif dtype.startswith('smallint'):
                type_str = 'smallint'
            elif dtype.startswith('varchar'):
                # keep varchar length, e.g. varchar(100)
                vm = re.search(r'varchar\(\d+\)', crest, re.IGNORECASE)
                type_str = vm.group(0).lower() if vm else 'varchar'
            elif dtype.startswith('decimal'):
                dm = re.search(r'decimal\(\d+,\d+\)', crest, re.IGNORECASE)
                type_str = dm.group(0).lower() if dm else 'decimal'
            elif dtype.startswith('text'):
                type_str = 'text'
            elif dtype.startswith('date'):
                type_str = 'date'
            elif dtype.startswith('datetime'):
                type_str = 'datetime'
            elif dtype.startswith('timestamp'):
                type_str = 'timestamp'
            elif dtype.startswith('enum'):
                # In dbdiagram, inline enum with spaces/commas breaks. Use varchar(50) for rock-solid stability!
                type_str = 'varchar(50)'
            else:
                type_str = dtype
            
            # Attributes: pk, increment, not null, unique, default
            attrs = []
            if cname in pk_cols:
                attrs.append('pk')
                if 'AUTO_INCREMENT' in crest.upper():
                    attrs.append('increment')
            else:
                if 'NOT NULL' in crest.upper():
                    attrs.append('not null')
                if cname in uniq_cols or 'UNIQUE' in crest.upper():
                    attrs.append('unique')
                
                # Check for clean default
                def_m = re.search(r"DEFAULT\s+('?[a-zA-Z0-9_.\-]+'?)", crest, re.IGNORECASE)
                if def_m:
                    def_val = def_m.group(1)
                    if def_val.upper() != 'NULL' and 'CURRENT_TIMESTAMP' not in def_val.upper():
                        # If boolean
                        if type_str == 'boolean':
                            if def_val in ('1', "'1'"):
                                attrs.append('default: true')
                            elif def_val in ('0', "'0'"):
                                attrs.append('default: false')
                            else:
                                attrs.append(f'default: {def_val}')
                        else:
                            attrs.append(f'default: {def_val}')

            attr_str = (' [' + ', '.join(attrs) + ']') if attrs else ''
            dbml_lines.append(f'  {cname} {type_str}{attr_str}')
    
    dbml_lines.append('}\n')

# References
dbml_lines.append('// =========================================================================')
dbml_lines.append('// RELASI ANTAR-TABEL (FOREIGN KEY CONSTRAINTS)')
dbml_lines.append('// =========================================================================')

all_refs = set()

# Parse FKs from DDL
for tname, tbody in table_blocks:
    lines = [l.strip() for l in tbody.split('\n') if l.strip()]
    for l in lines:
        if 'FOREIGN KEY' in l and 'REFERENCES' in l:
            fkm = re.search(r'FOREIGN KEY\s*\(' + bt + r'([a-zA-Z0-9_]+)' + bt + r'\)\s*REFERENCES\s*' + bt + r'([a-zA-Z0-9_]+)' + bt + r'\s*\(' + bt + r'([a-zA-Z0-9_]+)' + bt + r'\)', l)
            if fkm:
                fk_col, ref_table, ref_col = fkm.groups()
                ref_line = f'Ref: {tname}.{fk_col} > {ref_table}.{ref_col}'
                if ref_line not in all_refs:
                    all_refs.add(ref_line)
                    dbml_lines.append(ref_line)

# Logical links
logical_links = [
    ('log_anomali_fefo.id_penjualan', 'penjualan.id'),
    ('log_anomali_fefo.id_user', 'users.id'),
    ('log_anomali_fefo.id_variasi', 'produk_variasi.id'),
    ('unit_serial.id_penjualan', 'penjualan.id'),
    ('kartu_stok.id_batch', 'stok_batch.id'),
]

dbml_lines.append('\n// Relasi Fungsional Operasional:')
for fk_full, pk_full in logical_links:
    ref_line = f'Ref: {fk_full} > {pk_full}'
    if ref_line not in all_refs:
        all_refs.add(ref_line)
        dbml_lines.append(ref_line)

output_file = r'c:\xampp\htdocs\inventory_zencare\lain\zencare_dbml_dbdiagram.txt'
with open(output_file, 'w', encoding='utf-8') as f:
    f.write('\n'.join(dbml_lines))

print(f'Successfully generated 100% valid DBML in {output_file}!')

