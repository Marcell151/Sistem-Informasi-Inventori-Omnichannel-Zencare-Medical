with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print("Total lines:", len(lines))
for i in range(min(50, len(lines))):
    print(lines[i], end='')

