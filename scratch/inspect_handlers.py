with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos_start = text.find("elseif ($aksi === 'simpan_hitung_fisik')")
pos_end = text.find("elseif ($aksi === 'rekonsiliasi_masal')")
print(text[pos_start:pos_end])

