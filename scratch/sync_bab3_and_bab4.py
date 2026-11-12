# Read updated BAB_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    bab4_full = f.read()

# Extract the paper content from BAB_IV
start_tag = '<div id="full_bab_iv_content"'
end_tag = '<!-- Bottom Notification'
start_idx = bab4_full.find(start_tag)
end_idx = bab4_full.find(end_tag)

if start_idx == -1 or end_idx == -1:
    print(f"Error finding markers: start_idx={start_idx}, end_idx={end_idx}")
    exit(1)

bab4_paper = bab4_full[start_idx:end_idx].strip()
# Remove the wrapping <div id="full_bab_iv_content"...> and its last </div> if needed, or keep it
print(f"Extracted bab4_paper length: {len(bab4_paper)}")

# Read BAB_III_DAN_IV_ZENCARE_MEDICAL.html
with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8', errors='ignore') as f:
    combined = f.read()

# Find BAB IV start in combined
comb_bab4_start = combined.find('<div class="text-center mb-10">\n            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-wide">BAB IV</h1>')
if comb_bab4_start == -1:
    comb_bab4_start = combined.find('BAB IV')
    # search backwards for container
    comb_bab4_start = combined.rfind('<div', 0, comb_bab4_start)

comb_bab4_end = combined.find('<!-- Bottom Notification', comb_bab4_start)
if comb_bab4_end == -1:
    comb_bab4_end = combined.find('</body>', comb_bab4_start)

print(f"Combined markers: comb_bab4_start={comb_bab4_start}, comb_bab4_end={comb_bab4_end}")

# In bab4_full, find from <div class="text-center mb-10">\n            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-wide">BAB IV</h1>
b4_inner_start = bab4_full.find('<div class="text-center mb-10">\n            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-wide">BAB IV</h1>')
if b4_inner_start == -1:
    b4_inner_start = bab4_full.find('BAB IV')
    b4_inner_start = bab4_full.rfind('<div', 0, b4_inner_start)

b4_inner_content = bab4_full[b4_inner_start:end_idx].strip()
# remove trailing </div> if it closed paper
if b4_inner_content.endswith('</div>'):
    b4_inner_content = b4_inner_content[:-6].strip()

# Replace in combined
new_combined = combined[:comb_bab4_start] + b4_inner_content + "\n        </div>\n\n    " + combined[comb_bab4_end:]

with open('laporan_ta/BAB_III_DAN_IV_ZENCARE_MEDICAL.html', 'w', encoding='utf-8') as f:
    f.write(new_combined)

print("Synchronized BAB_III_DAN_IV_ZENCARE_MEDICAL.html successfully!")

