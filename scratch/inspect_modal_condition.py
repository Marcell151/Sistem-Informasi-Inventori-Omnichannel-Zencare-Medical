with open('inventori/stok_opname.php', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('id="modal_mutasi"')
# find enclosing php if
lines = text[:pos].splitlines()
for l in lines[-40:]:
    if 'if' in l or 'else' in l or 'tahap' in l or 'status' in l:
        print(l)

