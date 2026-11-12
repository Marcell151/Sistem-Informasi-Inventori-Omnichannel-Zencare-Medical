from test_processor_bab_iii_iv import process_html
import re

FORBIDDEN = [
    'farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna'
]

def check_forbidden(text, name):
    violations = []
    for f in FORBIDDEN:
        # For Bab 4, check with word boundary
        if f == 'Bab 4':
            m = re.findall(r'\bBab 4\b', text, re.IGNORECASE)
        elif f == 'HPP':
            m = re.findall(r'\bHPP\b', text)
        else:
            m = re.findall(re.escape(f), text, re.IGNORECASE)
        if m:
            violations.append((f, len(m)))
    if violations:
        print(f'[{name}] VIOLATIONS FOUND:', violations)
    else:
        print(f'[{name}] 100% CLEAN! Zero forbidden words.')

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_iv = f.read()

with open(r'C:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text_iii_iv = f.read()

proc_iv = process_html(text_iv)
proc_iii_iv = process_html(text_iii_iv)

check_forbidden(proc_iv, 'BAB_IV')
check_forbidden(proc_iii_iv, 'BAB_III_DAN_IV')

