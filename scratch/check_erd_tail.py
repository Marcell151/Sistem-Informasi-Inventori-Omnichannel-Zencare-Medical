with open('scratch/generate_orthogonal_erd.py', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

lines = text.splitlines()
print("Last 60 lines of generate_orthogonal_erd.py:")
for l in lines[-60:]:
    print(l)

