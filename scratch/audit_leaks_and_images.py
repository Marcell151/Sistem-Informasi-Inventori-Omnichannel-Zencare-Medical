import os
import re

files_to_check = [
    r'laporan_ta\BAB_I_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_II_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_V_ZENCARE_MEDICAL.html',
    r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html',
    r'laporan_ta\LAPORAN_TA_ZENCARE_MEDICAL_FULL.html'
]

print("=== 1. AUDITING IMAGES IN ALL HTML FILES ===")

for fpath in files_to_check:
    if not os.path.exists(fpath):
        print(f"File not found: {fpath}")
        continue

    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    file_dir = os.path.dirname(os.path.abspath(fpath))

    # Find all <img> tags
    img_matches = re.findall(r'<img\s+[^>]*src=[\"\']([^\"\']+)[\"\']', text, re.IGNORECASE)
    print(f"\nChecking images in: {fpath} (Found {len(img_matches)} images)")

    broken_count = 0
    for src in img_matches:
        if src.startswith('http') or src.startswith('data:'):
            continue
        # Resolve path
        # Normalize slashes
        clean_src = src.replace('/', os.sep).replace('\\', os.sep)
        full_path = os.path.normpath(os.path.join(file_dir, clean_src))
        if not os.path.exists(full_path):
            print(f"  [BROKEN IMAGE 404] src='{src}' resolved to: {full_path}")
            broken_count += 1

    if broken_count == 0:
        print(f"  [SUCCESS] All {len(img_matches)} images exist on disk!")
    else:
        print(f"  [ERROR] {broken_count} broken images found!")

print("\n=== 2. AUDITING PAPER LAYER / DIV LEAKS ===")

for fpath in files_to_check:
    if not os.path.exists(fpath):
        continue

    with open(fpath, 'r', encoding='utf-8') as f:
        text = f.read()

    # Find where .paper starts
    paper_starts = [m.start() for m in re.finditer(r'class=[\"\'][^\"\']*\bpaper\b[^\"\']*[\"\']', text)]
    print(f"\nChecking .paper in: {fpath} (Found {len(paper_starts)} .paper containers)")

    tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)
    bottom_pos = text.rfind('<!-- Bottom Notification')

    for i, p_start in enumerate(paper_starts):
        # Find closing bracket of the div
        div_open_end = text.find('>', p_start) + 1
        pos = div_open_end
        depth = 1

        while pos < len(text):
            m = tag_re.search(text, pos)
            if not m:
                print(f"  Paper #{i+1} never closed before EOF!")
                break
            tag = m.group(0)
            pos = m.end()
            if tag.startswith('</'):
                depth -= 1
                if depth == 0:
                    dist_to_bottom = bottom_pos - pos if bottom_pos != -1 else len(text) - pos
                    print(f"  Paper #{i+1} closed at pos {pos}. Distance to bottom notification: {dist_to_bottom} chars.")
                    # Check what is between pos and bottom_pos (or next paper)
                    next_paper = paper_starts[i+1] if i + 1 < len(paper_starts) else (bottom_pos if bottom_pos != -1 else len(text))
                    leaked_content = text[pos:next_paper].strip()
                    # Filter out divider comments/tags
                    real_leaked = re.sub(r'<!--.*?-->', '', leaked_content, flags=re.DOTALL).strip()
                    real_leaked = re.sub(r'<div[^>]*class=[\"\'][^\"\']*divider[^\"\']*[\"\'][^>]*>.*?</div>', '', real_leaked, flags=re.DOTALL).strip()
                    real_leaked = re.sub(r'<div[^>]*class=[\"\'][^\"\']*scroll-mt[^\"\']*[\"\'][^>]*>', '', real_leaked).strip()
                    real_leaked = re.sub(r'</?div[^>]*>', '', real_leaked).strip()
                    if len(real_leaked) > 50:
                        print(f"  [LEAK DETECTED!] Content leaked outside paper #{i+1}: length {len(real_leaked)}")
                        print(f"    Preview: {real_leaked[:150].replace(chr(10), ' ')}...")
                    else:
                        print(f"  [OK] Paper #{i+1} wraps properly.")
                    break
            else:
                depth += 1

