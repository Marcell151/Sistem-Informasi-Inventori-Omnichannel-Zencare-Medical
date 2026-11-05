# -*- coding: utf-8 -*-
import os, re

files = [
    'penjualan.php',
    'ketersediaan_stok.php',
    'logistik_medis.php',
    'penerimaan_barang.php',
    'mutasi_konversi.php',
    'anomali_fefo.php'
]

for f in files:
    p = os.path.join('laporan', f)
    with open(p, 'r', encoding='utf-8', errors='ignore') as fp:
        txt = fp.read()
    
    # Find all GET/POST param accesses like $_GET['...']
    get_params = re.findall(r"\$_GET\['([^']+)'\]", txt)
    print(f, "--> Filters used:", list(dict.fromkeys(get_params)))

