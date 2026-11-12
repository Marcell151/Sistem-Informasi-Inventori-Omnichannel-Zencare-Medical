import re

def fix_mermaid_in_html(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    def clean_block(match):
        open_tag = match.group(1)
        code = match.group(2)
        close_tag = match.group(3)

        # 1. Fix line breaks after arrows like '--> \n Target'
        code = re.sub(r'(-->)\s*\n\s*', r'\1 ', code)
        
        # 2. Fix line breaks between style NodeName and fill:...
        code = re.sub(r'(style\s+[A-Za-z0-9_]+)\s*\n\s*(fill:)', r'\1 \2', code)

        # 3. Fix repeated node shapes like 'Kasir((Kasir)) --> UC11'
        code = re.sub(r'Kasir\(\(Kasir\)\)\s*-->\s*UC11', 'Kasir --> UC11', code)

        return f"{open_tag}{code}{close_tag}"

    new_content = re.sub(r'(<div class="mermaid[^"]*">)([\s\S]*?)(</div>)', clean_block, content)

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print(f"Fixed: {filepath}")

fix_mermaid_in_html('laporan_ta/BAB_III_ZENCARE_MEDICAL.html')
fix_mermaid_in_html('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html')

