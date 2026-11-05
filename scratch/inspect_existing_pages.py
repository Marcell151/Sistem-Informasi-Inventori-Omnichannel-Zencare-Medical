import re

# Read current wireframe_zencare.html to get styles and existing page templates
with open('lain/wireframe_zencare.html', 'r', encoding='utf-8') as f:
    orig = f.read()

# Let's inspect all page functions in orig
page_blocks = re.findall(r'\{\s*title:\s*[\'"]([^\'"]+)[\'"].*?sub:\s*[\'"]([^\'"]+)[\'"].*?role:\s*[\'"]([^\'"]+)[\'"].*?html:\s*\(\)\s*=>\s*`([^`]+)`\s*\}', orig, re.DOTALL)
print(f"Extracted {len(page_blocks)} existing page templates.")
for b in page_blocks:
    print(" -", b[0])

