with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t1 = f.read()

with open(r'laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    t2 = f.read()

idx1 = t1.find('bg-white')
print("Bab 4 bg-white:\n", t1[idx1-50:idx1+250])

idx2 = t2.find('bg-white')
print("\nCombined bg-white:\n", t2[idx2-50:idx2+250])

# Check where the main bg-white container is closed in BAB_IV
# Let's count <div> and </div> in BAB_IV
div_open = len(t1.split('<div')) - 1
div_close = len(t1.split('</div>')) - 1
print(f"\nBAB_IV div count: open={div_open}, close={div_close}, diff={div_open - div_close}")

div_open_c = len(t2.split('<div')) - 1
div_close_c = len(t2.split('</div>')) - 1
print(f"Combined div count: open={div_open_c}, close={div_close_c}, diff={div_open_c - div_close_c}")

