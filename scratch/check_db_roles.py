import pymysql

conn = pymysql.connect(host='localhost', user='root', password='', database='inventory_zencare')
cursor = conn.cursor()
cursor.execute("SHOW COLUMNS FROM users LIKE 'role'")
print('Role column info:', cursor.fetchall())
cursor.execute("SELECT id, username, role, is_active FROM users")
for u in cursor.fetchall():
    print('User:', u)
conn.close()

