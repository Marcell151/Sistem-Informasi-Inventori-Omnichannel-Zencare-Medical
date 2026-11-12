import os

for f in ['scratch/generate_clean_erd.py', 'scratch/generate_orthogonal_erd.py']:
    if os.path.exists(f):
        print(f"=== {f} ===")
        with open(f, 'r', encoding='utf-8', errors='ignore') as fp:
            lines = fp.readlines()
            print(f"Total lines: {len(lines)}")
            for l in lines[:50]:
                print(l, end='')

