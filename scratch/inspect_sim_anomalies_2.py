with open('scratch/run_full_opname_simulation.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, line in enumerate(lines[150:230]):
    print(f"{i+151}: {line}", end='')
