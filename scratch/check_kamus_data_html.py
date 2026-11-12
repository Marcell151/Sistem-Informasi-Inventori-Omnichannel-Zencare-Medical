import re

with open('lain/kamus_data_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    kd = f.read()

print("kamus_data_zencare.html length:", len(kd))

# Check for sesi_opname and detail_opname_batch in kamus_data_zencare.html
print("sesi_opname in kamus_data_zencare.html:", "sesi_opname" in kd)
print("detail_opname_batch in kamus_data_zencare.html:", "detail_opname_batch" in kd)
print("fisik_baik in kamus_data_zencare.html:", "fisik_baik" in kd)
print("tahap in kamus_data_zencare.html:", "tahap" in kd)
print("ditutup_hitung_oleh in kamus_data_zencare.html:", "ditutup_hitung_oleh" in kd)

# Let's inspect sesi_opname and detail_opname_batch definitions in kamus_data_zencare.html
for term in ['sesi_opname', 'detail_opname_batch']:
    pos = kd.find(term)
    if pos != -1:
        print(f"\n--- Snippet {term} in kamus_data_zencare.html ---")
        print(kd[pos-100:pos+800])

