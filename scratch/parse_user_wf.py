import re

with open('scratch/latest_user_wf.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Let's find all headers / wireframe items in user draft
# Categories: A. Login Staf, B. Kasir, C. Admin, D. Superadmin, E. Pelanggan (E-Commerce)
categories = re.findall(r'^[A-E]\.\t.*$', text, flags=re.MULTILINE)
print('Categories found:', categories)

# Let's find bullet points
bullets = re.findall(r'^[•\t]\s*(.*)$', text, flags=re.MULTILINE)
print(f'Total bullet points: {len(bullets)}')
for b in bullets:
    print(' -', b.strip())

