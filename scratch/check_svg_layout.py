import re

with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

# Find all table groups
tables = re.findall(r'<g id="(table_[a-zA-Z0-9_]+)"[^>]*>.*?<rect x="(\d+)" y="(\d+)" width="(\d+)" height="(\d+)"', text, re.DOTALL)
for t, x, y, w, h in tables:
    print(f"{t}: x={x}, y={y}, w={w}, h={h}")

# Check lines or connections connected to table_sesi_opname or table_detail_opname_batch
conn = re.findall(r'<path[^>]*d="[^"]*".*?>', text)
print(f"\nTotal paths/connectors: {len(conn)}")

