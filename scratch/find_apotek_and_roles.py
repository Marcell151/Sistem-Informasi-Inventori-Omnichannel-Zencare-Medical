with open(r'c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    bab3_text = f.read()

import re

print("--- APOTEK IN BAB_III_ZENCARE_MEDICAL.html ---")
matches_apotek = [(m.start(), m.group()) for m in re.finditer(r'apotek', bab3_text, re.IGNORECASE)]
print(f"Total occurrences of 'apotek': {len(matches_apotek)}")
for pos, match in matches_apotek:
    snippet = bab3_text[max(0, pos-80):min(len(bab3_text), pos+100)].replace('\n', ' ')
    print(f"[{pos}]: {snippet}")

print("\n--- ACTOR / ROLE CHECKS ---")
matches_actors = [(m.start(), m.group()) for m in re.finditer(r'(tiga aktor|3 aktor|superadmin dan admin|admin dan superadmin)', bab3_text, re.IGNORECASE)]
print(f"Total occurrences of limited actors: {len(matches_actors)}")
for pos, match in matches_actors:
    snippet = bab3_text[max(0, pos-80):min(len(bab3_text), pos+100)].replace('\n', ' ')
    print(f"[{pos}]: {snippet}")

