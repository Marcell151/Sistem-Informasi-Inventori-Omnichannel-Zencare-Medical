import re

with open('scratch/apply_true_uat_system.py', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace gambar_451 with empty string
text = re.sub(r'gambar_451\s*=\s*f?""".*?"""', 'gambar_451 = ""', text, flags=re.DOTALL)
# Replace gambar_452 with empty string
text = re.sub(r'gambar_452\s*=\s*f?""".*?"""', 'gambar_452 = ""', text, flags=re.DOTALL)

# Ensure Status and Sesuai
text = text.replace('Kategori: Sangat Layak', 'Status: Sesuai Kebutuhan (93.75%)')
text = text.replace('Sangat Layak', 'Sesuai')
text = text.replace('Kategori</th>', 'Status</th>')
text = text.replace('Kategori Penerimaan</th>', 'Status</th>')

# Ensure closing of paper layer properly in BAB_IV_ZENCARE_MEDICAL.html
# Let's inspect how complete_42 is inserted
text = text.replace(
    'updated_b4 = text_b4[:idx_start_b4] + complete_42 + "\\n    " + text_b4[idx_end_b4:]',
    'updated_b4 = text_b4[:idx_start_b4] + complete_42 + "\\n    </div> <!-- Closes full_bab_iv_content (paper) -->\\n\\n    " + text_b4[idx_end_b4:]'
)

# And in BAB_III_DAN_IV_ZENCARE_MEDICAL.html
text = text.replace(
    'updated_comb = text_comb[:idx_start_comb] + complete_42 + "\\n    " + text_comb[idx_end_comb:]',
    'updated_comb = text_comb[:idx_start_comb] + complete_42 + "\\n    </div> <!-- Closes full_bab_iv_content (paper) -->\\n\\n    " + text_comb[idx_end_comb:]'
)

with open('scratch/apply_pure_table_uat.py', 'w', encoding='utf-8') as f:
    f.write(text)

print("Created scratch/apply_pure_table_uat.py successfully")

