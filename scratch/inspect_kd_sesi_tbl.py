with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('id="sesi_opname"')
pos_table = text.find('<table>', pos)
pos_table_end = text.find('</table>', pos_table)
print("=== sesi_opname table in kamus_data_zencare.html ===")
print(text[pos_table:pos_table_end+8])

