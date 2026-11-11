with open('scratch/run_full_opname_simulation.php', 'r', encoding='utf-8') as f:
    text = f.read()

import re
m = re.search(r'// Skenario 4: Tensimeter Digital Omron.*?// Skenario 5: Betadine Solution', text, re.DOTALL)
if m:
    print(m.group(0))

