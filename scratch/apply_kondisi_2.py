# apply_kondisi_2.py

with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    content = f.read()

m1 = '<!-- KONDISI 2: SESI OPNAME AKTIF (FREEZE & PENGHITUNGAN BERJALAN)       -->'
m2 = '<!-- Modal Eksekusi Mutasi Selisih (Obat vs Alkes & Multi-Alasan Defisit) -->'

p1 = content.find(m1)
p2 = content.find(m2)

if p1 == -1 or p2 == -1:
    print(f"ERROR: Markers not found! p1={p1}, p2={p2}")
    exit(1)

with open('scratch/kondisi_2_template.php', 'r', encoding='utf-8') as f:
    new_kondisi = f.read()

# Note: m1 is above '<?php else: ?>' in content, let's check
# Let's find '<?php else: ?>' immediately after m1
pos_else = content.find('<?php else: ?>', p1)
if pos_else == -1 or pos_else > p2:
    print("ERROR: else marker not found between p1 and p2")
    exit(1)

new_content = content[:pos_else] + new_kondisi + '\n\n' + content[p2:]

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(new_content)

print("Successfully replaced KONDISI 2 in stok_opname.php!")

