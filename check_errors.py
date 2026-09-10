import os
import subprocess
import sys

root = r'c:\xampp\htdocs\inventory_zencare'
php = r'c:\xampp\php\php.exe'
errors = []

for dirpath, dirnames, filenames in os.walk(root):
    # Skip konsep and node_modules folders
    dirnames[:] = [d for d in dirnames if d not in ('konsep', 'node_modules', '.git')]
    for fn in filenames:
        if fn.endswith('.php'):
            fp = os.path.join(dirpath, fn)
            result = subprocess.run([php, '-l', fp], capture_output=True, text=True)
            if result.returncode != 0:
                errors.append(fp + ': ' + result.stderr.strip())

if errors:
    print("=== SYNTAX ERRORS ===")
    for e in errors:
        print(e)
else:
    print("No syntax errors found in any PHP file.")
