import re

with open('scratch/check_user_draft.py', 'r', encoding='utf-8') as f:
    code = f.read()

# Fix regex escape
pos_text_start = code.find('text = """') + len('text = """')
pos_text_end = code.find('"""\n\nimport re')
text = code[pos_text_start:pos_text_end]

forbidden = ['farmasi', 'apotek', 'HPP', 'Akuntansi', 'Audit', 'Dosen', 'Bab 4', 'Sidang', 'Sempurna']
for w in forbidden:
    matches = re.findall(r'\b' + re.escape(w) + r'\b', text, re.IGNORECASE)
    if matches:
        print(f"FOUND FORBIDDEN WORD: {w} ({len(matches)} times)")
        for m in re.finditer(r'\b' + re.escape(w) + r'\b', text, re.IGNORECASE):
            start = max(0, m.start() - 40)
            end = min(len(text), m.end() + 40)
            print(f"   Context: ...{text[start:end]}...")

