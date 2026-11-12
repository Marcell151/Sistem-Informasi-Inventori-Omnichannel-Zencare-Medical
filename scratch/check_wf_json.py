with open('lain/wireframe_zencare.html', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import json
import re

m = re.search(r'const wireframes = (\[.*?\]);', text, re.DOTALL)
if m:
    wf_list = json.loads(m.group(1))
    print(f"Total wireframes loaded: {len(wf_list)}")
    for wf in wf_list:
        if wf['id'] == 'stok_opname':
            print("\n=== Stok Opname Wireframe JSON ===")
            print(json.dumps(wf, indent=2))
        if 'audit' in json.dumps(wf).lower():
            print(f"Wireframe {wf['fig']} ({wf['id']}) contains 'audit'!")

