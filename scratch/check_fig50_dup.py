from verify_reassembly import piece_50figs
import re

matches = [m.start() for m in re.finditer(r'Gambar 4\.50', piece_50figs)]
print("Gambar 4.50 occurrences:", matches)
for pos in matches:
    print(piece_50figs[max(0, pos-100):pos+200])
    print("="*50)

