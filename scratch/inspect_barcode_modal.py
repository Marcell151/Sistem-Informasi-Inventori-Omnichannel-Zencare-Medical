with open(r'c:\xampp\htdocs\inventory_zencare\admin\master_produk.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('id="modal_cetak_barcode"')
if pos != -1:
    print('Found modal_cetak_barcode!')
    print(text[pos:pos+1500])

