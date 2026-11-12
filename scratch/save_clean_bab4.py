from verify_reassembly import piece_top, piece_50figs, piece_uat_tables, footer
import re

cut_idx = piece_50figs.find('Gambar 4.27 Tampilan Antarmuka Halaman Pembayaran')
assert cut_idx != -1
cut_marker = '</div> <!-- Closes 4.1 -->'
end_pos = piece_50figs.rfind(cut_marker, 0, cut_idx) + len(cut_marker)

clean_piece_50figs = piece_50figs[:end_pos].strip()

assembled = piece_top + "\n\n" + clean_piece_50figs + "\n\n" + piece_uat_tables + "\n\n" + footer

with open("laporan_ta/BAB_IV_ZENCARE_MEDICAL.html", "w", encoding="utf-8") as f:
    f.write(assembled)

print("Saved clean BAB_IV_ZENCARE_MEDICAL.html successfully!")

