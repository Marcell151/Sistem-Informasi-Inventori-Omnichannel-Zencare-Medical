import re
with open('scratch/cache/32_Stok_Opname_Verifikasi_Admin.html', 'r', encoding='utf-8', errors='ignore') as f:
    t = f.read()

for m in re.finditer(r'bg-slate-900[^\"]*', t):
    s = m.start()
    print(t[max(0, s-20):min(len(t), s+60)].replace('\n', ' '))

