with open('scratch/build_katalog_50.py', 'r', encoding='utf-8') as f:
    text = f.read()

pos_cats = text.find('categories =')
if pos_cats == -1:
    pos_cats = text.find('filter-btn')
print(text[pos_cats-100:pos_cats+600])

