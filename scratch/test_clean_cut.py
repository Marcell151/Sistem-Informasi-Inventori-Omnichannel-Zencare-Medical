from verify_reassembly import piece_top, piece_50figs, piece_uat_tables, footer
import re

# Cut piece_50figs right after the first closing of 4.1
cut_idx = piece_50figs.find('Gambar 4.27 Tampilan Antarmuka Halaman Pembayaran')
assert cut_idx != -1
# find the closing tags right before that
cut_marker = '</div> <!-- Closes 4.1 -->'
end_pos = piece_50figs.rfind(cut_marker, 0, cut_idx) + len(cut_marker)

clean_piece_50figs = piece_50figs[:end_pos].strip()

# Now assemble
assembled = piece_top + "\n\n" + clean_piece_50figs + "\n\n" + piece_uat_tables + "\n\n" + footer

# Check divs depth
p_start = assembled.find('class="paper')
div_open_end = assembled.find('>', p_start) + 1
pos = div_open_end
depth = 1

tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
bottom_pos = assembled.find('<!-- Bottom Notification')

while pos < len(assembled):
    m = tag_re.search(assembled, pos)
    if not m:
        break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            print(f"Paper closed at pos {pos}!")
            dist = bottom_pos - pos
            print(f"Distance to bottom notification: {dist} chars.")
            leak = assembled[pos:bottom_pos].strip()
            print(f"Leaked text length: {len(leak)}")
            break
    else:
        depth += 1

print(f"Final depth at EOF: {depth}")

# Count figures and tables
figs = sorted(list(set(int(x) for x in re.findall(r'Gambar 4\.(\d+)', assembled))))
tbls = sorted(list(set(int(x) for x in re.findall(r'Tabel 4\.(\d+)', assembled))))
print(f"Figures: min {min(figs)}, max {max(figs)}, count {len(figs)}")
print(f"Tables: min {min(tbls)}, max {max(tbls)}, count {len(tbls)}")

