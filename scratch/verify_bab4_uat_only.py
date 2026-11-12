import re

for path in [r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html']:
    with open(path, 'r', encoding='utf-8') as f:
        text = f.read()
    
    print(f"\n=== Verifying {path} ===")
    # Check 4.2 heading
    h_42 = re.findall(r'<h3[^>]*>.*?4\.2.*?</h3>', text, re.DOTALL)
    print("4.2 Heading:", h_42)
    
    # Check any 4.2.1 or 4.2.2 subbabs
    h_sub = re.findall(r'<h[45][^>]*>.*?4\.2\.[12].*?</h[45]>', text, re.DOTALL)
    print("4.2.x Sub-headings:", h_sub)
    
    # Check tables in Bab IV
    tbls = re.findall(r'Tabel 4\.\d+', text)
    print("Tables found:", sorted(list(set(tbls))))
    
    # Check figures in Bab IV
    figs = sorted(list(set(int(x) for x in re.findall(r'Gambar 4\.(\d+)', text))))
    print(f"Figures found: {min(figs)} to {max(figs)} (Total: {len(figs)})")
    
    # Check status values in UAT table
    berhasil_cnt = text.count('text-center font-bold text-emerald-700">BERHASIL</td>')
    diterima_cnt = text.count('text-center font-bold text-emerald-700">DITERIMA</td>')
    print(f"Status counts: BERHASIL={berhasil_cnt}, DITERIMA={diterima_cnt}")

