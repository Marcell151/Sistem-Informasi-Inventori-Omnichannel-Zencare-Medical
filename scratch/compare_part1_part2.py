with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

part1 = text[:137102]
part2 = text[137102:]

print("--- PART 1 (0 to 137,102) ---")
print("Length:", len(part1))
print("Contains '4.1.1.1'?", '4.1.1.1' in part1)
print("Contains '4.2 PENGUJIAN'?", '4.2 PENGUJIAN' in part1)
print("Contains 'Tabel 4.10'?", 'Tabel 4.10' in part1)
print("Contains 'Gambar 4.12'?", 'Gambar 4.12' in part1)
print("Contains 'Gambar 4.50'?", 'Gambar 4.50' in part1)

print("\n--- PART 2 (137,102 to EOF) ---")
print("Length:", len(part2))
print("Contains '4.1.1.1'?", '4.1.1.1' in part2)
print("Contains '4.2 PENGUJIAN'?", '4.2 PENGUJIAN' in part2)
print("Contains 'Tabel 4.10'?", 'Tabel 4.10' in part2)
print("Contains 'Gambar 4.12'?", 'Gambar 4.12' in part2)
print("Contains 'Gambar 4.50'?", 'Gambar 4.50' in part2)
print("Contains '4.3 PEMBAHASAN'?", '4.3 PEMBAHASAN' in part2)
print("Contains 'Black-Box'?", 'Black-Box' in part2)

