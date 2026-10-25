import urllib.request
import urllib.parse
import re
import ssl
import json
import os
import io
import time
import shutil
from PIL import Image

ctx = ssl._create_unverified_context()

OUTPUT_DIR = "c:/xampp/htdocs/inventory_zencare/assets/img/produk"
os.makedirs(OUTPUT_DIR, exist_ok=True)

with open("c:/xampp/htdocs/inventory_zencare/scratch/products_list.json", "r", encoding="utf-8") as f:
    products = json.load(f)

print(f"Total products to process: {len(products)}")

def search_bing_images(query, count=6):
    url = f"https://www.bing.com/images/search?q={urllib.parse.quote(query)}&form=HDRSC2&first=1"
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    })
    try:
        with urllib.request.urlopen(req, timeout=7, context=ctx) as res:
            html = res.read().decode('utf-8', errors='ignore')
            murls = re.findall(r'murl&quot;:&quot;(https?://[^&]+)&quot;', html)
            clean = []
            for u in murls:
                u_clean = u.split('?')[0] if '?' in u else u
                if any(u_clean.lower().endswith(ext) for ext in ['.jpg', '.jpeg', '.png', '.webp']) or 'tokopedia' in u or 'shopee' in u or 'halodoc' in u or 'k24klik' in u or 'static-src' in u or 'farmaku' in u or 'bukalapak' in u:
                    if u not in clean:
                        clean.append(u)
                if len(clean) >= count:
                    break
            return clean
    except Exception as e:
        return []

def download_and_format(url, target_path):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    })
    try:
        with urllib.request.urlopen(req, timeout=6, context=ctx) as res:
            data = res.read()
            if len(data) < 3000:
                return False
            img = Image.open(io.BytesIO(data))
            if img.mode != 'RGB':
                img = img.convert('RGB')
            w, h = img.size
            if w < 120 or h < 120:
                return False
            max_dim = max(w, h)
            square = Image.new('RGB', (max_dim, max_dim), (255, 255, 255))
            square.paste(img, ((max_dim - w) // 2, (max_dim - h) // 2))
            square.thumbnail((600, 600), Image.Resampling.LANCZOS)
            square.save(target_path, "JPEG", quality=88)
            return True
    except Exception as e:
        return False

sql_updates = []

for p in products:
    p_id = p['id']
    nama_p = p['nama_produk']
    nama_v = p['nama_variasi']
    kat = p['kategori']
    
    # Specific search term per product & variation
    # Ensure variations like Hufagrip Merah, Biru, Hijau, Kuning have distinct targeted keywords
    q_main = f"{nama_p} {nama_v}"
    if "Hufagrip" in nama_p:
        if "Biru" in nama_v or "Pilek" in nama_v:
            q_main = "hufagrip pilek biru sirup botol"
        elif "Merah" in nama_v or "TMP" in nama_v:
            q_main = "hufagrip tmp merah penurun panas sirup"
        elif "Hijau" in nama_v or "BP" in nama_v:
            q_main = "hufagrip bp hijau batuk pilek sirup"
        elif "Kuning" in nama_v or "Flu" in nama_v:
            q_main = "hufagrip flu batuk kuning sirup botol"
        elif "Anak" in nama_v:
            q_main = "hufagrip bp anak sirup"
    elif "Paracetamol" in nama_p:
        if "500" in nama_v:
            q_main = "paracetamol 500mg tablet strip box"
        elif "250" in nama_v:
            q_main = "paracetamol sirup 250mg botol"
        elif "125" in nama_v:
            q_main = "paracetamol drops 125mg sirup"
            
    print(f"\nProcessing ID {p_id}: {nama_p} - {nama_v} (Query: {q_main})")
    
    saved_files = []
    
    # Check existing primary file
    existing_primary = f"{OUTPUT_DIR}/var_{p_id}.jpg"
    target_1 = f"{OUTPUT_DIR}/var_{p_id}_1.jpg"
    if os.path.exists(existing_primary) and os.path.getsize(existing_primary) > 3000:
        if not os.path.exists(target_1) or os.path.getsize(target_1) < 1000:
            try:
                shutil.copyfile(existing_primary, target_1)
            except:
                pass
        saved_files.append(f"assets/img/produk/var_{p_id}_1.jpg")
        
    queries = [
        f"{q_main} kemasan asli apotek",
        f"{q_main} botol strip dus box",
        f"{q_main} komposisi aturan pakai bpom"
    ]
    
    candidate_urls = []
    for q in queries:
        urls = search_bing_images(q, count=4)
        for u in urls:
            if u not in candidate_urls:
                candidate_urls.append(u)
        if len(candidate_urls) >= 10:
            break
            
    img_idx = len(saved_files) + 1
    for u in candidate_urls:
        if img_idx > 4: # 3 to 4 images per product
            break
        target_file = f"{OUTPUT_DIR}/var_{p_id}_{img_idx}.jpg"
        if os.path.exists(target_file) and os.path.getsize(target_file) > 3000:
            saved_files.append(f"assets/img/produk/var_{p_id}_{img_idx}.jpg")
            img_idx += 1
            continue
        success = download_and_format(u, target_file)
        if success:
            saved_files.append(f"assets/img/produk/var_{p_id}_{img_idx}.jpg")
            print(f"  -> Downloaded angle {img_idx}: {target_file}")
            img_idx += 1
            time.sleep(0.15)
            
    if not saved_files:
        if os.path.exists(existing_primary):
            saved_files.append(f"assets/img/produk/var_{p_id}.jpg")
            
    gambar_str = ",".join(saved_files)
    sql_updates.append(f"UPDATE produk_variasi SET gambar = '{gambar_str}' WHERE id = {p_id};")
    print(f"  [READY] ID {p_id}: {len(saved_files)} images")

with open("c:/xampp/htdocs/inventory_zencare/scratch/update_images.sql", "w", encoding="utf-8") as f_sql:
    f_sql.write("\n".join(sql_updates))

print(f"\nWROTE {len(sql_updates)} SQL UPDATES TO scratch/update_images.sql")
