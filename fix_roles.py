import os

# Fix requireRole to include admin for master data files
to_fix = [
    r'c:\xampp\htdocs\inventory_zencare\admin\master_produk.php',
    r'c:\xampp\htdocs\inventory_zencare\admin\master_supplier.php',
]

for fp in to_fix:
    with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    new_content = content.replace(
        "requireRole(['superadmin'])",
        "requireRole(['superadmin', 'admin'])",
        1
    )
    with open(fp, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print('Fixed: ' + os.path.basename(fp))
