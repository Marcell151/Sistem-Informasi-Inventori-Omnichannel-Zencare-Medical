import shutil
import os

src_dir = 'lain/File PPT/Workflow'
dst_dir = r'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784'

for f in os.listdir(src_dir):
    s = os.path.join(src_dir, f)
    d = os.path.join(dst_dir, f)
    shutil.copy2(s, d)
    print(f"Copied {f} to artifact dir")

