with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace the extra closing div
text_fixed = text.replace('        </div> <!-- Closes subbab_41_content -->\n        </div> <!-- Closes 4.1 -->', '        </div> <!-- Closes 4.1 -->')

# Add closing div for paper before bottom notification
text_fixed = text_fixed.replace('    <!-- Bottom Notification (No Print) -->', '    </div> <!-- Closes full_bab_iv_content (paper) -->\n\n    <!-- Bottom Notification (No Print) -->')

# Now trace depth of full_bab_iv_content in text_fixed
import re
idx_start = text_fixed.find('id="full_bab_iv_content"')
pos = text_fixed.find('>', idx_start) + 1
depth = 1
tag_re = re.compile(r'</?div[^>]*>', re.IGNORECASE)

while pos < len(text_fixed):
    m = tag_re.search(text_fixed, pos)
    if not m: break
    tag = m.group(0)
    pos = m.end()
    if tag.startswith('</'):
        depth -= 1
        if depth == 0:
            print(f"Paper closes at index {pos}!")
            print("Surrounding:\n", text_fixed[pos-100:pos+100])
            break
        elif depth < 0:
            print(f"Depth went negative at {pos}!")
            break
    else:
        depth += 1

print("Bottom notif at:", text_fixed.find('<!-- Bottom Notification (No Print) -->'))
print("Depth at end:", depth)

