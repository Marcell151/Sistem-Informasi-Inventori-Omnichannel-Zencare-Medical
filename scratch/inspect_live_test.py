with open('scratch/cache/test_live_opname.html', 'r', encoding='utf-8') as f:
    text = f.read()

print('Length:', len(text))
print('Has modal_mutasi:', 'id="modal_mutasi"' in text)
print('Has Tahap 2:', 'Tahap 2' in text)
print('Has OPN-20261112:', 'OPN-20261112' in text)

