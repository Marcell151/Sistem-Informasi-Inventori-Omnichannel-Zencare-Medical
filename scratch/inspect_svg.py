with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

print("SVG length:", len(text))
print("Contains sesi_opname?:", "sesi_opname" in text)
print("Contains detail_opname_batch?:", "detail_opname_batch" in text)
print("Contains fisik_baik?:", "fisik_baik" in text)
print("Contains tahap?:", "tahap" in text)

import re
# Find table blocks or text blocks for sesi_opname and detail_opname_batch
pos = text.find('detail_opname_batch')
if pos != -1:
    print("\n--- Snippet around detail_opname_batch ---")
    print(text[max(0, pos-200):min(len(text), pos+1500)])

pos_sesi = text.find('sesi_opname')
if pos_sesi != -1:
    print("\n--- Snippet around sesi_opname ---")
    print(text[max(0, pos_sesi-200):min(len(text), pos_sesi+1500)])

