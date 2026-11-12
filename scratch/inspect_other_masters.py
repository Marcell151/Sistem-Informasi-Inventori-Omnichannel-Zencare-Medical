import os
import re

for fname in ['master_supplier.php', 'master_uom.php', 'master_user.php', 'pengaturan_web.php']:
    p = os.path.join(r'c:\xampp\htdocs\inventory_zencare\admin', fname)
    if os.path.exists(p):
        with open(p, 'r', encoding='utf-8') as f:
            text = f.read()
        modals = re.findall(r'<div[^>]*id="([^"]*modal[^"]*)"', text, re.IGNORECASE)
        forms = re.findall(r'<form[^>]*action="([^"]*)"', text, re.IGNORECASE)
        print(f'[{fname}] Modals: {modals}, Forms: {len(forms)}')

