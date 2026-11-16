import pathlib, zipfile, re, xml.etree.ElementTree as ET
from PIL import Image
import io

for p in [
    r'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
    r'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    r'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
]:
    print('BROWSER:', p, pathlib.Path(p).exists())

with zipfile.ZipFile(r'c:\xampp\htdocs\inventory_zencare\lain\PPT\Seminar Proposal TA.pptx') as z:
    for n in range(1, 23):
        s = z.read(f'ppt/slides/slide{n}.xml').decode('utf-8')
        rels = z.read(f'ppt/slides/_rels/slide{n}.xml.rels').decode('utf-8')
        rmap = {}
        rroot = ET.fromstring(rels)
        for rel in rroot:
            rmap[rel.attrib['Id']] = rel.attrib['Target']
        blips = re.findall(r'r:embed="(rId\d+)"', s)
        info = []
        for b in blips:
            tgt = rmap.get(b, '')
            mname = tgt.split('/')[-1]
            dim = ''
            if tgt.startswith('../media/'):
                mdata = z.read('ppt/' + tgt[3:])
                try:
                    im = Image.open(io.BytesIO(mdata))
                    dim = f'{im.size[0]}x{im.size[1]}'
                except Exception:
                    dim = '?'
            info.append(f'{mname}({dim})')
        print(f'Slide {n:2d}: {info}')
