import shutil

src = r"c:\xampp\htdocs\inventory_zencare\Demo_Bersih.sql"
dst = r"C:\xampp\backup_data\Demo_Bersih.sql"

shutil.copy2(src, dst)
print("Updated C:\\xampp\\backup_data\\Demo_Bersih.sql from clean src.")

