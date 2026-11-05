import pymysql

conn = pymysql.connect(host='localhost', user='root', password='', database='inventory_zencare')
cursor = conn.cursor(pymysql.cursors.DictCursor)
cursor.execute("SELECT * FROM log_anomali_fefo")
rows = cursor.fetchall()
print(f"Total rows in log_anomali_fefo: {len(rows)}")
for r in rows:
    print(r)
conn.close()

