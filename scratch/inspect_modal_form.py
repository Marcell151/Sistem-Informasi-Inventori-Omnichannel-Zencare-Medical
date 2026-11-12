with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('id="modal_mutasi"')
end_pos = text.find('</form>', pos)
print(text[pos:end_pos+200])

