with open("scratch/rendered_stok_opname.html", "r", encoding="utf-8") as f:
    lines = f.readlines()

for idx, line in enumerate(lines):
    if "flex flex-col md:flex-row md:items-center justify-between" in line:
        start_idx = idx
        break

print(f"Header flex div starts at line {start_idx+1}")
depth = 0
for i in range(start_idx, min(start_idx + 150, len(lines))):
    l = lines[i]
    opens = l.count("<div")
    closes = l.count("</div>")
    depth += opens - closes
    print(f"L{i+1} (depth {depth}): {l.strip()[:80]}")
    if depth == 0:
        print(f"--> Header flex div CLOSED at line {i+1}")
        break

