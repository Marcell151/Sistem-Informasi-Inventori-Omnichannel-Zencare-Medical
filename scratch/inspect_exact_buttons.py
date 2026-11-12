with open('scratch/cache/11_Stok_Opname_Riwayat.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = text.find('Status Sistem: Normal')
print("In 11:")
print(text[pos-100:pos+600])

with open('scratch/cache/32_Stok_Opname_Verifikasi_Admin.html', 'r', encoding='utf-8') as f:
    text32 = f.read()

pos32 = text32.find('Jalankan Uji Coba')
print("\nIn 32:")
print(text32[pos32-200:pos32+400])

