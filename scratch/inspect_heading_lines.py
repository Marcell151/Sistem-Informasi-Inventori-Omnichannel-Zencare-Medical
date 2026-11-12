with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for line_num in [203, 216, 974, 993, 1219, 1236, 1244, 1254, 1264]:
    print(f"=== LINE {line_num} ===")
    for j in range(max(0, line_num-3), min(len(lines), line_num+4)):
        print(f"{j+1}: {lines[j].strip()}")

