with open('scratch/run_full_opname_simulation.php', 'r', encoding='utf-8') as f:
    text = f.read()

target = "$pdo->query(\"INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, alasan_sn) VALUES ({$rHilangA['id']}, {$targetSn['id']}, 'Hilang')\");"
replacement = "$pdo->query(\"INSERT INTO detail_opname_sn (id_detail_opname, id_unit_serial, serial_number, jenis_tindakan) VALUES ({$rHilangA['id']}, {$targetSn['id']}, '{$targetSn['serial_number']}', 'Kurang_Hilang')\");"

assert target in text, "target not found!"
text = text.replace(target, replacement, 1)

with open('scratch/run_full_opname_simulation.php', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated simulation script!")

