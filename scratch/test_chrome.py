import subprocess

chrome_path = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
cmd = [
    chrome_path,
    "--headless=new",
    "--disable-gpu",
    "--no-sandbox",
    "--virtual-time-budget=5000",
    "--dump-dom",
    "http://localhost/inventory_zencare/pos/pos.php"
]

res = subprocess.run(cmd, capture_output=True, text=True)
print("Return code:", res.returncode)
print("DOM length:", len(res.stdout))
if "panel_batch_terlewat" in res.stdout:
    print("Found panel_batch_terlewat in DOM!")

