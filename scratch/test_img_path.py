import os

rel = "../lain/File PPT/Workflow/Workflow Barang Keluar (Berjalan).png"
full = os.path.normpath(os.path.join("laporan_ta", rel))
print(f"Path: {full}, Exists: {os.path.exists(full)}")
