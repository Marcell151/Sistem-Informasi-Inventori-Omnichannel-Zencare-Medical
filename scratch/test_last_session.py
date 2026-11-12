import subprocess

res = subprocess.check_output([r"C:\xampp\php\php.exe", "scratch/get_last_session.php"]).decode().strip()
print("Last session ID:", res)
