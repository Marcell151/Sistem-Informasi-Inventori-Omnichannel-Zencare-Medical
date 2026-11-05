# -*- coding: utf-8 -*-
import os
import re

files_to_check = [
    r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html",
    r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html",
    r"c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html",
]

base_dir = r"c:\xampp\htdocs\inventory_zencare"

for fpath in files_to_check:
    print(f"\n==========================================")
    print(f"Checking: {os.path.basename(fpath)}")
    print(f"==========================================")
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    print(f"File size: {len(content)} characters ({len(content.encode('utf-8'))} bytes)")
    
    # Check 'farmasi'
    farmasi_count = content.lower().count('farmasi')
    print(f"Occurrences of 'farmasi': {farmasi_count}")
    assert farmasi_count == 0, f"Found 'farmasi' in {fpath}!"
    
    # Check Bab IV figures
    if "BAB_IV" in fpath or "BAB_III_ZENCARE" in fpath or "DAN_IV" in fpath:
        for i in range(1, 28):
            fig_str = f"Gambar 4.{i} "
            if fig_str not in content:
                raise ValueError(f"Missing {fig_str} in {fpath}")
        print("All figures Gambar 4.1 through Gambar 4.27 verified!")
        
        # Check all img tags pointing to UI_UX_Asli
        img_srcs = re.findall(r'<img\s+src="([^"]+)"', content)
        ui_asli_imgs = [s for s in img_srcs if "UI_UX_Asli" in s]
        print(f"Found {len(ui_asli_imgs)} images referencing UI_UX_Asli.")
        assert len(ui_asli_imgs) == 27, f"Expected 27 UI_UX_Asli images, got {len(ui_asli_imgs)}"
        
        for src in ui_asli_imgs:
            # Clean up relative path from laporan_ta directory
            # src is like ../lain/File PPT/UI_UX_Asli/01_Login_Staf.png
            norm_path = os.path.normpath(os.path.join(os.path.dirname(fpath), src))
            if not os.path.exists(norm_path):
                raise FileNotFoundError(f"Image not found on disk: {norm_path} (from {src})")
        print("All 27 UI_UX_Asli image files physically exist on disk and resolve correctly!")

print("\n>>> ALL VERIFICATION CHECKS PASSED WITH 100% SUCCESS! <<<")

