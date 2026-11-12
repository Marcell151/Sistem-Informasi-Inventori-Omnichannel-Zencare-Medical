with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find("if ($aksi === 'reset_database_demo')")
print(text[pos:pos+1500])

