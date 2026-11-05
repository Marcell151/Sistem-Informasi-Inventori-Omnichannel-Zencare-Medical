import subprocess
import os

# Test rendering Checkout Pembayaran via headless Chrome from wireframe_zencare.html
# Let's see how wireframe_zencare.html renders page 2 (Checkout Pembayaran)
chrome = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
html_path = os.path.abspath('lain/wireframe_zencare.html')
out_test = os.path.abspath('scratch/test_wf_screenshot.png')

cmd = [
    chrome, '--headless', '--disable-gpu',
    f'--screenshot={out_test}',
    '--window-size=1600,900',
    f'file:///{html_path}'
]
res = subprocess.run(cmd, capture_output=True, text=True)
print("Return code:", res.returncode)
print("File exists:", os.path.exists(out_test), "Size:", os.path.getsize(out_test) if os.path.exists(out_test) else 0)

