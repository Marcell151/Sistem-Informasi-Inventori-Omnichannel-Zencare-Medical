import pymysql

conn = pymysql.connect(host="localhost", user="root", password="", database="inventory_zencare")
cur = conn.cursor()
cur.execute("UPDATE supplier SET alamat = REPLACE(alamat, 'Farmasi', 'Kesehatan')")
conn.commit()
print("Updated supplier address in DB.")

# Also update Demo_Bersih.sql
with open("database/Demo_Bersih.sql", "r", encoding="utf-8") as f:
    sql = f.read()
sql = sql.replace("Jl. Farmasi Raya", "Jl. Kesehatan Raya")
with open("database/Demo_Bersih.sql", "w", encoding="utf-8") as f:
    f.write(sql)
print("Updated Demo_Bersih.sql.")

