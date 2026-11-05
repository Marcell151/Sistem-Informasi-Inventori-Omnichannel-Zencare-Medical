import os
import shutil

root_dir = r"c:\xampp\htdocs\inventory_zencare"
target_dir = r"c:\xampp\htdocs\inventory_zencare\scratch"

files_to_move = [
    "build_full_subbab_3_4.py",
    "build_subbab_3_5.py",
    "generate_clean_erd.py",
    "generate_kamus_data_html.py",
    "generate_orthogonal_erd.py",
    "inject_clean_paper_subbab_3_4_5.py",
    "inject_subbab_3_4.py",
    "view_capture.php",
    "DOKUMENTASI_SRS_LENGKAP_ZENCARE_MEDICAL.md"
]

print("--- MOVING SCRIPTS TO scratch/ ---")
for fname in files_to_move:
    src = os.path.join(root_dir, fname)
    dst = os.path.join(target_dir, fname)
    if os.path.exists(src):
        # If dst exists, overwrite
        if os.path.exists(dst):
            os.remove(dst)
        shutil.move(src, dst)
        print(f"Moved: {fname} -> scratch/{fname}")
    else:
        print(f"Not found: {fname}")

# Remove root __pycache__ if exists
pycache_dir = os.path.join(root_dir, "__pycache__")
if os.path.exists(pycache_dir):
    shutil.rmtree(pycache_dir)
    print("Removed: root __pycache__/")

print("\n--- ROOT DIRECTORY LISTING AFTER CLEANUP ---")
for item in sorted(os.listdir(root_dir)):
    p = os.path.join(root_dir, item)
    if os.path.isdir(p):
        print(f"  [DIR]  {item}/")
    else:
        size = os.path.getsize(p)
        print(f"  [FILE] {item:<30} ({size:,} bytes)")

