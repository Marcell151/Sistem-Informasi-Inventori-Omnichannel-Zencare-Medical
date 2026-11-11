# Script to update UI in inventori/stok_opname.php
import re

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    code = f.read()

# 1. Update query to include harga_jual_kecil, harga_jual_besar for financial loss estimation
old_q = "SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi,"
new_q = "SELECT dob.*, pv.nama_variasi, pv.sku_variasi, pv.satuan_besar, pv.satuan_kecil, pv.rasio_konversi, pv.harga_jual_kecil, pv.harga_jual_besar,"

if old_q in code:
    code = code.replace(old_q, new_q, 1)
    print("Added price columns to query.")

# 2. Check currentTahap definition
tahap_def = """$currentTahap = $activeSession['tahap'] ?? 'Penghitungan';"""
if "$currentTahap" not in code:
    marker = "$activeSession = cekSesiOpnameAktif($pdo);"
    code = code.replace(marker, marker + "\n$currentTahap = $activeSession['tahap'] ?? 'Penghitungan';", 1)
    print("Added $currentTahap.")

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(code)

print("Base variables updated.")

