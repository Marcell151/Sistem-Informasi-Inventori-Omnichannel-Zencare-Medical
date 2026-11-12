with open(r'c:\xampp\htdocs\inventory_zencare\admin\master_supplier.php', 'r', encoding='utf-8') as f:
    text_sup = f.read()

pos = text_sup.find('id="modal_tambah"')
if pos != -1:
    print('Found modal_tambah in master_supplier.php!')
    print(text_sup[pos:pos+1000])

with open(r'c:\xampp\htdocs\inventory_zencare\admin\master_user.php', 'r', encoding='utf-8') as f:
    text_usr = f.read()

pos_u = text_usr.find('id="modal_tambah_user"')
if pos_u != -1:
    print('Found modal_tambah_user in master_user.php!')
    print(text_usr[pos_u:pos_u+1000])

