import subprocess

res = subprocess.run(['git', 'show', 'HEAD:laporan_ta/BAB_IV_ZENCARE_MEDICAL.html'], stdout=subprocess.PIPE, text=True, encoding='utf-8')
content = res.stdout
print("Length in HEAD:", len(content))
print("Contains Gambar 4.50?", "Gambar 4.50" in content)
print("Contains Gambar 4.27?", "Gambar 4.27" in content)
print("Headings in HEAD:")
import re
hs = re.findall(r'<h[1-6][^>]*>(.*?)</h[1-6]>', content, re.DOTALL)
for h in hs[:20]:
    print(" ", re.sub(r'<[^>]+>', '', h).strip()[:70])

