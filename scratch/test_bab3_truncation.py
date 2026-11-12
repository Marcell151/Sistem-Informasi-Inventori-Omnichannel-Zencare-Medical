with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

import re

# Find the end of Bab III
end_marker = "</div> <!-- Closes full_bab3_content paper -->"
idx_end = text.find(end_marker)
print(f"End marker pos: {idx_end}")

if idx_end != -1:
    bab3_body = text[:idx_end + len(end_marker)]
    print(f"bab3_body length: {len(bab3_body)}")
else:
    print("End marker not found!")

