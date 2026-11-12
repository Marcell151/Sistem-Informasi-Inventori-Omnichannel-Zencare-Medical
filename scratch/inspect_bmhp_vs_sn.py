with open('Demo_Bersih.sql', 'r', encoding='utf-8') as f:
    sql = f.read()

import re

# Find variasi IDs for Strip Gula Darah, Alkohol, Kasa
bmhp_names = ['Strip Gula Darah', 'Alkohol', 'Kasa Steril', 'Tensimeter', 'Termometer Digital']
for name in bmhp_names:
    matches = re.findall(rf"\(\d+,\s*(\d+),\s*'[^']+',\s*'[^']*?{name}[^']*?',", sql, re.IGNORECASE)
    print(f"Product containing '{name}': variasi IDs = {matches}")

# Let's check which variasi IDs appear in unit_serial vs stok_batch
# Find all id_variasi in unit_serial
unit_serial_vars = re.findall(r"INSERT INTO `unit_serial`.*?VALUES\s*(.*?);", sql, re.DOTALL)
if unit_serial_vars:
    sn_rows = re.findall(r"\(\d+,\s*(\d+),", unit_serial_vars[0])
    print(f"Total SN rows: {len(sn_rows)}, variasi IDs in unit_serial: {set(sn_rows)}")

# Find all id_variasi in stok_batch
stok_batch_vars = re.findall(r"INSERT INTO `stok_batch`.*?VALUES\s*(.*?);", sql, re.DOTALL)
if stok_batch_vars:
    batch_rows = re.findall(r"\(\d+,\s*(\d+),", stok_batch_vars[0])
    print(f"Total batch rows: {len(batch_rows)}, variasi IDs in stok_batch: {len(set(batch_rows))}")

