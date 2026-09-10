import re

# 1. admin/master_user.php
with open('c:/xampp/htdocs/inventory_zencare/admin/master_user.php', 'r', encoding='utf-8') as f:
    c = f.read()

c = re.sub(r'\$cabang\s*=\s*.*?;\n', '', c)
c = c.replace('INSERT INTO users (username,password,nama_lengkap,role,id_cabang,is_active)', 'INSERT INTO users (username,password,nama_lengkap,role,is_active)')
c = c.replace('->execute([$uname,$pass,$nama,$role,$cabang]);', '->execute([$uname,$pass,$nama,$role]);')
c = c.replace('$users    = $pdo->query("SELECT u.*, c.nama AS nama_cabang FROM users u LEFT JOIN cabang c ON u.id_cabang=c.id ORDER BY u.id ASC")->fetchAll();', '$users = $pdo->query("SELECT u.* FROM users u ORDER BY u.id ASC")->fetchAll();')
c = c.replace('$cabangList = $pdo->query("SELECT id, nama FROM cabang WHERE is_active=1")->fetchAll();', '')
c = re.sub(r'<!-- Cabang \(Staff/Admin\) -->.*?</div>', '', c, flags=re.DOTALL)
c = c.replace('<?= htmlspecialchars($u[\'nama_cabang\'] ?? \'Pusat\') ?>', 'Pusat')
c = c.replace('<?= $u[\'nama_cabang\'] ?? \'Pusat\' ?>', 'Pusat')

with open('c:/xampp/htdocs/inventory_zencare/admin/master_user.php', 'w', encoding='utf-8') as f:
    f.write(c)

# 2. ecommerce/header.php
with open('c:/xampp/htdocs/inventory_zencare/ecommerce/header.php', 'r', encoding='utf-8') as f:
    c = f.read()

c = re.sub(r'\$cabangList\s*=\s*\$pdo->query.*?;', '$cabangList = [];', c)
c = c.replace('foreach ($cabangList as $c) {', 'foreach ([] as $c) {')
c = c.replace('$activeCabangId = $_SESSION[\'cabang_ecommerce\'];', '$activeCabangId = 1;')

with open('c:/xampp/htdocs/inventory_zencare/ecommerce/header.php', 'w', encoding='utf-8') as f:
    f.write(c)
