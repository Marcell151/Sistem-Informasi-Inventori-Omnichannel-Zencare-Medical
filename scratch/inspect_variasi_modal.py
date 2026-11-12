with open(r'c:\xampp\htdocs\inventory_zencare\admin\master_produk.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('id="modal_tambah_variasi"')
if pos != -1:
    print('Found modal_tambah_variasi!')
    print(text[pos:pos+2500])
else:
    print('modal_tambah_variasi not found!')

