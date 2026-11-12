with open('scratch/run_full_opname_simulation.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, line in enumerate(lines[45:150]):
    print(f"{i+46}: {line}", end='')
