with open('scratch/latest_user_wf.txt', 'r', encoding='utf-8') as f:
    text = f.read()

# Replace the 2 audit occurrences
text_clean = text.replace(
    'guna menjamin keterlacakan audit persediaan barang secara transparan.',
    'guna menjamin keterlacakan evaluasi mutasi persediaan barang secara transparan.'
)
text_clean = text_clean.replace(
    'Pusat audit manajerial yang menyatukan dua modul pergerakan persediaan non-transaksional',
    'Pusat evaluasi manajerial yang menyatukan dua modul pergerakan persediaan non-transaksional'
)
text_clean = text_clean.replace('Omnichanel', 'Omnichannel')

forbidden = ['farmasi', 'apotek', 'hpp', 'akuntansi', 'audit', 'dosen', 'bab 4', 'sidang', 'sempurna']
found = {}
for w in forbidden:
    c = text_clean.lower().count(w)
    if c > 0:
        found[w] = c
print("Cleaned wireframe forbidden found:", found)

with open('scratch/cleaned_wf_data.txt', 'w', encoding='utf-8') as f:
    f.write(text_clean)
print("Saved cleaned wireframe data.")

