import re
from pathlib import Path

svg = Path(r"lain\Gambar_3.18_Logical_Database_Model.svg").read_text(encoding="utf-8")
tables = re.findall(r'<g id="table_([^"]+)"[^>]*>\s*<rect x="([^"]+)" y="([^"]+)" width="([^"]+)" height="([^"]+)"', svg)
for name, x, y, w, h in tables:
    x, y, w, h = float(x), float(y), float(w), float(h)
    print(f"{name:24s} x={x:6.0f}..{x+w:6.0f}  y={y:6.0f}..{y+h:6.0f}  (w={w:.0f}, h={h:.0f})")
