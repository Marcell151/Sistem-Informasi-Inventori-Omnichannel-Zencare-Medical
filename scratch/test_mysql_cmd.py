import subprocess
import os

mysql_path = r"C:\xampp\mysql\bin\mysql.exe"
clean_dump = r"C:\xampp\backup_data\Demo_Bersih.sql"

print("mysql_path exists:", os.path.exists(mysql_path))
print("clean_dump exists:", os.path.exists(clean_dump))

# Test cmd with stdin
with open(clean_dump, 'r', encoding='utf-8', errors='ignore') as f:
    res = subprocess.run([mysql_path, "-u", "root", "inventory_zencare"], stdin=f, capture_output=True, text=True)

print("Direct stdin returncode:", res.returncode)
print("Direct stdin stdout:", res.stdout[:200])
print("Direct stdin stderr:", res.stderr[:200])

