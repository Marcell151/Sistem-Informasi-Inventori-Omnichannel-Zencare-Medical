with open('pos/pos.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

terms = ["panel_batch_terlewat", "drawer_batch_content", "openKasirAnomaliModal", "toggleBatchTerlewatPanel", "rincian batch", "batch anomali", "batch terlewat"]
for term in terms:
    pos = 0
    print(f"=== SEARCH: {term} ===")
    while True:
        idx = text.lower().find(term.lower(), pos)
        if idx == -1: break
        start = max(0, idx - 100)
        end = min(len(text), idx + 150)
        snippet = text[start:end].replace('\n', ' ')
        print(f"[{idx}]: {snippet}")
        pos = idx + len(term)

