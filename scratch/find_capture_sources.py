import os

# Let's inspect scripts that generated 11_Stok_Opname_Riwayat.png and 44_Modal_Mutasi_Opname.png
for root, dirs, files in os.walk('scratch'):
    for f in files:
        if f.endswith('.py'):
            p = os.path.join(root, f)
            with open(p, 'r', encoding='utf-8', errors='ignore') as fl:
                c = fl.read()
                if '11_Stok_Opname_Riwayat' in c or '44_Modal_Mutasi_Opname' in c:
                    print(f"Found in {p}")

