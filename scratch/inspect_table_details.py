with open('lain/Gambar_3.18_Logical_Database_Model.svg', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

import re

def parse_table(tab_name):
    pattern = rf'(<!-- Table: {tab_name} -->.*?)(<!-- Table:|<g id="connections")'
    m = re.search(pattern, text, re.DOTALL)
    if m:
        block = m.group(1)
        print(f"==================== {tab_name} ====================")
        lines = block.split('\n')
        for l in lines[:40]:
            print(l)
        print("... (total lines: ", len(lines), ")")
    else:
        print(f"Table {tab_name} not found")

parse_table('sesi_opname')
parse_table('detail_opname_batch')

