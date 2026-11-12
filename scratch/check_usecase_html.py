with open('lain/semua_usecase_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

pos = text.find('Tabel 3.9')
if pos == -1: pos = text.find('Deskripsi Aktor')
print("semua_usecase_zencare.html snippet:")
print(text[pos:pos+2500])

