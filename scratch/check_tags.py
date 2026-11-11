with open("inventori/stok_opname.php", "r", encoding="utf-8") as f:
    text = f.read()

import re
tags = re.findall(r"<(/?)(\w+)(?:\s+[^>]*?)?>", text)
div_stack = 0
for i, (is_close, tag) in enumerate(tags):
    if tag.lower() == "div":
        if is_close == "/":
            div_stack -= 1
        else:
            div_stack += 1

print(f"Div balance difference: {div_stack}")

