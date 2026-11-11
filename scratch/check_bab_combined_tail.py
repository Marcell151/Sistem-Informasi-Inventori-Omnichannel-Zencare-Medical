with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()

print(f"Total lines in BAB_III_DAN_IV: {len(lines)}")
for i in range(len(lines)-25, len(lines)):
    print(f"{i+1}: {lines[i]}", end='')

