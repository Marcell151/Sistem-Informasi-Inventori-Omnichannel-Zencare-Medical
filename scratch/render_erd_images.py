import os
import subprocess
from PIL import Image

chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
svg_path = os.path.abspath(r'lain\Gambar_3.18_Logical_Database_Model.svg')
png_path = os.path.abspath(r'lain\Gambar_3.18_Logical_Database_Model.png')
jpg_path = os.path.abspath(r'lain\Gambar_3.18_Logical_Database_Model.jpg')

# Create a clean HTML wrapper to ensure perfect high-res rendering
html_wrapper = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  html, body {{
    margin: 0;
    padding: 0;
    background: #ffffff;
    width: 2050px;
    height: 1540px;
    overflow: hidden;
  }}
  img {{
    width: 2050px;
    height: 1540px;
    display: block;
  }}
</style>
</head>
<body>
  <img src="file:///{svg_path.replace(os.sep, '/')}" />
</body>
</html>"""

wrapper_path = os.path.abspath(r'scratch\render_svg_wrapper.html')
with open(wrapper_path, 'w', encoding='utf-8') as f:
    f.write(html_wrapper)

cmd = [
    chrome, '--headless', '--disable-gpu',
    f'--screenshot={png_path}',
    '--window-size=2050,1540',
    f'file:///{wrapper_path.replace(os.sep, "/")}'
]

print("Running chrome screenshot...")
res = subprocess.run(cmd, capture_output=True, text=True)

if os.path.exists(png_path):
    print(f"[OK] PNG rendered: {png_path} ({os.path.getsize(png_path)} bytes)")
    im = Image.open(png_path).convert('RGB')
    im.save(jpg_path, 'JPEG', quality=95)
    print(f"[OK] JPG rendered: {jpg_path} ({os.path.getsize(jpg_path)} bytes)")
else:
    print("[FAIL] PNG rendering failed!")

