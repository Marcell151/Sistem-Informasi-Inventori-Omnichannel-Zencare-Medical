with open('lain/wireframe_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

idx = text.find('Image Cross Boxes')
print(text[idx:idx+600].encode('ascii', errors='replace').decode('ascii'))
