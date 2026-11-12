with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

pos = 137052
tail = text[pos:]
print("Length of tail after pos 137052:", len(tail))
print("First 500 chars of tail:\n", tail[:500])
print("\nLast 500 chars of tail:\n", tail[-500:])

