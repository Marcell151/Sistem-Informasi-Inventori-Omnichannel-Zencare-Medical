with open('lain/wireframe_zencare.html', 'r', encoding='utf-8') as f:
    text = f.read()

print(text[-1500:].encode('ascii', errors='replace').decode('ascii'))
