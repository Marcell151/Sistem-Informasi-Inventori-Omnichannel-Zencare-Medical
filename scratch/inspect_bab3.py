import os

path = r'c:\xampp\htdocs\inventory_zencare\laporan_ta\BAB_III_ZENCARE_MEDICAL.html'
with open(path, 'r', encoding='utf-8') as f:
    html = f.read()

print("File size:", len(html))
print("Occurrences of 'class=\"paper':", html.count('class="paper'))
print("Occurrences of 'id=\"full_bab3_content\"':", html.count('id="full_bab3_content"'))
print("Occurrences of 'farmasi':", html.lower().count('farmasi'))

idx_paper = html.find('id="full_bab3_content"')
print("Snippet around full_bab3_content:")
print(html[idx_paper-200:idx_paper+300])

idx_end = html.rfind('</div>')
print("Snippet near end:")
print(html[-500:])

