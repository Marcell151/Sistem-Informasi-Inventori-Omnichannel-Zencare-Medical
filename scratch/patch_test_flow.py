with open('scratch/test_opname_flow.php', 'r', encoding='utf-8') as f:
    text = f.read()

text = text.replace("cekSesiOpnameAktif($pdo)", "cekSesiOpnameAktif($pdo, true)")

with open('scratch/test_opname_flow.php', 'w', encoding='utf-8') as f:
    f.write(text)
print("Updated test_opname_flow.php")

