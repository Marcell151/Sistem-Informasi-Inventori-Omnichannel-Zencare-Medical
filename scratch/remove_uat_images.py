import re

for fpath in ['laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    # Pattern for block 51
    pat51 = re.compile(r'\s*<!-- GAMBAR 4\.51 BLANGKO KOSONG INSTRUMEN UAT -->\s*<div class="my-6 border border-slate-300 rounded p-3 bg-slate-50 text-center">[\s\S]*?Gambar 4\.51[\s\S]*?</div>\s*</div>', re.MULTILINE)
    # Check if there are outer or inner divs
    # Let's locate exactly by substring
    pos51 = text.find('Gambar 4.51')
    if pos51 != -1:
        # Find start of comment or div
        comment_start = text.rfind('<!-- GAMBAR 4.51', 0, pos51)
        div_end = text.find('</div>', pos51) + 6
        # There's also the outer div
        div_end2 = text.find('</div>', div_end) + 6
        print(f"Removing 4.51 in {fpath}:", text[comment_start:div_end2][:60], "...", text[comment_start:div_end2][-40:])
        text = text[:comment_start] + '\n' + text[div_end2:]

    pos52 = text.find('Gambar 4.52')
    if pos52 != -1:
        comment_start2 = text.rfind('<!-- GAMBAR 4.52', 0, pos52)
        div_end = text.find('</div>', pos52) + 6
        div_end2 = text.find('</div>', div_end) + 6
        print(f"Removing 4.52 in {fpath}:", text[comment_start2:div_end2][:60], "...", text[comment_start2:div_end2][-40:])
        text = text[:comment_start2] + '\n' + text[div_end2:]

    with open(fpath, 'w', encoding='utf-8') as f:
        f.write(text)
    print(f"Updated {fpath}")

