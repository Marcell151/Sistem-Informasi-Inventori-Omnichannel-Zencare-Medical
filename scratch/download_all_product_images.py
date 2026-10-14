import urllib.request
import urllib.parse
import re
import ssl
import json
import os
import io
import time
from PIL import Image, ImageDraw, ImageFont

ctx = ssl._create_unverified_context()

OUTPUT_DIR = "c:/xampp/htdocs/inventory_zencare/assets/img/produk"
os.makedirs(OUTPUT_DIR, exist_ok=True)

VARIATIONS = [
    {"id": 1, "induk_id": 1, "query": "amoxicillin 500mg strip tablet", "nama": "Amoxicillin 500mg", "var": "Tablet 500mg"},
    {"id": 2, "induk_id": 2, "query": "sanmol syrup 60ml botol", "nama": "Sanmol Sirup Anak 60ml", "var": "Botol 60ml"},
    {"id": 3, "induk_id": 3, "query": "strip gula darah easytouch glucose test", "nama": "Strip Gula Darah EasyTouch", "var": "Tube (25 Pcs)"},
    {"id": 4, "induk_id": 4, "query": "tensimeter digital omron hem 7120", "nama": "Tensimeter Digital Omron", "var": "Unit HEM-7120"},
    {"id": 5, "induk_id": 5, "query": "termometer digital omron mc 246", "nama": "Termometer Digital Omron", "var": "Unit MC-246"},
    {"id": 6, "induk_id": 6, "query": "hufagrip tmp merah sirup demam", "nama": "Hufagrip", "var": "Hufagrip Merah (TMP)"},
    {"id": 7, "induk_id": 6, "query": "hufagrip pilek sirup biru", "nama": "Hufagrip", "var": "Hufagrip Biru (Pilek)"},
    {"id": 8, "induk_id": 6, "query": "hufagrip bp hijau sirup batuk pilek", "nama": "Hufagrip", "var": "Hufagrip Hijau (BP)"},
    {"id": 9, "induk_id": 6, "query": "hufagrip flu batuk sirup kuning", "nama": "Hufagrip", "var": "Hufagrip Kuning (Flu Batuk)"},
    {"id": 10, "induk_id": 7, "query": "paracetamol 500mg strip tablet pbf", "nama": "Paracetamol", "var": "Tablet 500mg"},
    {"id": 11, "induk_id": 7, "query": "paracetamol 250mg sirup tablet", "nama": "Paracetamol", "var": "Tablet 250mg"},
    {"id": 12, "induk_id": 7, "query": "paracetamol drops 125mg sirup", "nama": "Paracetamol", "var": "Tablet 125mg"},
    {"id": 13, "induk_id": 8, "query": "promag tablet kunyah obat maag", "nama": "Promag Tablet", "var": "Tablet Kunyah"},
    {"id": 14, "induk_id": 9, "query": "panadol extra merah kaplet", "nama": "Panadol Extra", "var": "Kaplet Merah"},
    {"id": 15, "induk_id": 10, "query": "tolak angin cair sido muncul sachet", "nama": "Tolak Angin Cair", "var": "Sachet 15ml"},
    {"id": 16, "induk_id": 11, "query": "mylanta cair 150ml botol antasida", "nama": "Mylanta Cair 150ml", "var": "Botol 150ml"},
    {"id": 17, "induk_id": 12, "query": "insto reguler 7.5ml tetes mata", "nama": "Insto Reguler 7.5ml", "var": "Botol 7.5ml"},
    {"id": 18, "induk_id": 13, "query": "betadine solution antiseptik 15ml", "nama": "Betadine Solution 15ml", "var": "Botol 15ml"},
    {"id": 19, "induk_id": 14, "query": "counterpain krim pereda nyeri 30g", "nama": "Counterpain Krim 30g", "var": "Tube 30g"},
    {"id": 20, "induk_id": 15, "query": "neurobion forte 5000 tablet strip", "nama": "Neurobion Forte", "var": "Tablet Merah"},
    {"id": 21, "induk_id": 16, "query": "sangobion kapsul penambah darah strip", "nama": "Sangobion Kapsul", "var": "Kapsul Merah"},
    {"id": 22, "induk_id": 17, "query": "diapet kapsul obat diare strip", "nama": "Diapet Kapsul", "var": "Kapsul Isi 10"},
    {"id": 23, "induk_id": 18, "query": "entrostop tablet obat diare kalbe", "nama": "Entrostop", "var": "Tablet"},
    {"id": 24, "induk_id": 19, "query": "woods peppermint antitussive expectorant 100ml", "nama": "Woods Peppermint Syr", "var": "Botol 100ml"},
    {"id": 25, "induk_id": 20, "query": "komix herbal sachet obat batuk", "nama": "Komix Herbal", "var": "Sachet 15ml"},
    {"id": 26, "induk_id": 21, "query": "antangin jrg cair sachet masuk angin", "nama": "Antangin JRG", "var": "Sachet 15ml"},
    {"id": 27, "induk_id": 22, "query": "cdr effervescent kalsium vitamin c tube", "nama": "CDR Effervescent", "var": "Tube Isi 15"},
    {"id": 28, "induk_id": 23, "query": "redoxon double action effervescent orange tube", "nama": "Redoxon Double Action", "var": "Tube Isi 10"},
    {"id": 29, "induk_id": 24, "query": "enervon c multivitamin strip tablet", "nama": "Enervon-C Multivitamin", "var": "Strip Isi 4"},
    {"id": 30, "induk_id": 25, "query": "imboost force kaplet strip daya tahan tubuh", "nama": "Imboost Force", "var": "Strip Isi 10"},
    {"id": 31, "induk_id": 26, "query": "bodrex migra strip obat sakit kepala", "nama": "Bodrex Migra", "var": "Strip Isi 4"},
    {"id": 32, "induk_id": 27, "query": "neozep forte strip obat flu pilek", "nama": "Neozep Forte", "var": "Strip Isi 4"},
    {"id": 33, "induk_id": 28, "query": "decolgen strip obat flu dan batuk", "nama": "Decolgen", "var": "Strip Isi 4"},
    {"id": 34, "induk_id": 29, "query": "obh combi batuk flu plus menthol botol 100ml", "nama": "OBH Combi Plus", "var": "Botol 100ml"},
    {"id": 35, "induk_id": 30, "query": "salonpas koyo pereda nyeri sachet", "nama": "Salonpas Koyo", "var": "Sachet Isi 10"},
    {"id": 36, "induk_id": 31, "query": "vicks vaporub balsem 10g pot", "nama": "Vicks VapoRub 10g", "var": "Pot 10g"},
    {"id": 37, "induk_id": 32, "query": "tolak linu sido muncul herbal cair sachet", "nama": "Tolak Linu", "var": "Sachet 15ml"},
    {"id": 38, "induk_id": 33, "query": "decadryl expectorant sirup batuk 120ml", "nama": "Decadryl Expectorant", "var": "Botol 120ml"},
    {"id": 39, "induk_id": 34, "query": "polysilane strip tablet obat maag antasida", "nama": "Polysilane Kapsul", "var": "Strip Isi 10"},
    {"id": 40, "induk_id": 35, "query": "combantrin rasa jeruk sirup 10ml obat cacing", "nama": "Combantrin Jeruk 10ml", "var": "Botol 10ml"},
    {"id": 41, "induk_id": 36, "query": "cefadroxil 500mg kapsul strip antibiotik", "nama": "Cefadroxil 500mg", "var": "Strip Isi 10"},
    {"id": 42, "induk_id": 37, "query": "chlorpheniramine maleate ctm tablet strip", "nama": "CTM", "var": "Strip Isi 12"},
    {"id": 43, "induk_id": 38, "query": "cetirizine 10mg tablet strip alergi", "nama": "Cetirizine 10mg", "var": "Strip Isi 10"},
    {"id": 44, "induk_id": 39, "query": "masker sensi earloop 3 ply box 50", "nama": "Masker Sensi Earloop", "var": "Box Isi 50"},
    {"id": 45, "induk_id": 40, "query": "hansaplast kain elastis plester luka box 100", "nama": "Hansaplast Kain Elastis", "var": "Box Isi 100"},
    {"id": 46, "induk_id": 41, "query": "plester dermafix t 5x7 onemed transparan", "nama": "Plester Dermafix", "var": "Pcs 5x7cm"},
    {"id": 47, "induk_id": 42, "query": "kasa steril onemed kotak isi 10 16x16", "nama": "Kasa Steril Onemed", "var": "Kotak Isi 10"},
    {"id": 48, "induk_id": 43, "query": "alkohol onemed 70 persen 100ml botol", "nama": "Alkohol Onemed 70% 100ml", "var": "Botol 100ml"},
    {"id": 49, "induk_id": 44, "query": "rivanol 100ml botol kompres luka onemed", "nama": "Rivanol 100ml", "var": "Botol 100ml"},
    {"id": 50, "induk_id": 45, "query": "povidone iodine 1 liter onemed botol", "nama": "Povidone Iodine 1 Liter", "var": "Botol 1L"},
    {"id": 51, "induk_id": 46, "query": "strip asam urat easytouch uric acid tube 25", "nama": "Alat Cek Asam Urat EasyTouch", "var": "Tube Isi 25"},
    {"id": 52, "induk_id": 47, "query": "strip kolesterol nesco multicheck tube 10", "nama": "Alat Cek Kolesterol Nesco", "var": "Tube Isi 10"},
    {"id": 53, "induk_id": 48, "query": "kursi roda standard sella ky809", "nama": "Kursi Roda Standard Sella", "var": "Unit Standar"},
    {"id": 54, "induk_id": 49, "query": "tabung oksigen 1m3 set lengkap medis", "nama": "Tabung Oksigen 1m3", "var": "Tabung 1m3"},
    {"id": 55, "induk_id": 50, "query": "regulator oksigen medis flowmeter onemed", "nama": "Regulator Oksigen", "var": "Unit Flowmeter"},
    {"id": 56, "induk_id": 51, "query": "nebulizer omron comp air ne c104", "nama": "Nebulizer Omron", "var": "Unit NE-C104"},
    {"id": 57, "induk_id": 52, "query": "thermometer infrared gun tembak digital dahi", "nama": "Termometer Infra Merah", "var": "Unit Pistol"},
    {"id": 58, "induk_id": 53, "query": "stetoskop littmann classic iii stethoscope 3m", "nama": "Stetoskop Littmann", "var": "Unit Classic III"},
    {"id": 59, "induk_id": 56, "query": "kardus karton box farmasi kemasan obat", "nama": "Obat Uji Skenario Dosen (5 Kardus)", "var": "Karton Isi 10 Box"}
]

def search_bing(query):
    url = f"https://www.bing.com/images/search?q={urllib.parse.quote(query)}&form=HDRSC2&first=1"
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    })
    try:
        with urllib.request.urlopen(req, timeout=6, context=ctx) as res:
            html = res.read().decode('utf-8', errors='ignore')
            murls = re.findall(r'murl&quot;:&quot;(https?://[^&]+)&quot;', html)
            if murls:
                return murls[0]
            turls = re.findall(r'turl&quot;:&quot;(https?://[^&]+)&quot;', html)
            if turls:
                return turls[0]
    except Exception as e:
        pass
    return None

def download_and_process_image(url, target_path):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    })
    with urllib.request.urlopen(req, timeout=8, context=ctx) as res:
        data = res.read()
        img = Image.open(io.BytesIO(data))
        if img.mode != 'RGB':
            img = img.convert('RGB')
        # Square crop/pad with clean white background
        w, h = img.size
        max_dim = max(w, h)
        square = Image.new('RGB', (max_dim, max_dim), (255, 255, 255))
        square.paste(img, ((max_dim - w) // 2, (max_dim - h) // 2))
        square.thumbnail((500, 500), Image.Resampling.LANCZOS)
        square.save(target_path, "JPEG", quality=88)
        return True

def generate_fallback_packshot(nama, var, target_path):
    img = Image.new('RGB', (500, 500), '#f8fafc')
    draw = ImageDraw.Draw(img)
    draw.rounded_rectangle([20, 20, 480, 480], radius=24, outline='#cbd5e1', width=3)
    # Header bar
    draw.rounded_rectangle([40, 40, 460, 100], radius=12, fill='#1a75d2')
    # Icon
    draw.rounded_rectangle([200, 160, 300, 260], radius=16, fill='#e0f2fe', outline='#0284c7', width=2)
    draw.rectangle([240, 180, 260, 240], fill='#0284c7')
    draw.rectangle([220, 200, 280, 220], fill='#0284c7')

    try:
        font_lg = ImageFont.truetype("arial.ttf", 22)
        font_sm = ImageFont.truetype("arial.ttf", 16)
        font_xs = ImageFont.truetype("arial.ttf", 14)
    except:
        font_lg = font_sm = font_xs = ImageFont.load_default()

    draw.text((250, 70), "ZENCARE MEDICAL", fill='#ffffff', font=font_lg, anchor='mm')
    draw.text((250, 320), nama[:35], fill='#0f172a', font=font_lg, anchor='mm')
    draw.text((250, 355), var[:35], fill='#64748b', font=font_sm, anchor='mm')
    draw.text((250, 430), "Distributor Resmi Alkes & Farmasi", fill='#94a3b8', font=font_xs, anchor='mm')
    img.save(target_path, "JPEG", quality=88)

results = {}
for item in VARIATIONS:
    var_id = item['id']
    filename = f"var_{var_id}.jpg"
    target_path = os.path.join(OUTPUT_DIR, filename)
    print(f"[{var_id}/{len(VARIATIONS)}] Processing: {item['nama']} - {item['var']}...")
    
    success = False
    img_url = search_bing(item['query'])
    if img_url:
        try:
            success = download_and_process_image(img_url, target_path)
            print(f"  -> SUCCESS (Downloaded: {filename})")
        except Exception as e:
            print(f"  -> Download failed ({e}), trying fallback generator...")
    else:
        print("  -> Bing image not found, generating packshot...")
    
    if not success or not os.path.exists(target_path):
        generate_fallback_packshot(item['nama'], item['var'], target_path)
        print(f"  -> Generated fallback packshot: {filename}")
    
    results[var_id] = {
        "filename": filename,
        "induk_id": item['induk_id']
    }
    time.sleep(0.3)

with open("c:/xampp/htdocs/inventory_zencare/scratch/product_images_map.json", "w") as f:
    json.dump(results, f, indent=2)

print("\nALL 59 PRODUCTS PROCESSED! Mapping saved to scratch/product_images_map.json")
