import subprocess
import time

# We can test by running a quick node script with puppeteer if installed or curl
print("Testing AJAX endpoint via curl:")
res = subprocess.run(["curl", "-s", "http://localhost/inventory_zencare/inventori/stok_opname.php?ajax=get_demo_counts"], capture_output=True, text=True)
print("Curl returncode:", res.returncode)
print("Response preview:", res.stdout[:200])

