import os
import sys

# Import sections from build_complete_50_bab4
sys.path.append('scratch')
from build_complete_50_bab4 import sections

print(f"Total sections: {len(sections)}")
all_figs = []
for s in sections:
    print(f"\n--- Subbab {s['num']}: {s['title']} ---")
    for f in s['figures']:
        f_num, f_title, f_file, f_short = f
        all_figs.append((f_num, f_title, f_file, f_short, s['num'], s['title']))
        print(f"  {f_num}: {f_file} -> {f_title}")

print(f"\nTotal figures: {len(all_figs)}")

# Check for identical files
from collections import Counter
file_counts = Counter([f[2] for f in all_figs])
dups = {k: v for k, v in file_counts.items() if v > 1}
print(f"\nFile duplicates: {dups}")
