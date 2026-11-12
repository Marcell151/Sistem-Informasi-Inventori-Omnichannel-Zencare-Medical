with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "r", encoding="utf-8") as f:
    text = f.read()

end_marker = "</div> <!-- Closes full_bab3_content paper -->"
idx = text.find(end_marker)

# We need one more </div>
fixed = text[:idx] + "</div>\n    " + text[idx:]

with open("laporan_ta/BAB_III_ZENCARE_MEDICAL.html", "w", encoding="utf-8") as f:
    f.write(fixed)

print("Added missing closing div in BAB_III!")

