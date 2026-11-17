import pathlib, re

sql = pathlib.Path("Demo_Bersih.sql").read_text(encoding="utf-8", errors="ignore")
tbls = re.findall(r"CREATE TABLE\s+`?(\w+)`?", sql, re.I)
fks = re.findall(r"CONSTRAINT\s+`\w+`\s+FOREIGN KEY\s+\(`([^`]+)`\)\s+REFERENCES\s+`([^`]+)`", sql, re.I)
svg = pathlib.Path("lain/Gambar_3.18_Logical_Database_Model.svg").read_text(encoding="utf-8", errors="ignore")

print("SQL TABLES (" + str(len(tbls)) + "):", tbls)
print("SQL FKs (" + str(len(fks)) + "):", fks)
print("MISSING IN SVG:", [t for t in tbls if t not in svg])
