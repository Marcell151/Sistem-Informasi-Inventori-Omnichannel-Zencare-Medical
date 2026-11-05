import os

found_any = False
for folder in ['laporan_ta', 'lain']:
    for root, dirs, files in os.walk(folder):
        for f in files:
            if f.endswith(('.html', '.md')):
                p = os.path.join(root, f)
                with open(p, 'r', encoding='utf-8', errors='ignore') as fp:
                    txt = fp.read()
                c_apotek = txt.lower().count('apotek')
                c_farmasi = txt.lower().count('farmasi')
                if c_apotek > 0 or c_farmasi > 0:
                    print(f'{p}: apotek={c_apotek}, farmasi={c_farmasi}')
                    found_any = True

if not found_any:
    print("SUCCESS: 0 occurrences of 'apotek' and 0 occurrences of 'farmasi' across ALL files in laporan_ta and lain!")

