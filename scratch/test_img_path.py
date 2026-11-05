import os

base_dir = os.path.abspath('laporan_ta')
sample_rel = '../lain/File PPT/Wireframe/Checkout Pembayaran.png'
resolved = os.path.abspath(os.path.join(base_dir, sample_rel))
print("Resolved path:", resolved)
print("File exists?", os.path.exists(resolved))

