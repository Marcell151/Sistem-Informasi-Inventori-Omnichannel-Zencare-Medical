with open(r'laporan_ta\BAB_IV_ZENCARE_MEDICAL.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Let's inspect around index 86678 where the leak happened
idx_leak = text.find('</div> <!-- Closes subbab_41_content -->')
print("Surrounding 400 chars of leak:\n", text[idx_leak-100:idx_leak+300])

