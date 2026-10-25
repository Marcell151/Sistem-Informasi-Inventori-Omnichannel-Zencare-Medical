import urllib.request
import urllib.parse
import re
import ssl
import json
import os
import io
import time
from PIL import Image

ctx = ssl._create_unverified_context()

def get_bing_image_urls(query, count=4):
    url = f"https://www.bing.com/images/search?q={urllib.parse.quote(query)}&form=HDRSC2&first=1"
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    })
    try:
        with urllib.request.urlopen(req, timeout=8, context=ctx) as res:
            html = res.read().decode('utf-8', errors='ignore')
            murls = re.findall(r'murl&quot;:&quot;(https?://[^&]+)&quot;', html)
            # Filter clean image extensions
            clean = []
            for u in murls:
                if any(u.lower().endswith(ext) for ext in ['.jpg', '.jpeg', '.png', '.webp']) or 'tokopedia' in u or 'shopee' in u or 'halodoc' in u or 'k24klik' in u:
                    if u not in clean:
                        clean.append(u)
                if len(clean) >= count:
                    break
            return clean
    except Exception as e:
        print(f"Error searching {query}: {e}")
        return []

print("Searching Hufagrip Biru...")
urls = get_bing_image_urls("hufagrip pilek biru sirup botol", count=4)
for i, u in enumerate(urls):
    print(f"  [{i+1}] {u}")

print("\nSearching Hufagrip Merah...")
urls_merah = get_bing_image_urls("hufagrip tmp merah sirup penurun panas", count=4)
for i, u in enumerate(urls_merah):
    print(f"  [{i+1}] {u}")
