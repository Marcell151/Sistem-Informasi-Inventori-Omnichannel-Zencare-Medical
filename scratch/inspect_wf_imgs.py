from PIL import Image
import os

wf_names = [
    'Workflow Barang Keluar (Berjalan).png',
    'Workflow Barang Masuk (Berjalan).png',
    'Workflow Barang Keluar (Sistem Usulan).png',
    'Workflow Barang Masuk & Logistik (Sistem Usulan).png'
]

for name in wf_names:
    p = os.path.join('lain/File PPT/Workflow', name)
    im = Image.open(p)
    print(f"{name}: size={im.size}, mode={im.mode}")

