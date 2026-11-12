import re

with open('scratch/latest_user_prompt.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Let's split by section 3.5.X
table_chunks = re.split(r'3\.5\.\d+\s+Tabel\s+', text)[1:]

for i, chunk in enumerate(table_chunks):
    lines = chunk.strip().split('\n')
    table_name = lines[0].strip()
    
    # find rows starting with a digit followed by tab
    rows = []
    for line in lines:
        m = re.match(r'^(\d+)\t([a-zA-Z0-9_]+(?:\s*\([^)]*\))?)\t', line.strip())
        if m:
            rows.append((m.group(1), m.group(2)))
    
    print(f"{i+1}. {table_name:25}: {len(rows)} columns (max row num: {rows[-1][0] if rows else 0})")

