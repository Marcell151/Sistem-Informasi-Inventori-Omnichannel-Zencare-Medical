with open('config/layout.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

target = """    if ($isStaff) {
        echo '<p class="sidebar-section">Transaksi</p>';
        if (in_array($role, ['superadmin', 'kasir'])) {
            $link('/inventory_zencare/pos/pos.php', 'pos', 'Kasir (POS)', 'pos');
        }
        $link('/inventory_zencare/admin/pesanan.php', 'orders', 'Transaksi', 'pesanan');"""

replacement = """    if ($isStaff) {
        echo '<p class="sidebar-section">Transaksi</p>';
        if (in_array($role, ['superadmin', 'kasir'])) {
            $link('/inventory_zencare/pos/pos.php', 'pos', 'Kasir (POS)', 'pos');
        }
        $link('/inventory_zencare/admin/pesanan.php', 'orders', 'Transaksi', 'pesanan');
        if ($role === 'kasir') {
            $link('/inventory_zencare/inventori/stok_opname.php', 'opname', 'Blind Count Opname', 'stok_opname');
        }"""

if target in text:
    new_text = text.replace(target, replacement, 1)
    with open('config/layout.php', 'w', encoding='utf-8') as f:
        f.write(new_text)
    print("Successfully updated config/layout.php")
else:
    print("Target not found in config/layout.php")

