import os
import shutil

backup_dir = r"C:\xampp\backup_data"
os.makedirs(backup_dir, exist_ok=True)

src_sql = r"C:\xampp\htdocs\inventory_zencare\Demo_Bersih.sql"
dst_sql = os.path.join(backup_dir, "Demo_Bersih.sql")

shutil.copy2(src_sql, dst_sql)
print("Copied Demo_Bersih.sql to:", dst_sql)
print("File size:", os.path.getsize(dst_sql), "bytes")

