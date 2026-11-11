import subprocess

script = """
require_once 'config/config.php';
require_once 'config/koneksi.php';

$stmt = $pdo->query("SELECT * FROM sesi_opname ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Recent sessions:\n";
foreach ($rows as $r) {
    echo "ID: {$r['id']} | Kode: {$r['kode_sesi']} | Status: {$r['status']} | Dibekukan: {$r['dibekukan_pada']}\n";
}
"""

res = subprocess.run(['php', '-r', script], capture_output=True, text=True)
print(res.stdout)
print(res.stderr)

