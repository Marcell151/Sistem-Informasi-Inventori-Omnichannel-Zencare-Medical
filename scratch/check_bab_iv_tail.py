with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print(f"Total lines: {len(lines)}")
for i in range(len(lines)-30, len(lines)):
    print(f"{i+1}: {lines[i]}", end='')

